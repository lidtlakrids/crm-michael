@extends('layout.main')
@section('page-title',"Statistics")

@section('styles')
@stop

@section('scripts')
@stop

@section('content')
<div class="row">
    <div class="panel panel-activities">
        <div class="panel-heading"><i class="fa fa-bar-chart-o"></i> Statistics </div>
        <div class="panel-body">
            <div class="row">
                <ol class="breadcrumb">
                    <li><a href="statistics/periodization">Periodization</a></li>
                    <li><a href="statistics/meetings">Meetings statistics</a></li>
                    <li><a href="statistics/contract-values">Contract values</a></li>
                    <li><a href="statistics/expected-payments">Expected payments</a></li>
                    <li><a href="statistics/missing-payments">Missing Payments</a></li>
                    <li><a href="statistics/clients">Client stats</a></li>
                    <li><a href="statistics/optimizations">Optimization statistics</a></li>
                    <li><a href="dashboard/ongoing-optimizations">Ongoing optimizations</a></li>
                    <li><a href="statistics/sellers-overview">Sellers Overview</a></li>
                </ol>
            </div>
        </div>
    </div>
</div>
@stop