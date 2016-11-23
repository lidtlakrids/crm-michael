@extends('layout.main')
@section('page-title',Lang::get('labels.booking'))
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            var callingNumber = getUserLocalNumber();
            var userId = getUserId();
            var start = new Date();
            start.setHours(0,0,0,0);
            var end = new Date();
            end.setHours(23,59,59,999);

            if(callingNumber){
            $.get(api_address+'CallLogs/$count?$filter=CallDirection eq \'Outgoing\' ' +
                'and EmployeeLocalNumber eq '+getUserLocalNumber()+'' +
                ' and TimeStamp gt '+getIsoDate(start)+' and TimeStamp lt '+getIsoDate(end))
                .success(function (data) {
                    $('.callsToday').text(data);
                });
            }

            $.get(api_address+'CalendarEvents/$count?$filter=Created gt '+getIsoDate(start)+' and Created lt '+getIsoDate(end)+' and Booker_Id eq \''+getUserId()+'\' ' +
                    'and (EventType eq \'Appointment\' or EventType eq \'HealthCheck\')')
                    .success(function (data) {
                       $('.bookedMeetingsToday').text(data);
                    });

            $.get(api_address+'Leads/$count?$filter=User_Id eq \''+userId+'\' and Status eq \'New\'')
                    .success(function (data) {
                        $('.newLeadsCount').text(data);
                    });

            $.get(api_address+'Leads/$count?$filter=User_Id eq \''+userId+'\' and Status ne \'New\'')
                    .success(function (data) {
                        $('.usedLeadsCount').text(data);
                    });
            $.get(api_address+'CalendarEvents/$count?$filter=Created gt '+getIsoDate(start)+' and Created lt '+getIsoDate(end)+' and User_Id eq \''+getUserId()+'\'')
                    .success(function (data) {
                        $('.appointmentsToday').text(data);
                    });


            dashboardTasks();

            openCalendarIFrame(getUserName());
        });
    </script>
@stop
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-sales">
                <div class="panel-heading">
                    <h4><i class="fa fa-phone"></i> @lang('labels.booking') @lang('labels.dashboard')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="list-group">
                                <a href="#" class="list-group-item"><span class="badge callsToday"></span> <i class="fa fa-phone"></i>@lang('labels.calls-today') <i title="@lang('messages.calls-update-delay')" class="fa fa-exclamation"></i></a>
                                <a href="{{url('appointments')}}" class="list-group-item"><span class="badge bookedMeetingsToday"></span> <i class="fa fa-envelope"></i>@lang('labels.meetings-booked-today')</a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="list-group">
                                <a href="{{url('leads')}}" class="list-group-item"><span class="badge newLeadsCount"></span> <i class="fa fa-eye"></i>@lang('labels.new-leads')</a>
                                <a href="{{url('leads')}}" class="list-group-item"><span class="badge usedLeadsCount"></span> <i class="fa fa-comments">@lang('labels.used-leads')</i>@</a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="list-group">
                                <a href="{{url('appointments')}}" class="list-group-item"><span class="badge appointmentsToday"></span> <i class="fa fa-comments"></i>@lang('labels.appointments-today')</a>
                                <a href="{{url('statistics/meetings')}}" class="list-group-item"><span class="badge bookedClients"></span> <i class="fa fa-thumbs-up"></i>@lang('labels.booked-clients')</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task manager panel -->
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
@stop
