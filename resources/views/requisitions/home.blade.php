@extends('adminlte::page')


@section('template_fastload_css')
@endsection

@section('template_title')
    {!! trans('hyplast.showing-all-requisitions') !!}
@endsection

@section('template_linked_css')
    @if(config('hyplast.enabledDatatablesJs'))
        <link rel="stylesheet" type="text/css" href="{{ config('hyplast.datatablesCssCDN') }}">
    @endif
    <style type="text/css" media="screen">
       .machine-table {
            border: 0;
        }
        .machine-table tr td:first-child {
            padding-left: 15px;
        }
        .machine-table tr td:last-child {
            padding-right: 15px;
        }
        .machine-table.table-responsive,
        .machine-table.table-responsive table {
            margin-bottom: 0;
        }

    </style>
@endsection


@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="card_title">
                                {!! trans('hyplast.showing-all-requisitions') !!}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        @role('admin|extrudersupervisor|manager')
                            <a class="btn btn-success" href="/requisitions/create">
                                {!! trans('hyplast.buttons.create-new4') !!}
                            </a>
                        @endrole
                        @role('admin|supervisornave|manager')
                            <a class="btn btn-info" type="button" id="btnNuevo" data-toggle="modal" data-target="#modalRegister" data-keyboard="false" data-backdrop="static" onclick="clearData()">
                                {!! trans('hyplast.buttons.create-new8') !!}
                            </a>
                        @endrole
                        @role('admin|supervisornave|manager')
                            <a class="btn btn-info" type="button" id="btnNuevo" data-toggle="modal" data-target="#modalConsume" data-keyboard="false" data-backdrop="static" onclick="clearData()">
                                {!! trans('hyplast.buttons.create-new7') !!}
                            </a>
                        @endrole
                            <a class="btn btn-success" href="{{route("requisitions.used")}}">
                                {!! trans('hyplast.buttons.finalized') !!}
                            </a>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="filter_orden">N° Orden</label>
                                <input type="text" class="form-control" id="filter_orden" placeholder="Buscar...">
                            </div>
                            <div class="col-md-2">
                                <label for="filter_cliente">Cliente</label>
                                <input type="text" class="form-control" id="filter_cliente" placeholder="Buscar...">
                            </div>
                            <div class="col-md-2">
                                <label for="filter_producto">Producto</label>
                                <input type="text" class="form-control" id="filter_producto" placeholder="Buscar...">
                            </div>
                            <div class="col-md-2">
                                <label for="filter_estado">Estado</label>
                                <select class="form-control" id="filter_estado">
                                    <option value="">Todos</option>
                                    <option value="1">Pendiente</option>
                                    <option value="2">En Proceso</option>
                                    <option value="3">En Proceso</option>
                                    <option value="4">Completada</option>
                                    <option value="5">Cancelada</option>
                                    <option value="6">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filter_fecha_desde">Fecha Desde</label>
                                <input type="date" class="form-control" id="filter_fecha_desde">
                            </div>
                            <div class="col-md-2">
                                <label for="filter_fecha_hasta">Fecha Hasta</label>
                                <input type="date" class="form-control" id="filter_fecha_hasta">
                            </div>
                        </div>

                        <div class="table-responsive machine-table">
                            <table id="data-table" class="table table-striped table-bordered table-sm data-table" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center">Orden</th>
                                        <th class="text-center">Cliente</th>
                                        <th class="text-center">Producto</th>
                                        <th class="text-center">Requerido</th>
                                        <th class="text-center">Fabricado</th>
                                        <th class="text-center">Fecha Inicio</th>
                                        <th class="text-center">Fecha Fin</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Aprobación</th>
                                        <th class="text-center no-search no-sort">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requisitions as $requisition)
                                        <tr>
                                            <td>{{$requisition->id}}</td>
                                            <td>{{ $requisition->client_name ?? 'N/A' }}</td>
                                            <td>{{ $requisition->product_name ?? 'N/A' }}</td>
                                            <td style="text-align: center">{{ $requisition->requested}}</td>
                                            <td style="text-align: center">{{ $requisition->manufactured }}</td>
                                            <td style="text-align: center">{{ $requisition->date_fabrication_init ?? '-' }}</td>
                                            <td style="text-align: center">{{ $requisition->date_fabrication_finish ?? '-' }}</td>
                                            <td style="text-align: center">
                                                @if ($requisition->status_id == 1)
                                                    <span class="badge badge-warning">{{ $requisition->status_name ?? 'Pendiente' }}</span>
                                                @elseif ($requisition->status_id == 2)
                                                    <span class="badge badge-info">{{ $requisition->status_name ?? 'En Proceso' }}</span>
                                                @elseif ($requisition->status_id == 3)
                                                    <span class="badge badge-primary">{{ $requisition->status_name ?? 'En Proceso' }}</span>
                                                @elseif ($requisition->status_id == 4)
                                                    <span class="badge badge-success">{{ $requisition->status_name ?? 'Completada' }}</span>
                                                @elseif ($requisition->status_id == 5 || $requisition->status_id == 6)
                                                    <span class="badge badge-danger">{{ $requisition->status_name ?? 'Cancelada' }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $requisition->status_name ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                @if($requisition->approved_by)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> Aprobada
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-exclamation-circle"></i> Por Aprobar
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                @can('requisitions.approve')
                                                    @if(!$requisition->approved_by)
                                                        <button class="btn btn-sm btn-success" onclick="approveRequisitionTable({{ $requisition->id }})" title="Aprobar Orden" style="min-width: 100px; margin-right: 2px; display: inline-flex; flex-direction: column; align-items: center; padding: 5px;">
                                                            <i class="fas fa-check-circle" style="font-size: 18px; margin-bottom: 3px;"></i>
                                                            <span style="font-size: 10px;">Aprobar</span>
                                                        </button>
                                                    @else
                                                        <button class="btn btn-sm btn-secondary" disabled title="Aprobada" style="min-width: 100px; margin-right: 2px; display: inline-flex; flex-direction: column; align-items: center; padding: 5px;">
                                                            <i class="fas fa-check-circle" style="font-size: 18px; margin-bottom: 3px;"></i>
                                                            <span style="font-size: 10px;">Aprobada</span>
                                                        </button>
                                                    @endif
                                                @endcan

                                                @role('admin|extrudersupervisor|supervisornave|extruderleader|manager')
                                                    <a class="btn btn-sm btn-info" href="{{ URL::to('requisitions/' . $requisition->id) }}" title="Ver Detalles" style="min-width: 100px; margin-right: 2px; display: inline-flex; flex-direction: column; align-items: center; padding: 5px;">
                                                        <i class="fas fa-eye" style="font-size: 18px; margin-bottom: 3px;"></i>
                                                        <span style="font-size: 10px;">Mostrar</span>
                                                    </a>
                                                @endcan

                                                @role('admin|extrudersupervisor|manager')
                                                    @if(!$requisition->date_fabrication_init)
                                                        <a class="btn btn-sm btn-primary" href="{{ URL::to('requisitions/' . $requisition->id . '/edit') }}" title="Editar" style="min-width: 100px; display: inline-flex; flex-direction: column; align-items: center; padding: 5px;">
                                                            <i class="fas fa-edit" style="font-size: 18px; margin-bottom: 3px;"></i>
                                                            <span style="font-size: 10px;">Editar</span>
                                                        </a>
                                                    @else
                                                        <button class="btn btn-sm btn-secondary" disabled title="No se puede editar" style="min-width: 100px; display: inline-flex; flex-direction: column; align-items: center; padding: 5px;">
                                                            <i class="fas fa-edit" style="font-size: 18px; margin-bottom: 3px;"></i>
                                                            <span style="font-size: 10px;">Editar</span>
                                                        </button>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @include('modals.modal-registerboxes')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
     @include('modals.modal-consumecoil')
     @include('modals.modal-scannercoil')



@endsection

@section('footer_scripts')
    @include('partials.requisitions-approve-script')
    @include('scripts.requisitions-choices')

    @if ((count($requisitions) > config('hyplast.datatablesJsStartCount')) && config('hyplast.enabledDatatablesJs'))
        @include('scripts.datatables.datatables-requisitions')
    @endif
    @include('scripts.save-modal-script')
    @if(config('hyplast.tooltipsEnabled'))
        @include('scripts.tooltips')
    @endif
    @if(config('hyplast.enableSearch'))
        @include('scripts.searchs.search-requisitions')
    @endif

    @include('scripts.requisition')



@endsection
