@extends('layout.main')
@section('page-title',Lang::get('labels.statistics').' '.$year)

@section('styles')
    <style>
        .aggregatedStatistics td {
            cursor: pointer;
            position: relative;
        }
    </style>

@endsection


@section('scripts')
    @include('scripts.statistics-scripts')
    <script>
        $(document).ready(function(){
            var currentYear = moment().year();

            if(currentYear == year ){  // year comes from the controller
                var start = moment().startOf('year');
                var end   = moment();
            }else{
                var start = new Date(year,0,1,0,0,0,0);
                var end   = new Date(year,11,31,23,59,59);

            }

            var months = monthsListBetweenDates(start,end);

            getStats(months);


            $('.aggregatedStatistics').on('click','td',function (event) {
                var type = $(event.target).closest('td').prop('id');
                if(type) window.open(base_url+'/accounting/stat/'+type,'_blank')
            })
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-apple"></i>Stat</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" onclick="getStats()" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <form method="get">
                                <div class="form-group col-md-4">
                                    {!! Form::select('year',[''=>'Select','2016'=>'2016','2015'=>'2015','2014'=>'2014'],$year,['class'=>'form-control','id'=>'statYearChange']) !!}
                                </div>
                                <div class="btn-toolbar">
                                    <button type="submit" class="btn btn-green">Go</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <hr />
                    <h4>Total Invoiced</h4>
                    <table class="table table-condensed datatables fixed aggregatedStatistics">
                        <thead>
                            <tr>
                                <th>Summary for {{$year}}</th>
                                @for($i=1;$i<=12;$i++)
                                    <th id="{{$year.'-'.$i}}">{{$months[$i]}}</th>
                                @endfor
                                <!--<th></th>-->
                            </tr>
                        </thead>
                        <tbody>

                            <!-- <tr class="summary">
                                <td colspan="14"><span class="header"><h4>Total Invoiced</h4></span></td>
                            </tr>-->
                            <tr class="summary section-one">
                                <td>Sent</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'invoiced-sent-'.$year.'-'.$i}}"></td>
                                @endfor
                            </tr>

                            <tr class="summary section-one">
                                <td>Overdue</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'invoiced-overdue-'.$year.'-'.$i}}"></td>
                                @endfor
                            </tr>

                            <tr class="summary section-one">
                                <td>Debt Collection</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'invoiced-debtcollection-'.$year.'-'.$i}}"></td>
                                @endfor
                            </tr>

                            </tbody>
                        </table>
                        <br />
                        <h4>Paid Amounts</h4>
                        <table class="table table-condensed datatables fixed aggregatedStatistics">
                                                    <thead>
                            <tr>
                                <th>Summary for {{$year}}</th>
                                @for($i=1;$i<=12;$i++)
                                    <th id="{{$year.'-'.$i}}">{{$months[$i]}}</th>
                                @endfor
                                <!--<th></th>-->
                            </tr>
                        </thead>
                            <tbody>

<!--                            <tr class="summary">
                                <td colspan="14"><span class="header"><h4>Paid Amounts</h4></span></td>
                            </tr>-->
                            <tr class="summary section-one">
                                <td>By month</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'paid-'.$year.'-'.$i}}"></td>
                                @endfor
                            </tr>

                                </tbody>
                            </table>
                            <br />
                            <h4>Expected payments</h4>
                            <table class="table table-condensed datatables fixed aggregatedStatistics">
                                <thead>
                                    <tr>
                                        <th>Summary for {{$year}}</th>
                                        @for($i=1;$i<=12;$i++)
                                            <th id="{{$year.'-'.$i}}">{{$months[$i]}}</th>
                                        @endfor
                                        <!--<th></th>-->
                                    </tr>
                                </thead>
                            <tbody>

 <!--                           <tr class="summary">
                                <td colspan="14"><span class="header"><h4>Expected payments</h4></span></td>
                            </tr>-->
                            <tr class="summary section-one">
                                <td>By month</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'expected-'.$year.'-'.$i}}"></td>
                                @endfor
                            </tr>

                                                            </tbody>
                            </table>
                            <br />
                            <h4>Meetings</h4>
                            <table class="table table-condensed datatables fixed aggregatedStatistics">
                                <thead>
                                    <tr>
                                        <th>Summary for {{$year}}</th>
                                        @for($i=1;$i<=12;$i++)
                                            <th id="{{$year.'-'.$i}}">{{$months[$i]}}</th>
                                        @endfor
                                        <!--<th></th>-->
                                    </tr>
                                </thead>
                            <tbody>

