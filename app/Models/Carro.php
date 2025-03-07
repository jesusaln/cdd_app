<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carro extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca',  // Marca del carro
        'modelo', // Modelo del carro
        'anio',   // Año del carro
        'color',  // Color del carro
        'precio', // Precio del carro
    ];
}
