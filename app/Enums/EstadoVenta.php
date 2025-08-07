<?php

namespace App\Enums;

enum EstadoVenta: string
{
    case Borrador = 'borrador';
    case Pendiente = 'pendiente';
    case Aprobada = 'aprobada';
    case Enviada = 'enviada';
    case Facturada = 'facturada';
    case Cancelada = 'cancelada';
    case SinEstado = 'sin_estado';

    /**
     * Devuelve la etiqueta legible para el estado.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Pendiente => 'Pendiente',
            self::Aprobada => 'Aprobada',
            self::Enviada => 'Enviada',
            self::Facturada => 'Facturada',
            self::Cancelada => 'Cancelada',
            self::SinEstado => 'Sin Estado',
        };
    }

    /**
     * Devuelve el color asociado al estado para la interfaz de usuario.
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::Borrador => 'yellow',
            self::Pendiente => 'gray',
            self::Aprobada => 'blue',
            self::Enviada => 'purple',
            self::Facturada => 'green',
            self::Cancelada => 'red',
            self::SinEstado => 'gray',
        };
    }
}
