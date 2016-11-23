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


                //Orders tile - Orders for the seller period and total unconfirmed
                $.post(api_address+"Orders/action.OrderCount")
                        .success(function (data) {
                            $('.totalOrders').text(data.Confirmed);
                            $('.unconfirmedOrders').text(data.Unconfirmed);

                        });

                // Paid amounts
                $.post(api_address+"Salaries/action.PaymentCount")
                        .success(function(paid){
                            $('.PaymentCount').text(Number(paid.value).format(0)+" DKK")
                        });

                //Pipeline
                $.post(api_address+"Salaries/action.PipelineCount")
                        .success(function(pipeline){
                            $('.PipelineCount').text(Number(pipeline.value).format(0)+" DKK")

                        });
                //overdue amount
                $.post(api_address+"Salaries/action.OverDueCount")
                        .success(function(overdue){
                            $('.overdueAmount').text(Number(overdue.value).format(0)+" DKK")

                        });

                //Contracts renewal
                $.post(api_address+"Contracts/action.RenewalCount")
                        .success(function(renewal){
                            $('.contractForRenewal').text(Number(renewal.value))

                        });




                //total customers
                $.post(api_address+'ClientAlias/action.ActiveClients')
                        .success(function (data) {
                            $('.totalClients').text(data.ActiveClients);
                            $('.activeClientsForPeriod').text(data.NewActiveClients);
                        });

            }

            function dashboardTasks() {
                var tasksContainer = $('.panel-tasks');

                var userQuery = "AssignedTo_Id eq '"+userId+"'";
                $.get(api_address + "TaskLists?$expand=Children($filter=Value eq false)&$filter="+userQuery+"and Value eq false and ParentTaskListId eq null&$top=8&$orderby=Created").success(function (data) {
                    var tasks = $.map(data.value, function (row) {
                        var task = {};
                        task.Title = row.Title + " (" + row.Children.length + ")";
                        task.Id = row.Id;
                        task.taskLink= base_url+'/tasks/show/'+row.Id;
                        return task;
                    });

                    tasksContainer.loadTemplate(base_url + '/templates/tasks/dashboardTask.html', tasks,{success:function(){
                    }
                    });
                });
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
                                    {{--
                                     <a href="#" class="list-group-item"><span class="badge">201</span><i class="fa fa-envelope"></i> Inbox</a>
                                     <a href="#" class="list-group-item"><span class="badge">4</span><i class="fa fa-eye"></i> Review</a>
                                      --}}
                                    <a href="{{url('dashboard/appointments')}}" class="list-group-item"><span class="badge appointmentsCount"></span><i class="fa fa-comments"></i> @lang('labels.appointments')</a>
                                    <a href="{{url('tasks')}}" class="list-group-item"><span class="badge taskCount"></span> <i class="fa fa-check"></i> @lang('labels.tasks')</a>
                               </div>
                           </div>
                       </div>
                       <div class="col-md-3">
                           <div class="panel">
                               <div class="list-group">
                                   <a href="{{url('dashboard/optimizations')}}" class="list-group-item"><span class="badge optimization"></span><i class="fa fa-bell"></i> Optimizations</a>

                                   <a href="{{url('dashboard/produce')}}" class="list-group-item"><span class="badge uproduceContract"></span><i class="fa fa-fire"></i> Produce</a>
                               </div>
                           </div>
                       </div>
                       <div class="col-md-3">
                           <div class="panel">
                               <div class="list-group">

                                   <a href="#" class="list-group-item"><span class="badge">?</span> <i class="fa fa-sort-amount-desc"></i> Lost customers</a>
                                   <a href="#" class="list-group-item"><span class="badge activeContracts"></span> <i class="fa fa-folder-open"></i> @lang('labels.active-contracts')</a>
                               </div>
                           </div>
                       </div>
                       <div class="col-md-3">
                           <div class="panel">
                               <div class="list-group">
                                   <a href="" class="list-group-item"><span class="badge red totalClients"></span> <i class="fa fa-smile-o"></i> @lang('labels.active-clients')</a>
                                   <a href="#" class="list-group-item"><span class="badge red">?</span> <i class="fa fa-thumbs-up"></i> New Customers</a>
                                   {{--  <a href="#" class="list-group-item"><span class="badge">?</span> <i class="fa fa-sort-amount-desc"></i> Lost customers</a> --}}
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
                    <a class="info-tiles tiles-toyo" href="{{url('/dashboard/optimizations')}}">
                        <div class="tiles-heading">Optimizations</div>
                        <div class="tiles-body-alt">
                            <i class="fa fa-bell"></i>
                            <div class="text-center"></div>
                            <small>	&nbsp; </small>
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
                    <a class="info-tiles tiles-alizarin" href="">
                        <div class="tiles-heading">Contract Value</div>
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
        <div class="col-md-6">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="icon-highlight fa fa-check"></i> To-do List</h4>
                    <div class="options">
                        <a href="{{url('tasks/create')}}" title="Add Task"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body" style="height:450px;">
                    <ul class="panel-tasks">
                    </ul>
                    <a href="{{url('tasks/create')}}" class="btn btn-success btn-sm pull-left">@lang('labels.create-task')</a>
                    <a href="{{url('tasks')}}" class="btn btn-default-alt btn-sm pull-right">@lang('labels.all-tasks')</a>
                </div>
            </div>
        </div>
        <!-- Calendar panel start -->
        <div class="col-md-6">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4><i class="icon-highlight fa fa-calendar"></i> Calendar</h4>
                    <div class="options">
                        <a href="#" class="refreshCalendar"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body" id="calendardemo" style="height: 450px;">
                    <div id='calendarIFrame' class="responsive-iframe-container">
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
