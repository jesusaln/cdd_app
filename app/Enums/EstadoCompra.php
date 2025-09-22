<?php

namespace App\Enums;

enum EstadoCompra: string
{
    case Pendiente = 'pendiente';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';
    case Recibida = 'recibida';
    case Recibido = 'recibido';
    case Cancelada = 'cancelada';
    case Devuelto = 'devuelto';
    case Borrador = 'borrador';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
            self::Recibida => 'Recibida',
            self::Recibido => 'Recibido',
            self::Cancelada => 'Cancelada',
            self::Devuelto => 'Devuelto',
            self::Borrador => 'Borrador',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'gray',
            self::Aprobada => 'blue',
            self::Rechazada => 'red',
            self::Recibida => 'green',
            self::Recibido => 'green',
            self::Cancelada => 'red',
            self::Devuelto => 'orange',
            self::Borrador => 'yellow',
        };
    }
}
