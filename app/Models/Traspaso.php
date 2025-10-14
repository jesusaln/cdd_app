<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Traspaso extends Model
{
    use HasFactory;

    protected $table = 'traspasos';

    protected $fillable = [
        'producto_id',
        'almacen_origen_id',
        'almacen_destino_id',
        'cantidad',
        'estado',
        'usuario_autoriza',
        'usuario_envia',
        'usuario_recibe',
        'fecha_envio',
        'fecha_recepcion',
        'observaciones',
        'referencia',
        'costo_transporte',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'fecha_recepcion' => 'datetime',
        'costo_transporte' => 'decimal:2',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function usuarioAutoriza(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_autoriza');
    }

    public function usuarioEnvia(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_envia');
    }

    public function usuarioRecibe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_recibe');
    }
}
