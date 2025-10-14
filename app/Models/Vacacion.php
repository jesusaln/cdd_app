<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vacacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'dias_pendientes',
        'dias_aprobados',
        'dias_rechazados',
        'motivo',
        'estado',
        'observaciones',
        'aprobador_id',
        'fecha_aprobacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'fecha_aprobacion' => 'date',
        ];
    }

    // Relaciones
    public function empleado()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobador_id')->withDefault();
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    public function scopeRechazadas($query)
    {
        return $query->where('estado', 'rechazada');
    }

    public function scopeDelEmpleado($query, $empleadoId)
    {
        return $query->where('user_id', $empleadoId);
    }

    // Métodos útiles
    public function getDiasTotalesAttribute()
    {
        return $this->dias_aprobados + $this->dias_rechazados + $this->dias_pendientes;
    }

    public function aprobar($aprobadorId, $observaciones = null)
    {
        $this->update([
            'estado' => 'aprobada',
            'aprobador_id' => $aprobadorId,
            'fecha_aprobacion' => now(),
            'dias_aprobados' => $this->dias_solicitados,
            'dias_pendientes' => 0,
            'dias_rechazados' => 0,
            'observaciones' => $observaciones,
        ]);

        // Registrar el uso de días en el sistema de registro de vacaciones
        $registroActual = RegistroVacaciones::actualizarRegistroAnual($this->user_id);
        if ($registroActual) {
            $registroActual->registrarUsoVacaciones($this->dias_solicitados);
        }
    }

    public function rechazar($aprobadorId, $observaciones = null)
    {
        $this->update([
            'estado' => 'rechazada',
            'aprobador_id' => $aprobadorId,
            'fecha_aprobacion' => now(),
            'dias_aprobados' => 0,
            'dias_pendientes' => 0,
            'dias_rechazados' => $this->dias_solicitados,
            'observaciones' => $observaciones,
        ]);
    }

    public function getEstadoColorAttribute()
    {
        return match($this->estado) {
            'pendiente' => 'yellow',
            'aprobada' => 'green',
            'rechazada' => 'red',
            'cancelada' => 'gray',
            default => 'gray'
        };
    }

    public function getEstadoLabelAttribute()
    {
        return match($this->estado) {
            'pendiente' => 'Pendiente',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
            'cancelada' => 'Cancelada',
            default => 'Desconocido'
        };
    }
}
