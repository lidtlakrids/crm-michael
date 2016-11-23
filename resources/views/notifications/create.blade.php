@extends('layout.main')
@section('page-title',Lang::get('labels.create-notification'))

@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function(){

            $('#createNotification').on('submit', function (event) {
                event.preventDefault();

                var formData = convertSerializedArrayToHash($(this).serializeArray());

                delete(formData['_token']);


                // sets null for all empty input
                for (var prop in formData) {
                    if (formData[prop] === "") {
                        delete(formData[prop]);
                    }
                }
                formData.Recipient_Id = "36";

                $.ajax({
                    type: "POST",
                    url: api_address + 'Notifications',
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
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    @lang('labels.create-notification')
                </div>
                <div class="panel-body">
                    <div class="col-md-5">
                        <div class="form-horizontal">
                            {!! Form::open(['id'=>'createNotification']) !!}

                            <div class="form-group">
                                {!! Form::label('notification-Model',Lang::get('labels.item-nr'),['class'=>'col-md-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::text('Model',null,['class'=>'form-control','placeholder'=>'Invoice,Contract...']) !!}
                                </div>
                                <div class="col-sm-3">
                                    {!! Form::number('ModelId',null,['class'=>'form-control','placeholder'=>Lang::get('labels.number')]) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('notification-Title',Lang::get('labels.title'),['class'=>'col-md-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::text('Title',null,['class'=>'form-control','placeholder'=>Lang::get('labels.title'),'id'=>'notification-Title','required'=>'required']) !!}
                                </div>
                                <div class="col-sm-3">
                                    <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('notification-Content',Lang::get('labels.content'),['class'=>'col-md-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::textarea('Content',null,['class'=>'form-control','placeholder'=>Lang::get('labels.content'),'id'=>'notification-Title','id'=>'notification-Content']) !!}
                                </div>
                                <div class="col-sm-3">
                                    <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                </div>
                            </div>

                            <div class="btn-toolbar">
                                {!! Form::submit(Lang::get('labels.save'),['class'=> 'btn btn-primary form-control']) !!}
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>

                    <div class="col-md-5">


                    </div>

                </div> <!-- end panel body -->

            </div>
        </div>
    </div>

@stop