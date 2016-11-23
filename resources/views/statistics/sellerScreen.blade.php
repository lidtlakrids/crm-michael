@extends('layout.screen_layout')
@section('page-title','Seller Overview')

@section('styles')
    <style>
        td{
            position: relative;
            text-align: center;
        }
        tbody td {
            height: 50px;
            font-size: 24px;
        }
        th{
            vertical-align: middle;
            text-align: center;
            font-size: 24px;
        }

        #screen-container {
            width : 100%
        }
    </style>
@endsection

@section('scripts')
    @include('scripts.dataTablesScripts')
    @include('scripts.statistics-scripts')
    <script>
        $(document).ready(function(){

            $('.forLoading').addClass('spinner');
                var today = moment().utc();
                var start = today.startOf('month').format();
                var end = today.endOf('month').format();

            screenStats(start,end);

            $('#seller-period').on('change',function () {
                $('#seller-period-filter').submit();

            });
            sellersCount = Object.keys(sellers).length;
        });


        function screenStats(start,end) {
            var i = 0;
            $.each(sellers,function (id,name) {
                $.when(
                        pendingSalesPerSellerAndMonth(id,start,end),
                        ordersPerMonth(id,getIsoDate(start), getIsoDate(end)),
                        callsPerMonth(id,getIsoDate(start),getIsoDate(end))
                ).then(function (data1,data2,data3) {
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
                    }
                });
            })
        }

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

    </script>
@stop

@section('screen')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-orange">
                <div class="panel-heading">
                    <h4>Seller Overview - {{$period}}</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                            <table class="table table-striped" width="100%">
                                <thead>
                                <tr>
                                    <th>Seller <i class="fa fa-user"></i></th>
                                    <th>Calls <i class="fa fa-phone-square"></i></th>
                                    <th>Calls duration (Month) <i class="fa fa-clock-o"></i></th>
                                    <th>Pipeline commission</th>
                                    <th>Orders today (Month)</th>
                                    <th>Calls today (Month)</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sellers as $id=>$name)
                                    <tr>
                                        <td style="vertical-align: middle">{{$name}}</td>
                                        <td style="vertical-align: middle" class="forLoading calls-{{$id}}"></td>
                                        <td style="vertical-align: middle" class="forLoading calls-duration-{{$id}}"></td>

                                        <td style="vertical-align: middle" class="forLoading pipeline-total-{{$id}}"></td>
                                        <td style="vertical-align: middle" class="forLoading pipeline-commission-{{$id}}"></td>
                                        <td style="vertical-align: middle" class="forLoading orders-{{$id}}"></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>
        </div>
    </div>

@stop




