@extends('adminlte::page')

@section('template_linked_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css" />
@endsection

@section('template_title')
    {!! trans('hyplast.create-new-requisition') !!}
@endsection

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="card card-info">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            {!! trans('hyplast.create-new-requisition') !!}
                            <div class="pull-right">
                                <a href="{{ route('requisitions') }}" class="btn btn-dark btn-sm float-right" data-toggle="tooltip" data-placement="left" title="{{ trans('hyplast.tooltips.back-products') }}">
                                    <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                                    {!! trans('hyplast.buttons.back-to-requisitions') !!}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="form" name="form" action="#">
                        {{ csrf_field() }}
                        <div class="row align-items-center">
                            <div class="col-sm-5 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('order') ? ' has-error ' : '' }}">
                                    {!! Form::label('order', trans('forms.create_requisition_label_order'), array('class' => 'col-md-6 control-label')); !!}
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <select class="custom-select form-control" name="order" id="order"  onchange="loadOrder()">
                                                <option value="">{{ trans('forms.create_requisition_ph_order') }}</option>
                                                @foreach ($orderes as $order)
                                                    <option value="{{$order->ORDEN_PRODUCCION}}">{{$order->ORDEN_PRODUCCION}}</option>
                                                @endforeach
                                            </select>

                                            <div class="input-group-append">
                                                <label class="input-group-text" for="role">
                                                    <i class="{{ trans('forms.create_requisition_icon_order') }}" aria-hidden="true"></i>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($errors->has('order'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('order') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-7 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('product2') ? ' has-error ' : '' }}">
                                    <div class="col-md-12">
                                        <div class="producto2" id="producto2">
                                        </div>
                                        @if ($errors->has('propduct2'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('product2') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-sm-6 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('client') ? ' has-error ' : '' }}">
                                    {!! Form::label('client', trans('forms.create_requisition_label_client'), array('class' => 'col-md-3 control-label')); !!}
                                    <div class="col-md-9">
                                        <select class="custom-select form-control" name="client" id="client">
                                            <option value="">{{ trans('forms.create_requisition_ph_client') }}</option>
                                            @foreach ($clients as $client)
                                                <option value="{{$client->code}}">{{$client->name}}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('client'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('client') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('category_id') ? ' has-error ' : '' }}">
                                    {!! Form::label('category_id', trans('forms.create_product_label_categories'), array('class' => 'col-md-3 control-label')); !!}
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <select class="custom-select form-control" name="category_id" id="category_id" required="true" onchange="loadProducts()">
                                                <option value="">{{ trans('forms.create_product_ph_categories') }}</option>
                                                @foreach ($categories as $categorie)
                                                    @if (old('category_id') == $categorie->id)
                                                        <option value="{{$categorie->id}}" selected>{{$categorie->name}}</option>
                                                    @else
                                                        <option value="{{$categorie->id}}">{{$categorie->name}}</option>
                                                    @endif
                                                @endforeach
                                            </select>

                                            <div class="input-group-append">
                                                <label class="input-group-text" for="category_id">
                                                    <i class="{{ trans('forms.create_product_icon_categories') }}" aria-hidden="true"></i>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($errors->has('category_id'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('category_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-sm-12 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('product') ? ' has-error ' : '' }}">
                                    {!! Form::label('product', trans('forms.create_requisition_label_product'), array('class' => 'col-md-2 control-label')); !!}
                                    <div class="col-md-10">
                                        <select class="form-control" name="product" id="product" required="true" onchange="loadSupplies()">
                                            <option value="">Seleccione un Producto</option>
                                        </select>
                                        @if ($errors->has('product'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('product') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-sm-6 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('requested') ? ' has-error ' : '' }}">
                                    {!! Form::label('requested', trans('forms.create_requisition_label_requested'), array('class' => 'col-md-7 control-label')); !!}
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            {!! Form::number('requested',  old('requested'), array('id' => 'requested', 'class' => 'form-control', 'placeholder' => trans('forms.create_requisition_ph_requested'), 'type' => 'number')) !!}
                                            <div class="input-group-append">
                                                <label for="requested" class="input-group-text">
                                                    <i class="fa fa-fw {{ trans('forms.create_requisition_icon_requested') }}" aria-hidden="true"></i>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($errors->has('requested'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('requested') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-6 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('date_limit') ? ' has-error ' : '' }}">
                                    {!! Form::label('date_limit', trans('forms.create_requisition_label_date_limit'), array('class' => 'col-md-4 control-label')); !!}
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            {!! Form::date('date_limit',  old('date_limit', \Carbon\Carbon::now()->format('Y-m-d')), array('id' => 'date_limit', 'class' => 'form-control', 'placeholder' => trans('forms.create_requisition_ph_requested'), 'type' => 'date', 'min' => \Carbon\Carbon::now()->format('Y-m-d'))) !!}
                                            <div class="input-group-append">
                                                <label for="date_limit" class="input-group-text">
                                                    <i class="fa fa-fw {{ trans('forms.create_requisition_icon_date_limit') }}" aria-hidden="true"></i>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($errors->has('date_limit'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('date_limit') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-sm-6 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('priority') ? ' has-error ' : '' }}">
                                    {!! Form::label('priority', trans('forms.create_requisition_label_priority'), array('class' => 'col-md-4 control-label')); !!}
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <select class="custom-select form-control" name="priority" id="priority" required="true">
                                                <option value="">{{ trans('forms.create_requisition_ph_priority') }}</option>
                                                @if (old('priority') == "Urgente")
                                                    <option value="Urgente" selected>Urgente</option>
                                                @else
                                                    <option value="Urgente">Urgente</option>
                                                @endif
                                                @if (old('priority') == "A")
                                                    <option value="A" selected>A</option>
                                                @else
                                                    <option value="A">A</option>
                                                @endif
                                                @if (old('priority') == "B")
                                                    <option value="B" selected>B</option>
                                                @else
                                                    <option value="B">B</option>
                                                @endif
                                                @if (old('priority') == "C")
                                                    <option value="C" selected>C</option>
                                                @else
                                                    <option value="C">C</option>
                                                @endif
                                            </select>
                                            <div class="input-group-append">
                                                <label for="shift" class="input-group-text">
                                                    <i class="fa fa-fw {{ trans('forms.create_requisition_icon_priority') }}" aria-hidden="true"></i>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($errors->has('priority'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('priority') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('shifts') ? ' has-error ' : '' }}">
                                    {!! Form::label('shifts', trans('forms.create_requisition_label_shift'), array('class' => 'col-md-4 control-label')); !!}
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            {!! Form::number('shifts',  old('shifts'), array('id' => 'shifts', 'class' => 'form-control', 'placeholder' => trans('forms.create_requisition_ph_shift'), 'type' => 'number')) !!}
                                            <div class="input-group-append">
                                                <label for="shifts" class="input-group-text">
                                                    <i class="fa fa-fw {{ trans('forms.create_requisition_icon_shift') }}" aria-hidden="true"></i>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($errors->has('shifts'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('shifts') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group has-feedback row {{ $errors->has('notes') ? ' has-error ' : '' }}">
                            {!! Form::label('notes', trans('forms.create_requisition_label_notes'), array('class' => 'col col-sm-2 control-label')); !!}
                            <div class="col-sm-10 align-self-center">
                                <div class="input-group">
                                    {!! Form::textarea('notes',  old('notes'),array( 'rows' => 3, 'id' => 'notes', 'class' => 'form-control', 'placeholder' => trans('forms.create_requisition_ph_notes'))) !!}
                                    <div class="input-group-append">
                                        <label class="input-group-text" for="notes">
                                            <i class="fa fa-fw {{ trans('forms.create_requisition_icon_notes') }}" aria-hidden="true"></i>
                                        </label>
                                    </div>
                                </div>
                                @if ($errors->has('notes'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('notes') }}</strong>
                                    </span>
                                @endif
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
                        <div class="table-responsive machine-table">
                            <table class="table table-striped table-sm data-table"  id="detalles">
                                <thead class="thead">
                                    <tr>
                                        <th>{!! trans('hyplast.machines-table.code') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.name') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.quantity') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.required') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.unit') !!}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="table-body"></tbody>
                            </table>
                        </div>
                    </div>
                        </form>
                    </div>
                </div>
            <button class="btn btn-success btn-save guardar float-right" name="guardar" id="guardar" type="button" onclick="confirm_form()">{!! trans('forms.save-create') !!}</button>
        </div>

    </div>

@endsection

@section('footer_scripts')
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
    <script>



    //   $('#btnOpenSaltC').click(function() {
      //      event.preventDefault();
        function confirm_form() {

            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var orden = document.getElementById("order").value;
            //if(orden.length == 0) {
          //      alert('No has seleccionado la Orden');
          //      return;
         //   }
            var category_id = document.getElementById("category_id").value;
            if(category_id.length == 0) {
                alert('No has ingresado la Categoría');
                return;
            }
            var client  = document.getElementById("client").value;
            if(client.length == 0) {
                alert('No has seleccionado el Cliente');
                return;
            }
            var product =  document.getElementById("product").value;
            if(product.length == 0) {
                alert('No has ingresado el producto');
                return;
            }
            var requested = document.getElementById("requested").value;
            if(requested.length == 0) {
                alert('Debes indicar la cantidad de Cajas Requeridas');
                return;
            }
            var priority = document.getElementById("priority").value;
            if(priority.length == 0) {
                alert('Seleccione la prioridad según planificación');
                return;
            }
            var shifts = document.getElementById("shifts").value;
            if(shifts.length == 0) {
                alert('Coloca la Cantidad de Turnos Requeridos según planificación');
                return;
            }
            var date_limit = document.getElementById("date_limit").value;
            var notes = document.getElementById("notes").value;
            let supplies2 = [];
            const tableData = document.querySelector('#detalles');
            let supplie2 = tableData.querySelectorAll('tbody tr');
            supplie2.forEach(function(e){
                // Usar querySelector con clase en lugar de getElementById
                let supplieInput = e.querySelector(".supplie");
                let quantityInput = e.querySelector(".quantity");
                let requiredInput = e.querySelector(".required");

                if(supplieInput && supplieInput.value){
                    let fila = {
                        supplie: supplieInput.value,
                        quantity: quantityInput ? quantityInput.value : 0,
                        required: requiredInput ? requiredInput.value : 0,
                    };
                    supplies2.push(fila);
                }
            });

            var formData = {
                _token: csrfToken,
                category_id: category_id,
                client: client,
                product: product,
                requested: requested,
                date_limit: date_limit,
                shifts: shifts,
                order: orden,
                priority: priority,
                notes: notes,
                supplies: supplies2
            };


            swal({
                title: "Guardar",
                text: "Se Guardará una Nueva Requisición con los Valores Indicados!",
                type: "info",
                showCancelButton: true,
                confirmButtonText: "Si",
                cancelButtonText: "No",
                reverseButtons: true
            }).then(function (e) {
                if (e.value === true) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('requisitions.register') }}",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        success: function (results) {
                            if (results.success === true) {
                                swal({
                                    title: "¡Éxito!",
                                    text: results.message,
                                    type: "success",
                                    confirmButtonText: "OK"
                                }).then(function () {
                                    // Limpiar formulario
                                    document.getElementById("form").reset();
                                    $("#detalles > tbody").empty();
                                    $("#product").empty();
                                    $("#product").append("<option value=''>Seleccione un Producto</option>");
                                    // Opcional: recargar página
                                    // window.location.reload();
                                });
                            } else {
                                swal({
                                    title: "Error",
                                    text: results.message,
                                    type: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error AJAX:', xhr.responseText);
                            swal({
                                title: "Error",
                                text: "Ocurrió un error al guardar la requisición. Por favor, intente nuevamente.",
                                type: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    });
                }
            })

        }
        //});

        let choicesInstance = null;
        let choicesInstanceClient = null;

        function initializeChoices() {
            const element = document.getElementById('product');

            if (choicesInstance) {
                choicesInstance.destroy();
            }

            choicesInstance = new Choices(element, {
                searchEnabled: true,
                searchPlaceholderValue: 'Buscar producto...',
                itemSelectText: 'Presione para seleccionar',
                noResultsText: 'No se encontraron productos',
                noChoicesText: 'No hay opciones disponibles',
                removeItemButton: false,
                shouldSort: false,
                allowHTML: false
            });
        }

        function initializeClientChoices() {
            const element = document.getElementById('client');

            if (choicesInstanceClient) {
                choicesInstanceClient.destroy();
            }

            choicesInstanceClient = new Choices(element, {
                searchEnabled: true,
                searchPlaceholderValue: 'Buscar cliente...',
                itemSelectText: 'Presione para seleccionar',
                noResultsText: 'No se encontraron clientes',
                noChoicesText: 'No hay opciones disponibles',
                removeItemButton: false,
                shouldSort: false,
                allowHTML: false
            });
        }

        function loadProducts() {
            var id = document.getElementById("category_id").value;
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            if (!id) {
                return;
            }

            $.ajax({
                type:'GET',
                url: "{{ url('requisitions/categoryproducts') }}/" + id,
                data: {_token: CSRF_TOKEN},
                success: function (result) {
                    if (choicesInstance) {
                        choicesInstance.destroy();
                    }

                    $("#product").empty();
                    $("#product").append("<option value=''>Seleccione un Producto</option>");

                    if (result.length != 0) {
                        $.each(result, function(index, item) {
                            $("#product").append("<option value='" + item.code + "'>" + item.code + " - " + item.name + "</option>");
                        });
                    } else {
                        $("#product").append("<option value=''>Categoría sin Productos</option>");
                    }

                    initializeChoices();
                },
                error: function (response, status, error) {
                    console.error('Error cargando productos:', response);
                }
            });
        };

        function loadOrder() {
            var id = document.getElementById("order").value;
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $("#product2").empty();
            $.ajax({
                type:'GET',
                url: "{{ url('orderproduct') }}/" + id,
                data: {_token: CSRF_TOKEN},
                success: function (result) {
                    if (result.length != 0) {
                        //$("#producto2").append("<strong>Producto: " + result[0].name + "</strong>");
                    };
                },

                error: function (response, status, error) {
                    if (response.status === 422) {
                        $("#product2").empty();
                    };
                },
            });

        };

        function loadSupplies() {
            var id = document.getElementById("product").value;
            let noResulsHtml ='<tr>' +
                                '<td colspan=5 id="supplie" class="supplie">{!! trans("hyplast.products-supplies-no") !!}</td>' +
                                '<tr>';
            $("#detalles > tbody").empty();

            if (!id) {
                $('#detalles > tbody').append(noResulsHtml);
                return;
            }

            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type:'POST',
                url: "{{ url('productssupplies') }}/" + id,
                data: {_token: CSRF_TOKEN},
                success: function (data) {
                    $("#detalles > tbody").empty();

                    if (data.tabla && data.tabla.trim() !== '') {
                        // Parsear las filas del controlador y adaptarlas para requisitions
                        var $tempTable = $('<table>' + data.tabla + '</table>');
                        var $rows = $tempTable.find('tr');

                        if ($rows.length > 0) {
                            $rows.each(function() {
                                var $cols = $(this).find('td');
                                if ($cols.length >= 4) {
                                    var codigo = $cols.eq(0).text().trim();
                                    var nombre = $cols.eq(1).text().trim();
                                    var unidad = $cols.eq(2).text().trim();
                                    var cantidad = $cols.eq(3).text().trim();

                                    // Construir fila adaptada para requisitions
                                    let fila = '<tr>' +
                                        '<td>' +
                                        '<input type="hidden" class="supplie" value="' + codigo + '">' +
                                        codigo +
                                        '</td>' +
                                        '<td>' + nombre + '</td>' +
                                        '<td>' +
                                        '<input type="hidden" class="quantity" value="' + cantidad + '">' +
                                        cantidad +
                                        '</td>' +
                                        '<td>' +
                                        '<input type="text" class="required form-control" />' +
                                        '</td>' +
                                        '<td>' + unidad + '</td>' +
                                        '</tr>';
                                    $('#detalles > tbody').append(fila);
                                }
                            });
                        } else {
                            $('#detalles > tbody').append(noResulsHtml);
                        }
                    } else {
                        $('#detalles > tbody').append(noResulsHtml);
                    }
                },
                error: function (response, status, error) {
                    $('#detalles > tbody').append(noResulsHtml);
                }
            });
        };

        $(document).ready(function(){
            $("form").keypress(function(e) {
                if (e.which == 13) {
                    return false;
                }
            });
            $('[name="requested"]').on('input', sumar);
            sumar();

            // Inicializar Choices.js
            initializeChoices();
            initializeClientChoices();

            // Cargar productos con tipo T (Terminado)
            loadProducts();
        });

        function sumar() {
            let total = 0;
            let precios = [];
            let cantidad =  this.value || 0;

            $('.quantity').each((index, item) => {
                precios.push($(item).val()*cantidad);
                $('.required')[index].value = (precios[index].toFixed(2));
            });



        }



    </script>
@endsection



