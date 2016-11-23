@extends('layout.main')
@section('page-title',Lang::get('labels.edit-task-template')." : ".$taskTemplate->Title)
@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function(){
            var form = $('#editTaskTemplate');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
            delete(startItems['_token']);
            $(form).on('submit', function (event) {
                event.preventDefault();

                var taskTemplateId = $('#ModelId').val();
                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());
                delete(currentItems['_token']);
                var itemsToSubmit = hashDiff( startItems, currentItems);

                itemsToSubmit.NotifyCreator = $('#tt-NotifyCreator').prop("checked");
                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'TaskListTemplates('+taskTemplateId+')',
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
    {!! Form::hidden('Model','TaskListTemplate',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $taskTemplate->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-brown">
                <div class="panel-heading"><h4>@lang('labels.edit-task-template')</h4>
                    <div class="options">
                        <a href="{{url('taskTemplates/show',($taskTemplate->ParentTaskListId == null)? $taskTemplate->Id : $taskTemplate->ParentTaskListId)}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'editTaskTemplate']) !!}
                        <div class="form-group">
                            {!! Form::label('tt-Title',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Title',$taskTemplate->Title,['class'=>'form-control','required'=>'required','id'=>'tt-Title']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('tt-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::textarea('Description',$taskTemplate->Description,['class'=>'form-control','id'=>'tt-Description']) !!}
                            </div>
                        </div>


                        <div class="form-group">
                            {!! Form::label('tt-NotifyCreator',Lang::get('labels.notify-creator'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::checkbox('NotifyCreator',$taskTemplate->NotifyCreator,$taskTemplate->NotifyCreator,['class'=>'form-control','id'=>'tt-NotifyCreator']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('tt-AssignedTo_Id',Lang::get('labels.user'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::select('AssignedTo_Id',withEmpty($users),$taskTemplate->AssignedTo_Id,['class'=>'form-control','id'=>'tt-AssignedTo_Id','required'=>'required']) !!}
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