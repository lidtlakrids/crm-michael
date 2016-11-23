@extends('layout.main')
@section('page-title',"Clients stats")

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    @include('scripts.statistics-scripts')
    <script>
        $(document).ready(function () {
            var startDate = moment('2016-08-01');
            var enddate = moment().utc().endOf('year');
            var months = monthsListBetweenDates(startDate,enddate);

            $('.clientsResultsSwitch').on('click',function (event) {
                var data = $(event.target).closest('.clientsResultsSwitch').data();
//                if(data.period) window.open(base_url+'/statistics/expected-payments/'+data.period,'_blank');
                $('.clientStatsResults').hide();
                $('#'+data.period).removeClass('hidden').show();
            });

//            $('.clientStatsResults').DataTable({
//                'bFilter':false,
//                bPaginate:false,
//                bInfo:false,
//                aaSorting:[[1,"asc"]] // shows the newest items first
//            });

//            lostClients(months);
        })

    </script>

@stop

@section('content')
<div class="row">
    <div class="panel panel-activities">
        <div class="panel-heading"><h4><i class="fa fa-bar-chart-o"></i> Statistics</h4></div>
        <div class="panel-body">
            <div class="row">
                <ol class="breadcrumb">
                    <li>{{link_to_action('StatisticsController@clientsByType','By product type')}}</li>
                    <li>{{link_to_action('StatisticsController@activeClients','Active clients')}}</li>
                </ol>
            </div>
            <div class="row">
                <div class="table-responsive">
                    <table class="table datatables" id="table-list">
                        <thead>
                        <tr>
                            <th>Summary By month</th>
                            @foreach($months as $k=>$period)
                                <th>{{date('Y-M',strtotime($period))}}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="summary section-one">
                            <td>By month</td>
                            @foreach($periods as $period=>$stat)
                                <td>
                                    @if($stat !=='error')
                                        <span class="pseudolink clientsResultsSwitch"  data-period="{{$period}}" >
                                            <span class="alert-danger">Lost : {{count($stat['Lost'])}} </span><br>
                                            <span>Confirmed : {{count($stat['NewConfirmed'])}} </span><br>
                                            <span>Paid : {{count($stat['NewPayed'])}}</span><br>
                                            <span>Winback : {{count($stat['Winback'])}}</span><br>
                                        </span>
                                    @else
                                        error
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                @foreach($periods as $period=>$values)
                    @if($values != 'error')
                        <div class="hidden clientStatsResults" id="{{$period}}" >
                            @foreach($values as $val=>$list)
                                @if(count($list) !== 0)
                                    <div class="col-md-6">
                                    <h4>{{$val}}</h4>
                                        <?php
                                            $class ='';
                                        switch ($val){
                                            case "Lost":
                                                $class = 'danger';
                                                break;
                                            case "Winback":
                                                $class  = 'success';
                                                break;
                                            case "NewConfirmed":
                                                $class = 'info';
                                                break;
                                            case "NewPayed":
                                                $class = 'success';
                                                break;
                                            default:
                                                $class  = 'warning';
                                                break;
                                        }
                                        ?>
                                        <div class="table-responsive">
                                            <table class="table table-condensed table-striped table-hover {{$val}}" style="font-size:12px">
                                                <thead>
                                                <tr>
                                                    <th>Client</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                    <th>Seller</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($list as $c)
                                                    <tr class="{{$class}}">
                                                        <td><a target="_blank" href="{{url('clientAlias/show',$c->ClientAlias_Id)}}">{{$c->ClientAlias->Name or ""}}</a></td>
                                                        <td>{{ $c->State }}</td>
                                                        <td>{{toDate($c->DayOfStat)}}</td>
                                                        <td>{{$c->Seller->FullName or ''}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>


        </div>
    </div>
</div>
@stop