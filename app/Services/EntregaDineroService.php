<?php

namespace App\Services;

use App\Models\EntregaDinero;
use Carbon\Carbon;

class EntregaDineroService
{
    /**
     * Decide estado por método según configuración.
     */
    public static function estadoPorMetodo(string $metodoPago): string
    {
        $auto = config('entregas.auto_recibido_metodos', ['transferencia']);
        return in_array($metodoPago, $auto, true) ? 'recibido' : 'pendiente';
    }

    /**
     * Crear entrega aplicando política de estado por método (parametrizable).
     */
    public static function crearAutoPorMetodo(
        string $tipoOrigen,
        int $idOrigen,
        float $monto,
        string $metodoPago,
        string $fechaEntregaYmd,
        int $userId,
        ?string $notas = null,
        ?int $recibidoPor = null
    ): EntregaDinero {
        $estado = self::estadoPorMetodo($metodoPago);
        return self::crearDesdeOrigen(
            $tipoOrigen,
            $idOrigen,
            $monto,
            $metodoPago,
            $fechaEntregaYmd,
            $userId,
            $estado,
            $recibidoPor,
            $notas
        );
    }

    /**
     * Crear una Entrega de Dinero desde un registro de origen unificado.
     * - Mapea el método de pago a montos (efectivo/cheques/tarjetas).
     * - Permite crear en estado 'pendiente' o 'recibido'.
     */
    public static function crearDesdeOrigen(
        string $tipoOrigen,
        int $idOrigen,
        float $monto,
        string $metodoPago,
        string $fechaEntregaYmd,
        int $userId,
        string $estado = 'pendiente',
        ?int $recibidoPor = null,
        ?string $notas = null
    ): EntregaDinero {
        $montoEfectivo = 0.0;
        $montoTransferencia = 0.0;
        $montoCheques  = 0.0;
        $montoTarjetas = 0.0;

        switch ($metodoPago) {
            case 'efectivo':
            case 'otros':
                $montoEfectivo = $monto;
                break;
            case 'transferencia':
                $montoTransferencia = $monto;
                break;
            case 'cheque':
                $montoCheques = $monto;
                break;
            case 'tarjeta':
                $montoTarjetas = $monto;
                break;
            default:
                $montoEfectivo = $monto;
                break;
        }

        $data = [
            'user_id'        => $userId,
            'fecha_entrega'  => Carbon::parse($fechaEntregaYmd)->format('Y-m-d'),
            'monto_efectivo' => $montoEfectivo,
            'monto_transferencia' => $montoTransferencia,
            'monto_cheques'  => $montoCheques,
            'monto_tarjetas' => $montoTarjetas,
            'total'          => $monto,
            'estado'         => $estado,
            'notas'          => $notas,
            'tipo_origen'    => $tipoOrigen,
            'id_origen'      => $idOrigen,
        ];

        if ($estado === 'recibido') {
            $data['recibido_por']   = $recibidoPor ?: $userId;
            $data['fecha_recibido'] = Carbon::now();
        }

        return EntregaDinero::create($data);
    }
}