<!--                            <tr class="summary">
                                <td colspan="14"><span class="header"><h4>Meetings</h4></span></td>
                            </tr>-->
                            <tr class="summary section-one">
                                <td>Booked Meetings</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'meetings-'.$year.'-'.$i}}"></td>
                                @endfor
                            </tr>


                                                                                        </tbody>
                            </table>
                            <br />
                            <h4>Sales</h4>
                            <table class="table table-condensed datatables fixed aggregatedStatistics">
                                <thead>
                                    <tr>
                                        <th>Summary for {{$year}}</th>
                                        @for($i=1;$i<=12;$i++)
                                            <th id="{{$year.'-'.$i}}">{{$months[$i]}}</th>
                                        @endfor
                                        <!--<th></th>-->
                                    </tr>
                                </thead>
                            <tbody>
<!--                            <tr class="summary">
                                <td colspan="14"><span class="header"><h4>Sales</h4></span></td>
                            </tr>-->
                            <tr class="summary section-one">
                                <td>Resales</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'resales-'.$year.'-'.$i}}" class="">...</td>
                                @endfor
                                <!--<td>0</td>-->
                            </tr>
                            <tr class="summary section-two">
                                <td>New sales</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'newsales-'.$year.'-'.$i}}" class="">...</td>
                                @endfor
                                <!--<td>0</td>-->
                            </tr>
                            <tr class="summary section-three">
                                <td><strong>Total Sales</strong></td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'total-sales-'.$year.'-'.$i}}" class="">...</td>
                                @endfor
                                <!--<td>0</td>-->
                            </tr>
                            </tbody>
                            </table>
                            <br />
                            <h4>Goals</h4>
                            <table class="table table-condensed datatables fixed aggregatedStatistics">
                                <thead>
                                    <tr>
                                        <th>Summary for {{$year}}</th>
                                        @for($i=1;$i<=12;$i++)
                                            <th id="{{$year.'-'.$i}}">{{$months[$i]}}</th>
                                        @endfor
                                        <!--<th></th>-->
                                    </tr>
                                </thead>
                            <tbody>

<!--                            <tr class="summary">
                                <td colspan="14"><h4 class="header">Goals </h4></td>
                            </tr>-->
                            <tr class="summary section-one">
                                <td>Resales Goals</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'resales-goal-'.$year.'-'.$i}}" class="">...</td>
                            @endfor
                                <!--<td>0</td>-->
                            </tr>
                            <tr class="summary section-one">
                                <td>Resales difference</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'resales-goal-diff-'.$year.'-'.$i}}" class="">...</td>
                            @endfor
                                <!--<td>0</td>-->
                            </tr>
                            <tr class="summary section-two">
                                <td>New sales goals</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'newsales-goal-'.$year.'-'.$i}}" class="">...</td>
                            @endfor
                                <!--<td>0</td>-->
                            </tr>
                            <tr class="summary section-two">
                                <td>New sales difference</td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'newsales-goal-diff-'.$year.'-'.$i}}" class="">...</td>
                            @endfor
                                <!--<td>0</td>-->
                            </tr>

                            <tr class="summary section-three">
                                <td><strong>Total goals</strong></td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'total-goals-'.$year.'-'.$i}}" class="">...</td>
                            @endfor
                                <!--<td>0</td>-->
                            </tr>
                            <tr class="summary section-three">
                                <td>  <strong>Total difference  </strong></td>
                                @for($i=1;$i<=12;$i++)
                                    <td id="{{'total-goals-diff-'.$year.'-'.$i}}">...</td>
                            @endfor
                                <!--<td>0</td>-->
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop




