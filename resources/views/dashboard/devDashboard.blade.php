@extends('layout.main')
@section('page-title',"Development")
@section('scripts')
    <script>
        $(document).ready(function () {
            var userId = getUserId();
            var userName = getUserName();
            var userQuery = "User_Id eq '"+userId+"'";
            var today = new Date();

            $('.refreshCalendar').click(function (event) {
                event.preventDefault();
                openCalendarIFrame(userName);
            });

            function dashboardStats(){
                //new errors
                $.get(api_address+'Logs/$count?$filter=Seen eq false')
                        .success(function (data) {
                            $('.newErrors').text(data);
                        });

            }

            dashboardTasks();

            openCalendarIFrame(userName);

            dashboardStats();

            $('#orderHashSearch').on('submit',function (event) {
                var results = $('.orderByHash');
                results.empty();
                results.addClass('spinner');
                event.preventDefault();
                var form = $(this);
                var data = form.serializeJSON();
                $.get(api_address+"Orders?$filter=HashCode eq '"+data.HashCode +"'&$top=1")
                        .success(function (data) {
                            results.removeClass('spinner');
                            if(data.value[0]){
                                results.append("<a target='_blank' href='"+base_url+"/orders/show/"+data.value[0].Id+"'>Go to order</a>")
                            }else{
                                results.append('Nope')
                            }

                        })
            })

        })
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-sales">
                <div class="panel-heading">
                        <h4><i class="fa fa-code"></i> Development @lang('labels.dashboard')</h4>
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
                    <a class="info-tiles tiles-alizarin" href="{{url('/logs')}}">
                        <div class="tiles-heading">Errors</div>
                        <div class="tiles-body-alt">
                            <i class="fa fa-exclamation-circle"></i>
                            <div class="text-center newErrors"></div>
                            <small>	&nbsp; </small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-success" href="">
                        <div class="tiles-heading">New Ideas</div>
                        <div class="tiles-body-alt">
                            <div class="text-center"><span class=" renewedClients">0</span></div>
                            <small>	&nbsp;</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-orange" href="#">
                        <div class="tiles-heading">User logged in</div>
                        <div class="tiles-body-alt">
                            <i class="fa fa-group"></i>
                            <div class="text-center totalClients"></div>

                        </div>
                    </a>
                </div>`

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <form id="orderHashSearch">
                        <input type="text" name="HashCode" placeholder="Enter order hash">
                        <button type="submit">Search</button>
                    </form>
                    <div class="orderByHash" style="position: relative;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Task manager panel -->
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
