<?php

namespace App\Enums;

enum EstadoCompra: string
{
    case Procesada = 'procesada';
    case Cancelada = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Procesada => 'Procesada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Procesada => 'green',
            self::Cancelada => 'red',
        };
    }
}
