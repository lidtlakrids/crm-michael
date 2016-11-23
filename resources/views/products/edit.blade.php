@extends('layout.main')
@section('page-title',Lang::get('labels.edit-product')." : ".$product->Name)
@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function() {

            var form = $('#editProduct');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            $(form).on('submit', function (event) {
                event.preventDefault();

                var productId = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());

                var itemsToSubmit = hashDiff( startItems, currentItems);

                // sets null for all empty input
                for (var prop in itemsToSubmit) {
                    if (itemsToSubmit[prop] === "") {
                        itemsToSubmit[prop] = null;
                    }
                }

                //best one-liner ever to exist.
                itemsToSubmit.Active = $('#product-Active').is(':checked')? true:false;

                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'Products('+productId+')',
                        data: JSON.stringify(itemsToSubmit),
                        success: function (data) {
                            new PNotify({
                                title: Lang.get('labels.success'),
                                text: Lang.get('messages.update-was-successful'),
                                type: 'success'
                            });
                        },
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
                }
            });
        });
    </script>
@stop

@section('content')

    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Product',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $product->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4>@lang('labels.edit')</h4>
                    <div class="options">
                        <a href="{{URL::previous()}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'editProduct']) !!}

                        <div class="form-group">
                            {!! Form::label('product-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::text('Name',$product->Name,['class'=>'form-control','id'=>'product-Name','required'=>'required']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-Active',Lang::get('labels.active'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::checkbox('Active',$product->Active,$product->Active,['class'=>'form-control','id'=>'product-Active']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-SalePrice',Lang::get('labels.sale-price'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('SalePrice',$product->SalePrice,['class'=>'form-control','id'=>'product-SalePrice','required'=>'required','min'=>0]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-CostPrice',Lang::get('labels.cost-price'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('CostPrice',$product->CostPrice,['class'=>'form-control','id'=>'product-CostPrice','min'=>0]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-Number',Lang::get('labels.number'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('Number',$product->Number,['class'=>'form-control','id'=>'product-Number','min'=>0]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-ProductCommission',Lang::get('labels.commission'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('ProductCommission',$product->ProductCommission,['class'=>'form-control','id'=>'product-ProductCommission','min'=>0,'max'=>1,'step'=>'0.01','required'=>'required']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block">format : 0.04</p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-Description',"Description on for the Invoice",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::textarea('Description',$product->Description,['class'=>'form-control','id'=>'product-Description']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-OptimizeInterval',Lang::get('labels.optimize-interval'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('OptimizeInterval',$product->OptimizeInterval,['class'=>'form-control','id'=>'product-OptimizeInterval','min'=>0]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-TimeAllowance',Lang::get('labels.time-allowance'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('TimeAllowance',$product->TimeAllowance,['class'=>'form-control','id'=>'product-TimeAllowance','min'=>0]) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>


                        <div class="form-group">
                            {!! Form::label('ProductDepartment',Lang::get('labels.product-department'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-3">
                                {!! Form::select('ProductDepartment_Id',withEmpty($departments),(isset($product->ProductDepartment->Id)? $product->ProductDepartment->Id: null),['class'=>'form-control','id'=>'ProductDepartment']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('ProductType',Lang::get('labels.product-type'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-3">
                                {!! Form::select('ProductType_Id',withEmpty($types),(isset($product->ProductType->Id)? $product->ProductType->Id: null),['class'=>'form-control','id'=>'ProductType']) !!}
                            </div>
                        </div>

                        <div class="btn-toolbar">
                            {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
                        </div>

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop