<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carro extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca',          // Marca del carro
        'modelo',         // Modelo del carro
        'anio',           // Año del carro
        'color',          // Color del carro
        'precio',         // Precio del carro
        'numero_serie',   // Número de serie único del carro
        'combustible',    // Tipo de combustible (Gasolina, Diésel, Eléctrico, Híbrido)
        'kilometraje',    // Kilometraje actual del carro
        'placa',          // Placa del carro
        'foto',           // Ruta de la foto del carro
    ];

    
}
