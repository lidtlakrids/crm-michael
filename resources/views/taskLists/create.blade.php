@extends('layout.main')
@section('page-title',Lang::get('labels.create-task'))

@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}

@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
<script>
$(document).ready(function() {
    $("#StartTime").datetimepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        minDate:'-1970/01/01',
        allowTimes:allowedTimes(),
    });

    $("#DueTime").datetimepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        minDate:'-1970/01/01',
        allowTimes:allowedTimes(),
    });

    $('#createTaskForm').on('submit',function (event) {
        event.preventDefault();
        var formData = $(this).find(':input').filter(function () {
            return $.trim(this.value).length > 0
        }).serializeJSON();
        delete(formData._token);

        if(formData.StartTime) formData.StartTime = moment(formData.StartTime).utc();
        if(formData.DueTime) formData.DueTime = moment(formData.DueTime).utc();
        formData.NotifyCreator = $('#taskList-NotifyCreator').prop('checked');
        $.ajax({
            type: "POST",
            url: api_address + 'TaskLists',
            data: JSON.stringify(formData),
            success: function (data) {
                new PNotify({
                    title: Lang.get('labels.success'),
                    text: Lang.get('Task was created. Redirecting.'),
                    type: 'success'
                });

                window.location = base_url+'/tasks/show/'+data.Id;
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });


    })
});
</script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-tasks"> @lang('labels.create-task')</i></h4>
                </div>
                <!-- todo switch to ajax -->
                <div class="panel-body">
                    {!! Form::open(['class'=>'form-horizontal','id'=>'createTaskForm']) !!}
                    <div class="form-group">
                        {!! Form::label('Model',Lang::get('labels.item-nr'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            <div class="col-md-6">
                                {!! Form::text('Model',null,['class'=>'form-control','id'=>'task-Model','placeholder'=>Lang::get('labels.item'),'list'=>'modelNames','autocomplete'=>'off']) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::text('ModelId',null,['class'=>'form-control','id'=>'task-ModelId','placeholder'=>Lang::get('labels.number')]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label id="clientName" style="margin-top: 5px;"></label>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Title',Lang::get('labels.title'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Title',null,['class'=>'form-control','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::textarea('Description',null,['class'=>'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('StartTime',Lang::get('labels.start-time'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::input('text','StartTime',null,['class'=>'form-control','autocomplete'=>'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('DueTime',Lang::get('labels.due-time'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::input('text','DueTime',null,['class'=>'form-control','autocomplete'=>'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('AssignedTo_Id',Lang::get('labels.assigned-to'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::select('AssignedTo_Id', array('' => Lang::get('labels.select-user')) + $users , Auth::user()->externalId, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('taskList-NotifyCreator',Lang::get('labels.notify-creator'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::checkbox('NotifyCreator', false, false, ['class' => 'form-control','id'=>'taskList-NotifyCreator']) !!}
                        </div>
                    </div>
                    <div class="btn-toolbar">
                        {!! Form::submit(Lang::get('labels.save'),['class'=> 'btn btn-primary form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop