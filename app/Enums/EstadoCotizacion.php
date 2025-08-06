<?php

namespace App\Enums;

enum EstadoCotizacion: string
{
    case Pendiente = 'pendiente';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';
    case Enviada = 'enviada';
    case ConvertidaPedido = 'convertida_pedido';
    case EnviadoPedido = 'enviado_pedido'; // Añade este nuevo estado
    case Borrador = 'borrador';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
            self::Enviada => 'Enviada',
            self::ConvertidaPedido => 'Convertida a Pedido',
            self::EnviadoPedido => 'Enviado a Pedido',
            self::Borrador => 'Borrador',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'gray',
            self::Aprobada => 'blue',
            self::Rechazada => 'red',
            self::Enviada => 'purple',
            self::ConvertidaPedido => 'green',
            self::EnviadoPedido => 'indigo',
            self::Borrador => 'yellow',
        };
    }
}
