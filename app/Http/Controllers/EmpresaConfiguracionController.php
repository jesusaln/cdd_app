<?php

namespace App\Http\Controllers;

use App\Models\EmpresaConfiguracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Services\CfdiValidationService;

class EmpresaConfiguracionController extends Controller
{
    /**
     * Mostrar la configuración de la empresa
     */
    public function index()
    {
        $configuracion = EmpresaConfiguracion::getConfig();

        return Inertia::render('EmpresaConfiguracion/Index', [
            'configuracion' => $configuracion,
        ]);
    }

    /**
     * Actualizar la configuración de la empresa
     */
    public function update(Request $request)
    {
        // Si es un PUT request desde Inertia, convertir a POST
        if ($request->isMethod('put')) {
            $request = Request::createFrom($request);
            $request->setMethod('POST');
        }

        $validator = Validator::make($request->all(), [
            'nombre_empresa' => 'required|string|max:255',
            'rfc' => 'nullable|string|min:12|max:13',
            'razon_social' => 'nullable|string|max:255',
            'calle' => 'nullable|string|max:255',
            'numero_exterior' => 'nullable|string|max:50',
            'numero_interior' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'sitio_web' => 'nullable|url|max:255',
            'codigo_postal' => 'nullable|string|max:10',
            'colonia' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
            'descripcion_empresa' => 'nullable|string|max:1000',
            'color_principal' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'color_secundario' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'pie_pagina_facturas' => 'nullable|string|max:1000',
            'pie_pagina_cotizaciones' => 'nullable|string|max:1000',
            'terminos_condiciones' => 'nullable|string|max:2000',
            'politica_privacidad' => 'nullable|string|max:2000',
            'iva_porcentaje' => 'nullable|numeric|min:0|max:100',
            'moneda' => 'nullable|string|size:3',
            'formato_numeros' => 'nullable|string|max:10',
            'mantenimiento' => 'nullable|boolean',
            'mensaje_mantenimiento' => 'nullable|string|max:500',
            'registro_usuarios' => 'nullable|boolean',
            'notificaciones_email' => 'nullable|boolean',
            'formato_fecha' => 'nullable|string|max:20',
            'formato_hora' => 'nullable|string|max:20',
            'backup_automatico' => 'nullable|boolean',
            'frecuencia_backup' => 'nullable|integer|min:1|max:365',
            'retencion_backups' => 'nullable|integer|min:1|max:365',
            'intentos_login' => 'nullable|integer|min:1|max:20',
            'tiempo_bloqueo' => 'nullable|integer|min:1|max:1440',
            'requerir_2fa' => 'nullable|boolean',
            // Datos bancarios
            'banco' => 'nullable|string|max:255',
            'sucursal' => 'nullable|string|max:50',
            'cuenta' => 'nullable|string|max:50',
            'clabe' => 'nullable|string|size:18|regex:/^[0-9]{18}$/',
            'titular' => 'nullable|string|max:255',
            'numero_cuenta' => 'nullable|string|max:50',
            'numero_tarjeta' => 'nullable|string|max:19|regex:/^[0-9\s\-]{13,19}$/',
            'nombre_titular' => 'nullable|string|max:255',
            'informacion_adicional_bancaria' => 'nullable|string|max:1000',
            // Configuración de correo
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|in:tls,ssl',
            'email_from_address' => 'nullable|email|max:255',
            'email_from_name' => 'nullable|string|max:255',
            'email_reply_to' => 'nullable|email|max:255',
            // Configuración DKIM
            'dkim_selector' => 'nullable|string|max:255',
            'dkim_domain' => 'nullable|string|max:255',
            'dkim_public_key' => 'nullable|string|max:2000',
            'dkim_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Normalizar strings clave
        $data['nombre_empresa'] = trim($data['nombre_empresa']);
        if (!empty($data['rfc'])) {
            $data['rfc'] = strtoupper(trim($data['rfc']));
            $cfdi = new CfdiValidationService();
            $erroresRfc = $cfdi->validarRfc($data['rfc']);
            if (!empty($erroresRfc)) {
                return redirect()->back()->withErrors(['rfc' => implode('. ', $erroresRfc)])->withInput();
            }
        }
        if (!empty($data['color_principal'])) {
            $data['color_principal'] = strtoupper($data['color_principal']);
        }
        if (!empty($data['color_secundario'])) {
            $data['color_secundario'] = strtoupper($data['color_secundario']);
        }
        if (!empty($data['moneda'])) {
            $data['moneda'] = strtoupper($data['moneda']);
        }

        // Forzar actualización de booleanos aunque no vengan en el request (checkbox desmarcado)
        $booleanos = ['mantenimiento','registro_usuarios','notificaciones_email','backup_automatico','requerir_2fa','dkim_enabled'];
        foreach ($booleanos as $b) {
            $data[$b] = $request->boolean($b);
        }

        $configuracion = EmpresaConfiguracion::getConfig();
        $configuracion->update($data);

        return redirect()->route('empresa-configuracion.index')->with('success', 'Configuración actualizada correctamente.');
    }

    /**
     * Subir logo de la empresa
     */
    public function subirLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $configuracion = EmpresaConfiguracion::getConfig();

        // Eliminar logo anterior si existe
        if ($configuracion->logo_path && Storage::exists($configuracion->logo_path)) {
            Storage::delete($configuracion->logo_path);
        }

        // Guardar nuevo logo
        $path = $request->file('logo')->store('logos', 'public');

        $configuracion->update([
            'logo_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Logo subido correctamente.');
    }

    /**
     * Subir favicon
     */
    public function subirFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:jpeg,png,jpg,gif,ico|max:1024',
        ]);

