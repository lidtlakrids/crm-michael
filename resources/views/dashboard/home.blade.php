@extends('layout.main')
@section('page-title',Lang::get('labels.dashboard'))
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {

            dashboardTasks();

            openCalendarIFrame(getUserName());

        });
    </script>
@stop
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-shield"> </i> @lang('labels.dashboard')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <h3>Hello {{ Auth::user()->name }}</h3>
                    What do you want to do today?
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="row">
            <div class="col-md-4">
                @include('layout.dashboardTasks')
            </div>

            <div class="col-md-4">

            </div>

            <div class="col-md-4">
                <!-- Calendar panel start -->
                @include('layout.calendar')
            </div>
        </div>
    </div>

@stop