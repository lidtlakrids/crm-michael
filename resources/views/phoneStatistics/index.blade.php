@extends('layout.screen_layout')

@section('screen')
<style>
    table a:link {
        color: #666;
        font-weight: bold;
        text-decoration:none;
    }
    table a:visited {
        color: #999999;
        font-weight:bold;
        text-decoration:none;
    }
    table a:active,
    table a:hover {
        color: #bd5a35;
        text-decoration:underline;
    }
    table {

        font-family:Arial, Helvetica, sans-serif;
        color:#666;
        font-size:25px;
        text-shadow: 1px 1px 0px #fff;
        background:#eaebec;
        margin:0 auto;
        border:#ccc 1px solid;

        -moz-border-radius:3px;
        -webkit-border-radius:3px;
        border-radius:3px;

        -moz-box-shadow: 0 1px 2px #d1d1d1;
        -webkit-box-shadow: 0 1px 2px #d1d1d1;
        box-shadow: 0 1px 2px #d1d1d1;
    }
    table td { text-align: center; border-left: 1px solid #666; }
    table td:first-child { border-left: none; }
</style>
    <table>
        <thead>
        <tr>
            <th>@lang('labels.user')</th>
            <th>@lang('labels.count')</th>
            <th>@lang('labels.duration')</th>
        </tr>
        </thead>
        <tbody>
        @foreach($phoneStats as $stats)
            <tr>
              <td>{{$stats->EmployeeUsername}}</td>
              <td>{{$stats->Count}}</td>
              <td>{{ sprintf('%02d:%02d:%02d', ($stats->Duration / 3600), ($stats->Duration / 60 % 60), $stats->Duration % 60)}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@stop