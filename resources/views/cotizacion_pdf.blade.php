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
            /* Centrado del nombre de la empresa */
        }

        /* Barra de cotización: UNA sola línea
           Izquierda: Cotización
           Centro: Fecha (centrado real)
           Derecha: Validez
        */
        .cotizacion-bar {
            margin-top: 1.5mm;
            padding: 1.8mm 2mm;
            background: #f5f7fa;
            border: 1px solid #e6e9ef;
            border-radius: 4px;

            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            column-gap: 8mm;

            font-size: 9.5px;
            white-space: nowrap;
        }

        .cotizacion-left {
            justify-self: start;
        }

        .cotizacion-center {
            justify-self: center;
            text-align: center;
        }

        .cotizacion-right {
            justify-self: end;
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

        /* Totales ABAJO */
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

        .note {
            margin-top: 3mm;
            padding: 2mm 2.2mm;
            background: #fff7e0;
            border-left: 3px solid #ffc107;
            border-radius: 3px;
            font-size: 9px;
        }

        /* Firmas al final */
        .firmas-section {
            margin-top: 10mm;
        }

        .firmas-box {
            min-height: 22mm;
            display: flex;
            gap: 10mm;
            align-items: flex-end;
            justify-content: space-between;
        }

        .firma {
            width: 48%;
            border-top: 1px solid #999;
            padding-top: 1.5mm;
            text-align: center;
            font-size: 9px;
            color: #555;
        }

        .firma .label {
            display: block;
            font-weight: 700;
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

    {{-- BLOQUES INFO (Empresa, Cliente) - SIN FIRMAS ARRIBA --}}
    <div class="info avoid-break">
        <div class="col">
            <h3>Empresa</h3>
            <table class="kv">
                <tr>
                    <td class="k">Empresa:</td>
                    <td class="v">{{ $configuracion->nombre_empresa }}</td>
                </tr>
                @if ($configuracion->rfc)
                    <tr>
                        <td class="k">RFC:</td>
                        <td class="v">{{ $configuracion->rfc }}</td>
                    </tr>
                @endif
                @if ($configuracion->telefono)
                    <tr>
                        <td class="k">Tel:</td>
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
                    <td class="k">Cliente:</td>
                    <td class="v">{{ $cotizacion->cliente->nombre_razon_social }}</td>
                </tr>
                @if ($cotizacion->cliente->email)
                    <tr>
                        <td class="k">Email:</td>
                        <td class="v">{{ $cotizacion->cliente->email }}</td>
                    </tr>
                @endif
                @if ($cotizacion->cliente->telefono)
                    <tr>
                        <td class="k">Tel:</td>
                        <td class="v">{{ $cotizacion->cliente->telefono }}</td>
                    </tr>
                @endif
                @if ($cotizacion->cliente->rfc)
                    <tr>
                        <td class="k">RFC:</td>
                        <td class="v">{{ $cotizacion->cliente->rfc }}</td>
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

    {{-- TOTALES ABAJO + NOTAS --}}
    <div class="totals avoid-break">
        <div class="left">
            @if ($cotizacion->notas)
                <div class="note">
                    <strong>📝 Notas</strong><br>
                    {{ Str::limit($cotizacion->notas, 450) }}
                </div>
            @endif

            @if ($configuracion->pie_pagina_cotizaciones)
                <div class="note" style="background:#f5f7fa; border-left-color: {{ $HEX }};">
                    {!! nl2br(e(Str::limit($configuracion->pie_pagina_cotizaciones, 450))) !!}
                </div>
            @endif
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
                    <td class="k">IVA:</td>
                    <td class="v">${{ $money($cotizacion->iva) }}</td>
                </tr>
            </table>
            <div class="grand">TOTAL &nbsp;&nbsp; ${{ $money($cotizacion->total) }}</div>
        </div>
    </div>

    {{-- FIRMAS AL FINAL --}}
    <div class="firmas-section avoid-break">
        <div class="firmas-box">
            <div class="firma">
                <span class="label">Cliente</span>
                {{ Str::limit($cotizacion->cliente->nombre_razon_social, 40) }}
            </div>
            <div class="firma">
                <span class="label">Proveedor</span>
                {{ Str::limit($configuracion->nombre_empresa, 40) }}
            </div>
        </div>
    </div>

</body>

</html>
