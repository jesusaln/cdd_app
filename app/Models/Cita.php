<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'tecnico_id',
        'cliente_id',
        'tipo_servicio',
        'fecha_hora',
        'descripcion',
        'tipo_equipo',
        'marca_equipo',
        'modelo_equipo',
        'problema_reportado',
        'estado', // Nuevo campo de estado
    ];

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
