{{--
Plantilla PDF para cotizaciones
Ubicación: resources/views/cotizacion_pdf.blade.php
--}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cotización #{{ $cotizacion->numero_cotizacion }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #{{ $configuracion->color_principal ?? '#3B82F6' }};
            margin: 0;
            font-size: 24px;
        }

        .header .subtitle {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }

        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .info-block {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .info-block h3 {
            color: #{{ $configuracion->color_principal ?? '#3B82F6' }};
            margin: 0 0 10px 0;
            font-size: 14px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 0;
            font-size: 11px;
        }

        .info-table td.label {
            font-weight: bold;
            color: #666;
            width: 120px;
        }

        .products-section {
            margin: 20px 0;
        }

        .products-section h3 {
            color: #{{ $configuracion->color_principal ?? '#3B82F6' }};
            margin: 0 0 15px 0;
            font-size: 16px;
            border-bottom: 1px solid #{{ $configuracion->color_principal ?? '#3B82F6' }};
            padding-bottom: 5px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .products-table th {
            background-color: #3B82F6;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }

        .products-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 10px;
        }

        .products-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .totals-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 5px 0;
            font-size: 12px;
        }

        .totals-table td.label {
            text-align: right;
            font-weight: bold;
            padding-right: 20px;
        }

        .totals-table td.value {
            text-align: right;
            width: 100px;
        }

        .total-final {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
            background-color: #d4edda;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pendiente { background-color: #fff3cd; color: #856404; }
        .status-aprobada { background-color: #d4edda; color: #155724; }
        .status-rechazada { background-color: #f8d7da; color: #721c24; }
        .status-borrador { background-color: #e2e3e5; color: #383d41; }
        .status-enviada { background-color: #d1ecf1; color: #0c5460; }
        .status-enviado_pedido { background-color: #fff3cd; color: #856404; }
        .status-cancelado { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $configuracion->nombre_empresa }}</h1>
        <div class="subtitle">Cotización #{{ $cotizacion->numero_cotizacion }}</div>
    </div>

    <div class="info-section">
        <div class="info-block">
            <h3>Información de la Empresa</h3>
            <table class="info-table">
                <tr>
                    <td class="label">Empresa:</td>
                    <td>{{ $configuracion->nombre_empresa }}</td>
                </tr>
                @if($configuracion->rfc)
                <tr>
                    <td class="label">RFC:</td>
                    <td>{{ $configuracion->rfc }}</td>
                </tr>
                @endif
                @if($configuracion->telefono)
                <tr>
                    <td class="label">Teléfono:</td>
                    <td>{{ $configuracion->telefono }}</td>
                </tr>
                @endif
                @if($configuracion->email)
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $configuracion->email }}</td>
                </tr>
                @endif
                @if($configuracion->direccion_completa)
                <tr>
                    <td class="label">Dirección:</td>
                    <td>{{ $configuracion->direccion_completa }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="info-block">
            <h3>Información de la Cotización</h3>
            <table class="info-table">
                <tr>
                    <td class="label">Número de Cotización:</td>
                    <td>{{ $cotizacion->numero_cotizacion }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha:</td>
                    <td>{{ $cotizacion->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Estado:</td>
                    <td>
                        <span class="status-badge status-{{ $cotizacion->estado->value }}">
                            {{ ucfirst($cotizacion->estado->label()) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Validez:</td>
                    <td>{{ $cotizacion->validez_dias ?? 30 }} días</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="info-section">
        <div class="info-block">
            <h3>Información del Cliente</h3>
            <table class="info-table">
                <tr>
                    <td class="label">Cliente:</td>
                    <td>{{ $cotizacion->cliente->nombre_razon_social }}</td>
                </tr>
                @if($cotizacion->cliente->email)
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $cotizacion->cliente->email }}</td>
                </tr>
                @endif
                @if($cotizacion->cliente->telefono)
                <tr>
                    <td class="label">Teléfono:</td>
                    <td>{{ $cotizacion->cliente->telefono }}</td>
                </tr>
                @endif
                @if($cotizacion->cliente->rfc)
                <tr>
                    <td class="label">RFC:</td>
                    <td>{{ $cotizacion->cliente->rfc }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="info-block">
            <h3>Resumen Financiero</h3>
            <table class="info-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td>${{ number_format($cotizacion->subtotal, 2) }}</td>
                </tr>
                @if($cotizacion->descuento_general > 0)
                <tr>
                    <td class="label">Descuento General:</td>
                    <td>- ${{ number_format($cotizacion->descuento_general, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">IVA ({{ $configuracion->iva_porcentaje }}%):</td>
                    <td>${{ number_format($cotizacion->iva, 2) }}</td>
                </tr>
                <tr>
                    <td class="label" style="font-size: 14px; font-weight: bold; color: #28a745;">TOTAL:</td>
                    <td style="font-size: 14px; font-weight: bold; color: #28a745;">${{ number_format($cotizacion->total, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="products-section">
        <h3>Productos y Servicios</h3>

        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Descripción</th>
                    <th style="width: 15%; text-align: center;">Cantidad</th>
                    <th style="width: 15%; text-align: right;">Precio Unit.</th>
                    <th style="width: 15%; text-align: right;">Descuento</th>
                    <th style="width: 15%; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cotizacion->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->cotizable->nombre ?? $item->cotizable->descripcion }}</strong>
                        <br>
                        <small style="color: #666;">
                            {{ $item->cotizable_type === 'App\Models\Producto' ? 'Producto' : 'Servicio' }}
                            @if($item->cotizable->codigo ?? false)
                                - Código: {{ $item->cotizable->codigo }}
                            @endif
                        </small>
                    </td>
                    <td style="text-align: center;">{{ $item->cantidad }}</td>
                    <td style="text-align: right;">${{ number_format($item->precio, 2) }}</td>
                    <td style="text-align: right;">
                        @if($item->descuento > 0)
                            {{ $item->descuento }}%<br>
                            <small>- ${{ number_format($item->descuento_monto, 2) }}</small>
                        @else
                            0%
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        ${{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">${{ number_format($cotizacion->subtotal, 2) }}</td>
            </tr>
            @if($cotizacion->descuento_general > 0)
            <tr>
                <td class="label">Descuento General:</td>
                <td class="value">- ${{ number_format($cotizacion->descuento_general, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">IVA ({{ $configuracion->iva_porcentaje }}%):</td>
                <td class="value">${{ number_format($cotizacion->iva, 2) }}</td>
            </tr>
            <tr>
                <td class="label" style="font-size: 16px; font-weight: bold; color: #28a745; border-top: 2px solid #28a745; padding-top: 10px;">TOTAL:</td>
                <td class="value" style="font-size: 16px; font-weight: bold; color: #28a745; border-top: 2px solid #28a745; padding-top: 10px;">
                    ${{ number_format($cotizacion->total, 2) }}
                </td>
            </tr>
        </table>
    </div>

    @if($cotizacion->notas)
    <div style="margin: 20px 0; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
        <h4 style="margin: 0 0 10px 0; color: #856404;">📝 Notas de la Cotización</h4>
        <p style="margin: 0; color: #856404; font-size: 11px;">{{ $cotizacion->notas }}</p>
    </div>
    @endif

    @if($configuracion->pie_pagina_cotizaciones)
    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; text-align: center;">
        <p style="margin: 0; font-size: 10px; color: #666; line-height: 1.3;">
            {!! nl2br(e($configuracion->pie_pagina_cotizaciones)) !!}
        </p>
    </div>
    @endif

    <div class="footer">
        <p style="margin: 0;">
            Documento generado automáticamente por {{ $configuracion->nombre_empresa }} el {{ now()->format('d/m/Y \a \l\a\s H:i:s') }}
        </p>
        <p style="margin: 5px 0 0 0;">
            Sistema de Gestión CDD - {{ $configuracion->nombre_empresa }} - Cotización #{{ $cotizacion->numero_cotizacion }}
        </p>
    </div>
</body>
</html>
