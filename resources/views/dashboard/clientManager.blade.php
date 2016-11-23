@extends('layout.main')
@section('page-title',Lang::get('labels.dashboard'))
@section('scripts')
    <script>
        $(document).ready(function () {
            var userId = $('#user-Id').val();
            var userName = $('#user-UserName').val();
            var userQuery = "User_Id eq '"+userId+"'";
            var today = new Date();

            $('.refreshCalendar').click(function (event) {
                event.preventDefault();
                openCalendarIFrame(userName);
            });

            function dashboardStats(){

                //future appointments
                $.get(api_address+"CalendarEvents/$count?$filter=Start ge "+today.toISOString()+" and "+userQuery)
                        .success(function (data) {
                            $('.appointmentsCount').text(data);
                        });

                //unpaid invoices
                var invoiceQuery = checkOwnership('Invoices');
                $.get(api_address+"Invoices/$count?$filter=(Status eq webapi.Models.InvoiceStatus'Sent' or Status eq webapi.Models.InvoiceStatus'Overdue' or Status eq webapi.Models.InvoiceStatus'Reminder')"+invoiceQuery)
                        .success(function (data){
                            $('.unpaidInvoicesCount').text(data);
                        });

//                //Orders tile - Orders for the seller period and total unconfirmed
//                $.post(api_address+"Orders/action.OrderCount")
//                        .success(function (data) {
//                            $('.totalOrders').text(data.Confirmed);
//                            $('.unconfirmedOrders').text(data.Unconfirmed);
//
//                        });
//
//                // Paid amounts
//                $.post(api_address+"Salaries/action.PaymentCount")
//                        .success(function(paid){
//                            $('.PaymentCount').text(Number(paid.value).format(0)+" DKK")
//                        });
//
//                //Pipeline
//                $.post(api_address+"Salaries/action.PipelineCount")
//                        .success(function(pipeline){
//                            $('.PipelineCount').text(Number(pipeline.value).format(0)+" DKK")
//
//                        });
//                //overdue amount
//                $.post(api_address+"Salaries/action.OverDueCount")
//                        .success(function(overdue){
//                            $('.overdueAmount').text(Number(overdue.value).format(0)+" DKK")
//
//                        });
//
//                //Contracts renewal
//                $.post(api_address+"Contracts/action.RenewalCount")
//                        .success(function(renewal){
//                            $('.contractForRenewal').text(Number(renewal.value))
//
//                        });
//
//                //total customers
//                $.post(api_address+'ClientAlias/action.ActiveClients')
//                        .success(function (data) {
//                            $('.totalClients').text(data.ActiveClients);
//                            $('.activeClientsForPeriod').text(data.NewActiveClients);
//                        });

                // contracts that need information from client manager
                var infoQuery = checkOwnership('Contract');
                $.get(api_address+"Contracts/$count?$filter=Status eq webapi.Models.ContractStatus'Active' and NeedInformation eq true"+infoQuery)
                        .success(function(data){
                            $('.contractsNeedInformation').text(data);
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
            <div class="panel panel-sales">
                <div class="panel-heading">
                    <h4><i class="fa fa-tachometer"></i> Client Manager @lang('labels.dashboard')</h4>
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
                                    {{--
                                     <a href="#" class="list-group-item"><span class="badge">201</span><i class="fa fa-envelope"></i> Inbox</a>
                                     <a href="#" class="list-group-item"><span class="badge">4</span><i class="fa fa-eye"></i> Review</a>
                                      --}}
                                    <a href="{{url('dashboard/appointments')}}" class="list-group-item"><span class="badge appointmentsCount"></span><i class="fa fa-comments"></i> @lang('labels.appointments')</a>
                                    {{--<a href="{{url('tasks')}}" class="list-group-item"><span class="badge taskCount"></span> <i class="fa fa-check"></i> @lang('labels.tasks')</a>--}}
                                    <a href="#" class="list-group-item"><span class="badge">14</span><i class="fa fa-phone"></i> Call backs</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel">
                                <div class="list-group">
                                    <a href="{{url('dashboard/overdue')}}" class="list-group-item"><span class="badge red overdueAmount"></span><i class="fa fa-bell"></i> @lang('labels.overdue-invoices')</a>

                                    <a href="{{url('dashboard/unpaid')}}" class="list-group-item"><span class="badge unpaidInvoicesCount"></span><i class="fa fa-fire"></i> @lang('labels.unpaid-invoices')</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel">
                                <div class="list-group">

                                    <a href="{{url('/contracts/need-information')}}" class="list-group-item"><span class="badge needInformation"></span> <i class="fa fa-exclamation-circle"></i> @lang('labels.need-information')</a>
                                    <a href="#" class="list-group-item"><span class="badge activeContracts"></span> <i class="fa fa-folder-open"></i> @lang('labels.active-contracts')</a>
                                    <a href="#" class="list-group-item"><span class="badge"></span> <i class="fa fa-sort-amount-desc"></i> Startups</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel">
                                <div class="list-group">
                                    <a href="" class="list-group-item"><span class="badge red totalClients"></span> <i class="fa fa-smile-o"></i> @lang('labels.active-clients')</a>
                                    <a href="#" class="list-group-item"><span class="badge red">?</span> <i class="fa fa-thumbs-up"></i> New Customers</a>
                                    <a href="#" class="list-group-item"><span class="badge"></span> <i class="fa fa-sort-amount-desc"></i> Lost customers</a>
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
                    <a class="info-tiles tiles-toyo" href="{{url('/contracts/need-information')}}">
                        <div class="tiles-heading">@lang('labels.need-information')</div>
                        <div class="tiles-body-alt">
                            <i class="fa fa-info"></i>
                            <div class="text-center contractsNeedInformation"></div>
                            <small>Active contracts</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-success" href="">
                        <div class="tiles-heading">Renewed Clients</div>
                        <div class="tiles-body-alt">
                            <div class="text-center"><span class=" renewedClients">0</span></div>
                            <small>	&nbsp;</small>
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
                    <a class="info-tiles tiles-alizarin" href="{{url('missing-payments',Auth::user()->externalId)}}">
                        <div class="tiles-heading">@lang('labels.overdue-invoices')</div>
                        <div class="tiles-body-alt">
                            <div class="text-center"><span class="PipelineCount"></span></div>
                            <small>	&nbsp;</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Task manager panel -->
    <div class="row">
    @include('layout.dashboardTasks')
    <!-- Calendar panel start -->
    @include('layout.calendar')
    </div>

@stop
