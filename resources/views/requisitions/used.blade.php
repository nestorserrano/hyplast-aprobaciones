@extends('adminlte::page')


@section('template_fastload_css')
@endsection

@section('template_title')
    {!! trans('hyplast.showing-all-machines') !!}
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
                        <div class="card-header text-white bg-success">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                {!! trans('hyplast.showing-used-coils') !!}
                                <div class="pull-right">
                                    <a href="{{ route('requisitions') }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="left" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                        <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                                        {!! trans('hyplast.buttons.back-to-machines') !!}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive machine-table">
                            <table class="table table-striped table-sm data-table">
                                <caption id="machine_count" name="machine_count">
                                    {{ trans_choice('hyplast.machines-table.caption', 1, ['machinescount' => $requisitions->count()]) }}
                                </caption>
                                <thead class="thead">
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th colspan=2 style="text-align: center">{!! trans('hyplast.table.box') !!}</th>
                                        <th colspan=2 style="text-align: center">{!! trans('hyplast.table.coil') !!}</th>
                                        <th></th>
                                        <th></th>
                                        <th colspan=4></th>
                                    </tr>
                                    <tr>
                                        <th>{!! trans('hyplast.machines-table.id') !!}</th>
                                        <th class="hidden-xs text-align-center" >{!! trans('hyplast.machines-table.client') !!}</th>
                                        <th class="hidden-xs text-align-center">{!! trans('hyplast.machines-table.product') !!}</th>
                                        <th class="hidden-xs text-align-center" style="text-align: center">{!! trans('hyplast.machines-table.request') !!}</th>
                                        <th class="hidden-xs text-align-center" style="text-align: center">{!! trans('hyplast.machines-table.manufactured') !!}</th>
                                        <th class="hidden-xs" style="text-align: center">{!! trans('hyplast.machines-table.weight_request') !!}</th>
                                        <th class="hidden-xs" style="text-align: center">{!! trans('hyplast.machines-table.weight_manufactured') !!}</th>
                                        <th class="hidden-sm hidden-xs hidden-md" style="text-align: center">{!! trans('hyplast.machines-table.init') !!}</th>
                                        <th class="hidden-sm hidden-xs hidden-md" style="text-align: center">{!! trans('hyplast.machines-table.finish') !!}</th>
                                        <th>{!! trans('hyplast.machines-table.actions') !!}</th>
                                        <th class="no-search no-sort"></th>
                                        <th class="no-search no-sort"></th>
                                    </tr>
                                </thead>
                                @php
                                    $i = 1
                                @endphp
                                <tbody id="machine-table">
                                    @foreach($requisitions as $requisition)
                                        <tr>
                                            <td>{{$requisition->id}}</td>
                                            <td>{{ $requisition->cliente->NOMBRE ?? 'N/A' }}</td>
                                            <td><a href="products/{{$requisition->product->id}}">{{ $requisition->product->name }}</a></td>
                                            <td style="text-align: center">{{ $requisition->requested}}</td>
                                            <td style="text-align: center">{{ $requisition->manufactured }}</td>
                                            <td style="text-align: center">{{ $requisition->total_weight }}</td>

                                            @if($requisition->total_weight - $requisition->manufactured_weight <= 0)
                                                <td align="center"   style="color:red; text-align: center">{{ $requisition->manufactured_weight }} kg </td>
                                            @else
                                                <td align="center"  style="text-align: center">{{ $requisition->manufactured_weight }} kg </td>
                                            @endif
                                            <td class="hidden-sm hidden-xs hidden-md" style="text-align: center">{{ $requisition->date_fabrication_init }}</td>
                                            <td class="hidden-sm hidden-xs hidden-md" style="text-align: center">{{ $requisition->date_fabrication_finish }}</td>
                                            <td>
                                                <a class="btn btn-sm btn-warning btn-block" href="{{ URL::to('requisitions/print/' . $requisition->id)}}" data-toggle="tooltip" title="Iprimir Orden de Servicio">
                                                    {!! trans('hyplast.buttons.printpo') !!}
                                                </a>
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-success btn-block" href="{{ URL::to('requisitions/' . $requisition->id) }}" data-toggle="tooltip" title="Mostrar">
                                                    {!! trans('hyplast.buttons.show') !!}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tbody id="search_results"></tbody>
                                @if(config('hyplast.enableSearch'))
                                    <tbody id="search_results"></tbody>
                                @endif

                            </table>

                            @if(config('hyplast.enablePagination'))
                                {{ $requisitions->links() }}
                            @endif
                            @include('modals.modal-consumecoil')
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>



@endsection

