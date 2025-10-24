<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstadoPedido;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Blameable;

class Pedido extends Model
{
    use SoftDeletes, Blameable;

    protected $table = 'pedidos';

    protected $fillable = [
        'cliente_id',
        'cotizacion_id',
        'numero_pedido',
        'subtotal',
        'descuento_general',
        'iva',
        'total',
        'notas',
        'estado',
        // Campos para rastreo de email
        'email_enviado',
        'email_enviado_fecha',
        'email_enviado_por',
    ];

    protected $casts = [
        'estado' => EstadoPedido::class,
        // Campos de email
        'email_enviado'     => 'boolean',
        'email_enviado_fecha' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }



    // Relaciones polimórficas para productos y servicios
    public function productos()
    {
        return $this->morphedByMany(
            Producto::class,
            'pedible',
            'pedido_items',
            'pedido_id',
            'pedible_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto')
        ->wherePivotIn('pedible_type', [Producto::class, 'producto'])
        ->whereHasMorph('pedible', Producto::class, function ($query) {
            $query->active();
        });
    }

    public function servicios()
    {
        return $this->morphedByMany(
            Servicio::class,
            'pedible',
            'pedido_items',
            'pedido_id',
            'pedible_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto')
        ->wherePivotIn('pedible_type', [Servicio::class, 'servicio'])
        ->whereHasMorph('pedible', Servicio::class, function ($query) {
            $query->active();
        });
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    // Relaciones de auditoría
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Relación con el usuario que envió el email
    public function emailEnviadoPor()
    {
        return $this->belongsTo(User::class, 'email_enviado_por');
    }
}
