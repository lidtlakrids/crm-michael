@extends('layout.main')
@section('page-title','Time logs')


@section('styles')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
@stop
@section('scripts')
    <script>
        $(document).ready(function(){

            $('#timelogsTab').on('click', function () {
                getTimelogsStats();
            });

            //declare statistics variables
            var expectedWorkingHours = 0;
            var totalCheckedInTime = 0;
            var diffWorkingDays = 0;
            var totalBreakTime = 0;
            var diffWithBreak = 0;
            var totalOtherTime = 0;
            var diffOtherTime = 0;
            var expectedWorkingDays = 0;

            var usersFilter = "Id eq " + $('#usersByRole').html();
            var timeLogsDatePicker = $('#timeLogsFilter').daterangepicker({
                maxDate: moment(),
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear',
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });
            var timeRegFilters = '(date(CheckIn) ge ' + timeLogsDatePicker.data('daterangepicker').startDate.format('YYYY-MM-DD') + ' and date(CheckIn) le ' + timeLogsDatePicker.data('daterangepicker').endDate.format('YYYY-MM-DD') + ')';

            timeLogsDatePicker.on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });

            timeLogsDatePicker.on('apply.daterangepicker', function (event, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            //format time in milliseconds. Add low point to calculate difference in days and get initial date
            function formatTime(datetime, lowPoint){
                var year = 2000;
                var month = 0;
                var date = 1;
                if(+moment(lowPoint) < +moment(datetime)){
                    var diff = moment(datetime).dayOfYear() - moment(lowPoint).dayOfYear();
                    return +moment(datetime).year(year).month(month).date(date).add(1, 'hour').add(diff, 'day');
                }else {
                    return +moment(datetime).year(year).month(month).date(date).add(1, 'hour');
                }
            }

            getTimelogsStats();
            //get the time logs
            function getTimelogsStats() {

                //loop through all the active users
                $.each(users, function(index, element){
                    $.get(api_address + 'TimeRegistrations?$filter=User_Id eq \'' + index + "' and " + timeRegFilters + "&$select=CheckIn,CheckOut,Status&$expand=BreakRegistration($select=StartTime,EndTime)")
                        .success(function (data) {
                            var timeRegs = [];
                            var categories = [];
                            var x = 0;+
                            //create a time registration to be shown in the statistics for each log
                            $.each(data.value, function (index, el) {
                                var low = 0;
                                var high = 0;
                                var status = "";
                                var color = "";
                                categories[0] = moment(data.value[0].CheckIn).format('DD-MM-YYYY');

                                //check if there is more than one day in the data logs
                                if (index > 1 && moment(el.CheckIn).format('YYYY-MM-DD') != moment(data.value[index - 1].CheckIn).format('YYYY-MM-DD')) {
                                    //add the log to a new chart category
                                    x = x + 1;
                                    categories.push(moment(el.CheckIn).format('DD-MM-YYYY'));
                                }

                                //add low point to the time log
                                low = formatTime(el.CheckIn);

                                //add high point to the time log
                                if (el.CheckOut) {
                                    high = formatTime(el.CheckOut, el.CheckIn);
                                    status = "CheckedIn";
                                } else {
                                    status = el.Status;
                                    if (data.value[index + 1]) {
                                        high = formatTime(data.value[index + 1].CheckIn, el.CheckIn);
                                    } else {
                                        high = formatTime(Date.now(), el.CheckIn);
                                    }
                                }
                                //add the time registration before the break so that the break is visible on top
                                if (low > 0 && high > 0 && status) {
                                    //apply colors
                                    if (status == "CheckedOut") {
                                        color = "#ff8080";
                                    } else if (status == "CheckedIn") {
                                        color = "#79d279";
                                        totalCheckedInTime += high - low;
                                    } else if (status == "Break") {
                                        color = "#ffc266";
                                    } else {
                                        color = "#7094db";
                                        totalOtherTime += high - low;
                                    }
                                    var isExist = false;
                                    for(var i = 0; i < timeRegs.length; i++){
                                        if (timeRegs[i].name == status){
                                            timeRegs[i].data.push({
                                                x: x,
                                                low: low,
                                                high: high});
                                            isExist = true;
                                            break;
                                        }
                                    }
                                    if(!isExist) {
                                        timeRegs.push({
                                            name: status, color: color, data: [{
                                                x: x,
                                                low: low,
                                                high: high
                                            }]
                                        });
                                    }
                                }
                                //add break logs if any
                                if (el.BreakRegistration.length > 0) {

                                    var breakLow = [];
                                    var breakHigh = [];

                                    $.each(el.BreakRegistration, function (indexBreak, elBreak) {
                                        breakLow[indexBreak] = formatTime(elBreak.StartTime);
                                        //add high point to the break log
                                        if (elBreak.EndTime) {
                                            breakHigh[indexBreak] = formatTime(elBreak.EndTime, elBreak.StartTime);
                                        } else {
                                            breakHigh[indexBreak] = formatTime(Date.now(), elBreak.StartTime);
                                        }
                                        totalBreakTime += breakHigh[indexBreak]-breakLow[indexBreak];

                                        //add the current break time to the time registrations
                                        if (breakLow) {
                                            //find a previous time reg with the same status and save the data into it
                                            for(var i = 0; i < timeRegs.length; i++){
                                                if (timeRegs[i].name == "Break"){
                                                    timeRegs[i].data.push({
                                                        x: x,
                                                        low: breakLow[indexBreak],
                                                        high: breakHigh[indexBreak]});
                                                    return;
                                                }else{

                                                }
                                            }

//                                            totalBreakTime += breakHigh[indexBreak] - breakLow[indexBreak];
                                            timeRegs.push({
                                                name: "Break", color: "#ffc266", data: [{
                                                    x: x,
                                                    low: breakLow[indexBreak],
                                                    high: breakHigh[indexBreak]
                                                }]
                                            });
                                        }
                                    });
                                }
                            });

                            //initiate highchart
                             $('#timeChart' + index).highcharts({
                                chart: {
                                    type: 'columnrange',
                                    inverted: true
                                },
                                legend: {
                                    labelFormatter: function () {
                                        //format statuses' labels
                                        if (this.name == "CheckedIn") {
                                            return "Checked in";
                                        } else if (this.name == "CheckedOut") {
                                            return "Checked out";
                                        } else {
                                            return this.name;
                                        }
                                    }
                                },
                                title: {
                                    text: 'Time registrations - ' + element
                                },
                                xAxis: {
                                    categories: categories
                                },
                                yAxis: {
                                    type: 'datetime',
                                    dateTimeLabelFormats: {
                                        day: '+1 day'
                                    },
                                    title: {
                                        text: null
                                    }
                                },
                                 credits: {
                                     enabled: false
                                 },
                                tooltip: {
                                    formatter: function () {
                                        return moment(this.point.low).subtract(1, 'hour').format('HH:mm:ss') +
                                                ' - ' + moment(this.point.high).subtract(1, 'hour').format('HH:mm:ss') + ': <b>' + this.series.name;
                                    }
                                },
                                plotOptions: {
                                    columnrange: {
                                        grouping: false
                                    }
                                },
                                //add timeRegs array to the chart
                                series: timeRegs
                            });

                            //$('#totalCheckedInTime').html(moment.duration(totalCheckedInTime).humanize());
                            //$('#totalCheckedInTime').html(moment.duration(totalCheckedInTime).get('h'));

                            function formatTotalTime(duration, status) {
                                if(duration < 0 ){
                                    var hours = Math.floor(-moment.duration(duration).asHours());
                                    var remainder = -moment.duration(duration).asHours() % 1;
                                    var minutes = Math.round(remainder * 60);
                                } else{
                                    var hours =  - Math.floor(moment.duration(duration).asHours());
                                    var remainder = moment.duration(duration).asHours() % 1;
                                    var minutes = Math.round(remainder * 60);
                                }
                                return status + hours + " hours " + minutes + " minutes";
                            }

                            expectedWorkingDays += categories.length;
                            expectedWorkingHours = expectedWorkingDays*-8;
                            diffWorkingDays = moment.duration(moment.duration(expectedWorkingHours, 'h')).asMilliseconds() - totalCheckedInTime;
                            diffWithBreak = diffWorkingDays -  (diffWorkingDays < 0 ? totalBreakTime : totalBreakTime*-1);
                            diffOtherTime = diffWithBreak - totalOtherTime*-1;

                            $('#expectedWorkingHours').html(formatTotalTime(moment.duration(expectedWorkingHours, 'h'), "Expected working time: "));
                            $('#totalCheckedInTime').html(formatTotalTime(totalCheckedInTime, "Total checked in time: "));
                            $('#diffWorkingDays').html(formatTotalTime(diffWorkingDays, "Difference: "));

                            $('#totalBreakTime').html(formatTotalTime(totalBreakTime, "Total break time: "));
                            $('#diffWithBreak').html(formatTotalTime(diffWithBreak, "Difference with breaks: "));

                            $('#totalOtherTime').html(formatTotalTime(totalOtherTime, "Total other time: "));
                            $('#diffOtherTime').html(formatTotalTime(diffOtherTime, "Difference with other time: "));

                            $('#totalTimeChart').highcharts({
                            chart: {
                                plotBackgroundColor: null,
                                plotBorderWidth: null,
                                plotShadow: false,
                                type: 'pie'
                            },
                            title: {
                                text: 'Total time'
                            },
                            tooltip: {
                                pointFormat: ' <b>{point.percentage:.1f}%</b>'
                            },
                            credits: {
                                enabled: false
                            },
                            plotOptions: {
                                pie: {
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                        style: {
                                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                        }
                                    }
                                }
                            },
                            series: [{
                                data:[{
                                    name: 'Total checked in time',
                                    y: totalCheckedInTime,
                                    color: "#79d279"
                                    },
                                    {
                                        name: 'Total break time',
                                        y: totalBreakTime,
                                        color: "#ffc266"
                                    },
                                    {
                                        name: 'Total other time',
                                        y: totalOtherTime,
                                        color: "#7094db"
                                    }]
                            }]
                        });
                    });
                });
            }
        });
    </script>
