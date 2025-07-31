<?php

namespace App\Enums;

enum EstadoCotizacion: string
{
    case Pendiente = 'pendiente';
    case ConvertidoAPedido = 'convertido_a_pedido';
    case ConvertidoAVenta = 'convertido_a_venta';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::ConvertidoAPedido => 'Convertido a Pedido',
            self::ConvertidoAVenta => 'Convertido a Venta',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'gray',
            self::ConvertidoAPedido => 'blue',
            self::ConvertidoAVenta => 'green',
        };
    }

    public function puedeConvertir(): bool
    {
        return $this === self::Pendiente;
    }

    public function puedeEditar(): bool
    {
        return $this === self::Pendiente;
    }

    public function puedeEliminar(): bool
    {
        return $this === self::Pendiente;
    }

    public function siguientesEstados(): array
    {
        return match ($this) {
            self::Pendiente => [self::ConvertidoAPedido, self::ConvertidoAVenta],
            default => [],
        };
    }
}
