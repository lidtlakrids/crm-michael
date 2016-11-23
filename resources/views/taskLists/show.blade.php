@extends('layout.main')
@section('page-title',Lang::get('labels.task').": ".$task->Title)

@section('styles')
    <style>
        i {
            cursor: pointer;
        }
    </style>
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    @include('scripts.dataTablesScripts')

    <script>

        $(document).ready(function () {

            $("#StartTime").datetimepicker({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                minDate: '-1970/01/01',
                allowTimes: allowedTimes()

            });

            $("#DueTime").datetimepicker({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                minDate: '-1970/01/01',
                allowTimes: allowedTimes()
            });

            $('body').on('click', '.deleteSubTask', function () {
                var task_id = $(this).closest('tr').find('input.taskCheck').val();
                var tr = $(this).closest('tr');
                bootbox.confirm("Are you sure?", function (result) {
                    if (result) {
                        $.ajax({
                            url: api_address + "TaskLists(" + task_id + ")",
                            type: "DELETE",
                            success: function () {
                                tr.remove();
                            },
                            error: function (error) {
                                new PNotify({title: 'Error', text: 'Could not delete task', type: 'error'});
                            },
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });
                    }
                });
            });


            $('.show-more-container').more({
                length: 25, ellipsisText: ' ...',
                moreText: '<i class="fa  fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
            });

            $('#taskChildForm').on('submit', function (event) {
                event.preventDefault();
                var form = $(this);
                var btn = form.find('button[type=submit]');
                btn.prop('disabled', true);
                var obj = form.find(':input').filter(function () {
                    return $.trim(this.value).length > 0
                }).serializeJSON();

                obj.NotifyCreator = form.find('#NotifyCreator').prop('checked');


                $.post(api_address + 'TaskLists(' + getModelId() + ')/Children', obj)
                        .success(function (data) {
                            if(getModelId()){
                            subtaskstable.draw();
                            }else{

                            }
                            form[0].reset();
                            $('#taskDescription').prop('rows',5);
                            btn.prop('disabled', false);

                        }).error(function (err) {
                    btn.prop('disabled', false);
                });
            });

            if(task.Model && task.ModelId){
                $.when(getCompanyName(task.Model, task.ModelId))
                        .then(function (name) {
                            if (name.value !== 'Undefined') {
                                $('.itemName').prepend(name.value);
                            }
                            else {
                                $('.itemName').prepend("View");
                            }
                        });
            }

            initalizeSubTasksTable(getModelId(),'subTasksTable')
            taskProgress(task);

            // Return a helper with preserved width of cells
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $("#subTasksTable tbody").sortable({ helper: fixHelper, opacity: 0.8, cursor: 'move', stop: function(event,ui) {
                var Id = subtaskstable.row(ui.item).data().Id;
                var sortOrder =  ui.item.index();
                //update each field sort order

                $.ajax({
                type     : "PATCH",
                url      : api_address+'TaskLists('+Id+')',
                data     : JSON.stringify({SortOrder:sortOrder}),
                success  : function() {
                },
                beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
                }
                });
//
            }});


        });
    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','TaskList',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $task->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-5">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-tasks"></i> {{$task->Title or "--"}}</h4>
                    <div class="options">

                        @if($task->ParentTaskListId != null)
                            <a href="{{ url('/tasks/show',$task->ParentTaskListId) }}" title="Go back to the Parent task"><i
                                        class="fa fa-arrow-left"></i></a>
                        @endif

                        <a href="{{ url('/tasks/edit',$task->Id) }}" title="@lang('labels.edit-task')"><i
                                    class="fa fa-pencil"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    @if($task->Parent != null)
                        <div class="alert-info">This is a sub-task of <a href="{{url('tasks/show',$task->ParentTaskListId)}}">{{$task->Parent->Title}}</a></div>
                    @endif
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8">
                                <p>
                                    Created by <strong>{{$task->CreatedBy->FullName or "--"}}</strong>
                                    on
                                    <strong>{{toDateTime($task->Created)}}</strong>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="pull-right">
                                    @if($task->DueTime)
                                        Due:  {{toDateTime($task->DueTime)}}
                                    @else
                                        <em>No due date</em>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <hr>

                        <div class="multiline">{{$task->Description or ""}}</div>
                        @if(isset($task->Model) && isset($task->ModelId))
                        <p><strong>For item:</strong> <a href="{{linkToItem($task->Model,$task->ModelId,true)}}"
                                                             target="_blank" class="itemName">View</a></p>
                        @endif
                            <br/>
                            <div class="row">
                                <p class="col-sm-1 text-center progressP" data-task-id="{{$task->Id}}"
                                   style="font-size: 12px; margin-top: -5px;"></p>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success progressBar" data-task-id="{{$task->Id}}"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">

                                    <p>
                                        <input type="hidden" id="MainTaskStatus" value="true">
                                        Assigned to <strong>{{$task->AssignedTo->FullName or "--"}}</strong>
                                    </p>
                                    <p>
                                        @if($task->CompletedBy != null && $task->EndTime != null)
                                        Completed by <strong>{{$task->CompletedBy->FullName or ""}}</strong>
                                        on <strong>{{toDateTime($task->EndTime)}}</strong>
                                        @endif
                                    </p>
                                </div>
                            </div>
                    </div>
                </div>


                <div class="panel">
                    <div class="panel-body">
                        <h4><i class="fa fa-tasks"></i> Add sub-task</h4>
                        <div class="form-horizontal">
                            <form id="taskChildForm">
                                <div class="form-group">
                                    <label for="taskTitle"
                                           class="col-sm-3 control-label">@lang('labels.title')</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="Title" id="taskTitle"
                                               required="required" placeholder="@lang('labels.title')">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="taskDescription"
                                           class="col-sm-3 control-label">@lang('labels.description')</label>
                                    <div class="col-sm-6">
                                            <textarea class="form-control autosize" name="Description" id="taskDescription"
                                                      placeholder="@lang('labels.description')"></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="taskUserId"
                                           class="col-sm-3 control-label">Assigned to</label>
                                    <div class="col-sm-6">
                                        {!! Form::select("AssignedTo_Id",withEmpty($users),null,['class'=>'form-control']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('StartTime',Lang::get('labels.start-time'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::text('StartTime',null,['class'=>'form-control','autocomplete'=>'off',"style"=>"background-color: white;cursor: pointer;","readonly"=>"readonly"]) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('DueTime',Lang::get('labels.due-time'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::text('DueTime',null,['class'=>'form-control','autocomplete'=>'off',"style"=>"background-color: white;cursor: pointer;","readonly"=>"readonly"]) !!}
                                    </div>
                                </div>

                                {{--<div class="form-group">--}}
                                    {{--<label for="taskTitle"--}}
                                           {{--class="col-sm-3 control-label">Priority</label>--}}
                                    {{--<div class="col-sm-6">--}}
                                        {{--<input type="number" class="form-control" name="SortOrder" min="0"--}}
                                               {{--required="required" placeholder="Priority">--}}
                                    {{--</div>--}}
                                {{--</div>--}}

                                <div class="form-group">
                                    {!! Form::label('NotifyCreator',Lang::get('labels.notify-creator'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::checkbox('NotifyCreator', false, false, ['class' => 'form-control','id'=>'NotifyCreator']) !!}
                                    </div>
                                </div>
                                <div class="btn-toolbar">
                                    <button class="btn btn-success" type="submit"> Add Subtask</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>


            </div>
        </div>


        <div class="col-md-7">
            <div class="panel panel-success">
                <div class="panel-body">
                    <h4><i class="fa fa-tasks"></i> Sub tasks</h4>
                    <h6>Simply drag-and drop to reorder</h6>
                    <table class="table table-hover table-condensed table-responsive table-list"
                           style="width: 100%" id="subTasksTable">
                        <thead>
                        <tr>
                            <th>@lang('labels.title')</th>
                            <th>@lang('labels.description')</th>
                            <th>Assigned to</th>
                            <th>Created by</th>
                            <th>@lang('labels.start-time')</th>
                            <th>@lang('labels.due-time')</th>
                            {{--<th>Sort Order</th>--}}
                            <th>@lang('labels.actions')</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{--@foreach($task->Children as $child)--}}
                        {{--<tr @if($child->Value){{'class=crossed-through'}} @endif>--}}
                        {{--<td>{{$child->Title or "-"}}</td>--}}
                        {{--<td class="show-more-container">{{$child->Description or ""}}</td>--}}
                        {{--<td>{{$child->AssignedTo->FullName or ""}}</td>--}}
                        {{--<td>{{($child->StartTime != null)? Carbon::parse($child->StartTime)->format('d-m-Y H:i') : null}}</td>--}}
                        {{--<td>{{($child->DueTime != null)? Carbon::parse($child->DueTime)->format('d-m-Y H:i') : null}}</td>--}}
                        {{--<td>--}}
                        {{--<i class="fa fa-times deleteSubTask" title="@lang('labels.delete')"></i>/--}}
                        {{--<span title='Edit the sub task' class="pseudolink"><i class="fa fa-pencil quickEditSubTask"></i></span>/--}}
                        {{--<input class="taskCheck tableTask" @if($child->Value) {{"checked=checked"}} @endif title="@lang('labels.complete-task')" type="checkbox" id="task_{{$child->Id}}" value="{{$child->Id}}">--}}
                        {{--</td>--}}
                        {{--</tr>--}}
                        {{--@endforeach--}}
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    @include('layout.tabs-section',
                    [
                     'files'=>true,
                    ])
                </div>
            </div>
        </div>
    </div>

@stop