<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'carro_id', // ID del carro al que pertenece el mantenimiento
        'tipo',     // Tipo de mantenimiento (e.g., cambio de aceite, frenos)
        'fecha',    // Fecha del mantenimiento
        'proximo_mantenimiento', // Fecha del próximo mantenimiento
        'notas',    // Notas adicionales
    ];


    // Relación con el modelo Carro
    public function carro()
    {
        return $this->belongsTo(Carro::class);
    }
}
