@extends('layout.main')
@section('page-title',Lang::get('labels.edit-field')." : ".(isset($option->DisplayName)? $option->DisplayName : "error"))

@section('scripts')
    <script>
        $(document).ready(function() {

            var form = $('#editOrderFieldOption');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            $(form).on('submit', function (event) {
                event.preventDefault();

                var optionID = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());

                var itemsToSubmit = hashDiff(startItems, currentItems);

                // sets null for all empty input
                for (var prop in itemsToSubmit) {
                    if (itemsToSubmit[prop] === "") {
                        itemsToSubmit[prop] = null;
                    }
                }

                //send request only if something changed
                if (!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'OrderFieldOptions(' + optionID + ')',
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
                                text: Lang.get(err.responseJSON.error.innererror.message),
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
    {!! Form::hidden('Model','OrderFieldOption',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $option->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <i class="fa fa-gears">@lang('labels.edit-field')</i>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'editOrderFieldOption']) !!}
                        <div class="form-group">
                            {!! Form::label('DisplayName',Lang::get('labels.display-name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('DisplayName',$option->DisplayName,['class'=>'form-control','required'=>'required','id'=>'DisplayName']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('Value',Lang::get('labels.value'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Value',$option->Value,['class'=>'form-control','title'=>Lang::get('messages.order-field-value'),'required'=>'required','id'=>'Value']) !!}
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