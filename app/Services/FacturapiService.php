<?php
// app/Services/FacturapiService.php
namespace App\Services;

use Facturapi\Facturapi;
use Throwable;

class FacturapiService
{
    private Facturapi $client;

    public function __construct()
    {
        $mode = config('facturapi.mode');
        $key  = config("facturapi.keys.$mode");
        $this->client = new Facturapi($key);
    }

    public function upsertCustomer(array $data): string
    {
        // Puedes intentar buscar por tax_id antes de crear para "upsert real".
        try {
            $customer = $this->client->Customers->create([
                "legal_name" => $data['legal_name'],
                "tax_id"     => $data['tax_id'],
                "tax_system" => $data['tax_system'],
                "email"      => $data['email'] ?? null,
                "address"    => ["zip" => $data['zip']],
                "default_invoice_use" => $data['default_use'] ?? null,
            ]);
            return $customer->id;
        } catch (Throwable $e) {
            throw new \RuntimeException("Facturapi customer error: ".$e->getMessage(), 0, $e);
        }
    }

    public function createIncomeInvoice(array $payload)
    {
        try {
            return $this->client->Invoices->create([
                "customer"      => $payload['customer'], // id o objeto
                "items"         => $payload['items'],
                "use"           => $payload['use'] ?? null,
                "payment_form"  => $payload['payment_form'] ?? null,
            ]);
        } catch (Throwable $e) {
            throw new \RuntimeException("Facturapi invoice error: ".$e->getMessage(), 0, $e);
        }
    }

    public function sendByEmail(string $invoiceId): void
    {
        $this->client->Invoices->send_by_email($invoiceId);
    }

    public function downloadPdf(string $invoiceId)
    {
        return $this->client->Invoices->download_pdf($invoiceId);
    }

    public function downloadXml(string $invoiceId)
    {
        return $this->client->Invoices->download_xml($invoiceId);
    }
}

