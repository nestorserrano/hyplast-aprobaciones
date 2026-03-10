<head>
    <title>
        {!! trans('hyplast.showing-requisition', ['id' => $requisition->id]) !!}
    </title>
    <style>
        @page {
            margin: 0cm 0cm;
            font-family: Arial;
        }


        body {
            margin: 3.5cm 2cm 3cm;
        }

        header {
            position: fixed;
            top: 1cm;
            left: 2cm;
            right: 0cm;
            height: 2cm;
            color: rgb(0, 0, 0);
            text-align: center;
            line-height: 5px;

        }

        footer {
            position: fixed;
            bottom: 3cm;
            left: 0cm;
            right: 2cm;
            height: auto;
            color: #000;
            text-align: right;
            font-size: 10px;
        }

        footer .pagenum:before {
            content: counter(page);
        }

        footer .pagecount:before {
            content: counter(page);
        }


        .table-striped>thead>tr>th {
            background-color: #19181a71;
            color: #ffffff;
        }

</style>
</head>

<header>
        <table style="width:90%;"  class="table table-striped">
            <tr>
                <td style="border: 1px solid black; width:20%;" align='center'> <img style="width: 120px; align:center;" src="images/logo250x133.png" alt="Logo Hyplast"></td>
                <td style="border: 1px solid black; width:50%;" align='center' NOWRAP>
                    <h3>PLANIFICACION DE LA PRODUCCION</h3>
                    <h4>ORDEN DE PRODUCCIÓN</h4>
                </td>
                <td style="border: 1px solid black; width:20%;">
                    <p>Fecha: {{\Carbon\Carbon::now()->format('d - m - Y')}}</p>
                </td>
            </tr>
        </table>
