<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Herramienta;

class Tecnico extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'direccion',
        'activo',
    ];

    // Relación con la tabla herramientas
    public function herramientas()
    {
        return $this->hasMany(Herramienta::class, 'tecnico_id');
    }
}
