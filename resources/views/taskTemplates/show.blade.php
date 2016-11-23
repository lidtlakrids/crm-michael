@extends('layout.main')
@section('page-title',Lang::get('labels.task-template').": ".$taskTemplate->Title)

@section('styles')
    <style>
        i {
            cursor:pointer;
        }
    </style>

@stop

@section('scripts')

    <script>
        $(document).ready(function(){

            $('body').on('click','.deleteSubTask',function(){
                var task_id = $(this).data('child-id');
                var tr      = $(this).closest('tr');
                bootbox.confirm("Are you sure?", function(result) {
                    if(result){
                        $.ajax({
                            url: api_address+"TaskListTemplates("+task_id+")",
                            type: "DELETE",
                            success : function()
                            {
                                tr.remove();
                            },
                            error: function(error)
                            {
                                new PNotify({ title: 'Error', text: 'Could not delete task', type: 'error' });
                            },
                            beforeSend: function (request)
                            {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });
                    }
                });
            });

            $('.show-more-container').more({
                length: 100,ellipsisText: ' ...',
                moreText: '<i class="fa  fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
            });

            /**
             * function that makes a text appear multiline when we click "Show more"
             *
             */
            $('.more-link').on('click',function(){
                if($(this).closest('.is-more').hasClass('multiline')){
                    $(this).closest('.is-more').removeClass('multiline');
                }else{
                    $(this).closest('.is-more').addClass('multiline');
                }
            });

            $("#subTaskListTemplate").submit(function(event){
                event.preventDefault();
                var form = $(this);
                var parentId = $('input[name=Model_Id]').val();
                var data = $(this).serializeJSON();

                    //filter empties.
                    for (var prop in data) {
                        if (data[prop] === "") {
                            data[prop] = null;
                        }
                    }
                    $.ajax({
                        url: api_address + "TaskListTemplates("+parentId+")/Children",
                        type: "post",
                        data: JSON.stringify(data),
                        success: function (data) {

                            $('#subTasksTable >tbody:last-child').append('<tr>' +
                                    '<td>'+ data.Title+ '</td>' +
                                    '<td>'+ (data.Description == null ? "" : data.Description) + '</td>' +
                                    '<td>'+ users[data.AssignedTo_Id] + '</td>' +
                                    '<td><i class="fa fa-times deleteSubTask" title="@lang('labels.delete')" data-child-id="'+data.Id+'"></i>/' +
                                    '<a href="'+base_url+'/taskTemplates/edit/'+data.Id+'" title="@lang('labels.edit')"><i class="fa fa-pencil"></i></a>/'+
                                    '</tr>');
                            form[0].reset();
                        },
                        error: function (error) {
                            $(event.target).attr("disabled", false);
                            var notice =  new PNotify({
                                title: 'Error',
                                text: 'Could not save task',
                                type: 'error',
                                hide: false
                            });
                        },
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
            });
        });
    </script>
@stop

@section('content')
    <input type="hidden" value="{{$taskTemplate->Id}}" name="Model_Id">
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-tasks"></i> @lang('labels.task')</h4>
                    <div class="options">
                        <a href="{{ url('/taskTemplates/edit',$taskTemplate->Id) }}" title="@lang('labels.edit-task-template')"><i class="fa fa-pencil"></i></a>
                    </div>
                </div>

                <div class="panel-body" style="height: 200px;">
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>@lang('labels.title')</dt>
                            <dd>{{$taskTemplate->Title or "--"}}</dd>
                            <dt>@lang('labels.description')</dt>
                            <dd class="show-more-container">{{$taskTemplate->Description or "--"}}</dd>
                            <dt>@lang('labels.created-by')</dt>
                            <dd>{{$taskTemplate->Author->FullName or "--"}}</dd>
                            <dt>@lang('labels.assigned-to')</dt>
                            <dd>{{$taskTemplate->AssignedTo->FullName or "--"}}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-grape">
                <div class="panel-heading">
                   <h4><i class="fa fa-tasks"></i> @lang('labels.create-sub-tasks')</h4>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        <form id="subTaskListTemplate">
                        <div class="form-group">
                            <label for="taskTitle" class="col-sm-3 control-label">@lang('labels.title')</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="Title" id="taskTitle" required="required" placeholder="@lang('labels.title')">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="taskDescription" class="col-sm-3 control-label">@lang('labels.description')</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" name="Description" id="taskDescription" placeholder="@lang('labels.description')"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="taskUserId" class="col-sm-3 control-label">@lang('labels.user')</label>
                            <div class="col-sm-6">
                                {!! Form::select("AssignedTo_Id",withEmpty($users),null,['class'=>'form-control']) !!}
                            </div>
                        </div>
                            <button type="submit" class="btn addTaskToParent" > @lang('labels.save')</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-brown">
                <div class="panel-heading">
                   <h4><i class="fa fa-tasks"></i> @lang('labels.sub-tasks')</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-hover table-condensed table-responsive" id="subTasksTable">
                        <thead>
                        <tr>
                            <th>@lang('labels.title')</th>
                            <th>@lang('labels.description')</th>
                            <th>@lang('labels.user')</th>
                            <th>@lang('labels.actions')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($taskTemplate->Children as $child)
                            <tr>
                                <td>{{$child->Title or "-"}}</td>
                                <td class="show-more-container">{{$child->Description or ""}}</td>
                                <td class="">{{$child->AssignedTo->FullName or ""}}</td>
                                <td>
                                    <i class="fa fa-times deleteSubTask" data-child-id="{{$child->Id}}" title="@lang('labels.delete')"></i>/
                                    <a href="{{url('taskTemplates/edit',$child->Id)}}" title="@lang('labels.edit')"><i class="fa fa-pencil"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@stop