</header>
<main>
    <div class="container mt-3">

            @if($requisition->codebar)
            <div style="text-align: center; margin-bottom: 15px;">
                <div style="display: inline-block; text-align: center; border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9;">
                    <p style="margin: 0 0 5px 0; font-size: 11px; font-weight: bold;">OP-{{ str_pad($requisition->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($requisition->codebar, 'C128', 2, 50) }}" alt="barcode" style="max-width: 250px; height: auto;" />
                    <p style="margin: 5px 0 0 0; font-size: 9px; color: #333;">{{ $requisition->codebar }}</p>
                </div>
            </div>
            @endif

            <table style="width:100%;"  class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width:15%" align='center'>
                            Orden
                        </th>
                        <th style="width:65%"  align='left'>
                            <strong>{!! Form::label('client', trans('forms.create_requisition_label_client')); !!}</strong>
                        </th>
                        <th style="width:15%" align='center'>
                            <strong>{!! Form::label('status', trans('forms.create_requisition_label_status')); !!}</strong>
                        </th>
                        <th style="width:15%" align='center'>
                            <strong>{!! Form::label('create_at', trans('forms.requisition_label_create_at')); !!}</strong>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td align='center'><strong>{{$requisition->id}}</strong></td>
                        <td>{{$requisition->client_name ?? 'N/A' }}</td>
                        <td align='center'>{{ $requisition->status_name ?? 'N/A'}}</td>
                        <td align='center'>{{ \Carbon\Carbon::parse($requisition->created_at)->format('d-m-Y') }} </td>
                    </tr>
                </tbody>
            </table>
             <br>
            <strong>ORDEN DE PRODUCCIÓN</strong>
            <br>
            <table style="width:100%;"  class="table table-striped" >

                <thead class="thead">
                    <tr>
                        <th align='center'>
                            {!! Form::label('requested', 'Requerido'); !!}
                        </th>
                        <th align='center'>
                            {!! Form::label('weight', 'Peso Bulto'); !!}
                        </th>
                        <th align='center'>
                            {!! Form::label('total_weight', 'Total Peso Bultos'); !!}
                        </th>
                        <th align='center'>
                            {!! Form::label('total_weight', 'Total Peso Bobinas'); !!}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width:20%;" align='center'>{{ $requisition->requested }} bultos</td>
                        <td style="width:20%;" align='center'>{{ number_format($requisition->net_weight, 2, '.', ',') }} Kg  </td>
                        <td style="width:30%;" align='center'>{{ number_format($requisition->net_weight *  $requisition->requested, 2, '.', ',') }} Kg</td>
                        <td style="width:30%;" align='center'>{{ number_format($requisition->total_weight, 2, '.', ',') }}Kg </td>
                    </tr>
                </tbody>
            </table>

            <br>
            <strong>INFORMACIÓN DE LA LÁMINA</strong>
            <br>
            <table style="width:100%;"  class="table table-striped" >
                <thead class="thead">
                    <tr>
                        <th align='center'>
                            {!! Form::label('lamina_code', 'Código de Lámina'); !!}
                        </th>
                        <th align='center'>
                            {!! Form::label('lamina_desc', 'Descripción'); !!}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width:30%;" align='center'>
                            <strong>{{ $requisition->product_code ?? 'N/A' }}</strong>
                        </td>
                        <td style="width:70%;" align='left'>
                            {{ $requisition->lamina_description ?? 'N/A' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <strong>INFORMACIÓN DEL PRODUCTO A FABRICAR</strong>
            <br>
            <table style="width:100%;"  class="table table-striped" >

                <thead class="thead">
                    <tr>
                        <th style="width:80%"  align='left'>
                            <strong>{!! Form::label('product', trans('forms.create_requisition_label_product')); !!}</strong>
                        </th>
                        <th>
                            <strong>{!! Form::label('date_limit', trans('forms.requisition_label_date_limit')); !!}</strong>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{$requisition->product_id}} - {{$requisition->product_name}}</td>
                        <td align='center'>
                            @if(( is_null($requisition->date_limit)))
                                N/D
                            @else
                                {{\Carbon\Carbon::parse($requisition->date_limit)->format('d-m-Y')}}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <table style="width:100%;"  class="table table-striped" >

                <thead class="thead">
                    <tr>
                        <th align='center'>
                            Onzas
                        </th>
                        <th align='center'>
                            Diámetro
                        </th>
                        <th align='center'>
                            Nave
                        </th>
                        <th align='center'>
                            Máquina
                        </th>
                        <th align='center'>
                            Cavidades
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td align='center'>
                            {{ $requisition->U_ONZAS ?? 'N/A' }}
                        </td>
                        <td align='center'>
                            @if($requisition->U_DIAMETRO)
                                {{ $requisition->U_DIAMETRO }} mm
                            @else
                                N/A
                            @endif
                        </td>
                        <td align='center'>
                            {{ $requisition->U_NAVE ?? 'N/A' }}
                        </td>
                        <td align='center'>
                            {{ $requisition->U_MAQUINA ?? 'N/A' }}
                        </td>
                        <td align='center'>
                            {{ $requisition->U_CAVIDADES ? number_format($requisition->U_CAVIDADES, 0, '.', ',') : 'N/A' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <table style="width:100%;"  class="table table-striped" >

                <thead class="thead">
                    <tr>
                        <th align='center'>
                            {!! Form::label('caja_code', 'Código de Caja'); !!}
                        </th>
                        <th align='center'>
                            {!! Form::label('requested', 'Tamaño de la Caja'); !!}
                        </th>
                        <th align='center'>
                            {!! Form::label('manufacured', 'Tipo de Material'); !!}
                        </th>
                        <th align='center'>
                            {!! Form::label('missing', 'Color'); !!}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width:15%;" align='center'>
                            {{ $requisition->U_CAJA ?? 'N/A' }}
                        </td>
                        <td style="width:20%;" align='center'>
                            {{ $requisition->U_CAJA_MED ?? 'Sin Información' }}
                        </td>
                        <td style="width:20%;" align='center'>{{ $requisition->material_name ?? 'N/A' }} </td>
                        <td style="width:20%;" align='center'>{{ $requisition->color_name ?? 'N/A' }} </td>
                    </tr>
                </tbody>
            </table>
            <br>

            <strong>INFORMACIÓN DE EMPAQUE Y PALETIZADO</strong>
            <br>
            <table style="width:100%;"  class="table table-striped" >
                <thead class="thead">
                    <tr>
                        <th align='center'>Cantidad por Paquete</th>
                        <th align='center'>Paquetes por Caja</th>
                        <th align='center'>Unidades por Caja</th>
                        <th align='center'>Cajas por Camada</th>
                        <th align='center'>Camadas por Paleta</th>
                        <th align='center'>Cajas por Paleta</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td align='center'>{{ $requisition->U_CANT_PAQ ? number_format($requisition->U_CANT_PAQ, 0, '.', ',') : 'N/A' }}</td>
                        <td align='center'>{{ $requisition->U_PAQ_CAJA ? number_format($requisition->U_PAQ_CAJA, 0, '.', ',') : 'N/A' }}</td>
                        <td align='center'>{{ $requisition->U_UNID_CAJA ? number_format($requisition->U_UNID_CAJA, 0, '.', ',') : 'N/A' }}</td>
                        <td align='center'>{{ $requisition->U_CAJAS_CAMADA ? number_format($requisition->U_CAJAS_CAMADA, 0, '.', ',') : 'N/A' }}</td>
                        <td align='center'>{{ $requisition->U_CAMADA_PALETA ? number_format($requisition->U_CAMADA_PALETA, 0, '.', ',') : 'N/A' }}</td>
                        <td align='center'>{{ $requisition->U_CAJAS_PALETA ? number_format($requisition->U_CAJAS_PALETA, 0, '.', ',') : 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>

        <br>
        <strong>INSUMOS REQUERIDOS</strong>
        <br>
        <table style="width:100%;"  class="table table-striped" >

            <thead class="thead">
                <tr>
                    <tr>

                        <th align='center'>
                            Código
                        </th>
                        <th align='center'>
                            Descripción
                        </th>
                        <th align='center'>
                            Cantidad
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requisition->supplies as $reqsupplie)
                        <tr>
                            <td style="width:15%;" align='center'>
                                {{ $reqsupplie->ARTICULO}}
                            </td>
                            <td style="width:50%;" align='justify'>
                                {{ $reqsupplie->DESCRIPCION}}
                            </td>
                            <td style="width:5%;" align='right'>
                                {{ number_format($reqsupplie->quantity, 2, '.', ',') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <br>

    <table style="width:100%;" class="table table-striped">
        <thead class="thead">
            <tr>
                <th colspan="2" style="background-color: #19181a71; color: #ffffff; text-align: center; padding: 8px;">
                    <strong>DISTRIBUCIÓN DEL PRODUCTO TERMINADO</strong>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width:50%; padding: 10px; text-align: justify; vertical-align: top;">
                    <strong>Empaquetado:</strong> Cada bulto tendrá {{ number_format($requisition->U_UNID_CAJA ?? 0, 0, '.', ',') }} unidades divididas en {{ number_format($requisition->U_PAQ_CAJA ?? 0, 0, '.', ',') }} paquetes de {{ number_format($requisition->U_CANT_PAQ ?? 0, 0, '.', ',') }} unidades cada uno.
                </td>
                <td style="width:50%; padding: 10px; text-align: justify; vertical-align: top;">
                    <strong>Estiba:</strong> cada piso de la estiba estará compuesto de {{ number_format($requisition->U_CAJAS_CAMADA ?? 0, 0, '.', ',') }} bultos, serían {{ number_format($requisition->U_CAMADA_PALETA ?? 0, 0, '.', ',') }} pisos por estiba y el total de bultos serían de {{ number_format($requisition->U_CAJAS_PALETA ?? 0, 0, '.', ',') }}.
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Firmas -->
    <div style="margin-top: 40px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <!-- Columna 1: Elaborado por -->
                <td style="width: 18%; padding: 5px; text-align: center; vertical-align: top;">
                    <strong style="font-size: 11px;">Elaborado por</strong>
                    <div style="margin-top: 50px; border-top: 1px solid #000; padding-top: 3px;">
                        <p style="margin: 0; font-size: 10px;">{{ $requisition->user_first_name ?? '' }} {{ $requisition->user_last_name ?? '' }}</p>
                        <p style="margin: 0; font-size: 9px;">{{ \Carbon\Carbon::parse($requisition->created_at)->format('d/m/Y') }}</p>
                    </div>
                </td>

                <!-- Columna 2: Vacía (separación) -->
                <td style="width: 5%;"></td>

                <!-- Columna 3: Aprobado por -->
                <td style="width: 18%; padding: 5px; text-align: center; vertical-align: top;">
                    <strong style="font-size: 11px;">Aprobado por</strong>
                    <div style="margin-top: 50px; border-top: 1px solid #000; padding-top: 3px;">
                        @if($requisition->approved_by)
                            <p style="margin: 0; font-size: 10px;">{{ ($requisition->approver_first_name ?? '') . ' ' . ($requisition->approver_last_name ?? '') }}</p>
                            <p style="margin: 0; font-size: 9px;">{{ $requisition->approved_at ? \Carbon\Carbon::parse($requisition->approved_at)->format('d/m/Y') : '' }}</p>
                        @else
                            <p style="margin: 0; font-size: 10px;">&nbsp;</p>
                            <p style="margin: 0; font-size: 9px;">&nbsp;</p>
                        @endif
                    </div>
                </td>

                <!-- Columna 4: Vacía (separación) -->
                <td style="width: 5%;"></td>

                <!-- Columna 5: Recibido por -->
                <td style="width: 18%; padding: 5px; text-align: center; vertical-align: top;">
                    <strong style="font-size: 11px;">Recibido por</strong>
                    <div style="margin-top: 50px; border-top: 1px solid #000; padding-top: 3px;">
                        <p style="margin: 0; font-size: 10px;">&nbsp;</p>
                        <p style="margin: 0; font-size: 9px;">Fecha: _______________</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</main>

<footer></footer>

<script type="text/php">
    if (isset($pdf)) {
        $fecha = date('d/m/Y h:i A');
        $text = $fecha . " | Página {PAGE_NUM} de {PAGE_COUNT}";
        $size = 10;
        $font = $fontMetrics->getFont("Arial");
        // CONFIGURACIÓN ESTANDARIZADA: Posición del footer alineada a la derecha
        // X=400px es la posición óptima para alineación derecha en documentos tamaño carta
        $x = 400;
        $y = $pdf->get_height() - 85;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>
</body>
</html>


