<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla.
     */
    protected $table = 'equipos';

    /**
     * Atributos asignables en masa.
     */
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
    ];

    /**
     * Atributos que deben convertirse a tipos nativos.
     */
    protected $casts = [
        'especificaciones' => 'json',
        'accesorios' => 'json',
        'precio_renta_mensual' => 'decimal:2',
        'precio_compra' => 'decimal:2',
        'fecha_adquisicion' => 'date',
        'fecha_garantia' => 'date',
    ];

    /**
     * Mutaciones de fecha.
     */
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
}
