<div id="headerbar">
    <div class="container">
        <div class="row noPrint">
            <div class="col-xs-6 col-sm-2">
                <a target="_blank" href="{{url('timeRegistrations/screen')}}" class="shortcut-tiles tiles-midnightblue">
                    <div class="tiles-body">
                        <div class="pull-left"><i class="fa fa-users"></i></div>
                    </div>
                    <div class="tiles-footer">
                        @lang('labels.users-online')
                    </div>
                </a>
            </div>
            {{--<div class="col-xs-6 col-sm-2">--}}
                {{--<a target="_blank" href="{{url('phoneScreen')}}" class="shortcut-tiles tiles-orange">--}}
                    {{--<div class="tiles-body">--}}
                        {{--<div class="pull-left"><i class="fa fa-phone"></i></div>--}}
                    {{--</div>--}}
                    {{--<div class="tiles-footer">--}}
                        {{--@lang('labels.phone-screen')--}}
                    {{--</div>--}}
                {{--</a>--}}
            {{--</div>--}}
            <div class="col-xs-6 col-sm-2">
                <a target="_blank" href="{{url('employee-manual')}}" class="shortcut-tiles tiles-green">
                    <div class="tiles-body">
                        <div class="pull-left"><i class="fa fa-file-text"></i></div>
                    </div>
                    <div class="tiles-footer">
                        @lang('labels.employee-manual')
                    </div>
                </a>
            </div>
            <div class="col-xs-6 col-sm-2">
                <a href="#" class="shortcut-tiles tiles-danger sendBugReport">
                    <div class="tiles-body">
                        <div class="pull-left"><i class="fa fa-bug"></i></div>
                    </div>
                    <div class="tiles-footer">
                        Send a bug report
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>