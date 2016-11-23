@extends('layout.main')
@section('page-title',Lang::get('labels.dashboard'))
@section('scripts')
    <script>
        $(document).ready(function () {
            userId = $('#user-Id').val();
            userName = $('#user-UserName').val();
            userQuery = "User_Id eq '" + userId + "'";
            today = new Date();

            $('.refreshCalendar').click(function (event) {
                event.preventDefault();
                openCalendarIFrame(userName);
            });

            dashboardTasks();

            openCalendarIFrame(userName);

            dashboardStats();

            clientsToCall(userId);
        });

        function clientsToCall() {
            var placeholder = $('.panel-clients-to-call');
            var today = moment().utc().format('Y-MM-DD');
            $.get(api_address+"CalendarEvents?$count=true&$top=8&$filter=not Activity/any(d:d/ActivityType eq 'Completed' or d/ActivityType eq 'Cancel') and  EventType eq 'FollowUp' and User_Id eq '"+userId+"' and date(Start) ge "+today)
                .success(function (data) {
                    var count = data["@odata.count"];
                    var items = data.value;
                        var ctc = $.map(items,function (val) {
                            return {
                                Start       : val.Start,
                                Description : val.Description,
                                EventId     : val.Id,
                                Title       : val.Summary
                            }
                        });

                    placeholder.loadTemplate(base_url+'/templates/sales/clientsToCall.html',ctc,{
                        success:function () {
                            if(count > 8){
                                placeholder.append("<li class='text-center'><a href='"+base_url+"/clients-to-call'> See "+(count-8)+" more appointments</a></li>");
                            }
                        }
                    })
                })
        }


        function dashboardStats() {

            var monthStart = moment().utc().startOf('month').utc().format('YYYY-MM-DD');
            var monthEnd = moment().utc().endOf('month').utc().format('YYYY-MM-DD');
            var monthPeriod = {StartDate:monthStart,EndDate:monthEnd};

            //future appointments
            $.get(api_address + "CalendarEvents/$count?$filter=Start ge " + today.toISOString() + " and " + userQuery)
                    .success(function (data) {
                        $('.appointmentsCount').text(data);
                    });

            //unpaid invoices
            var invoiceQuery = checkOwnership('Invoices');
            $.get(api_address + "Invoices/$count?$filter=(Status eq webapi.Models.InvoiceStatus'Sent' or Status eq webapi.Models.InvoiceStatus'Overdue' or Status eq webapi.Models.InvoiceStatus'Reminder') and year(Due) eq 2016" + invoiceQuery)
                    .success(function (data) {
                        $('.unpaidInvoicesCount').text(data);
                    });

            //Orders tile - Orders for the seller period and total unconfirmed
            $.post(api_address + "Orders/action.OrderCount")
                    .success(function (data) {
                        $('.totalOrders').text(Number(data.Confirmed)+Number(data.Unconfirmed));
                        $('.unconfirmedOrders').text(data.Unconfirmed);
                    });

                // Paid amounts
                $.ajax({
                    url: api_address + "Salaries/action.PaymentCount",
                    type: "POST",
                    data: JSON.stringify(monthPeriod),
                    success: function (paid) {
                        $('.PaymentCount').text(Number(paid.value).format(0) + " DKK")
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

                //Pipeline
                $.ajax({
                    url: api_address + "Salaries/action.PipelineCount",
                    type: "POST",
                    data: JSON.stringify(monthPeriod),
                    success: function (pipeline) {
                        $('.PipelineCount').text(Number(pipeline.value).format(0) + " DKK")
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

                //overdue amount
                $.post(api_address + "Salaries/action.OverDueCount")
                        .success(function (overdue) {
                            $('.overdueAmount').text(Number(overdue.value).format(0) + " DKK")

                        });

                //Contracts renewal
                $.post(api_address + "Contracts/action.RenewalCount")
                    .success(function (renewal) {
                        $('.contractForRenewal').text(Number(renewal.value))

                    });


            //total customers
            $.post(api_address + 'ClientAlias/action.ActiveClients')
                    .success(function (data) {
                        $('.totalClients').text(data.ActiveClients);
                        $('.activeClientsForPeriod').text(data.NewActiveClients);
                    });
        }

    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-sales">
                <div class="panel-heading">
                    <h4><i class="fa fa-tachometer"></i> @lang('labels.dashboard')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" onclick="dashboardStats()" title="Refresh"><i class="fa fa-refresh"></i></a>
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
                                    {{--
                                     <a href="#" class="list-group-item"><span class="badge">201</span><i class="fa fa-envelope"></i> Inbox</a>
                                     <a href="#" class="list-group-item"><span class="badge">4</span><i class="fa fa-eye"></i> Review</a>
                                     <a href="#" class="list-group-item"><span class="badge">14</span><i class="fa fa-phone"></i> Call backs</a>
                                      --}}
                                    <a href="{{url('dashboard/appointments')}}" class="list-group-item"><span class="badge appointmentsCount"></span><i class="fa fa-comments"></i> @lang('labels.appointments')</a>
                                    <a href="{{url('tasks')}}" class="list-group-item"><span class="badge taskCount"></span> <i class="fa fa-check"></i> @lang('labels.tasks')</a>
                                    <a href="{{url('dashboard/unconfirmed')}}" class="list-group-item"><span class="badge unconfirmedOrders"></span> <i class="fa fa-exclamation-circle"></i> @lang('labels.unconfirmed-orders')</a>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-3">
                             <div class="panel">
                                 <div class="list-group">
                                     <a href="{{url('dashboard/overdue')}}" class="list-group-item"><span class="badge red overdueAmount"></span><i class="fa fa-bell"></i> @lang('labels.overdue-invoices')</a>
                                     <a href="{{url('payments',Auth::user()->externalId)}}" class="list-group-item"><span class="badge PaymentCount"></span><i class="fa fa-money"></i> @lang('labels.paid')</a>
                                     <a href="{{url('dashboard/unpaid')}}" class="list-group-item"><span class="badge unpaidInvoicesCount"></span><i class="fa fa-fire"></i> @lang('labels.unpaid-invoices')</a>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-3">
                             <div class="panel">
                                 <div class="list-group">

                                     <a href="#" class="list-group-item"><span class="badge red">10</span> <i class="fa fa-plus"></i> New Sales</a>
                                     <a href="#" class="list-group-item"><span class="badge">50</span> <i class="fa fa-refresh"></i> Resales</a>
                                     <a href="{{url('dashboard/contract-renewal')}}" class="list-group-item"><span class="badge contractForRenewal"></span> <i class="fa fa-warning "></i> @lang('labels.up-for-renewal')</a>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-3">
                             <div class="panel">
                                 <div class="list-group">
                                     <a href="" class="list-group-item"><span class="badge red totalClients"></span> <i class="fa fa-smile-o"></i> @lang('labels.active-clients')</a>
                                     {{-- <a href="#" class="list-group-item"><span class="badge activeContracts"></span> <i class="fa fa-folder-open"></i> @lang('labels.active-contracts')</a> --}}
                                    <a href="#" class="list-group-item"><span class="badge red">?</span> <i class="fa fa-thumbs-up"></i> New Customers</a>
                                    <a href="#" class="list-group-item"><span class="badge">?</span> <i class="fa fa-sort-amount-desc"></i> Lost customers</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Progress bar goals
                <div class="row">
                    <div class="col-md-12">
                        <strong>Month Goal</strong>
                        <div class="progress">
                            <div class="progress-bar progress-bar-danger" style="width: 20%"></div>
                            <div class="progress-bar progress-bar-warning" style="width: 25%"></div>
                            <div class="progress-bar progress-bar-success" style="width: 35%"></div>
                        </div>
                    </div>
                </div>
                --}}
                </div>
            </div>
        </div>
    </div>
    <!-- Info panels / boxes -->
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-toyo" href="{{url('missing-payments',Auth::user()->externalId)}}">
                        <div class="tiles-heading">@lang('labels.pipeline')</div>
                        <div class="tiles-body-alt">
                            <div class="text-center"><span class="text-top PipelineCount"></span></div>
                            <small>Commission for missng payments</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-success" href="{{url('payments',Auth::user()->externalId)}}">
                        <div class="tiles-heading">@lang('labels.paid')</div>
                        <div class="tiles-body-alt">
                            <div class="text-center"><span class="text-top PaymentCount"></span></div>
                            <small>Commission for the month</small>
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
        <div class="col-md-4">
            @include('layout.dashboardTasks')
        </div>

        <div class="col-md-4">
            @include('layout.clientsToCall')
        </div>

        <div class="col-md-4">
            @include('layout.calendar')
        </div>
    </div>

@stop
