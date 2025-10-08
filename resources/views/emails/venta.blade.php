{{--
Plantilla de email para envío automático de PDF de venta
Ubicación: resources/views/emails/venta.blade.php
--}}

@extends('emails.layout')

@section('content')
    <h2 style="color: #3B82F6; margin-bottom: 20px;">Nueva Venta #{{ $venta->numero_venta }}</h2>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0; color: #333;">Detalles de la Venta</h3>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #dee2e6;"><strong>Cliente:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #dee2e6;">{{ $cliente->nombre_razon_social }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #dee2e6;"><strong>Número de Venta:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #dee2e6;">{{ $venta->numero_venta }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #dee2e6;"><strong>Fecha:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #dee2e6;">{{ $venta->fecha->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #dee2e6;"><strong>Total:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #dee2e6; color: #28a745; font-weight: bold;">
                    ${{ number_format($venta->total, 2) }} {{ $configuracion->moneda }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>Estado:</strong></td>
                <td style="padding: 8px 0;">
                    <span style="background-color: #ffc107; color: #212529; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                        {{ ucfirst($venta->estado->value) }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div style="background-color: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3B82F6;">
        <h4 style="margin: 0 0 10px 0; color: #3B82F6;">📎 PDF Adjunto</h4>
        <p style="margin: 0; color: #666;">
            Se ha adjuntado el PDF detallado de la venta #{{ $venta->numero_venta }}.
            El documento incluye todos los productos/servicios, cantidades, precios y totales.
        </p>
    </div>

    <div style="margin: 20px 0;">
        <p style="color: #666; margin: 0;">
            <strong>Empresa:</strong> {{ $configuracion->nombre_empresa }}
        </p>
        @if($configuracion->telefono)
        <p style="color: #666; margin: 5px 0;">
            <strong>Teléfono:</strong> {{ $configuracion->telefono }}
        </p>
        @endif
        @if($configuracion->email)
        <p style="color: #666; margin: 5px 0;">
            <strong>Email:</strong> {{ $configuracion->email }}
        </p>
        @endif
    </div>

    @if($venta->notas)
    <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
        <h4 style="margin: 0 0 10px 0; color: #856404;">📝 Notas de la Venta</h4>
        <p style="margin: 0; color: #856404;">{{ $venta->notas }}</p>
    </div>
    @endif

    <div style="text-align: center; margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
        <p style="margin: 0; color: #666;">
            Este es un mensaje automático enviado desde {{ $configuracion->nombre_empresa }}.
        </p>
        <p style="margin: 10px 0 0 0; color: #999; font-size: 12px;">
            Sistema de Gestión CDD - Venta #{{ $venta->numero_venta }}
        </p>
    </div>
@endsection
