@extends('layout.main')
@section('page-title',Lang::get('labels.edit-setting')." : ".$setting->Name)

@section('scripts')
    <script>
        $(document).ready(function(){
            var form = $('#editSetting');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
            delete(startItems['_token']);

            $(form).on('submit', function (event) {
                event.preventDefault();
                var settingId = $('#ModelId').val();
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
                itemsToSubmit.Active = $('#setting-Active').prop("checked");
                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'Settings('+settingId+')',
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
    {!! Form::hidden('Model','Setting',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $setting->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <i class="fa fa-gears">@lang('labels.edit-setting')</i>
                </div>

                <div class="panel-body">
                    {!! Form::open(['class'=>'form-horizontal','id'=>'editSetting'] ) !!}
                    <div class="form-group">
                        {!! Form::label('Model','Model',['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Model',$setting->Model,['class'=>'form-control']) !!}
                            {!! Form::hidden('Id',$setting->Id,['class'=>'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Name','Name',['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Name',$setting->Name,['class'=>'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Value','Value',['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::textarea('Value',$setting->Value,['class'=>'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Description','Description',['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::textarea('Description',$setting->Description,['class'=>'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Active','Active',['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::checkbox('Active',$setting->Active,$setting->Active,['class'=>'form-control','id'=>'setting-Active']) !!}
                        </div>
                    </div>

                    <div class="btn-toolbar">
                        {!! Form::submit('Update setting',['class'=> 'btn btn-success form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop