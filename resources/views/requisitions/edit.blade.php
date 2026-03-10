@extends('adminlte::page')


@section('template_title')
    {!! trans('hyplast.editing-machine', ['name' => $requisition->id]) !!}
@endsection

@section('template_linked_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css" />
@endsection

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            {!! trans('hyplast.editing-machine', ['name' => $requisition->name]) !!}
                            <div class="pull-right">
                                <a href="{{ route('requisitions') }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="top" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                    <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                                    {!! trans('hyplast.buttons.back-to-machines') !!}
                                </a>
                                <a href="{{ url('/requisitions/' . $requisition->id) }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="left" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                    <i class="fa fa-fw fa-reply" aria-hidden="true"></i>
                                    {!! trans('hyplast.buttons.back-to-machine') !!}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {!! Form::open(array('route' => ['requisitions.update', $requisition->id], 'method' => 'PUT', 'role' => 'form', 'class' => 'needs-validation')) !!}
                        {!! csrf_field() !!}

                        <div class="row align-items-center">
                            <div class="col-sm-6 align-self-center">
                                <div class="form-group has-feedback row {{ $errors->has('client') ? ' has-error ' : '' }}">
                                    {!! Form::label('client', trans('forms.create_requisition_label_client'), array('class' => 'col-md-3 control-label')); !!}
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <select class="custom-select form-control" name="client" id="client">
                                                <option value="">{{ trans('forms.create_requisition_ph_client') }}</option>
                                                @foreach ($clients as $client)
                                                    @if ($requisition->client_code == $client->code)
                                                        <option value="{{$client->code}}" selected>{{$client->name}}</option>
                                                    @else
                                                        <option value="{{$client->code}}">{{$client->name}}</option>
                                                    @endif
                                                @endforeach
                                            </select>

                                            <div class="input-group-append">
                                                <label class="input-group-text" for="role">
                                                    <i class="{{ trans('forms.create_requisition_icon_client') }}" aria-hidden="true"></i>
                                                </label>
                                            </div>
                                        </div>
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
                                                    @if ($requisition->product && $requisition->product->CLASIFICACION_4 == $categorie->CLASIFICACION)
                                                        <option value="{{$categorie->CLASIFICACION}}" selected>{{$categorie->DESCRIPCION}}</option>
                                                    @else
                                                        <option value="{{$categorie->CLASIFICACION}}">{{$categorie->DESCRIPCION}}</option>
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
                                        <select class="form-control" name="product" id="product">
                                            @if ($requisition->product)
                                                <option value="{{$requisition->product->ARTICULO}}" selected>
                                                    {{$requisition->product->ARTICULO}} - {{$requisition->product->DESCRIPCION}}
                                                </option>
                                            @else
                                                <option value="">{{ trans('forms.create_requisition_ph_product') }}</option>
                                            @endif
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
                                            {!! Form::number('requested',  $requisition->requested, array('id' => 'requested', 'class' => 'form-control', 'placeholder' => trans('forms.create_requisition_ph_requested'), 'type' => 'number')) !!}
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
                                    {!! Form::label('requested', trans('forms.create_requisition_label_date_limit'), array('class' => 'col-md-4 control-label')); !!}
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            {!! Form::date('date_limit', $requisition->date_limit, array('date_limit' => 'requested', 'class' => 'form-control', 'placeholder' => trans('forms.create_requisition_ph_requested'), 'type' => 'date')) !!}
                                            <div class="input-group-append">
                                                <label for="requested" class="input-group-text">
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
                        <div class="row">
                                <div class="col-12 col-sm-6 mb-2">
                                </div>
                                <div class="col-12 col-sm-6">
                                    {!! Form::button(trans('forms.save-changes'), array('class' => 'btn btn-success btn-block margin-bottom-1 mt-3 mb-2 btn-save','type' => 'button', 'data-toggle' => 'modal', 'data-target' => '#confirmSave', 'data-title' => trans('modals.edit_machine__modal_text_confirm_title'), 'data-message' => trans('modals.edit_machine__modal_text_confirm_message'))) !!}
                                    @include('modals.modal-save')
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer_scripts')
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>

    <script>
        let choicesInstance = null;

        $(document).ready(function() {
            // Inicializar Choices.js en el combo de productos
            initializeChoices();
        });

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
                    console.error('Error al cargar productos:', error);
                }
            });
        }
    </script>
@endsection
