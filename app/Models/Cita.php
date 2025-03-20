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
        'evidencias', // Asegúrate de que esté aquí
        'foto_equipo',
        'foto_hoja_servicio',
        'foto_identificacion',
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
