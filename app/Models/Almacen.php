<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacenes';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'ubicacion',
    ];

    /**
     * Los atributos que deben ser ocultados para arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [];

    // Definir la relación con el modelo Producto
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
