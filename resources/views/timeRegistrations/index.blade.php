@extends('layout.main')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h4><i class="fa fa-clock-o"></i> Users checked in</h4>
                <div class="info-bar"></div>
            </div>
            <div class="panel-body">
                <table class="table table-striped table-bordered datatables">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Status</th>
                            <th>Checked In</th>
                            <th>Check out</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($checkedIn as $k)
                        <tr>
                            <td>{{ $k->User->UserName }} </td>
                            <td>{{ $k->Status}}</td>
                            <td>{{ Carbon::parse($k->CheckIn)->format('d-m-Y h:i')}}</td>
                            <td>@if($k->CheckOut != null){{ Carbon::parse($k->CheckOut)->format('d-m-Y h:i')}}@endif</td>
                            {{--<td>--}}
                                {{--@if($k->BreakRegistration != null)--}}
                                    {{--@foreach($k->BreakRegistration as $break)--}}
                                        {{--{{ Carbon::parse($break->StartTime)->diffInMinutes(Carbon::parse($break->EndTime)) }} minutes <br>--}}
                                    {{--@endforeach--}}
                                {{--@endif--}}
                            {{--</td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop