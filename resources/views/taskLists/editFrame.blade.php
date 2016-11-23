<script src="{{ asset('/js/lib/jquery.js') }}"></script>
<script>
    $(document).ready(function($){
        /**
         * function to load a given css file
         */
        loadCSS = function(href) {
            var cssLink = $("<link rel='stylesheet' type='text/css' href='"+href+"'>");
            $("head").append(cssLink);
        };

        /**
         * function to load a given js file
         */
        loadJS = function(src) {
            var jsLink = $("<script type='text/javascript' src='"+src+"'>");
            $("head").append(jsLink);
        };
        var base_url = location.origin;
        // load the css file
        loadCSS(base_url+"/css/jquery.datetimepicker.css");
        loadCSS(base_url+"/css/styles.css");

        // load the js file
        loadJS(base_url+"/js/jquery.datetimepicker.js");
        loadJS(base_url+"/js/jquery.js");

    });

    $(document).ready((function() {
        $( "#StartTime" ).datetimepicker({
                    changeMonth: true,
                    changeYear: true,
                    showButtonPanel: true,
                    format: "Y-m-d H:m"
                }
        );
        $( "#EndTime" ).datetimepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            format: "Y-m-d H:m"

        });
        $( "#DueTime" ).datetimepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            format: "Y-m-d H:m"

        });
    }));
</script>



<div class="col-md-6 col-md-offset-3">
    <div class="panel panel-grape">
        <div class="panel-heading">
            <i class="fa fa-gears">@lang('labels.edit-task')</i>
        </div>

        <div class="panel-body">
            {!! Form::open(['method'=>'PUT','action'=>['TaskListsController@update',$task->Id],'class'=>'form-horizontal']) !!}
            <div class="form-group">
                {!! Form::label('Model',Lang::get('labels.item-nr'),['class'=>'col-md-3 control-label']) !!}
                <div class="col-md-4">
                    {!! Form::text('Model',$task->Model,['class'=>'form-control']) !!}
                    {!! Form::text('ModelId',$task->ModelId,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('Title',Lang::get('labels.title'),['class'=>'col-md-3 control-label']) !!}
                <div class="col-md-4">
                    {!! Form::text('Title',$task->Title,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                <div class="col-md-4">
                    {!! Form::textarea('Description',$task->Description,['class'=>'form-control']) !!}
                </div>
            </div>


            <div class="form-group">
                {!! Form::label('StartTime',Lang::get('labels.start-time'),['class'=>'col-md-3 control-label']) !!}
                <div class="col-md-3">
                    {!! Form::input('date','StartTime',Carbon::parse($task->StartTime),['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('EndTime',Lang::get('labels.end-time'),['class'=>'col-md-3 control-label']) !!}
                <div class="col-md-3">
                    {!! Form::input('date','EndTime',Carbon::parse($task->EndTime),['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('DueTime',Lang::get('labels.due-time'),['class'=>'col-md-3 control-label']) !!}
                <div class="col-md-3">
                    {!! Form::input('date','DueTime',Carbon::parse($task->DueTime),['class'=>'form-control']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('AssignedTo',Lang::get('labels.assigned-to'),['class'=>'col-md-3 control-label']) !!}
                <div class="col-md-3">
                    {!! Form::select('AssignedTo', array('' => Lang::get('labels.select-user')) + $users , null, ['class' => 'field']) !!}
                </div>
            </div>

            <div class="btn-toolbar">
                {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>