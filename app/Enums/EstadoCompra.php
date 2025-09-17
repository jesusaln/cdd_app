<?php

namespace App\Enums;

enum EstadoCompra: string
{
    case Pendiente = 'pendiente';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';
    case Recibida = 'recibida';
    case Cancelada = 'cancelada';
    case Borrador = 'borrador';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
            self::Recibida => 'Recibida',
            self::Cancelada => 'Cancelada',
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
            self::Cancelada => 'red',
            self::Borrador => 'yellow',
        };
    }
}
