<?php
// app/Http/Controllers/FacturacionController.php
namespace App\Http\Controllers;

use App\Http\Requests\TimbrarFacturaRequest;
use App\Models\Cliente;
use App\Models\Factura; // crea si no la tienes
use App\Services\FacturapiService;
use Illuminate\Http\Request;

class FacturacionController extends Controller
{
    public function syncCliente(FacturapiService $fx, Cliente $cliente)
    {
        $id = $fx->upsertCustomer([
            'legal_name'  => $cliente->nombre_razon_social,
            'tax_id'      => $cliente->rfc,
            'tax_system'  => $cliente->regimen_fiscal,  // 601, 603, etc.
            'email'       => $cliente->email,
            'zip'         => $cliente->codigo_postal,
            'default_use' => $cliente->cfdi_default_use ?: $cliente->uso_cfdi,
        ]);

        $cliente->update(['facturapi_customer_id' => $id]);

        return back()->with('ok', 'Cliente sincronizado/validado con Facturapi');
    }

    public function timbrar(TimbrarFacturaRequest $request, FacturapiService $fx)
    {
        $cliente = Cliente::findOrFail($request->cliente_id);

        if (!$cliente->facturapi_customer_id) {
            return back()->withErrors(['cliente_id' => 'Sincroniza primero el cliente con Facturapi.']);
        }

        $items = collect($request->items)->map(function ($it) {
            return [
                "quantity" => (float)$it['cantidad'],
                "product"  => [
                    "description"  => $it['descripcion'],
                    "product_key"  => $it['sat_clave_prodserv'],
                    "unit_key"     => $it['sat_clave_unidad'] ?? 'H87',
                    "price"        => (float)$it['precio'],
                    "tax_included" => $it['precio_con_iva'] ?? true,
                    "taxes"        => [
                        ["type" => "IVA", "rate" => (float)($it['iva_tasa'] ?? 0.16)]
                    ],
                ],
            ];
        })->values()->all();

        $invoice = $fx->createIncomeInvoice([
            "customer"     => $cliente->facturapi_customer_id,
            "items"        => $items,
            "use"          => $request->use ?? $cliente->cfdi_use_effective,       // accessor del modelo
            "payment_form" => $request->payment_form ?? $cliente->payment_form_effective,
        ]);

        // Guarda local (si aún no tienes la tabla, ver sección 7)
        $fac = Factura::create([
            'cliente_id'           => $cliente->id,
            'facturapi_invoice_id' => $invoice->id,
            'uuid'                 => $invoice->uuid ?? null,
            'serie'                => $invoice->series ?? null,
            'folio'                => $invoice->folio_number ?? null,
            'estatus'              => $invoice->status ?? 'valid',
            'total'                => $invoice->total ?? 0,
            'pdf_url'              => $invoice->pdf_url ?? null,
            'xml_url'              => $invoice->xml_url ?? null,
        ]);

        // Opcional: enviar por correo
        // $fx->sendByEmail($invoice->id);

        return redirect()->route('facturas.show', $fac)->with('ok', 'Factura timbrada');
    }
}
