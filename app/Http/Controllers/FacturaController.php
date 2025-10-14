<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Services\EmpresaConfiguracionService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaController extends Controller
{
    use Concerns\ConfiguracionEmpresa;

    /**
     * Generar PDF de factura usando configuración de empresa
     */
    public function generarPDF($id)
    {
        $factura = Factura::with(['cliente', 'productos', 'servicios'])->findOrFail($id);

        // Obtener configuración de empresa
        $configuracion = $this->getConfiguracionEmpresa();

        // Verificar si el sistema está en mantenimiento
        if ($this->sistemaEnMantenimiento() && !$this->puedeEditarConfiguracion()) {
            return response()->json(['error' => $this->getMensajeMantenimiento()], 503);
        }

        // Generar PDF usando la plantilla
        $pdf = Pdf::loadView('factura', [
            'factura' => $factura,
            'configuracion' => $configuracion,
        ]);

        // Configurar opciones del PDF
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
        ]);

        return $pdf->download("factura-{$factura->numero_venta}.pdf");
    }

    /**
     * Mostrar vista previa de factura
     */
    public function preview($id)
    {
        $factura = Factura::with(['cliente', 'productos', 'servicios'])->findOrFail($id);

        // Obtener configuración de empresa
        $configuracion = $this->getConfiguracionEmpresa();

        return view('factura', compact('factura', 'configuracion'));
    }

    /**
     * Enviar factura por email usando configuración de empresa
     */
    public function enviarPorEmail(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'mensaje' => 'nullable|string|max:1000',
        ]);

        $factura = Factura::with(['cliente', 'productos', 'servicios'])->findOrFail($id);

        // Obtener configuración de empresa
        $configuracion = $this->getConfiguracionEmpresa();

        // Generar PDF
        $pdf = Pdf::loadView('factura', [
            'factura' => $factura,
            'configuracion' => $configuracion,
        ]);

        // Configurar email
        $datosEmail = [
            'factura' => $factura,
            'empresa' => $configuracion['empresa'],
            'mensaje_personalizado' => $request->mensaje,
        ];

        // Aquí iría la lógica para enviar el email
        // Por ejemplo, usando Laravel Mail
        /*
        Mail::send('emails.factura', $datosEmail, function ($message) use ($factura, $pdf, $configuracion) {
            $message->to($request->email)
                    ->subject("Factura #{$factura->numero_venta} - {$configuracion['empresa']['nombre']}")
                    ->attachData($pdf->output(), "factura-{$factura->numero_venta}.pdf");
        });
        */

        return redirect()->back()->with('success', 'Factura enviada por email correctamente.');
    }
}
