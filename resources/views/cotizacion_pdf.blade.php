{{-- resources/views/cotizacion_pdf.blade.php --}}
@php
    use Illuminate\Support\Str;

    // Color principal saneado
    $COLOR = ltrim($configuracion->color_principal ?? '#3B82F6', '#');
    $HEX = "#{$COLOR}";
    $money = fn($n) => number_format((float) $n, 2);
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cotización #{{ $cotizacion->numero_cotizacion }}</title>
    <style>
        @page {
            margin: 14mm 12mm 12mm 12mm;
            size: auto;
        }

        html,
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #222;
            font-size: 10px;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        .muted {
            color: #666;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .avoid-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        tr,
        td,
        th {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Encabezado */
        .header {
            border-bottom: 2px solid {{ $HEX }};
            padding: 2mm 0 3mm;
            margin-bottom: 4mm;
        }

        .empresa {
            font-size: 15px;
            font-weight: 700;
            color: {{ $HEX }};
            text-align: center;
        }

        .cotizacion-bar {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin: 6px 0 10px;
            font-size: 11px;
        }

        .cotizacion-bar>div {
            display: table-cell;
            vertical-align: middle;
            white-space: nowrap;
        }

        .cotizacion-left {
            text-align: left;
        }

        .cotizacion-center {
            text-align: center;
        }

        .cotizacion-right {
            text-align: right;
        }

        .cotizacion-bar b {
            color: #333;
        }

        /* Info boxes (Empresa, Cliente) */
        .info {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin: 4mm 0;
        }

        .info .col {
            display: table-cell;
            vertical-align: top;
            padding: 3mm;
            background: #f5f7fa;
            border: 1px solid #e6e9ef;
            border-radius: 4px;
        }

        .info .col+.col {
            padding-left: 3mm;
        }

        .info h3 {
            margin: 0 0 2mm;
            font-size: 11px;
            color: {{ $HEX }};
        }

        .kv {
            width: 100%;
            border-collapse: collapse;
        }

        .kv td {
            padding: .5mm 0;
            vertical-align: top;
        }

        .kv .k {
            width: 22mm;
            color: #666;
            font-weight: 600;
        }

        .kv .v {
            word-wrap: break-word;
        }

        /* Tabla de conceptos */
        .tbl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2mm;
        }

        .tbl th {
            background: {{ $HEX }};
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            padding: 2.2mm 1.6mm;
            text-align: left;
        }

        .tbl td {
            font-size: 9px;
            padding: 2mm 1.6mm;
            border-bottom: 1px solid #e8ecf2;
        }

        .tbl .c {
            text-align: center;
        }

        .tbl .r {
            text-align: right;
        }

        .tbl tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .item-name {
            font-weight: 700;
        }

        .item-code {
            font-size: 8px;
            color: #555;
        }

        /* Totales */
        .totals {
            margin-top: 4mm;
            display: table;
            width: 100%;
        }

        .totals .left {
            display: table-cell;
            vertical-align: top;
            width: 55%;
            padding-right: 3mm;
        }

        .totals .right {
            display: table-cell;
            vertical-align: top;
            width: 45%;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            background: #f5f7fa;
            border: 1px solid #e6e9ef;
            border-radius: 4px;
        }

        .totals-table td {
            padding: 1.6mm 2mm;
            font-size: 9.5px;
        }

        .totals-table .k {
            text-align: right;
            font-weight: 700;
            color: #444;
        }

        .totals-table .v {
            text-align: right;
            width: 30mm;
        }

        .grand {
            margin-top: 2mm;
            padding: 2.2mm 2mm;
            background: #e7f5ea;
            color: #127a2a;
            font-weight: 800;
            font-size: 12px;
            text-align: right;
            border-radius: 3px;
        }

        /* Footer fijo con notas y datos bancarios */
        .fixed-footer {
            position: fixed;
            bottom: 0;
            left: 12mm;
            right: 12mm;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 4mm;
            font-size: 7px;
            line-height: 1.2;
            max-height: 30mm;
            overflow: hidden;
        }

        .footer-grid {
            display: table;
            width: 100%;
        }

        .footer-col {
            display: table-cell;
            vertical-align: top;
            padding: 0 2mm;
        }

        .footer-col:first-child {
            width: 40%;
        }

        .footer-col:nth-child(2) {
            width: 35%;
        }

        .footer-col:last-child {
            width: 25%;
        }

        .footer-title {
            font-weight: bold;
            color: {{ $HEX }};
            font-size: 7px;
            margin-bottom: 1mm;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5mm;
        }

        .footer-text {
            font-size: 6px;
            color: #555;
            margin: 0;
            line-height: 1.1;
        }

        .bank-info {
            font-family: monospace;
            font-size: 6px;
            background: #f8f9fa;
            padding: 1mm;
            border-radius: 2px;
            margin: 0.5mm 0;
        }

        .bank-label {
            font-weight: bold;
            color: #333;
        }

        /* Ajustes finos */
        .tbl td,
        .tbl th {
            line-height: 1.15;
        }
    </style>
</head>

<body>
    {{-- ENCABEZADO --}}
    <div class="header avoid-break">
        <div class="empresa">{{ $configuracion->nombre_empresa }}</div>

        {{-- Barra UNA SOLA FILA (izq-centrado-der) --}}
        <div class="cotizacion-bar">
            <div class="cotizacion-left">
                <b>Cotización:</b> #{{ $cotizacion->numero_cotizacion }}
            </div>
            <div class="cotizacion-center">
                <b>Fecha:</b> {{ $cotizacion->created_at->format('d/m/Y') }}
            </div>
            <div class="cotizacion-right">
                <b>Validez:</b> {{ $cotizacion->validez_dias ?? 30 }} días
            </div>
        </div>
    </div>

    {{-- BLOQUES INFO (Empresa, Cliente) --}}
    <div class="info avoid-break">
        <div class="col">
            <h3>Proveedor</h3>
            <table class="kv">
                <tr>
                    <td class="k">Razón Social:</td>
                    <td class="v"><strong>{{ $configuracion->nombre_empresa }}</strong></td>
                </tr>
                @if ($configuracion->rfc)
                    <tr>
                        <td class="k">RFC:</td>
                        <td class="v">{{ $configuracion->rfc }}</td>
                    </tr>
                @endif
                @if ($configuracion->direccion_completa)
                    <tr>
                        <td class="k">Dirección:</td>
                        <td class="v">{{ $configuracion->direccion_completa }}</td>
                    </tr>
                @endif
                @if ($configuracion->municipio && $configuracion->estado && $configuracion->codigo_postal)
                    <tr>
                        <td class="k">Municipio:</td>
                        <td class="v">{{ $configuracion->municipio }}, {{ $configuracion->estado }},
                            {{ $configuracion->codigo_postal }}</td>
                    </tr>
                @endif
                @if ($configuracion->telefono)
                    <tr>
                        <td class="k">Teléfono:</td>
                        <td class="v">{{ $configuracion->telefono }}</td>
                    </tr>
                @endif
                @if ($configuracion->email)
                    <tr>
                        <td class="k">Email:</td>
                        <td class="v">{{ $configuracion->email }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="col">
            <h3>Cliente</h3>
            <table class="kv">
                <tr>
                    <td class="k">Razón Social:</td>
                    <td class="v">{{ $cotizacion->cliente->nombre_razon_social }}</td>
                </tr>
                @if ($cotizacion->cliente->rfc)
                    <tr>
                        <td class="k">RFC:</td>
                        <td class="v">{{ $cotizacion->cliente->rfc }}</td>
                    </tr>
                @endif
                @if ($cotizacion->cliente->calle || $cotizacion->cliente->numero_exterior || $cotizacion->cliente->colonia)
                    <tr>
                        <td class="k">Dirección:</td>
                        <td class="v">
                            {{ trim(
                                implode(
                                    ' ',
                                    array_filter([
                                        $cotizacion->cliente->calle,
                                        $cotizacion->cliente->numero_exterior,
                                        $cotizacion->cliente->numero_interior ? 'Int. ' . $cotizacion->cliente->numero_interior : null,
                                        $cotizacion->cliente->colonia,
                                    ]),
                                ),
                            ) }}
                        </td>
                    </tr>
                @endif
                @if ($cotizacion->cliente->municipio || $cotizacion->cliente->estado || $cotizacion->cliente->codigo_postal)
                    <tr>
                        <td class="k">Municipio:</td>
                        <td class="v">
                            {{ trim(
                                implode(
                                    ', ',
                                    array_filter([
                                        $cotizacion->cliente->municipio,
                                        $cotizacion->cliente->estado,
                                        $cotizacion->cliente->codigo_postal,
                                    ]),
                                ),
                            ) }}
                        </td>
                    </tr>
                @endif
                @if ($cotizacion->cliente->telefono)
                    <tr>
                        <td class="k">Teléfono:</td>
                        <td class="v">{{ $cotizacion->cliente->telefono }}</td>
                    </tr>
                @endif
                @if ($cotizacion->cliente->email)
                    <tr>
                        <td class="k">Email:</td>
                        <td class="v">{{ $cotizacion->cliente->email }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- CONCEPTOS --}}
    <table class="tbl avoid-break">
        <thead>
            <tr>
                <th style="width:45%;">Producto / Servicio</th>
                <th style="width:10%; text-align:center;">Cant.</th>
                <th style="width:15%; text-align:right;">Precio</th>
                <th style="width:12%; text-align:right;">Dto.</th>
                <th style="width:18%; text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cotizacion->items as $item)
                @php
                    $nombre = $item->cotizable->nombre ?? ($item->cotizable->descripcion ?? '—');
                    $codigo = $item->cotizable->codigo ?? null;
                @endphp
                <tr>
                    <td>
                        <div class="item-name">{{ Str::limit($nombre, 60) }}</div>
                        @if ($codigo)
                            <div class="item-code">Cód: {{ Str::limit($codigo, 24) }}</div>
                        @endif
                    </td>
                    <td class="c">{{ $item->cantidad }}</td>
                    <td class="r">${{ $money($item->precio) }}</td>
                    <td class="r">
                        @if (($item->descuento ?? 0) > 0)
                            {{ rtrim(rtrim(number_format($item->descuento, 2), '0'), '.') }}%
                        @else
                            —
                        @endif
                    </td>
                    <td class="r"><strong>${{ $money($item->subtotal) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTALES --}}
    <div class="totals avoid-break">
        <div class="left">
            {{-- Notas movidas al footer fijo --}}
        </div>

        <div class="right">
            <table class="totals-table">
                <tr>
                    <td class="k">Subtotal:</td>
                    <td class="v">${{ $money($cotizacion->subtotal) }}</td>
                </tr>
                @if (($cotizacion->descuento_general ?? 0) > 0)
                    <tr>
                        <td class="k">Descuento:</td>
                        <td class="v">- ${{ $money($cotizacion->descuento_general) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="k">IVA ({{ $configuracion->iva_porcentaje }}%):</td>
                    <td class="v">${{ $money($cotizacion->iva) }}</td>
                </tr>
            </table>
            <div class="grand">TOTAL &nbsp;&nbsp; ${{ $money($cotizacion->total) }}</div>
        </div>
    </div>

    {{-- FOOTER FIJO CON NOTAS, DATOS BANCARIOS Y FIRMA --}}
    <div class="fixed-footer">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-title">📝 Notas de la Cotización</div>
                @if ($cotizacion->notas)
                    <p class="footer-text">{{ Str::limit($cotizacion->notas, 250) }}</p>
                @else
                    <p class="footer-text" style="color: #999; font-style: italic;">Sin notas adicionales</p>
                @endif
            </div>
            <div class="footer-col">
                <div class="footer-title">🏦 Información Bancaria</div>
                @if (
                    $configuracion->banco ||
                        $configuracion->sucursal ||
                        $configuracion->cuenta ||
                        $configuracion->clabe ||
                        $configuracion->titular)
                    <div class="bank-info">
                        @if ($configuracion->banco)
                            <div><span class="bank-label">Banco:</span> {{ $configuracion->banco }}</div>
                        @endif
                        @if ($configuracion->sucursal)
                            <div><span class="bank-label">Sucursal:</span> {{ $configuracion->sucursal }}</div>
                        @endif
                        @if ($configuracion->cuenta)
                            <div><span class="bank-label">Cuenta:</span> {{ $configuracion->cuenta }}</div>
                        @endif
                        @if ($configuracion->clabe)
                            <div><span class="bank-label">CLABE:</span> {{ $configuracion->clabe }}</div>
                        @endif
                        @if ($configuracion->titular)
                            <div><span class="bank-label">Titular:</span> {{ $configuracion->titular }}</div>
                        @endif
                    </div>
                @else
                    <p class="footer-text" style="color: #999; font-style: italic;">Datos bancarios no configurados</p>
                @endif
            </div>
            <div class="footer-col">
                <div class="footer-title">✍️ Autorización del Cliente</div>
                <div style="margin-top: 10px;">
                    <div style="border-top: 2px solid #333; width: 100%; margin-bottom: 4px; padding-top: 8px;"></div>
                    <p class="footer-text" style="margin: 0; font-weight: bold; font-size: 8px;">JESUS LOPEZ</p>
                    <p class="footer-text" style="margin: 0; font-size: 6px; color: #666;">Cliente que autoriza</p>
                    <p class="footer-text" style="margin: 2px 0 0 0; font-size: 6px; color: #666;">
                        {{ $cotizacion->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