        $configuracion = EmpresaConfiguracion::getConfig();

        // Eliminar favicon anterior si existe
        if ($configuracion->favicon_path && Storage::exists($configuracion->favicon_path)) {
            Storage::delete($configuracion->favicon_path);
        }

        // Guardar nuevo favicon
        $path = $request->file('favicon')->store('favicons', 'public');

        $configuracion->update([
            'favicon_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Favicon subido correctamente.');
    }

    /**
     * Subir logo para reportes
     */
    public function subirLogoReportes(Request $request)
    {
        $request->validate([
            'logo_reportes' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $configuracion = EmpresaConfiguracion::getConfig();

        // Eliminar logo anterior si existe
        if ($configuracion->logo_reportes && Storage::exists($configuracion->logo_reportes)) {
            Storage::delete($configuracion->logo_reportes);
        }

        // Guardar nuevo logo para reportes
        $path = $request->file('logo_reportes')->store('logos_reportes', 'public');

        $configuracion->update([
            'logo_reportes' => $path,
        ]);

        return redirect()->back()->with('success', 'Logo para reportes subido correctamente.');
    }

    /**
     * Eliminar logo
     */
    public function eliminarLogo()
    {
        $configuracion = EmpresaConfiguracion::getConfig();

        if ($configuracion->logo_path && Storage::exists($configuracion->logo_path)) {
            Storage::delete($configuracion->logo_path);
        }

        $configuracion->update([
            'logo_path' => null,
        ]);

        return redirect()->back()->with('success', 'Logo eliminado correctamente.');
    }

    /**
     * Eliminar favicon
     */
    public function eliminarFavicon()
    {
        $configuracion = EmpresaConfiguracion::getConfig();

        if ($configuracion->favicon_path && Storage::exists($configuracion->favicon_path)) {
            Storage::delete($configuracion->favicon_path);
        }

        $configuracion->update([
            'favicon_path' => null,
        ]);

        return redirect()->back()->with('success', 'Favicon eliminado correctamente.');
    }

    /**
     * Eliminar logo para reportes
     */
    public function eliminarLogoReportes()
    {
        $configuracion = EmpresaConfiguracion::getConfig();

        if ($configuracion->logo_reportes && Storage::exists($configuracion->logo_reportes)) {
            Storage::delete($configuracion->logo_reportes);
        }

        $configuracion->update([
            'logo_reportes' => null,
        ]);

        return redirect()->back()->with('success', 'Logo para reportes eliminado correctamente.');
    }

    /**
     * Obtener configuración actual (API)
     */
    public function getConfig()
    {
        $configuracion = EmpresaConfiguracion::getConfig();

        return response()->json([
            'configuracion' => $configuracion,
        ]);
    }

    /**
     * Probar configuración de colores
     */
    public function previewColores(Request $request)
    {
        $colores = $request->validate([
            'color_principal' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'color_secundario' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        return response()->json([
            'success' => true,
            'colores' => $colores,
        ]);
    }

    /**
     * Enviar correo de prueba
     */
    public function testEmail(Request $request)
    {
        $data = $request->validate([
            'email_destino' => ['required','email'],
        ]);

        try {
            // ✅ OBTENER CONFIGURACIÓN ACTUAL DE LA BASE DE DATOS
            $configuracion = EmpresaConfiguracion::getConfig();

            Log::info('Configuración obtenida de BD para prueba de correo:', [
                'smtp_host' => $configuracion->smtp_host,
                'smtp_port' => $configuracion->smtp_port,
                'smtp_username' => $configuracion->smtp_username,
                'smtp_encryption' => $configuracion->smtp_encryption,
            ]);

            // TIP: valida coherencia puerto/cifrado ANTES de configurar
            $port = (int) $configuracion->smtp_port;
            $enc  = strtolower((string) $configuracion->smtp_encryption);

            Log::info("Validando coherencia: puerto={$port}, cifrado={$enc}");

            if (($port === 587 && $enc !== 'tls') || ($port === 465 && $enc !== 'ssl')) {
                throw new \RuntimeException("Configuración inválida puerto/cifrado: puerto={$port}, enc={$enc}");
            }

            // ✅ CONFIGURAR MAIL CON DATOS DE BD
            config([
                'mail.mailers.smtp.host' => $configuracion->smtp_host,
                'mail.mailers.smtp.port' => $configuracion->smtp_port,
                'mail.mailers.smtp.username' => $configuracion->smtp_username,
                'mail.mailers.smtp.password' => $configuracion->smtp_password,
                'mail.mailers.smtp.encryption' => $configuracion->smtp_encryption,
                'mail.from.address' => $configuracion->email_from_address,
                'mail.from.name' => $configuracion->email_from_name,
            ]);

            Log::info('Configuración aplicada a Laravel Mail:', [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
            ]);

            Log::info('Configuración de Laravel Mail aplicada desde BD');

            Mail::raw('Prueba de SMTP OK - ' . now()->format('Y-m-d H:i:s'), function ($m) use ($data) {
                $m->to($data['email_destino'])->subject('Prueba SMTP - CDD');
            });

            Log::info('Correo de prueba enviado exitosamente');

            // IMPORTANTE: con Inertia, redirige con flash (no JSON)
            return back()->with('success', 'Correo de prueba enviado correctamente.');
        } catch (\Throwable $e) {
            // Log técnico completo
            Log::error('Fallo al enviar correo de prueba', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mensaje legible para el usuario (STRING, no array ni objeto)
            $mensaje = $this->formatearErrorSmtp($e);

            // Opción A: usar ValidationException (se muestra en form.errors.smtp)
            throw ValidationException::withMessages([
                'smtp' => $mensaje,  // <= STRING
            ]);

            // Opción B (alternativa): flash de error y redirect
            // return back()->with('error', $mensaje);
        }
    }

    /**
     * Extrae un mensaje corto y útil del Throwable (si viene de SMTP/Symfony).
     */
    private function formatearErrorSmtp(\Throwable $e): string
    {
        $msg = $e->getMessage();

        // Error específico de Hostinger: remitente no autorizado (solo si no tiene DKIM)
        if (str_contains($msg, '553 5.7.1') && str_contains($msg, 'Sender address rejected')) {
            return 'Error de Hostinger: Verifica que DKIM/SPF estén configurados correctamente en tu panel de Hostinger.';
        }

        // Acorta mensajes largos de autenticación (Hostinger suele devolver 535...)
        if (str_contains($msg, 'Expected response code "235"') || str_contains($msg, '535 5.7.8')) {
            return 'Autenticación SMTP fallida (535). Revisa usuario/contraseña y el cifrado/puerto.';
        }

        // Si llega algo tipo array serializado, quédate con la primera línea
        $line = strtok($msg, "\n");
        return mb_strimwidth($line ?: 'Error SMTP desconocido.', 0, 300, '…');
    }
}
