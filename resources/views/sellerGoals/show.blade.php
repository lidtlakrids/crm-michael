@extends('layout.main')
@section('page-title', "Seller goal ".$goal->User->FullName)
@section('styles')
    <style>
    .test {
    color: lightgrey;
    }
        td{
            text-align: center;
        }
    </style>
    @stop
@section('scripts')
    <script>

    $(document).ready(function () {
        $('td').each(function (index,td) {
            var $td = $(td);
            var $th = $td.closest('table').find('tr.colours').find('th').eq($td.index());
            var border = ($th.css('border-right-style'));
            if(border == 'double') $td.css('border-right',border).css('border-right-color','black');

        });
        $('table').addClass('table-hover');
        $("body").addClass("collapse-leftbar");


//        $.get(api_address+'SellerGoals('+goal.Id+')/Stats')
//            .success(function (data) {
//                $.each(data.value,function (index,val) {
//                    var date = new Date(val.Date);
//                    var day = date.getDate();
//                    $.each(val,function (index,val) {
//                        $('#'+index+'_'+day).text(Number(val).format(true));
//
//                    })
//                })
//            })
    });
//
//    function isWeekday(year, month, day) {
//        var day = new Date(year, month, day).getDay();
//        return day != 0 && day != 6;
//    }


//
//        function daysInMonth(year, month) {
//            return new Date(year, month, 0).getDate();
//        }
//
//        function getWeekdaysInMonth(month, year) {
//
//            var days = daysInMonth(month, year);
//            var week = 0;
//            var weeks=[];
//            for(var i=1; i< days; i++) {
//                if (isWeekday(year, month, i)){
//
//                    weeks.push({day: i, week: week});
//                }
//                else {
//                    week++;
//                }
//            }
//            return weeks;
//        }
//
//        var date = new Date();
//        var WeekDays  =  getWeekdaysInMonth(date.getMonth() + 1, date.getYear());
//        console.log(WeekDays);
//
//        for(var i=0; i<WeekDays.length; i++){
//
//            if (WeekDays[i].week == 2 || WeekDays[i].week == 3|| WeekDays[i].week == 6 || WeekDays[i].week == 7){
//                $("#table-list tr").append("<th class='test'>"+ WeekDays[i].day + "</th>");
//            }
//            else {
//                $("#table-list tr").append("<th>" + WeekDays[i].day + "</th>");
//            }
//        }

        //note: month is 0 based, just like Dates in js
        function getWeeksInMonth(month, year){
            var weeks=[],
            firstDate=new Date(year, month, 1),
            lastDate=new Date(year, month+1, 0),
            numDays= lastDate.getDate();
            var start=1;
            var end=7-firstDate.getDay();
            while(start<=numDays){
                weeks.push({start:start,end:end});
                start = end + 1;
                end = end + 7;
                if(end>numDays)
                    end=numDays;
            }
            return weeks;
        }
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-bar-chart-o"></i>&nbsp;Seller goals - {{$goal->User->FullName}} {{date('Y-M',strtotime($goal->Year.'-'.$goal->Month.'-15'))}}</h4>
                    <div class="options">
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="table-responsive">
                    <table class="table table-condensed table-bordered table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="130px" style="border-right: double black"></th>
                                @for($i=0;$i<=4;$i++)
                                    <th colspan="5" style="border-right: double black"> Week {{$i+1}}</th>
                                @endfor
                            </tr>
                            <tr class="colours">
                                <th style="border-right: double black"></th>
                                @for($i=0;$i<=4;$i++)
                                    @for($u=0;$u<=4;$u++)
                                        <th style="width: 50px; @if($u==4) border-right: double black @endif"> {{$days[$u]}}</th>
                                    @endfor
                                @endfor
                                <th width="50px" style="background-color: #00aa00">Totals</th>
                                <th width="130px"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $u = 1; ?>
                        @foreach($statistics as $name=>$value)
                            @foreach($value as $valName=>$numbers)
                                <tr>
                                    <td @if((strpos($valName, 'Diff') !== false)) style="font-weight: bold"  @endif>{{$name}} - {{$valName}}</td>
                                    @if($startOffset >0)
                                        @for($i = 0; $i< $startOffset;$i++)
                                            <td></td>
                                        @endfor
                                    @endif
                                    @foreach($workdays as $wd)
                                        <td title="{{"Date : ".$wd[0]}}"  style="@if((strpos($valName, 'Diff') !== false)) font-weight: bold;
                                                @if($numbers[$wd[0]] < -1) color:red @elseif($numbers[$wd[0]] >0) color:green @endif
                                        @endif"
                                             id="{{$name.'-'.$valName.'-'.$wd[0]}}">
                                            @if((strpos($valName, 'Diff') !== false))
                                                {{round($numbers[$wd[0]],1)}}
                                            @else
                                                {{round($numbers[$wd[0]],0)}}
                                            @endif
                                        </td>
                                    @endforeach

                                    @if($endOffset > 0)
                                        @for($i = 0; $i<$endOffset;$i++)
                                            <td></td>
                                        @endfor
                                    @endif
                                    <?php  $tots = formatMoney(array_sum($numbers),0)?>
                                    <td style="font-weight: bold; @if($tots < 0) color:red @elseif($tots >0) color:green @endif ">{{$tots}}</td>
                                    <td>{{$name}} - {{$valName}}</td>
                                </tr>
                            @endforeach
                            <?php $u++?>
                        @endforeach

                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop