<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta #{{ $venta->numero_venta }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #10b981;
            margin: 0;
            font-size: 28px;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-block {
            display: table-cell;
            width: 50%;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 0 10px;
        }
        .info-block h3 {
            color: #10b981;
            margin-top: 0;
        }
        .total {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            background-color: #d4edda;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .button:hover {
            background-color: #059669;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $configuracion->nombre_empresa }}</h1>
            <p>Venta #{{ $venta->numero_venta }}</p>
        </div>

        <div class="info-section">
            <div class="info-block">
                <h3>Información de la Venta</h3>
                <p><strong>Venta:</strong> #{{ $venta->numero_venta }}</p>
                <p><strong>Fecha:</strong> {{ $venta->fecha ? $venta->fecha->format('d/m/Y') : $venta->created_at->format('d/m/Y') }}</p>
                <p><strong>Estado:</strong> {{ ucfirst($venta->estado->label()) }}</p>
                @if($venta->pagado)
                <p><strong>Pago:</strong> {{ $venta->metodo_pago ?? 'Pagado' }}</p>
                @endif
            </div>

            <div class="info-block">
                <h3>Cliente</h3>
                <p><strong>Nombre:</strong> {{ $cliente->nombre_razon_social }}</p>
                @if($cliente->email)
                <p><strong>Email:</strong> {{ $cliente->email }}</p>
                @endif
                @if($cliente->telefono)
                <p><strong>Teléfono:</strong> {{ $cliente->telefono }}</p>
                @endif
            </div>
        </div>

        <h3>Productos/Servicios</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background-color: #10b981; color: white;">
                    <th style="padding: 10px; text-align: left;">Producto/Servicio</th>
                    <th style="padding: 10px; text-align: center;">Cantidad</th>
                    <th style="padding: 10px; text-align: right;">Precio</th>
                    <th style="padding: 10px; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->items as $item)
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                        {{ $item->ventable->nombre ?? $item->ventable->descripcion }}
                    </td>
                    <td style="padding: 10px; text-align: center; border-bottom: 1px solid #dee2e6;">
                        {{ $item->cantidad }}
                    </td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #dee2e6;">
                        ${{ number_format($item->precio, 2) }}
                    </td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #dee2e6;">
                        ${{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            Total: ${{ number_format($venta->total, 2) }}
        </div>

        @if($venta->notas)
        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin: 0 0 10px 0; color: #856404;">📝 Notas de la Venta</h4>
            <p style="margin: 0; color: #856404;">{{ $venta->notas }}</p>
        </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <p>Gracias por su compra. Para cualquier duda o aclaración, no dude en contactarnos.</p>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático generado por {{ $configuracion->nombre_empresa }}</p>
            <p>Por favor, no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
