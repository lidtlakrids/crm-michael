@extends('layout.main')
@section('page-title',Lang::get('labels.dashboard'))
@section('scripts')
    <script>
        $(document).ready(function () {
            var userId = $('#user-Id').val();
            var userName = $('#user-UserName').val();
            var DayStart = moment().utc().startOf('day');
            var DayEnd = moment().utc().startOf('day');
            var userQuery = "User_Id eq '"+userId+"'";
            var MonthStart = moment().utc().startOf('month');
            var MonthEnd = moment().utc().endOf('month');


            function dashboardStats(){

                //My optimizations
                var optimizationsQuery = checkOwnership('Contract');
                $.get(api_address+"Contracts/$count?$filter=Status eq 'Active' and NeedInformation eq false and NextOptimize ne null " +
                        "and ((ProductPackage_Id ne null and Parent_Id ne null) or (Parent_Id ne null and ProductPackage_Id eq null) or (Parent_Id eq null and ProductPackage_Id eq null))"+optimizationsQuery)
                    .success(function (data){
                        $('.optimizationsCount').text(data);
                    });

                $.get(api_address+"Contracts/$count?$filter=Status eq 'Active' and NeedInformation eq false and NextOptimize lt "+getIsoDate(DayEnd)+" and NextOptimize gt "+getIsoDate(DayStart)+optimizationsQuery)
                        .success(function (data){
                            $('.todaysOptimizations').text(data);
                        });

                $.get(api_address+"Contracts/$count?$filter=Status eq 'Active' and NeedInformation eq false and NextOptimize ne null and NextOptimize lt "+getIsoDate(DayStart)+optimizationsQuery+" and ((ProductPackage_Id ne null and Parent_Id ne null) or (Parent_Id ne null and ProductPackage_Id eq null) or (Parent_Id eq null and ProductPackage_Id eq null) or " +
                        "(Parent_Id eq null and ProductPackage_Id ne null and (Product/ProductType_Id eq 3 or Product/ProductType_Id eq 18 or Product/ProductType_Id eq 20))) ")
                        .success(function (data){
                            $('.lateOptimizations').text(data);
                        });


                $.get(api_address+'CalendarEvents/$count?$filter=Created gt '+getIsoDate(DayStart)+' and Created lt '+getIsoDate(DayEnd)+' and User_Id eq \''+userId+'\' ' +
                    'and (EventType eq \'Appointment\' or EventType eq \'HealthCheck\')')
                    .success(function (data) {
                        $('.appointmentsToday').text(data);
                    });

                $.get(api_address+'ClientAlias/$count?$filter=Contract/any(d:d/Manager_Id eq \''+userId+'\' and d/Status eq \'Active\')')
                        .success(function (data) {
                            $('.activeClients').text(data);
                        });

                var contractsValueParams = {
                    User_Id : userId,
                    StartDate : MonthStart,
                    EndDate : MonthEnd
                };

                $.ajax({
                    type: "POST",
                    url: api_address + 'ContractDailyStats/Summary',
                    data:JSON.stringify(contractsValueParams),
                    success: function (data) {
                        $('.contractMonthlyValue').text(data.value.format());
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

                $.ajax({
                    type: "POST",
                    url: api_address + 'Contracts/OptimizeStats',
                    data:JSON.stringify(contractsValueParams),
                    success: function (data) {
                        $('.optimizationsMonthlyCount').text(data.OptimizationsDone);
                        $('.optimizationsAverage').text(Number(data.OptimizationsDoneAverageDaily).toFixed(2) + ' average per day')
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

                // ingoing optimizations
                $.get(api_address+'Contracts/$count?$filter=Activity/any(d:d/ActivityType eq \'StartOptimize\') and Manager_Id eq \''+userId+"'")
                    .success(function (data) {
                    $('.ongoingOptimizations').text(data)
                })
            }

            dashboardTasks();
            openCalendarIFrame(userName);
            dashboardStats();
        })
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-adwords">
                <div class="panel-heading">
                    <h4><i class="fa fa-tachometer"></i> AdWords @lang('labels.dashboard')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <?php $firstName =explode(" ",Auth::user()->fullName);
                        $firstName = $firstName[0];
                    ?>
                    <span class="header">@lang('messages.dashboard-welcome') {{$firstName}} </span>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="panel">
                                <div class="list-group">
                                    <a href="{{url('dashboard/optimizations')}}" class="list-group-item"><span class="badge optimizationsCount"></span><i class="fa fa-gears"></i>@lang('labels.my-optimizations')</a>
                                    <a href="{{url('tasks')}}" class="list-group-item"><span class="badge taskCount"></span> <i class="fa fa-check"></i>@lang('labels.tasks')</a>
                                    <a href="{{url('appointments')}}" class="list-group-item"><span class="badge appointmentsToday"></span><i class="fa fa-users"></i>Appointments</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel">
                                <div class="list-group">
                                    <a href="{{url('dashboard/optimizations')}}" class="list-group-item"><span class="badge red lateOptimizations"></span><i class="fa fa-bell"></i>Overdue optimizations</a>
                                    <a href="{{url('dashboard/ongoing-optimizations',Auth::user()->externalId)}}" class="list-group-item"><span class="badge ongoingOptimizations"></span><i class="fa fa-clock-o"></i> Ongoing optimizations</a>
{{--                                    <a href="{{url('dashboard/unpaid')}}" class="list-group-item"><span class="badge unpaidInvoicesCount"></span><i class="fa fa-fire"></i> @lang('labels.unpaid-invoices')</a>--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel">
                                <div class="list-group">

                                    <a href="{{url('dashboard/optimizations')}}" class="list-group-item"><span class="badge red todaysOptimizations"></span> <i class="fa fa-plus"></i> Today's optimizations</a>
                                    {{--<a href="#" class="list-group-item"><span class="badge">50</span> <i class="fa fa-refresh"></i> Resales</a>--}}
{{--                                    <a href="{{url('dashboard/contract-renewal')}}" class="list-group-item"><span class="badge contractForRenewal"></span> <i class="fa fa-warning "></i> @lang('labels.up-for-renewal')</a>--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel">
                                <div class="list-group">
                                    <a href="" class="list-group-item"><span class="badge red activeClients"></span> <i class="fa fa-smile-o"></i> @lang('labels.active-clients')</a>
                                    {{-- <a href="#" class="list-group-item"><span class="badge activeContracts"></span> <i class="fa fa-folder-open"></i> @lang('labels.active-contracts')</a> --}}
                                    <a href="#" class="list-group-item"><span class="badge red">?</span> <i class="fa fa-thumbs-up"></i> New Customers</a>
                                    <a href="#" class="list-group-item"><span class="badge">?</span> <i class="fa fa-sort-amount-desc"></i> Lost customers</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Info panels / boxes -->
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-toyo" href="{{url('statistics/contract-values')}}">
                        <div class="tiles-heading">Monthly contracts value</div>
                        <div class="tiles-body-alt">
                            <div class="text-center"><span class="text-top contractMonthlyValue"></span></div>
                            <small></small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-success" href="{{url('statistics/optimizations',Auth::user()->externalId)}}">
                        <div class="tiles-heading">Monthly Optimizations</div>
                        <div class="tiles-body-alt">
                            <div class="text-center"><span class="text-top optimizationsMonthlyCount"></span></div>
                            <small class="optimizationsAverage">*</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-orange" href="{{url('dashboard/active-clients')}}">
                        <div class="tiles-heading">@lang('labels.active-clients')</div>
                        <div class="tiles-body-alt">
                            <i class="fa fa-group"></i>
                            <div class="text-center totalClients"></div>
                            <small>@lang('labels.current-period') : <span class="activeClientsForPeriod"></span></small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-alizarin" href="{{url('orders')}}">
                        <div class="tiles-heading">@lang('labels.orders')</div>
                        <div class="tiles-body-alt">
                            <i class="fa fa-shopping-cart"></i>
                            <div class="text-center totalOrders"></div>
                            <small><span class="unconfirmedOrders"></span> @lang('labels.unconfirmed-orders') </small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            @include('layout.dashboardTasks')
        </div>

        <div class="col-md-6">
            <!-- Calendar panel start -->
            @include('layout.calendar')
        </div>
    </div>
@stop