@section('footer_scripts')
    @if ((count($requisitions) > config('hyplast.datatablesJsStartCount')) && config('hyplast.enabledDatatablesJs'))
        @include('scripts.datatables.datatables')
    @endif
    @include('scripts.save-modal-script')
    @if(config('hyplast.tooltipsEnabled'))
        @include('scripts.tooltips')
    @endif
    @if(config('hyplast.enableSearch'))
        @include('scripts.searchs.search-requisitions')
    @endif
    <script type="text/javascript">

        function closeRequisition(id) {
            swal({
                title: "Cerrar o Cancelar?",
                text: "Por Favor, asegurese y luego Confirme!",
                html:   '<br><div class="form-group has-feedback row {{ $errors->has("cancel_requisition") ? " has-error " : "" }}">'+
                        '{!! Form::label("cancel_requisition", "Motivo", array("class" => "col-md-4 control-label")); !!}' +
                        ' <div class="col-8 align-self-center">' +
                        '<div class="input-group">' +
                        '<select class="custom-select form-control" name="cancel_requisition" id="cancel_requisition">' +
                        '<option value="0">Seleccione El Motivo</option>' +
                        '<option value="4">Orden Completada</option>' +
                        '<option value="5">Retiro de la Orden</option>' +
                        '<option value="6">Falta de Materia Prima</option>' +
                        '</select>' +
                        '</div>'+
                        '</div>'+
                        '</div>',
                type: "warning",
                showCancelButton: !0,
                confirmButtonText: "Si, Cerrar o Cancelar esta Orden!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: !0
            }).then(function (e) {
                if (e.value === true) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    var cancel_requisition = document.getElementById("cancel_requisition").value;
                    if (cancel_requisition == 0) {
                        swal({
                        title: "Seleccione un Motivo",
                        text: "Por Favor, Seleccione un Motivo para el cierre de la Orden, Intente de Nuevo!",
                        type: "warning",
                         });
                    }
                    $.ajax({
                        type: 'POST',
                        url: "{{url('cancelreq')}}/" + id + "/" + cancel_requisition,
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
        function createButton(text, cb) {
            return $('<button>' + text + '</button>').on('click', cb);
        }
        function clearData()
        {
            document.getElementById("requisition").value = "";
            document.getElementById("barcode").value = "";
            $("#machine").empty();
            $("#barcode").empty();
            $('#result_coil').empty();
            $('#buttom_consume').empty();
        }
        function loadData() {
            var id = document.getElementById("requisition").value;
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $("#machine").empty();
            $("#barcodetext").append("");
            $("#machine").append("<option value=''>Seleccione La máquina</option>");
            $.ajax({
                type:'POST',
                url: "{{ url('reqmachine') }}/" + id,
                data: {_token: CSRF_TOKEN},
                success: function (result) {
                    document.getElementById("barcode").value = "";
                    if (result.length != 0) {
                        var i = 0;
                        $.each(result, function() {
                                $("#machine").append("<option value=" + result[i].id + ">" + result[i].name + "</option>");
                                i++;
                        });
                    } else {
                        $("#machine").append("<option value=''>El producto No tiene Máquinas Asignadas</option>");
                        $("#buttom_consume").append("<a class='btn btn-warning' type='button' name='btnSend' id='btnSend' onclick='sendData()' disabled>");
                    };

                },

                error: function (response, status, error) {
                    if (response.status === 422) {

                    };
                },
            });

        };
        $("#cancelbutton5").click(function() {
            clearData();
        });
        $("#cancelbutton6").click(function() {
            clearData();
        });

        $("#barcode").change(function(e){
            $('#buttom_consume').empty();
            let resultsContainer = $('#result_coil');
            let noResulsHtml ='<tr>' +
                                '<td colspan="3">{!! trans("hyplast.machines-requisition-no") !!}</td>' +
                                '<tr>';
            resultsContainer.html("");
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var id = document.getElementById("requisition").value;
            var codebar = document.getElementById("barcode").value;
            $.ajax({
                type:'POST',
                url: "{{ url('requisitions_coils') }}/" + id + "/" + codebar,
                data: {_token: CSRF_TOKEN},
                success: function (result) {
                    resultsContainer.html('');
                    if (result.length != 0) {
                        if (result.success){
                            resultsContainer.append(
                            '<tr>' +
                                '<td>' + result.extruder.requisition_id + '</td>' +
                                '<td>' + result.extruder.codebar + '</td>' +
                                '<td>' + result.extruder.name + '</td>' +
                            '<tr>'
                            );
                            $("#buttom_consume").append('<a class="btn btn-warning" type="button" name="btnSend" id="btnSend" onclick="sendData()">{!! trans("hyplast.buttons.consume") !!}</a>');
                        } else {
                            resultsContainer.append(
                                '<tr>' +
                                    '<td colspan="3">' + result.message + '</td>' +
                                '<tr>'
                            );
                        }
                    } else {
                        resultsContainer.append(noResulsHtml);
                        clearData;
                    };

                },

                error: function (response, status, error) {
                    if (response.status === 422) {
                        resultsContainer.append(noResulsHtml);
                        clearData;
                    };

                },
            });

        });

        function noedit() {
            swal({
                title: "No puede Editar",
                text: "No puede editar esta Orden ya que tiene un inicio de producción!",
                type: "error",
                timer: 1000,
                showConfirmButton: true,
                confirmButtonText: "Aceptar",
                position: 'center',
                toast: false,
            });
        }


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
                        url: "{{url('/requisitions/delete')}}/" + id,
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



        function sendData(id) {
            swal({
                title: "Asignar Bobina?",
                text: "Por Favor, asegurese y luego Confirme que desea asignar la Bobina a la máquina seleccionada!",
                type: "warning",
                showCancelButton: !0,
                confirmButtonText: "Si, asignar!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: !0
            }).then(function (e) {
                if (e.value === true) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    var id = document.getElementById("requisition").value;
                    var codebar = document.getElementById("barcode").value;
                    var machine = document.getElementById("machine").value;
                    $.ajax({
                        type: 'GET',
                        url: "{{url('assign_coil')}}/" + id + "/" + machine + "/" + codebar,
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
    </script>


@endsection
