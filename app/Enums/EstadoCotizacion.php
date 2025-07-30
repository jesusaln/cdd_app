<?php

namespace App\Enums;

enum EstadoCotizacion: string
{
    case PENDIENTE = 'pendiente';
    case PEDIDO = 'pedido';
    case VENTA = 'venta';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::PEDIDO => 'Convertida a Pedido',
            self::VENTA => 'Convertida a Venta',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDIENTE => 'yellow',
            self::PEDIDO => 'blue',
            self::VENTA => 'green',
        };
    }

    public function puedeConvertir(): bool
    {
        return $this === self::PENDIENTE;
    }
}
