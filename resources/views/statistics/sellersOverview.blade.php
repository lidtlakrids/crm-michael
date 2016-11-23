@extends('layout.main')
@section('page-title','Seller Overview')

@section('styles')
    <style>
        td{
            position: relative;
            text-align: center;
        }
        th{
            text-align: center;

        }

        #seller-screen .modal-dialog {
            width: 95%;
            height: 100%;
            padding: 0;
        }

        #seller-screen .modal-content {
            height: 100%;
            border-radius: 0;
        }
    </style>
@endsection

@section('scripts')
    @include('scripts.dataTablesScripts')
    @include('scripts.statistics-scripts')
    <script>
        $(document).ready(function(){

            $('.forLoading').addClass('spinner');
            if(!monthPeriod) {
                var today = moment().utc();
                var start = today.startOf('month').format();
                var end = today.endOf('month').format();
            }else{
                var date = moment(monthPeriod+'-01');
                var start = date.startOf('month').format();
                var end = date.endOf('month').format();
            }
            pendingOverview(start,end);
            paymentOverview(start,end);

            $('#seller-period').on('change',function () {
                $('#seller-period-filter').submit();

//                var month = $('#seller-period option:selected').text();
//                var today = moment(month+'-01').utc();
//                var start = today.startOf('month').format();
//                var end = today.endOf('month').format();
//                $('.forLoading').addClass('spinner');
//                pendingOverview(start,end);
//                paymentOverview(start,end);
            });
            sellersCount = Object.keys(sellers).length;
        });

        function ordersPerMonth(userId,start,end) {
            return $.get(api_address+'Orders?$filter=ArchivedDate eq null and User/Active eq true and User_Id eq \''+userId+'\' and (Created le '+end+' and Created ge '+start+')'+
                        '&$select=Created');
        }
        function callsPerMonth(userId,start,end) {

            return $.when($.get(api_address+"Users('"+userId+"')?$select=EmployeeLocalNumber"))
                .then(function (data) {
                    if(data.EmployeeLocalNumber != null){
                        return $.get(api_address+'CallLogs?$filter=EmployeeLocalNumber eq '+data.EmployeeLocalNumber+
                                ' and Duration ge 30 and (TimeStamp le '+end+' and TimeStamp ge '+start+") and CallDirection eq 'Outgoing'&$select=TimeStamp,Duration")
                    }
                });
        }

        function callsOverview() {
            var SalesRole_Id = '';

            $.ajax({
                type: "POST",
                url: api_address + 'CallLogs/CallsOverview',
                data: JSON.stringify({User_Id:id,StartDate:start,EndDate:end}),
                success: function (data) {

                },
                error: handleError,
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });}
        function pendingOverview(start,end) {
            var i = 0;
            $.each(sellers,function (id,name) {
                $.when(
                       pendingSalesPerSellerAndMonth(id,start,end),
                       ordersPerMonth(id,getIsoDate(start), getIsoDate(end)),
                       callsPerMonth(id,getIsoDate(start),getIsoDate(end))
                    )
                    .then(function (data1,data2,data3) {
                        var commission = 0,total = 0,ordersToday = 0,ordersMonth = 0,callsToday = 0,callsMonth=0,durationToday = 0,durationMonth =0;
                        $.each(data1[0].value,function (index,inv) {
                            commission += inv.Commission;
                            total      += inv.NetValue;
                        });
                        var pipeTotal = $('.pipeline-total-'+id);
                            pipeTotal.text(Number(total).format()).removeClass('spinner');
                            pipeTotal.attr('data-order',total);

                        var pipeCommission = $('.pipeline-commission-'+id);
                            pipeCommission.text(Number(commission).format()).removeClass('spinner');
                            pipeCommission.attr('data-order',commission);

                        var orders = $('.orders-'+id);
                        if(data2[0].value.length == 0) {
                            orders.text(0 +' ( 0 )').removeClass('spinner');
                            orders.attr('data-order',ordersMonth);

                        }else{
                            $.each(data2[0].value,function (index,ord) {
                                var date = moment(ord.Created);
                                var TODAY = moment();
                                ordersMonth++;
                                if(date.isSame(TODAY,'d')){
                                    ordersToday++;
                                }
                                orders.text(ordersToday+' ( '+ordersMonth+' )').removeClass('spinner');
                                orders.attr('data-order',ordersMonth);

                            });
                        }

                        var calls = $('.calls-'+id);
                        var duration = $('.calls-duration-'+id);
                        if(!data3  || data3[0].value.length == 0) {
                            calls.text(0 +' ( 0 )').removeClass('spinner');
                            calls.attr('data-order',callsMonth);
                            duration.text(0 +' ( 0 )').removeClass('spinner');
                            duration.attr('data-order',durationMonth);
                        }else{
                            $.each(data3[0].value,function (index,ord) {
                                var date = moment(ord.TimeStamp);
                                var TODAY = moment();
                                callsMonth++;
                                durationMonth+=Math.round(ord.Duration/60);
                                if(date.isSame(TODAY,'d')){
                                    callsToday++;
                                    durationToday+=Math.round(ord.Duration/60);
                                }
                                calls.text(callsToday+' ( '+callsMonth+' )').removeClass('spinner');
                                calls.attr('data-order',callsMonth);
                                duration.text(minutesToStr(durationToday)+' ( '+minutesToStr(durationMonth)+' )').removeClass('spinner');
                                duration.attr('data-order',durationMonth);
                            });
                        }

                        i++;
                        if(i==sellersCount) {
                            tableInit(0);
                            tableInit(2)
                        }
                    });

//                $.ajax({
//                    type: "POST",
//                    url: api_address + 'Salaries/PendingOverview',
//                    data: JSON.stringify({User_Id:id,StartDate:start,EndDate:end}),
//                    success: function (data) {
//
//                    },
//                    beforeSend: function (request) {
//                        request.setRequestHeader("Content-Type", "application/json");
//                    }
//                });
            })
        }

        function pendingSalesPerSellerAndMonth(sellerId,start,end) {
            return  $.ajax({
                type: "POST",
                url: api_address + 'Salaries/PendingOverview',
                data: JSON.stringify({User_Id:sellerId,StartDate:start,EndDate:end}),
                success: function (data) {
                   return data;
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        }


        function paymentOverview(start,end) {
            var i = 0;
            $.each(sellers,function (id,name) {
                $.ajax({
                    type: "POST",
                    url: api_address + 'Salaries/PaidOverview',
                    data: JSON.stringify({User_Id:id,StartDate:start,EndDate:end}),
                    success: function (data) {

                        var commission = 0,total = 0;
                        $.each(data.value,function (index,inv) {
                            commission += inv.Commission;
                            total      += inv.NetValue;
                        });
                        var paidTotal = $('.paid-total-'+id);
                            paidTotal.text(Number(total).format()).removeClass('spinner');
                            paidTotal.attr('data-order',total);

                        var paidCommission  = $('.paid-commission-'+id);
                            paidCommission.text(Number(commission).format()).removeClass('spinner');
                            paidCommission.attr('data-order',commission);
                        i++;
                        if(i==sellersCount) {
                            tableInit(1);
                        }
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            })
        }


        function tableInit(i) {
            var t = $('table')[i];
            if ( ! $.fn.DataTable.isDataTable( t ) ) {
                $(t).DataTable({
                    aaSorting:[[1,"desc"]], // shows the newest items first
                    'bFilter':false,
                    'bPaginate':false,
                    'bInfo':false
                });
            }
        }
        $('#sellersOverviewScreen').click(function (event) {
            screenMode();
        });

        function screenMode() {
            var modal = $('#seller-screen').modal()
        }
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-orange">
                <div class="panel-heading">
                    <h4>Seller Overview - {{$period}}</h4>
                    <div class="options">
                       <a href="{{url('statistics/sellers-screen')}}" target="_blank"><i class="fa fa-desktop" title="Open screen mode"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="seller-period" class="control-label col-md-3">@lang('labels.select-period')</label>
                            <div class="col-md-6">
                                <form id="seller-period-filter">
                                {!! Form::select('Period',$periods,$selected,['class'=>'form-control','id'=>'seller-period']) !!}
                                </form>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">Net amounts in Danish krona (DKK) per the selected month</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Pipeline</h4>
                            <table class="table datatables table-striped">
                                <thead>
                                    <tr>
                                        <th>Seller</th>
                                        <th>Pipeline total</th>
                                        <th>Pipeline commission</th>
                                        <th>Orders today (Month)</th>
                                        <th>Calls today (Month)</th>
                                        <th>Calls duration (Month) <i class="fa fa-clock-o"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sellers as $id=>$name)
                                        <tr>
                                            <td>{{$name}}</td>
                                            <td class="forLoading pipeline-total-{{$id}}"></td>
                                            <td class="forLoading pipeline-commission-{{$id}}"></td>
                                            <td class="forLoading orders-{{$id}}"></td>
                                            <td class="forLoading calls-duration-{{$id}}"></td>
                                            <td class="forLoading calls-{{$id}}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>Paid</h4>
                            <table class="table table-striped datatables">
                                <thead>
                                <tr>
                                    <th>Seller</th>
                                    <th>Paid total</th>
                                    <th>Paid commission</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sellers as $id=>$name)
                                    <tr>
                                        <td>{{$name}}</td>
                                        <td class="forLoading paid-total-{{$id}}"></td>
                                        <td class="forLoading paid-commission-{{$id}}"></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL START -->
    <div class="modal fade" id="seller-screen" role="dialog" aria-labelledby="ModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close btn-xs" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Seller Overview - {{$period}}</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Seller <i class="fa fa-user"></i></th>
                                <th>Pipeline <i class="fa fa-filter"></i></th>
                                <th>Paid <i class="fa fa-money"></i></th>
                                <th>Orders Today (Month) <i class="fa fa-shopping-cart"></i></th>
                                <th>Calls duration (Month) <i class="fa fa-clock-o"></i></th>
                                <th>Calls today (Month) <i class="fa fa-phone-square"></i></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($sellers as $id=>$name)
                                <tr>
                                    <td>{{$name}}</td>
                                    <td class="forLoading pipeline-commission-{{$id}}"></td>
                                    <td class="forLoading paid-commission-{{$id}}"></td>
                                    <td class="forLoading orders-{{$id}}"></td>
                                    <td class="forLoading calls-duration-{{$id}}"></td>
                                    <td class="forLoading calls-{{$id}}"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@stop




