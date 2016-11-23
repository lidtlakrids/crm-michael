@extends('layout.main')
@section('page-title',"Contract Values")

@section('styles')
@stop

@section('scripts')
    <script type='text/javascript'>
        $(document).ready(function () {
//            $('.contractValuesFilter').on('change',function(){
//                $('#contractValuesForm').submit();
//            });
        })
    </script>
@stop

@section('content')
    <div class="panel">
        <div class="panel-heading">
            <h4>{{$thisMonthName}} - @if(isset($user)){{$user->FullName}}@endif</h4>
        </div>
        <div class="panel-body">
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
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4>Contract Values By User</h4>
                        </div>
                        <div class="panel-body">
                            <!--<div class="col-md-6">
                                <dl class="dl-horizontal-row">
                                    <dt> TOTAL : </dt>
                                    <dd><strong>{{formatMoney($stats['total'])}} {{config('gcm.money-code')}}</strong></dd>
                                    @foreach($stats as $id=>$val)
                                        @if(isset($users[$id]))
                                        <dt><a href="{{url('statistics/contract-values',$id)}}">{{$users[$id]}}</a></dt>
                                        <dd>{{formatMoney($val)}} {{config('gcm.money-code')}}</dd>
                                        @endif
                                    @endforeach
                                    <dt> TOTAL : </dt>
                                    <dd><strong>{{formatMoney($stats['total'])}} {{config('gcm.money-code')}}</strong></dd>
                                </dl>
                            </div>-->

                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table datatables table-hover">
                                        <thead>
                                            <tr>
                                                <th>Total</th>
                                                <th>{{formatMoney($stats['total'])}} {{config('gcm.money-code')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stats as $id=>$val)
                                                @if(isset($users[$id]))
                                                <tr>
                                                    <td><a href="{{url('statistics/contract-values',$id)}}">{{$users[$id]}}</a></td>
                                                    <td>{{formatMoney($val)}} {{config('gcm.money-code')}}</td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>Total</th>
                                                <td>{{formatMoney($stats['total'])}} {{config('gcm.money-code')}}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@stop