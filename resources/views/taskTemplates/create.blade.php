@extends('layout.main')
@section('page-title',Lang::get('labels.create-task-template'))
@section('styles')
@stop

@section('scripts')
    <script>
        $('#createTaskTemplate').on('submit', function (event) {
            event.preventDefault();

            var formData = convertSerializedArrayToHash($(this).serializeArray());

            delete(formData['_token']);

            // sets null for all empty input
            for (var prop in formData) {
                if (formData[prop] === "") {
                    delete(formData[prop]);
                }
            }

            formData.NotifyCreator = $('#tt-NotifyCreator').prop("checked");

            $.ajax({
                type: "POST",
                url: api_address + 'TaskListTemplates/action.NewTask',
                data: JSON.stringify(formData),
                success: function (data) {
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('messages.update-was-successful'),
                        type: 'success'
                    });
                    window.location = base_url+'/taskTemplates/show/'+data.Id;
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-inverse">
                <div class="panel-heading">@lang('labels.create-task-template')</div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'createTaskTemplate']) !!}

                        <div class="form-group">
                            {!! Form::label('tt-Title',Lang::get('labels.title'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Title',null,['class'=>'form-control','required'=>'required','id'=>'tt-Title']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('tt-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::textarea('Description',null,['class'=>'form-control','required'=>'required','id'=>'tt-Description']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tt-AssignedTo" class="col-sm-3 control-label">@lang('labels.user')</label>
                            <div class="col-sm-6">
                                {!! Form::select("AssignedTo",withEmpty($users),null,['class'=>'form-control','id'=>'tt-AssignedTo']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('tt-NotifyCreator',Lang::get('labels.notify-creator'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-3">
                                {!! Form::checkbox('NotifyCreator',null,null,['class'=>'form-control','id'=>'tt-NotifyCreator']) !!}
                            </div>
                        </div>

                        <div></div>

                        <div class="btn-toolbar">
                            {!! Form::submit(Lang::get('labels.save'),['class'=> 'btn btn-primary form-control']) !!}
                        </div>
                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>

@stop