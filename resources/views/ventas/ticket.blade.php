<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta #{{ $venta->id }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            margin: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            width: 58mm;
            /* Ancho estándar de impresora térmica */
            max-width: 58mm;
            margin: 0 auto;
            padding: 2mm;
            font-size: 11px;
            color: #000;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 3px 0;
            width: 100%;
        }

        table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
            /* Fuerza los anchos de columna fijos */
        }

        th,
        td {
            padding: 2px 0;
            vertical-align: top;
            word-wrap: break-word;
            /* Evita que las palabras largas desborden */
        }

        .col-qty {
            width: 15%;
            text-align: left;
        }

        .col-desc {
            width: 55%;
            text-align: left;
            padding-right: 2px;
        }

        .col-total {
            width: 30%;
            text-align: right;
        }

        .totals {
            margin-top: 5px;
            text-align: right;
            font-size: 11px;
            width: 100%;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            width: 100%;
        }

        .ticket {
            width: 100%;
            overflow: hidden;
        }

        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .ticket {
                padding: 2mm;
            }
        }
    </style>
</head>

<body>
    <div class="ticket">
        <h2 style="font-size: 14px; margin-bottom: 2px;">{{ 'AF Nutrition' }}</h2>
        <div class="text-center" style="font-size: 10px; margin-bottom: 5px;">
            Ticket: #{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}<br>
            Cajero: {{ $venta->user->name ?? 'Usuario' }}<br>
            Cliente: {{ $venta->cliente_nombre ?? 'Público en General' }}<br>
            Fecha: {{ $venta->created_at->format('d/m/Y H:i') }}
        </div>

        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th class="col-qty">Cant</th>
                    <th class="col-desc">Descripción</th>
                    <th class="col-total">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $detalle)
                    <tr>
                        <td class="col-qty">{{ $detalle->cantidad }}</td>
                        <td class="col-desc">
                            {{ subStr($detalle->productAlmacen->nombre ?? 'Producto', 0, 15) }}
                            @if($detalle->descuento_porcentaje > 0)
                                <br><small>DESC {{ $detalle->descuento_porcentaje }}%</small>
                            @endif
                        </td>
                        <td class="col-total">${{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="totals" style="margin-bottom: 5px;">
            <div class="totals-row font-bold" style="font-size: 13px;">
                <span>TOTAL:</span>
                <span>${{ number_format($venta->total, 2) }}</span>
            </div>

            @if($venta->metodo_pago == 'EFECTIVO')
                <div class="totals-row">
                    <span>Efectivo:</span>
                    <span>${{ number_format($pago_recibido, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>Cambio:</span>
                    <span>${{ number_format($cambio_entregado, 2) }}</span>
                </div>
            @else
                <div class="totals-row">
                    <span>Pago:</span>
                    <span>{{ $venta->metodo_pago }}</span>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        <div class="text-center" style="margin-top: 5px;">
            <p>¡Gracias por su compra!</p>
        </div>
    </div>

    <script>
        window.onload = function () {
            window.print();
        }
    </script>
</body>

</html>