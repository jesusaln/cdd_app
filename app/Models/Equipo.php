<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipo extends Model
{
    use SoftDeletes;

    protected $table = 'equipos';

    protected $fillable = [
        'codigo',
        'nombre',
        'marca',
        'modelo',
        'numero_serie',
        'descripcion',
        'especificaciones',
        'precio_renta_mensual',
        'precio_compra',
        'fecha_adquisicion',
        'estado',
        'condicion',
        'ubicacion_fisica',
        'notas',
        'accesorios',
        'fecha_garantia',
        'proveedor',
        'computadora_numero_serie',
        'bascula_numero_serie',
        'lector_codigo_barras_numero_serie',
        'cajon_dinero_numero_serie',
        'sistema_numero_serie',
        'impresora_ticket_numero_serie',
        'otro_componente',
        'otro_numero_serie',
        'foto_kit',
    ];

    protected $casts = [
        'especificaciones' => 'array',
        'accesorios' => 'array',
        'fecha_adquisicion' => 'date',
        'fecha_garantia' => 'date',
    ];
}
