@extends('layout.main')
@section('page-title',Lang::get('labels.create-setting'))
@section('scripts')

    <script>
        $(document).ready(function(){

            $('#createSetting').on('submit', function (event) {
                event.preventDefault();

                var formData = convertSerializedArrayToHash($(this).serializeArray());

                delete(formData['_token']);
                // sets null for all empty input
                for (var prop in formData) {
                    if (formData[prop] === "") {
                        delete(formData[prop]);
                    }
                }

                formData.Active = $('#set-Active').prop('checked');

                $.ajax({
                    type: "POST",
                    url: api_address + 'Settings',
                    data: JSON.stringify(formData),
                    success: function (data) {
                        console.log(data);
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get('messages.create-was-successful'),
                            type: 'success'
                        });
                    },
                    error: handleError,
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
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <i class="fa fa-gears">@lang('labels.create-setting')</i>
                </div>

                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'createSetting']) !!}

                        <div class="form-group">
                            {!! Form::label('sett-Model',Lang::get('labels.model'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Model',null,['class'=>'form-control','id'=>'sett-Model','required'=>'required']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('sett-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Name',null,['class'=>'form-control','required'=>'required','id'=>'sett-Name']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('sett-Value',Lang::get('labels.value'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Value',null,['class'=>'form-control','id'=>'sett-Value','required'=>'required']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('Active','Active',['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::checkbox('Active','true',true,['class'=>'form-control','id'=>'set-Active']) !!}
                            </div>
                        </div>

                        <div class="btn-toolbar">
                            {!! Form::submit('Save setting',['class'=> 'btn btn-success form-control']) !!}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop