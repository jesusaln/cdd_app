<?php

namespace App\Services;

use App\Models\Cfdi;
use App\Models\CfdiConcepto;
use App\Models\Cliente;
use Facturapi\Facturapi;
use Illuminate\Support\Facades\Log;
use Throwable;

class FacturapiService
{
    private Facturapi $client;

    public function __construct()
    {
        $mode = config('facturapi.mode', 'sandbox');
        $key = config("facturapi.keys.$mode");

        if (!$key) {
            throw new \RuntimeException("Facturapi API key not configured for mode: {$mode}");
        }

        $this->client = new Facturapi($key);
    }

    /**
     * Crear o actualizar cliente en Facturapi con soporte CFDI 4.0
     */
    public function upsertCustomer(array $data): string
    {
        try {
            // Preparar datos del cliente para CFDI 4.0
            $customerData = [
                "legal_name" => $data['legal_name'],
                "tax_id" => $data['tax_id'],
                "tax_system" => $data['tax_system'],
                "email" => $data['email'] ?? null,
                "phone" => $data['phone'] ?? null,
            ];

            // Dirección básica
            if (!empty($data['zip'])) {
                $customerData["address"] = [
                    "zip" => $data['zip'],
                    "country" => "MEX", // México por defecto
                ];
            }

            // Uso CFDI por defecto
            if (!empty($data['default_use'])) {
                $customerData["default_invoice_use"] = $data['default_use'];
            }

            // Campos adicionales para CFDI 4.0
            if (!empty($data['domicilio_fiscal_cp'])) {
                $customerData["fiscal_address"] = [
                    "zip" => $data['domicilio_fiscal_cp']
                ];
            }

            // Para extranjeros
            if (!empty($data['residencia_fiscal'])) {
                $customerData["foreign_fiscal_residence"] = $data['residencia_fiscal'];
            }

            if (!empty($data['num_reg_id_trib'])) {
                $customerData["foreign_tax_id"] = $data['num_reg_id_trib'];
            }

            $customer = $this->client->Customers->create($customerData);

            Log::info("Cliente creado/actualizado en Facturapi", [
                'customer_id' => $customer->id,
                'tax_id' => $data['tax_id']
            ]);

            return $customer->id;

        } catch (Throwable $e) {
            Log::error("Error al crear cliente en Facturapi", [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            throw new \RuntimeException("Error al sincronizar cliente con Facturapi: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Crear factura de ingreso con soporte CFDI 4.0
     */
    public function createIncomeInvoice(array $payload): object
    {
        try {
            // Validar payload básico
            $this->validarPayloadFactura($payload);

            // Preparar datos para CFDI 4.0
            $invoiceData = [
                "customer" => $payload['customer'],
                "items" => $this->prepararConceptos($payload['items']),
                "use" => $payload['use'] ?? null,
                "payment_form" => $payload['payment_form'] ?? null,
                "payment_method" => $payload['payment_method'] ?? null,
                "currency" => $payload['currency'] ?? 'MXN',
                "exchange_rate" => $payload['exchange_rate'] ?? null,
            ];

            // Campos adicionales CFDI 4.0
            if (!empty($payload['conditions'])) {
                $invoiceData["payment_conditions"] = $payload['conditions'];
            }

            if (!empty($payload['global_discount'])) {
                $invoiceData["global_discount"] = $payload['global_discount'];
            }

            $invoice = $this->client->Invoices->create($invoiceData);

            Log::info("Factura creada en Facturapi", [
                'invoice_id' => $invoice->id,
                'uuid' => $invoice->uuid ?? null,
                'total' => $invoice->total ?? null
            ]);

            return $invoice;

        } catch (Throwable $e) {
            Log::error("Error al crear factura en Facturapi", [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            throw new \RuntimeException("Error al crear factura: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Crear CFDI desde modelo Cfdi local
     */
    public function timbrarCfdi(Cfdi $cfdi): object
    {
        try {
            // Preparar datos del cliente
            $cliente = $cfdi->cliente;
            $customerData = [
                'legal_name' => $cliente->nombre_razon_social,
                'tax_id' => $cliente->rfc,
                'tax_system' => $cliente->regimen_fiscal,
                'email' => $cliente->email,
                'zip' => $cliente->domicilio_fiscal_cp,
                'default_use' => $cliente->uso_cfdi,
            ];

            if ($cliente->es_extranjero) {
                $customerData['residencia_fiscal'] = $cliente->residencia_fiscal;
                $customerData['num_reg_id_trib'] = $cliente->num_reg_id_trib;
            }

            // Crear/actualizar cliente
            $customerId = $this->upsertCustomer($customerData);

            // Preparar conceptos
            $items = $cfdi->conceptos->map(function (CfdiConcepto $concepto) {
                return [
                    "quantity" => $concepto->cantidad,
                    "product" => [
                        "description" => $concepto->descripcion,
                        "product_key" => $concepto->clave_prod_serv,
                        "unit_key" => $concepto->clave_unidad,
                        "price" => $concepto->valor_unitario,
                        "tax_included" => false, // Asumimos precios sin IVA
                    ],
                    "taxes" => $this->prepararImpuestos($concepto->impuestos),
                ];
            })->toArray();

            // Crear factura
            $invoiceData = [
                'customer' => $customerId,
                'items' => $items,
                'use' => $cfdi->uso_cfdi,
                'payment_form' => $cfdi->forma_pago,
                'payment_method' => $cfdi->metodo_pago,
                'currency' => $cfdi->moneda,
                'exchange_rate' => $cfdi->tipo_cambio,
            ];

            $invoice = $this->createIncomeInvoice($invoiceData);

            // Actualizar CFDI local con datos del timbrado
            $cfdi->marcarComoTimbrado([
                'uuid' => $invoice->uuid,
                'fecha_timbrado' => $invoice->created_at,
                'no_certificado_sat' => $invoice->certificate_number ?? null,
                'no_certificado_cfdi' => $invoice->cfdi_certificate_number ?? null,
                'sello_sat' => $invoice->sat_signature ?? null,
                'sello_cfdi' => $invoice->cfdi_signature ?? null,
                'cadena_original' => $invoice->original_string ?? null,
                'xml_url' => $invoice->xml_url ?? null,
                'pdf_url' => $invoice->pdf_url ?? null,
            ]);

            return $invoice;

        } catch (Throwable $e) {
            Log::error("Error al timbrar CFDI", [
                'cfdi_id' => $cfdi->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Cancelar CFDI
     */
    public function cancelarCfdi(string $invoiceId, string $motivo = '02'): object
    {
        try {
            $result = $this->client->Invoices->cancel($invoiceId, [
                'motive' => $motivo
            ]);

            Log::info("CFDI cancelado en Facturapi", [
                'invoice_id' => $invoiceId,
                'cancellation_status' => $result->cancellation_status ?? null
            ]);

            return $result;

        } catch (Throwable $e) {
            Log::error("Error al cancelar CFDI", [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            throw new \RuntimeException("Error al cancelar CFDI: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Consultar estatus de CFDI
     */
    public function consultarEstatusCfdi(string $invoiceId): object
    {
        try {
            return $this->client->Invoices->retrieve($invoiceId);
        } catch (Throwable $e) {
            Log::error("Error al consultar estatus de CFDI", [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            throw new \RuntimeException("Error al consultar CFDI: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Enviar CFDI por email
     */
    public function sendByEmail(string $invoiceId, array $emails = []): void
    {
        try {
            $this->client->Invoices->send_by_email($invoiceId, [
                'emails' => $emails
            ]);

            Log::info("CFDI enviado por email", ['invoice_id' => $invoiceId]);

        } catch (Throwable $e) {
            Log::error("Error al enviar CFDI por email", [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            throw new \RuntimeException("Error al enviar CFDI por email: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Descargar PDF del CFDI
     */
    public function downloadPdf(string $invoiceId)
    {
        try {
            return $this->client->Invoices->download_pdf($invoiceId);
        } catch (Throwable $e) {
            Log::error("Error al descargar PDF", [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            throw new \RuntimeException("Error al descargar PDF: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Descargar XML del CFDI
     */
    public function downloadXml(string $invoiceId)
    {
        try {
            return $this->client->Invoices->download_xml($invoiceId);
        } catch (Throwable $e) {
            Log::error("Error al descargar XML", [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            throw new \RuntimeException("Error al descargar XML: " . $e->getMessage(), 0, $e);
        }
    }

    // ------------------------------------------------------------------
    // Métodos auxiliares privados
    // ------------------------------------------------------------------

    private function validarPayloadFactura(array $payload): void
    {
        if (empty($payload['customer'])) {
            throw new \InvalidArgumentException('Customer ID es obligatorio');
        }

        if (empty($payload['items']) || !is_array($payload['items'])) {
            throw new \InvalidArgumentException('Items son obligatorios');
        }

        if (count($payload['items']) === 0) {
            throw new \InvalidArgumentException('Debe incluir al menos un item');
        }
    }

    private function prepararConceptos(array $items): array
    {
        return array_map(function ($item) {
            $concepto = [
                "quantity" => (float) ($item['quantity'] ?? $item['cantidad']),
                "product" => [
                    "description" => $item['description'] ?? $item['descripcion'],
                    "product_key" => $item['product_key'] ?? $item['clave_prod_serv'],
                    "unit_key" => $item['unit_key'] ?? $item['clave_unidad'] ?? 'H87',
                    "price" => (float) ($item['price'] ?? $item['precio']),
                    "tax_included" => $item['tax_included'] ?? false,
                ]
            ];

            // Agregar impuestos si existen
            if (!empty($item['taxes'])) {
                $concepto["taxes"] = $item['taxes'];
            }

            return $concepto;
        }, $items);
    }

    private function prepararImpuestos(?array $impuestos): array
    {
        if (empty($impuestos)) {
            return [];
        }

        $taxes = [];

        // Impuestos trasladados
        if (!empty($impuestos['traslados'])) {
            foreach ($impuestos['traslados'] as $traslado) {
                $taxes[] = [
                    "type" => $traslado['tipo'] ?? 'IVA',
                    "rate" => (float) ($traslado['tasa'] ?? 0.16),
                ];
            }
        }

        return $taxes;
    }
}

