@extends('adminlte::page')


@section('template_title')
  {!! trans('hyplast.showing-requisition', ['id' => $requisition->id]) !!}
@endsection

@section('content')

<div class="container">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card card-info">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        {!! trans('hyplast.showing-requisition', ['id' => $requisition->id]) !!}
                        @if ($requisition->status_id == 1)
                            @php $badgeClass = 'warning' @endphp
                        @elseif ($requisition->status_id == 2)
                            @php $badgeClass = 'success' @endphp
                        @elseif ($requisition->status_id == 3)
                            @php $badgeClass = 'success' @endphp
                        @elseif ($requisition->status_id == 4)
                            @php $badgeClass = 'danger' @endphp
                        @elseif ($requisition->status_id == 5)
                            @php $badgeClass = 'danger' @endphp
                        @elseif ($requisition->status_id == 6)
                            @php $badgeClass = 'danger' @endphp
                        @else
                            @php $badgeClass = 'warning' @endphp
                        @endif
                        @if ($requisition->finished == true)
                            @php $message = 'Cerrada por ' @endphp
                        @else
                            @php $message = '' @endphp
                        @endif
                        <span class="badge badge-{{$badgeClass}}">{{ $message . $requisition->status_name }}</span>
                        @can('requisitions.approve')
                            @if($requisition->approved_by)
                                <span class="badge badge-success ml-2">
                                    <i class="fas fa-check-circle"></i> Aprobada
                                </span>
                            @endif
                        @endcan
                        <div class="pull-right">
                            <a href="{{ route('requisitions') }}" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="left" title="{{ trans('hyplast.tooltips.back-products') }}">
                                <i class="fas fa-fw fa-reply-all" aria-hidden="true"></i>
                                {!! trans('hyplast.buttons.back-to-requisitions') !!}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="position: relative;">
                    {!! Form::open(array('route' => 'requisitions', 'method' => 'POST', 'role' => 'form', 'class' => 'needs-validation')) !!}
                    {!! csrf_field() !!}

                    @if($requisition->codebar)
                    <div style="position: absolute; top: 10px; right: 20px; z-index: 10;">
                        <svg id="barcode-{{ $requisition->id }}"></svg>
                    </div>
                    @endif

                    <div class="row align-items-center">
                        <div class="col-sm-12 align-self-center">
                            <div class="form-group has-feedback row {{ $errors->has('client') ? ' has-error ' : '' }}">
                                {!! Form::label('client', trans('forms.create_requisition_label_client'), array('class' => 'col-md-2 control-label')); !!}
                                <div class="col-md-9">
                                    <div class="input-group">
                                        {{$requisition->client_name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-sm-6 align-self-center">
                            <div class="form-group has-feedback row {{ $errors->has('date_limit') ? ' has-error ' : '' }}">
                                {!! Form::label('product', trans('forms.requisition_label_date_limit'), array('class' => 'col-md-4 control-label')); !!}
                                <div class="col-md-8">
                                    <div class="input-group">
                                        {{$requisition->date_limit}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 align-self-center">
                            <div class="form-group has-feedback row {{ $errors->has('create_at') ? ' has-error ' : '' }}">
                                {!! Form::label('client', trans('forms.requisition_label_create_at'), array('class' => 'col-md-3 control-label')); !!}
                                <div class="col-md-9">
                                    <div class="input-group">
                                        {{$requisition->created_at }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-sm-6 align-self-center">
                            <div class="form-group has-feedback row {{ $errors->has('client') ? ' has-error ' : '' }}">
                                {!! Form::label('client', trans('forms.requisition_label_date_fabrication_init'), array('class' => 'col-md-4 control-label')); !!}
                                <div class="col-md-8">
                                    <div class="input-group">
                                        {{$requisition->date_fabrication_init }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 align-self-center">
                            <div class="form-group has-feedback row {{ $errors->has('product') ? ' has-error ' : '' }}">
                                {!! Form::label('product', trans('forms.requisition_label_date_fabrication_finish'), array('class' => 'col-md-3 control-label')); !!}
                                <div class="col-md-9">
                                    <div class="input-group">
                                        {{$requisition->date_fabrication_finish}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ORDEN DE PRODUCCIÓN --}}
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-industry"></i> ORDEN DE PRODUCCIÓN</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">Requerido</th>
                                    <th class="text-center">Peso Bulto</th>
                                    <th class="text-center">Total Peso Bultos</th>
                                    <th class="text-center">Total Peso Bobinas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">{{ number_format($requisition->requested ?? 0) }} bultos</td>
                                    <td class="text-center">
                                        @php
                                            // Peso Bulto = peso_neto del producto
                                            $pesoBulto = $requisition->product_peso_neto ?? 0;
                                        @endphp
                                        @if($pesoBulto > 0)
                                            {{ number_format($pesoBulto, 2) }} Kg
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            // Total Peso Bultos = Requerido × Peso Bulto
                                            $totalPesoBultos = ($requisition->requested ?? 0) * $pesoBulto;
                                        @endphp
                                        {{ number_format($totalPesoBultos, 2) }} Kg
                                    </td>
                                    <td class="text-center">{{ number_format($requisition->total_weight ?? 0, 2) }} Kg</td>
                                </tr>
                            </tbody>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div class="card card-info">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; ">
                        Rollos
                    </div>
                </div>

                <div class="card-body">
                    @php
                        // Obtener el código de bobina del primer extruder si existe
                        $coilInfo = $extruders->first();
                    @endphp
                    @if($coilInfo && $coilInfo->coil_code)
                    <div class="row align-items-center mb-3">
                        <div class="col-sm-12">
                            <strong>Bobina:</strong> {{ $coilInfo->coil_code }} - {{ $coilInfo->coil_name ?? 'N/A' }}
                        </div>
                    </div>
                    @endif

                    <div class="row align-items-center">
                        <div class="col-sm-12">
                            <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Material</th>
                                        <th class="text-center">Color</th>
                                        <th class="text-center">Ancho (cm)</th>
                                        <th class="text-center">Calibre</th>
                                        <th class="text-center">Fecha Creación</th>
                                        <th class="text-center">Fecha Finalización</th>
                                        <th class="text-center">Peso (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalRollos = 0;
                                        $totalPeso = 0;
                                        $totalFinalizados = 0;
                                    @endphp
                                    @forelse($extruders as $extruder)
                                        @php
                                            $totalRollos++;
                                            $totalPeso += $extruder->weight ?? 0;
                                            if($extruder->finished) {
                                                $totalFinalizados++;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $extruder->id }}</td>
                                            <td class="text-center">
                                                @if($extruder->finished)
                                                    <span class="badge badge-success"><i class="fas fa-check"></i> Listo</span>
                                                @else
                                                    <span class="badge badge-warning"><i class="fas fa-cog fa-spin"></i> Fabricando</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $extruder->material_name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $extruder->color_name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ number_format($extruder->width_finish ?? 0, 2) }}</td>
                                            <td class="text-center">{{ number_format($extruder->caliber ?? 0, 2) }}</td>
                                            <td class="text-center">{{ $extruder->date_create ? \Carbon\Carbon::parse($extruder->date_create)->format('d-m-Y H:i') : 'N/A' }}</td>
                                            <td class="text-center">{{ $extruder->ending ? \Carbon\Carbon::parse($extruder->ending)->format('d-m-Y H:i') : 'N/A' }}</td>
                                            <td class="text-center"><strong>{{ number_format($extruder->weight ?? 0, 2) }}</strong></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No hay rollos fabricados para esta requisición</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($totalRollos > 0)
                                <tfoot class="thead-light">
                                    <tr>
                                        <td colspan="8" class="text-right"><strong>TOTALES:</strong></td>
                                        <td class="text-center"><strong>{{ number_format($totalPeso, 2) }} kg</strong></td>
                                    </tr>
                                    <tr class="table-info">
                                        <td colspan="9" class="text-center">
                                            <strong>Rollos: {{ $totalRollos }}</strong> ({{ $totalFinalizados }} Listos, {{ $totalRollos - $totalFinalizados }} Fabricando) |
                                            <strong>Requerido: {{ number_format($requisition->total_weight ?? 0, 2) }} kg</strong> |
                                            <strong>Faltante: {{ number_format(($requisition->total_weight ?? 0) - $totalPeso, 2) }} kg</strong> |
                                            @if(($requisition->total_weight ?? 0) > 0)
                                                <strong>Efectividad: {{ number_format(($totalPeso * 100) / $requisition->total_weight, 2) }}%</strong> |
                                            @else
                                                <strong>Efectividad: 0%</strong> |
                                            @endif
                                            @if (!IS_NULL($requisition->date_fabrication_init))
                                                @if (IS_NULL($requisition->date_fabrication_finish))
                                                    <strong>Transcurrido: {{ \Carbon\Carbon::parse($requisition->date_fabrication_init)->diffForHumans(null, false, false, 3) }}</strong>
                                                @else
                                                    <strong>Finalizado hace: {{ \Carbon\Carbon::parse($requisition->date_fabrication_finish)->diffForHumans(null, false, false, 3) }}</strong>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card card-info">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; ">
                        {!! trans('forms.requisitions_title_feature') !!}
                    </div>
                </div>

                <div class="card-body">
                    @php
                        // Obtener el código del producto final del primer storage si existe
                        $storageInfo = $storages->first();
                    @endphp
                    <div class="row align-items-center mb-3">
                        <div class="col-sm-12">
                            @if($storageInfo && $storageInfo->product_code)
                                <strong>Producto:</strong> {{ $storageInfo->product_code }} - {{ $storageInfo->product_name ?? 'N/A' }}
                            @else
                                <strong>Producto:</strong> {{$requisition->product_code ?? 'N/A'}} - {{$requisition->product_name ?? 'N/A'}}
                            @endif
                        </div>
                    </div>


                    <div class="row align-items-center">
                        <div class="col-sm-12">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center">Lote</th>
                                            <th class="text-center">Paletas</th>
                                            <th class="text-center">Fecha Producción</th>
                                            <th class="text-center">Fecha Almacenamiento</th>
                                            <th class="text-center">Usuario Producción</th>
                                            <th class="text-center">Peso Total (kg)</th>
                                            <th class="text-center">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalBultos = 0;
                                            $totalPesoBultos = 0;
                                            $totalCantidad = 0;
                                            $totalPaletas = 0;
                                        @endphp
                                        @forelse($storages as $storage)
                                            @php
                                                $totalBultos++;
                                                $totalPesoBultos += $storage->total_weight ?? 0;
                                                $totalCantidad += $storage->quantity ?? 0;
                                                $paletas = ($storage->boxes_per_pallet && $storage->boxes_per_pallet > 0)
                                                    ? ($storage->quantity / $storage->boxes_per_pallet)
                                                    : 0;
                                                $totalPaletas += $paletas;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $storage->id }}</td>
                                                <td class="text-center">{{ $storage->batch ?? 'N/A' }}</td>
                                                <td class="text-center">{{ number_format($paletas, 2) }}</td>
                                                <td class="text-center">{{ $storage->date_production ? \Carbon\Carbon::parse($storage->date_production)->format('d-m-Y H:i') : 'N/A' }}</td>
                                                <td class="text-center">{{ $storage->date_storage ? \Carbon\Carbon::parse($storage->date_storage)->format('d-m-Y H:i') : 'N/A' }}</td>
                                                <td class="text-center">{{ $storage->user_production_name ?? 'N/A' }}</td>
                                                <td class="text-center"><strong>{{ number_format($storage->total_weight ?? 0, 2) }}</strong></td>
                                                <td class="text-center"><strong>{{ $storage->quantity ?? 0 }}</strong></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No hay bultos fabricados para esta requisición</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($totalBultos > 0)
                                    <tfoot class="thead-light">
                                        <tr>
                                            <td colspan="6" class="text-right"><strong>TOTALES:</strong></td>
                                            <td class="text-center"><strong>{{ number_format($totalPesoBultos, 2) }} kg</strong></td>
                                            <td class="text-center"><strong>{{ $totalCantidad }}</strong></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td colspan="8" class="text-center">
                                                <strong>Paletas: {{ number_format($totalPaletas, 2) }}</strong> |
                                                <strong>Cantidad Total: {{ $totalCantidad }}</strong> |
                                                <strong>Requerido: {{ $requisition->requested ?? 0 }}</strong> |
                                                <strong>Faltante: {{ ($requisition->requested ?? 0) - $totalCantidad }}</strong> |
                                                @if($requisition->requested > 0)
                                                    <strong>Efectividad: {{ number_format(($totalCantidad * 100) / $requisition->requested, 2) }}%</strong>
                                                @else
                                                    <strong>Efectividad: 0%</strong>
                                                @endif
                                            </td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        {!! trans('hyplast.create-new-formulation') !!}
                    </div>
                </div>

                <div class="card-body">

                    <div class="row align-items-center">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>{!! trans('hyplast.machines-table.id') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.code') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.name') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.required') !!}</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    @foreach ($supplies->supplies as $reqsupplie)
                                        <tr>
                                            <td align="center">{{ $reqsupplie->id}}</td>
                                            <td align="center" style="text-align: center; font-weight: bold;" >{{ $reqsupplie->ARTICULO}}</td>
                                            <td>{{ $reqsupplie->DESCRIPCION}}</td>
                                            <td align="center" style="color:red; text-align: center; font-weight: bold;">{{ number_format($reqsupplie->quantity, 2, '.', ',') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </form>
                </div>
            </div>

            <!-- Botones de acción al final -->
            <div class="row justify-content-center mt-4 mb-3">
                <div class="col-md-10">
                    <div class="d-flex justify-content-center align-items-center flex-wrap">
                            <!-- Botón Aprobar (solo si tiene permiso y no está aprobada) -->
                            @can('requisitions.approve')
                                @if(!$requisition->approved_by)
                                    <button type="button" class="btn btn-success mb-2 mr-2" onclick="approveRequisition({{ $requisition->id }})" title="Aprobar Orden de Producción">
                                        <i class="fas fa-check-circle"></i> Aprobar OP
                                    </button>
                                @endif
                            @endcan

                            <!-- Botón Imprimir -->
                            @role('admin|extrudersupervisor|extruderleader|manager')
                                <a class="btn btn-warning mb-2 mr-2" href="{{ URL::to('requisitions/print/' . $requisition->id)}}" target="_blank" title="Imprimir Orden">
                                    <i class="fas fa-print"></i> Imprimir
                                </a>
                            @endrole

                            <!-- Botón Editar -->
                            @role('admin|extrudersupervisor|manager')
                                @if($requisition->finished == false)
                                    @if($requisition->date_fabrication_init || $requisition->date_fabrication_finish)
                                        <a class="btn btn-info mb-2 mr-2" href="" onclick="noedit()" data-toggle="tooltip" title="Edit">
                                            {!! trans('hyplast.buttons.edit-disable') !!}
                                        </a>
                                    @else
                                        <a class="btn btn-info mb-2 mr-2" href="{{ URL::to('requisitions/' . $requisition->id . '/edit') }}" data-toggle="tooltip" title="Edit">
                                            {!! trans('hyplast.buttons.edit') !!}
                                        </a>
                                    @endif
                                @else
                                    <a class="btn btn-info mb-2 mr-2" href="" onclick="noedit2()" data-toggle="tooltip" title="Edit">
                                        {!! trans('hyplast.buttons.edit-disable') !!}
                                    </a>
                                @endif
                            @endrole

                            <!-- Botón Eliminar (solo admin|extrudersupervisor, si no ha iniciado y no está aprobada) -->
                            @role('admin|extrudersupervisor|manager')
                                @if(!$requisition->date_fabrication_init && !$requisition->approved_by)
                                    <button class="btn btn-danger mb-2 mr-2" onclick="deleteConfirmation({{ $requisition->id }})" title="Eliminar">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                @endif

                                <!-- Botón Cerrar -->
                                <button class="btn btn-dark mb-2 mr-2" onclick="closeRequisition({{ $requisition->id }})" title="Cerrar Orden Actual">
                                    <i class="fas fa-times-circle"></i> Cerrar Orden Actual
                                </button>
                            @endrole

                            <!-- Botón Regresar -->
                            <a href="{{ route('requisitions') }}" class="btn btn-secondary mb-2" title="{!! trans('hyplast.tooltips.back-products') !!}">
                                <i class="fas fa-reply-all"></i> {!! trans('hyplast.buttons.back-to-requisitions') !!}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

    </div>
</div>

@endsection

@section('footer_scripts')

  @if(config('machine.tooltipsEnabled'))
    @include('scripts.tooltips')
  @endif

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    @include('partials.requisitions-approve-script')

    <script type="text/javascript">
        // Generar código de barras
        @if($requisition->codebar)
        JsBarcode("#barcode-{{ $requisition->id }}", "{{ $requisition->codebar }}", {
            format: "CODE128",
            width: 1.5,
            height: 45,
            displayValue: true,
            fontSize: 12,
            margin: 0
        });
        @endif
    </script>

    <script type="text/javascript">
        function deleteConfirmation(id) {
            swal({
                title: "Eliminar?",
                text: "Por Favor, asegurese y luego Confirme!",
                type: "warning",
                showCancelButton: !0,
                confirmButtonText: "Si, Eliminar Registro!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: !0
            }).then(function (e) {
                if (e.value === true) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        type: 'POST',
                        url: "{{url('/requisition/delete')}}/" + id,
                        data: {_token: CSRF_TOKEN},
                        dataType: 'JSON',
                        success: function (results) {
                            if (results.success === true) {
                                swal("Done!", results.message, "success");
                                window.location = "/requisitions";
                            } else {
                                swal("Error!", results.message, "error");
                            }
                        }
                    });
                } else {
                    e.dismiss;
                }
            }, function (dismiss) {
                return false;
            })
        }
        function noedit() {
            swal({
                title: "No puede Editar",
                text: "No puede editar esta Orden ya que tiene un inicio de producción!",
                type: "error",
                showConfirmButton: true,
                confirmButtonText: "Aceptar",
                position: 'center',
                toast: false,
            });
        }
        function noedit2() {
            swal({
                title: "No puede Editar",
                text: "Esta orden se encuentra cerrada, no puede editarla!",
                type: "error",
                showConfirmButton: true,
                confirmButtonText: "Aceptar",
                position: 'center',
                toast: false,
            });
        }

        function closeRequisition(id) {
            swal({
                title: "Cerrar o Cancelar?",
                html:
                    '<div class="row">' +
                    '<div class="col-md-12">' +
                    '<div class="form-group">' +
                    '<label for="cancel_requisition">Motivo de Cierre o Cancelación</label>' +
                    '<select id="cancel_requisition" class="form-control" style="width: 100%;">' +
                    '<option value="0" disabled selected>Seleccione un Motivo</option>' +
                    '<option value="4">Finalización Manual</option>' +
                    '<option value="5">Retiro de la Orden</option>' +
                    '<option value="6">Falta de Materia Prima</option>' +
                    '</select>' +
                    '</div>' +
                    '</div>' +
                    '</div>',
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, Cerrar o Cancelar esta Orden!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: true
            }).then(function (e) {
                if (e.value === true) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    var cancel_requisition = document.getElementById("cancel_requisition").value;
                    if (cancel_requisition == 0) {
                        swal({
                            title: "Seleccione un Motivo",
                            text: "Por favor, seleccione un motivo para el cierre de la orden. Intente de nuevo!",
                            type: "warning",
                        });
                        return;
                    }
                    $.ajax({
                        type: 'POST',
                        url: "{{url('cancelreq')}}/" + id + "/" + cancel_requisition,
                        data: {_token: CSRF_TOKEN},
                        dataType: 'JSON',
                        success: function (results) {
                            if (results.success === true) {
                                swal("¡Cerrada!", results.message, "success").then(function() {
                                    window.location = "/requisitions";
                                });
                            } else {
                                swal("Error!", results.message, "error");
                            }
                        },
                        error: function (xhr, status, error) {
                            swal("Error", "No se pudo cerrar la orden", "error");
                        }
                    });
                }
            }, function (dismiss) {
                return false;
            });
        }

    </script>
@endsection
