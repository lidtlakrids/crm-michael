@extends('layout.main')
@section('page-title','Administration')

@section('styles')
    <style>
        .simple_with_animation {
            list-style: none;
            padding-left: 0px;
        }

        body.dragging, body.dragging * {
            cursor: move !important;
        }

        .dragged {
            position: absolute;
            opacity: 0.5;
            z-index: 2000;
        }

        ol.example li.placeholder {
            position: relative;
            /** More li styles **/
        }
        ol.example li.placeholder:before {
            position: absolute;
            /** Define arrowhead **/
        }
    </style>
@stop

@section('scripts')
    {!! Html::script( asset('/js/lib/jquery-sortable.min.js')) !!}
    @include('scripts.statistics-scripts')
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

            $.when(ordersToday())
                .then(function (ordersCount) {
                    $('.ordersToday').text(ordersCount);
                })
        });
    </script>
    <script>
        var adjustment;

        $("ol.simple_with_animation").sortable({
            group: 'simple_with_animation',
            pullPlaceholder: true,
            handle:".fa",
            // animation on drop
            onDrop: function ($item, container, _super) {
                var $clonedItem = $('<li/>').css({height: 0});
                $item.before($clonedItem);
                $clonedItem.animate({'height': $item.height()});

                $item.animate($clonedItem.position(), function () {
                    $clonedItem.detach();
                    _super($item, container);
                });
            },

            // set $item relative to cursor position
            onDragStart: function ($item, container, _super) {
                var offset = $item.offset(),
                pointer = container.rootGroup.pointer;
                adjustment = {
                    left: pointer.left - offset.left,
                    top: pointer.top - offset.top
                };

                _super($item, container);
            },
            onDrag: function ($item, position) {
                $item.css({
                    left: position.left - adjustment.left,
                    top: position.top - adjustment.top
                });
            }
        });
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
                    <?php $firstName = explode(" ", Auth::user()->fullName);
                    $firstName = $firstName[0];
                    ?>
                    <span class="header">@lang('messages.dashboard-welcome') {{$firstName}} </span>
                    <div class="row simple_with_animation">
                        <div class="col-md-3 panel">
                            <ol class="simple_with_animation vertical">
                                {{--
                                 <a href="#" class="list-group-item"><span class="badge">201</span><i class="fa fa-envelope"></i> Inbox</a>
                                 <a href="#" class="list-group-item"><span class="badge">4</span><i class="fa fa-eye"></i> Review</a>
                                 <a href="#" class="list-group-item"><span class="badge">14</span><i class="fa fa-phone"></i> Call backs</a>
                                  --}}
                                <li><a href="{{url('dashboard/appointments')}}" class="list-group-item"><span
                                                class="badge appointmentsCount"></span><i
                                                class="fa fa-comments"></i> @lang('labels.appointments')</a></li>
                                <li><a href="{{url('tasks')}}" class="list-group-item"><span
                                                class="badge taskCount"></span> <i
                                                class="fa fa-check"></i> @lang('labels.tasks')</a></li>
                                <li><a href="{{url('dashboard/unconfirmed')}}" class="list-group-item"><span
                                                class="badge unconfirmedOrders"></span> <i
                                                class="fa fa-exclamation-circle"></i> @lang('labels.unconfirmed-orders')
                                    </a></li>
                                <li><a href="{{url('statistics/orders')}}" class="list-group-item"><span
                                                class="badge ordersToday"></span> <i
                                                class="fa fa-exclamation-circle"></i> Orders today
                                    </a></li>
                            </ol>
                        </div>
                        <div class="col-md-3 panel">
                            <ol class="simple_with_animation vertical">
                                <li><a href="{{url('dashboard/overdue')}}" class="list-group-item"><span
                                                class="badge red overdueAmount portlet-content"></span><i
                                                class="fa fa-bell"></i> @lang('labels.overdue-invoices')</a></li>
                                <li><a href="{{url('payments',Auth::user()->externalId)}}"
                                       class="list-group-item"><span
                                                class="badge PaymentCount portlet-content"></span><i
                                                class="fa fa-money"></i> @lang('labels.paid')</a></li>
                                <li><a href="{{url('dashboard/unpaid')}}" class="list-group-item"><span
                                                class="badge unpaidInvoicesCount portlet-content"></span><i
                                                class="fa fa-fire"></i> @lang('labels.unpaid-invoices')</a></li>
                                <li><a href="{{url('dashboard/unpaid')}}" class="list-group-item"><span
                                                class="badge unpaidInvoicesCount portlet-content"></span><i
                                                class="fa fa-fire"></i> @lang('labels.unpaid-invoices')</a></li>
                            </ol>
                        </div>
                        <div class="col-md-3 panel">
                            <ol class="simple_with_animation vertical">
                                <li><a href="#" class="list-group-item"><span class="badge red">10</span> <i
                                                class="fa fa-plus"></i> New Sales</a></li>
                                <li><a href="#" class="list-group-item"><span class="badge">50</span> <i
                                                class="fa fa-refresh"></i> Resales</a></li>
                                <li><a href="{{url('dashboard/contract-renewal')}}" class="list-group-item"><span
                                                class="badge contractForRenewal"></span> <i
                                                class="fa fa-warning "></i> @lang('labels.up-for-renewal')</a></li>
                                <li><a href="{{url('dashboard/contract-renewal')}}" class="list-group-item"><span
                                                class="badge contractForRenewal"></span> <i
                                                class="fa fa-warning "></i> @lang('labels.up-for-renewal')</a></li>
                            </ol>
                        </div>
                        <div class="col-md-3 panel column">
                            <ol class="simple_with_animation vertical">
                                <li><a href="" class="list-group-item"><span class="badge red totalClients"></span> <i
                                                class="fa fa-smile-o"></i> @lang('labels.active-clients')</a></li>
                                {{-- <a href="#" class="list-group-item"><span class="badge activeContracts"></span> <i class="fa fa-folder-open"></i> @lang('labels.active-contracts')</a> --}}
                                <li><a href="#" class="list-group-item"><span class="badge red">?</span> <i
                                                class="fa fa-thumbs-up"></i> New Customers</a></li>
                                <li><a href="#" class="list-group-item"><span class="badge">?</span> <i
                                                class="fa fa-sort-amount-desc"></i> Lost customers</a></li>
                                <li><a href="#" class="list-group-item"><span class="badge">?</span> <i
                                                class="fa fa-sort-amount-desc"></i> Lost customers</a></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
         Progress bar goals
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

        <!-- Info panels / boxes -->

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-toyo"
                       href="{{url('missing-payments',Auth::user()->externalId)}}">
                        <div class="tiles-heading">@lang('labels.pipeline')</div>
                        <div class="tiles-body-alt">
                            <div class="text-center"><span class="text-top PipelineCount"></span></div>
                            <small>*</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-success" href="{{url('payments',Auth::user()->externalId)}}">
                        <div class="tiles-heading">@lang('labels.paid')</div>
                        <div class="tiles-body-alt">
                            <div class="text-center"><span class="text-top PaymentCount"></span></div>
                            <small>*</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-orange" href="{{url('dashboard/active-clients')}}">
                        <div class="tiles-heading">@lang('labels.active-clients')</div>
                        <div class="tiles-body-alt">
                            <i class="fa fa-group"></i>
                            <div class="text-center totalClients"></div>
                            <small>@lang('labels.current-period') : <span
                                        class="activeClientsForPeriod"></span>
                            </small>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-xs-12 col-sm-6">
                    <a class="info-tiles tiles-alizarin" href="{{url('orders')}}">
                        <div class="tiles-heading">@lang('labels.orders')</div>
                        <div class="tiles-body-alt">
                            <i class="fa fa-shopping-cart"></i>
                            <div class="text-center totalOrders"></div>
                            <small><span
                                        class="unconfirmedOrders"></span> @lang('labels.unconfirmed-orders')
                            </small>
                        </div>
                    </a>
                </div>
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