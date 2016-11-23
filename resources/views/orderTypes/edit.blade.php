@extends('layout.main')
@section('page-title',Lang::get('labels.edit-order-type'))
@section('scripts')
    <script>
        $(document).ready(function(){
            var form = $('#updateOrderType');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
            delete(startItems['_token']);

            $(form).on('submit', function (event) {
                event.preventDefault();

                var orderId = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());
                delete(currentItems['_token']);
                var itemsToSubmit = hashDiff( startItems, currentItems);

                // sets null for all empty input
                for (var prop in itemsToSubmit) {
                    if (itemsToSubmit[prop] === "") {
                        itemsToSubmit[prop] = null;
                    }
                }
                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'OrderTypes('+orderId+')',
                        data: JSON.stringify(itemsToSubmit),
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
                }

            });
        });

    </script>
@stop
@section('content')

    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','OrderType',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $orderType->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}

    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    @lang('labels.edit-order-type')
                    <div class="options">
                        <a href="{{url('ordertypes/show',$orderType->Id)}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                    </div>
                </div>

                <div class="panel-body">
                    {!! Form::open(['method'=>'PUT','action'=>['OrderTypesController@update',$orderType->Id],'class'=>'form-horizontal','id'=>'updateOrderType']) !!}
                    {!! Form::hidden('OrderTypeId',$orderType->Id,['Id'=>'OrderTypeId']) !!}
                    <div class="form-group">
                        {!! Form::label('FormName','FormName',['class'=>'col-md-3 control-label','required'=>'required']) !!}
                        <div class="col-md-4">
                            {!! Form::text('FormName',$orderType->FormName,['class'=>'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Type_Id','FormName',['class'=>'col-md-3 control-label','required'=>'required']) !!}
                        <div class="col-md-4">
                            {!! Form::select('Type_Id',$types,$orderType->Type_Id,['class'=>'form-control']) !!}
                        </div>
                    </div>

                    <div class="btn-toolbar">
                        {!! Form::submit(Lang::get('labels.update-order-type'),['class'=> 'btn btn-success form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop