<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'nombre',
        'rfc',
        'contacto',
        'telefono',
        'email',
        'direccion',
        'codigo_postal',
        'municipio',
        'estado',
        'pais',

    ];

    /**
     * Relación con las compras.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function compras()
    {
        return $this->hasMany(Compra::class); // Relación uno a muchos
    }
}
