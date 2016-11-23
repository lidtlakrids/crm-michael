@extends('layout.main')
@section('page-title',Lang::get('labels.create-product'))
@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function(){

            $('#createProduct').on('submit', function (event) {
                event.preventDefault();

                var formData = convertSerializedArrayToHash($(this).serializeArray());
                delete(formData['_token']);
                // sets null for all empty input
                for (var prop in formData) {
                    if (formData[prop] === "") {
                        delete(formData[prop]);
                    }
                }
                formData.Active = (formData.Active)?true:false;
                $.ajax({
                    type: "POST",
                    url: api_address + 'Products',
                    data: JSON.stringify(formData),
                    success: function (data) {
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get('messages.update-was-successful'),
                            type: 'success'
                        });
                    },
                    error: function (err) {
                        new PNotify({
                            title: Lang.get('labels.error'),
                            text: Lang.get(err.statusText),
                            type: 'error'
                        });
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });
        });
    </script>

@stop

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4 class="panel-title">@lang('labels.create-product')</h4>
            </div>
            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'createProduct'])!!}

                    <div class="form-group">
                        {!! Form::label('product-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::text('Name',null,['class'=>'form-control','id'=>'product-Name','required'=>'required']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-SalePrice',Lang::get('labels.sale-price'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('SalePrice',null,['class'=>'form-control','id'=>'product-SalePrice','required'=>'required']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-RecommendedPrice',Lang::get('labels.recommended-price'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('RecommendedPrice',null,['class'=>'form-control','id'=>'product-RecommendedPrice']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-CostPrice',Lang::get('labels.cost-price'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('CostPrice',null,['class'=>'form-control','id'=>'product-CostPrice']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>


                    <div class="form-group">
                        {!! Form::label('product-Number',Lang::get('labels.number'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('Number',null,['class'=>'form-control','id'=>'product-Number','min'=>0]) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block">Economic ID</p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::textarea('Description',null,['class'=>'form-control','id'=>'product-Description']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-Active',Lang::get('labels.active'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::checkbox('Active',null,['class'=>'form-control','id'=>'product-Active']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-ProductCommission',Lang::get('labels.commission'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('ProductCommission',null,['class'=>'form-control','id'=>'product-ProductCommission','min'=>0,'max'=>1,'step'=>'0.01',]) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block">format 0.04</p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-ProductDepartment_Id',Lang::get('labels.department'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::select('ProductDepartment_Id',withEmpty($departments),null,['class'=>'form-control','id'=>'product-ProductDepartment_Id','required'=>'required']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-ProductType_Id',Lang::get('labels.type'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::select('ProductType_Id',withEmpty($types),null,['class'=>'form-control','id'=>'product-ProductType_Id','required'=>'required']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-OptimizeInterval',Lang::get('labels.optimize-interval'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('OptimizeInterval',null,['class'=>'form-control','id'=>'product-OptimizeInterval','required'=>'required']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-TimeAllowance',Lang::get('labels.time-allowance'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('TimeAllowance',null,['class'=>'form-control','id'=>'product-TimeAllowance','min'=>0]) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="btn-toolbar">
                        {!! Form::submit(strtoupper(Lang::get('labels.save')),['class'=> 'btn btn-orange btn-label form-control']) !!}
                    </div>
                    {!! Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>

@stop