@stop
@section('content')
    <div style="hidden: true;" id="usersByRole"></div>
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-clock-o"></i> Time Logs</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                            <form id="timeLogsRolesForm" method="get">
                                {!! Form::token() !!}
                                {{--<div class="form-group-sm col-md-3">--}}
                                    {{--{!! Form::select('Time',$months,$thisMonth,['class'=>'form-control contractValuesFilter']) !!}--}}
                                {{--</div>--}}
                                <div class="col-md-3">
                                    <div class="input-group">
                                         <span class="input-group-addon">
                                             <i class="fa fa-calendar"></i>
                                         </span>
                                        <input name="time" placeholder="Pick a date range" type="text"
                                               class="form-control " id="timeLogsFilter"
                                               style="background-color: white;cursor: pointer;"
                                               readonly="readonly" value="{{$time}}">
                                    </div>
                                </div>
                                <div class="form-group-sm col-md-3">
                                    {!! Form::select('Role',withEmpty($roles,'Select Role'),$role,['class'=>'form-control contractValuesFilter']) !!}
                                </div>

                                <div class="btn-toolbar col-sm-1">
                                    <button class="btn btn-green">Go</button>
                                </div>
                            </form>
                    </div>
                    <div class="row">
                        <div class="col-md-6" style="padding-top: 30px;">
                            <div id="expectedWorkingHours"></div>
                            <div id="totalCheckedInTime"></div>
                            <div id="diffWorkingDays"></div>
                            </br>
                            <div id="totalBreakTime"></div>
                            <div id="diffWithBreak"></div>
                            </br>
                            <div id="totalOtherTime"></div>
                            <div id="diffOtherTime"></div>
                        </div>
                        <div class="col-md-6" >
                            <div id="totalTimeChart"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            {{--{!! Form::open(['method'=>'get','action'=>'TimeRegistrationsController@timeLogs', 'class'=>'form-horizontal']) !!}--}}
                            {{--<div class="form-group">--}}
                            {{--{!! Form::label('start','Start Date:',['class'=>'col-md-3 control-label']) !!}--}}
                                {{--<div class="col-md-3">--}}
                                {{--{!! Form::input('date','start',null,['class'=>'form-control']) !!}--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            {{--<div class="form-group">--}}
                                {{--{!! Form::label('end','End Date',['class'=>'col-md-3 control-label']) !!}--}}
                                {{--<div class="col-md-3">--}}
                                {{--{!! Form::input('date','end',null,['class'=>'form-control']) !!}--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            {{--<div class="col-md-4"></div>--}}
                            {{--<div class="col-md-2">--}}
                            {{--{!! Form::submit('Get time logs',['class'=> 'btn btn-primary form-control']) !!}--}}
                            {{--</div>--}}
                            {{--{!! Form::close() !!}--}}
                        </div>
                    </div>
                    <div class="row" id="timeRegsContainer">
                        @foreach($users as $id=>$name)
                            @if(!in_array($id,$exclude))
                                <div id="timeChart{{$id}}" class="col-md-6"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection