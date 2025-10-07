<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class EmpresaConfiguracion extends Model
{
    protected $table = 'empresa_configuracion';

    protected $fillable = [
        'nombre_empresa',
        'rfc',
        'razon_social',
        'direccion',
        'telefono',
        'email',
        'sitio_web',
        'codigo_postal',
        'ciudad',
        'estado',
        'pais',
        'logo_path',
        'favicon_path',
        'descripcion_empresa',
        'color_principal',
        'color_secundario',
        'pie_pagina_facturas',
        'pie_pagina_cotizaciones',
        'terminos_condiciones',
        'politica_privacidad',
        'iva_porcentaje',
        'moneda',
        'formato_numeros',
        'mantenimiento',
        'mensaje_mantenimiento',
        'registro_usuarios',
        'notificaciones_email',
        'logo_reportes',
        'formato_fecha',
        'formato_hora',
        'backup_automatico',
        'frecuencia_backup',
        'retencion_backups',
        'intentos_login',
        'tiempo_bloqueo',
        'requerir_2fa',
    ];

    protected $casts = [
        'mantenimiento' => 'boolean',
        'registro_usuarios' => 'boolean',
        'notificaciones_email' => 'boolean',
        'backup_automatico' => 'boolean',
        'requerir_2fa' => 'boolean',
        'iva_porcentaje' => 'decimal:2',
        'intentos_login' => 'integer',
        'tiempo_bloqueo' => 'integer',
        'frecuencia_backup' => 'integer',
        'retencion_backups' => 'integer',
    ];

    /**
     * Obtener la configuración actual de la empresa
     * Si no existe, devuelve valores por defecto
     */
    public static function getConfig()
    {
        return Cache::remember('empresa_configuracion', 3600, function () {
            $config = self::first();

            if (!$config) {
                // Crear configuración por defecto si no existe
                $config = self::create([
                    'nombre_empresa' => 'CDD - Sistema de Gestión',
                    'rfc' => 'XAXX010101000',
                    'razon_social' => 'Empresa de Ejemplo S.A. de C.V.',
                    'direccion' => 'Dirección de ejemplo',
                    'telefono' => '555-000-0000',
                    'email' => 'contacto@empresa.com',
                    'sitio_web' => 'https://empresa.com',
                    'codigo_postal' => '00000',
                    'ciudad' => 'Ciudad de México',
                    'estado' => 'CDMX',
                    'pais' => 'México',
                    'color_principal' => '#3B82F6',
                    'color_secundario' => '#1E40AF',
                    'iva_porcentaje' => 16.00,
                    'moneda' => 'MXN',
                    'formato_numeros' => 'es-ES',
                    'formato_fecha' => 'd/m/Y',
                    'formato_hora' => 'H:i:s',
                    'registro_usuarios' => true,
                    'notificaciones_email' => true,
                    'backup_automatico' => true,
                    'frecuencia_backup' => 7,
                    'retencion_backups' => 30,
                    'intentos_login' => 5,
                    'tiempo_bloqueo' => 15,
                ]);
            }

            return $config;
        });
    }

    /**
     * Limpiar caché de configuración
     */
    public static function clearCache()
    {
        Cache::forget('empresa_configuracion');
    }

    /**
     * Obtener URL completa del logo
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return Storage::url($this->logo_path);
        }
        return null;
    }

    /**
     * Obtener URL completa del favicon
     */
    public function getFaviconUrlAttribute()
    {
        if ($this->favicon_path) {
            return Storage::url($this->favicon_path);
        }
        return null;
    }

    /**
     * Obtener URL completa del logo para reportes
     */
    public function getLogoReportesUrlAttribute()
    {
        if ($this->logo_reportes) {
            return Storage::url($this->logo_reportes);
        } elseif ($this->logo_path) {
            return Storage::url($this->logo_path);
        }
        return null;
    }

    /**
     * Obtener dirección completa formateada
     */
    public function getDireccionCompletaAttribute()
    {
        $partes = array_filter([
            $this->direccion,
            $this->codigo_postal ? 'C.P. ' . $this->codigo_postal : null,
            $this->ciudad,
            $this->estado,
            $this->pais,
        ]);

        return implode(', ', $partes);
    }

    /**
     * Verificar si el sistema está en modo mantenimiento
     */
    public static function enMantenimiento()
    {
        $config = self::getConfig();
        return $config->mantenimiento;
    }

    /**
     * Obtener mensaje de mantenimiento
     */
    public static function mensajeMantenimiento()
    {
        $config = self::getConfig();
        return $config->mensaje_mantenimiento;
    }

    /**
     * Obtener información básica de la empresa para documentos
     */
    public static function getInfoEmpresa()
    {
        $config = self::getConfig();

        return [
            'nombre' => $config->nombre_empresa,
            'rfc' => $config->rfc,
            'razon_social' => $config->razon_social,
            'direccion' => $config->direccion_completa,
            'telefono' => $config->telefono,
            'email' => $config->email,
            'sitio_web' => $config->sitio_web,
            'logo_url' => $config->logo_url,
        ];
    }

    /**
     * Obtener configuración de colores
     */
    public static function getColores()
    {
        $config = self::getConfig();

        return [
            'principal' => $config->color_principal,
            'secundario' => $config->color_secundario,
        ];
    }

    /**
     * Obtener configuración financiera
     */
    public static function getConfiguracionFinanciera()
    {
        $config = self::getConfig();

        return [
            'iva_porcentaje' => $config->iva_porcentaje,
            'moneda' => $config->moneda,
            'formato_numeros' => $config->formato_numeros,
        ];
    }

    /**
     * Obtener pie de página para documentos
     */
    public static function getPiePagina($tipo = 'facturas')
    {
        $config = self::getConfig();

        switch ($tipo) {
            case 'cotizaciones':
                return $config->pie_pagina_cotizaciones;
            case 'facturas':
            default:
                return $config->pie_pagina_facturas;
        }
    }

    /**
     * Override para guardar y limpiar caché
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
