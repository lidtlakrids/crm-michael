@extends('layout.main')
@section('page-title',Lang::get('labels.edit-task').':&nbsp'.$task->Title)
@section('styles')
@stop
@section('scripts')

<script>
    $(document).ready(function () {

        $("#StartTime").datetimepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            minDate:'-1970/01/01',
            allowTimes:allowedTimes()
        });

        $("#DueTime").datetimepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            minDate:'-1970/01/01',
            allowTimes:allowedTimes()
        });

        $('#editTask').on('submit', function (event) {
            event.preventDefault();
            var formData = $(this).serializeJSON();
            delete(formData._token);
            if(formData.StartTime){
                formData.StartTime = new Date(formData.StartTime);
            }
            if(formData.DueTime){
                formData.DueTime = new Date(formData.DueTime);
            }

            formData.NotifyCreator = $('#tt-NotifyCreator').prop('checked');
            formData.Value = $('#tt-Value').prop('checked');

            // don't send empty input
            for (var prop in formData) {
                if (formData[prop] === "") {
                    formData[prop] = null;
                }
            }
            $.ajax({
                type: "PATCH",
                url: api_address + 'TaskLists(' + getModelId() + ')',
                data: JSON.stringify(formData),
                success: function (data) {
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('messages.update-was-successful'),
                        type: 'success'
                    });
                    var id = $('#TaskParentId').val() !== '' ? $('#TaskParentId').val() : getModelId();
                    window.location = base_url+"/tasks/show/"+id;
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });
    })

</script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','TaskList',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $task->Id,['id'=>'ModelId']) !!}
    {!! Form::hidden('TaskParentId',$task->ParentTaskListId,['id'=>'TaskParentId']) !!}
    {{--hidden fields for tasks--}}
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <i class="fa fa-gears"> @lang('labels.edit-task')</i>
                <div class="options">
                    {{-- If we are editing child task, go back to the parent  --}}
                    <a href="{{url('tasks/show',($task->ParentTaskListId == null)? $task->Id : $task->ParentTaskListId)}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i> @lang('labels.back')</a>
                </div>
            </div>
            <input type="hidden" value="{{$task->Id}}" id="TaskId">
            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(["id"=>"editTask"]) !!}
                    @if($task->ParentTaskListId == null)
                    <div class="form-group">
                        {!! Form::label('Model',Lang::get('labels.item-nr'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            <div class="col-md-6">
                                {!! Form::text('Model',$task->Model,['class'=>'form-control','id'=>'task-Model','placeholder'=>Lang::get('labels.item'),'list'=>'modelNames','autocomplete'=>'off']) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::text('ModelId',$task->ModelId,['class'=>'form-control','id'=>'task-ModelId','placeholder'=>Lang::get('labels.number')]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label id="clientName" style="margin-top: 5px;"></label>
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        {!! Form::label('Title',Lang::get('labels.title'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Title',$task->Title,['class'=>'form-control','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::textarea('Description',$task->Description,['class'=>'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('tt-NotifyCreator',Lang::get('labels.notify-creator'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-4">
                            {!! Form::checkbox('NotifyCreator',$task->NotifyCreator,$task->NotifyCreator,['class'=>'form-control','id'=>'tt-NotifyCreator']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Value',Lang::get('labels.completed'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-4">
                            {!! Form::checkbox('Value', $task->Value,$task->Value ,['class'=>'form-control','id'=>'tt-Value']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('StartTime',Lang::get('labels.start-time'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            <?php $startTime = ($task->StartTime != null)? Carbon::parse($task->StartTime)->format('Y/m/d H:i') : null ?>
                            {!! Form::input('text','StartTime',$startTime,['class'=>'form-control','autocomplete'=>'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('DueTime',Lang::get('labels.due-time'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            <?php $dueTime = ($task->DueTime != null)? Carbon::parse($task->DueTime)->format('Y/m/d H:i') : null ?>
                            {!! Form::input('text','DueTime',$dueTime,['class'=>'form-control','autocomplete'=>'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('AssignedTo_Id',Lang::get('labels.assigned-to'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::select('AssignedTo_Id', withEmpty($users) , $task->AssignedTo_Id, ['class' => 'form-control']) !!}
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

@stop