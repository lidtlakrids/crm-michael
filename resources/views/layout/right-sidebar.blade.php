<!-- BEGIN RIGHTBAR -->
<div id="page-rightbar">
    <div id="widgetarea">
        {{-- START TASKS--}}
        <div class="widget">
            <div class="widget-heading">
                <a href="javascript:;" data-toggle="collapse" data-target="#taskbody"><h4> <i class="fa fa-plus"></i> Add @lang('labels.tasks')</h4></a>
            </div>
            <div class="widget-body collapse in" id="taskbody">
               <div id="forSideMenu"></div>
                <div class="col-xs-12" style="margin-top: 10px;  ">
                    <form id="quickAddTaskForm">
                           <div class="col-md-12">
                               <input class="form-control" type="text" required="required" placeholder="@lang('labels.create-task')" name="Title">
                           </div>
                        <div style="display: none"> <button type="submit" class="btn btn-xs saveTask" > @lang('labels.save') </button></div>
                    </form>
                </div>
            </div>
        </div>
        {{-- END TASKS --}}

        {{-- START MY TASKS--}}
        <div class="widget">
            <div class="widget-heading">
                <a href="javascript:;" data-toggle="collapse" data-target="#myTaskBody"><h4><i class="fa fa-tasks"></i> @lang('labels.my-tasks')</h4></a>
            </div>
            <div class="widget-body collapse in" id="myTaskBody" style="position: relative; margin-top: 10px;">
            </div>
            <div style="height: 20px; margin-top: 10px; margin-right:15px; font-size: 12px;">
            <span class="more pull-right"><a href="{{url('tasks')}}">See @lang('labels.all-tasks')</a></span>
            </div>
        </div>
        {{-- END MY TASKS --}}

        {{-- Item  TASKS--}}
        <div class="widget">
            <div class="widget-heading">
                <a href="javascript:;" data-toggle="collapse" data-target="#itemTasksBody"><h4><i class="fa fa-thumb-tack"></i> @lang('labels.item-tasks')</h4></a>
            </div>
            <div class="widget-body collapse in" id="itemTasksBody">
            </div>
        </div>
        {{-- END ITem TASKS --}}
    </div>
</div>
<!-- END RIGHTBAR -->