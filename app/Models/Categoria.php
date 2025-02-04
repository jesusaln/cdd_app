<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Categoria extends Model
{
    use HasFactory;

    // Define los atributos que pueden ser asignados masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación: Una categoría tiene muchos productos
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
