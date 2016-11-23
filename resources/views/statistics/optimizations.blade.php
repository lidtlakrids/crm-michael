@extends('layout.main')
@section('page-title',"Optimization statistics")
@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $('#optimizationsStatsTable').DataTable({
            "oLanguage": {
                "sSearch":       "",
                "sSearchPlaceholder": Lang.get('labels.search')
            }
        });
    </script>
@stop

@section('content')
    <div class="panel panel-adwords">
        <div class="panel-heading">
            <h4>{{$thisMonthName}} - @if(isset($user)){{$user->FullName or ''}}@endif</h4>
        </div>
        <div class="panel-body">
            @if(!isAdmin())
                <div class="row">
                    <div class="col-md-12">
                        <form id="contractValuesForm" method="post" class="form-inline">
                            {!! Form::token() !!}
                            <div class="form-group-sm col-md-3">
                                {!! Form::select('Time',$months,$thisMonth,['class'=>'form-control contractValuesFilter']) !!}
                            </div>
                            <div class="btn-toolbar">
                                <button class="btn btn-green">Go</button>
                            </div>
                        </form>
                    </div>
                </div>
                <hr>
                <div class="row">

                    <div class="col-md-3 col-xs-12 col-sm-6">
                        <div class="info-tiles tiles-success">
                            <div class="tiles-heading">Monthly Optimizations</div>
                            <div class="tiles-body-alt">
                                <div class="text-center"><span class="text-top optimizationsMonthlyCount">{{$stats[$user->Id]->OptimizationsDone}}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-xs-12 col-sm-6">
                        <div class="info-tiles tiles-success">
                            <div class="tiles-heading">Total time optimizing</div>
                            <div class="tiles-body-alt">
                                <div class="text-center"><span class="text-top optimizationsMonthlyCount">{{convertToHoursMins($stats[$user->Id]->TotalMinutesOnOptimize,'%02d hours %02d minutes')}}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-xs-12 col-sm-6">
                        <div class="info-tiles tiles-success">
                            <div class="tiles-heading">Average daily optimizations</div>
                            <div class="tiles-body-alt">
                                <div class="text-center"><span class="text-top optimizationsMonthlyCount">{{round($stats[$user->Id]->OptimizationsDoneAverageDaily,1)}}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-xs-12 col-sm-6">
                        <div class="info-tiles tiles-success">
                            <div class="tiles-heading">Average optimization time</div>
                            <div class="tiles-body-alt">
                                <div class="text-center"><span class="text-top optimizationsMonthlyCount">{{convertToHoursMins($stats[$user->Id]->AverageOptimizeTime,'%02d hours %02d minutes')}}</span></div>
                            </div>
                        </div>
                    </div>

                </div>
            @else
            <div class="row">
                <div class="col-md-12">
                    <form id="contractValuesForm" method="post">
                        {!! Form::token() !!}
                        <div class="form-group-sm col-md-3">
                            {!! Form::select('Time',$months,$thisMonth,['class'=>'form-control contractValuesFilter']) !!}
                        </div>

                        <div class="form-group-sm col-md-3">
                            {!! Form::select('Role',withEmpty($roles,'Select Role'),$role,['class'=>'form-control contractValuesFilter']) !!}
                        </div>

                        <div class="btn-toolbar">
                            <button class="btn btn-green">Go</button>
                        </div>
                    </form>
                </div>
            </div>
        <hr>
        <div class="row">

            <div class="col-md-12">

                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h4>Optimize stats</h4>
                    </div>
                    <div class="panel-body">
                        <div class="col-md-8">
                            <table class="table datatables" id="optimizationsStatsTable">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Total Optimizations</th>
                                        <th>Time spent optimizing</th>
                                        <th>Average daily optimizations</th>
                                        <th>Average optimization time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats as $id=>$stat)
                                        <tr>
                                            <td>{{$users[$id]}}</td>
                                            <td>{{$stat->OptimizationsDone}}</td>
                                            <td data-order="{{$stat->TotalMinutesOnOptimize}}">{{convertToHoursMins($stat->TotalMinutesOnOptimize,'%02d hours %02d minutes')}}</td>
                                            <td>{{round($stat->OptimizationsDoneAverageDaily,1)}}</td>
                                            <td>{{convertToHoursMins($stat->AverageOptimizeTime,'%02d hours %02d minutes')}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
           </div>
        @endif
        </div>
    </div>
@stop