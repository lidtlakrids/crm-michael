@extends('layout.main')
@section('page-title',Lang::get('labels.client-rate').' '.$clientRate->Rate)

@section('styles')
@stop

@section('scripts')
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','ClientRate',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $clientRate->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4>@lang('labels.client-rate') {{$clientRate->Rate}}</h4>
                    <div class="options">
                        @if(isAllowed('clientRates','patch'))<a href="{{url('client-rates/edit',$clientRate->Id)}}" title="@lang('labels.edit')"><i class="fa fa-pencil"></i></a>@endif
                    </div>
                </div>

                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <dt>@lang('labels.name')</dt>
                        <dd>{{$clientRate->Rate or "--"}}</dd>
                        <dt>@lang('labels.months')</dt>
                        <dd class="multiline">{{$clientRate->Months or ""}}</dd>
                        <dt>@lang('labels.salary-group')</dt>
                        <dd>{{$clientRate->SalaryGroup->Name or "--"}}</dd>

                    </dl>
                </div>

            </div>


        </div>
    </div>
@stop