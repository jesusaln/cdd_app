<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class BitacoraActividad extends Model
{
    use HasFactory;

    protected $table = 'bitacora_actividades';

    protected $fillable = [
        'user_id',
        'cliente_id',
        'titulo',
        'descripcion',
        'fecha',
        'hora',
        'inicio_at',
        'fin_at',
        'tipo',
        'estado',
        'prioridad',
        'ubicacion',
        'adjuntos',
        'es_facturable',
        'costo_mxn',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'hora'          => 'string',
        'inicio_at'     => 'datetime',
        'fin_at'        => 'datetime',
        'adjuntos'      => 'array',
        'es_facturable' => 'boolean',
        'costo_mxn'     => 'decimal:2',
    ];

    // Enviar estos campos calculados en las respuestas JSON (Inertia)
    protected $appends = ['fecha_fmt', 'hora_fmt'];

    // ===== Relaciones =====
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // ===== Accessors / atributos calculados =====

    // Duración en minutos
    public function getDuracionMinutosAttribute(): ?int
    {
        if (!$this->inicio_at || !$this->fin_at) return null;
        return $this->inicio_at->diffInMinutes($this->fin_at);
    }

    // Fecha formateada (ej. "10 sep 2025")
    public function getFechaFmtAttribute(): ?string
    {
        if (!$this->fecha) return null;

        // Usa locale español de MX si está disponible
        $this->fecha->locale('es_MX');

        // translatedFormat respeta el locale
        return $this->fecha->translatedFormat('d M Y');
        // Alternativa con ISO:
        // return $this->fecha->isoFormat('DD MMM YYYY'); // requiere ->locale('es')
    }

    // Hora formateada en zona America/Hermosillo (24h)
    public function getHoraFmtAttribute(): ?string
    {
        if ($this->inicio_at) {
            // inicio_at viene como Carbon por el cast; convertimos a Hermosillo
            return $this->inicio_at->copy()->setTimezone('America/Hermosillo')->format('H:i');
        }
        // Fallback a campo 'hora' (string "HH:mm")
        return $this->hora ?: null;
    }

    // ===== Scopes para filtros =====
    public function scopeDeUsuario($q, $userId)
    {
        return $q->when($userId, fn($qq) => $qq->where('user_id', $userId));
    }

    public function scopeDeCliente($q, $clienteId)
    {
        return $q->when($clienteId, fn($qq) => $qq->where('cliente_id', $clienteId));
    }

    public function scopeRangoFechas($q, $desde, $hasta)
    {
        return $q->when($desde, fn($qq) => $qq->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($qq) => $qq->whereDate('fecha', '<=', $hasta));
    }

    public function scopeBuscar($q, $term)
    {
        return $q->when($term, function ($qq) use ($term) {
            $like = "%{$term}%";
            $qq->where(function ($w) use ($like) {
                $w->where('titulo', 'like', $like)
                    ->orWhere('descripcion', 'like', $like);
            });
        });
    }
}
