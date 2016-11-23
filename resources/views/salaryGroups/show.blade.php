@extends('layout.main')
@section('page-title',Lang::get('labels.salary-group').' '.$sg->Name)

@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function(){

            $('#addRateToSalaryGroup').on('submit',function(event){
                event.preventDefault();
                var form = $(event.target);

                var json = form.serializeJSON();
                    json.Rate = json.Rate/100;
                $.ajax({
                    type: "POST",
                    url: api_address + 'ClientRates',
                    data: JSON.stringify(json),
                    success: function (data) {

                        $('.ratesList').append('<li><a href="'+base_url+'/client-rates/edit/'+data.Id+'">'+data.Rate*100 +" % - "+data.Months+" "+Lang.get('labels.months')+"</a></li>");

                        new PNotify({
                            title: Lang.get('labels.success'),
                            type: 'success'
                        });
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

            })
        })
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4>@lang('labels.salary-group') {{$sg->Name}}</h4>
                    <div class="options">
                        @if(isAllowed('salaryGroups','patch'))<a href="{{url('salary-groups/edit',$sg->Id)}}" title="@lang('labels.edit')"><i class="fa fa-pencil"></i></a>@endif
                    </div>
                </div>

                <div class="panel-body">
                    <div class="col-md-12">
                        <h4>@lang('labels.description')</h4>
                        <dl class="dl-horizontal">
                            <dt>@lang('labels.name')</dt>
                            <dd>{{$sg->Name or "--"}}</dd>
                            <dt>@lang('labels.description')</dt>
                            <dd class="multiline">{{$sg->Description or ""}}</dd>
                            <dt>@lang('labels.salary')</dt>
                            <dd>{{$sg->Salary or "--"}}</dd>
                            <dt>@lang('labels.min-turnover')</dt>
                            <dd>{{$sg->MinimumTurnover or "--"}}</dd>
                            <dt>Bonus %</dt>
                            <dd>{{$sg->BonusProcentage or ""}}</dd>
                        </dl>
                    </div>
                    {{--<div class="col-md-4">--}}
                        {{--<h4>@lang('labels.client-rates')</h4>--}}
                        {{--<ul class="ratesList">--}}
                            {{--@foreach($sg->Rates as $rate)--}}
                                {{--<li><a href="{{url('client-rates/edit',$rate->Id)}}">{{$rate->Rate*100 ." %"}} - {{$rate->Months}} @lang('labels.months')</a></li>--}}
                            {{--@endforeach--}}
                        {{--</ul>--}}
                    {{--</div>--}}

                    {{--<div class="col-md-4">--}}
                        {{--<h4>@lang('labels.create-client-rate')</h4>--}}
                        {{--<div class="form-horizontal">--}}
                        {{--<form id="addRateToSalaryGroup">--}}
                            {{--<div class="form-group">--}}
                                {{--{!! Form::label('clientRate-Rate',Lang::get('labels.rate'),['class'=>'col-md-3 control-label']) !!}--}}
                                {{--<div class="col-sm-6">--}}
                                    {{--{!! Form::number('Rate',null,['class'=>'form-control','id'=>'clientRate-Rate','required'=>'required','step'=>'0.01','min'=>0,'max'=>100]) !!}--}}
                                {{--</div>--}}
                                {{--<div class="col-sm-3">--}}
                                    {{--<p class="help-block">5%,10%..</p>--}}
                                {{--</div>--}}
                            {{--</div>--}}

                            {{--<div class="form-group">--}}
                                {{--{!! Form::label('clientRate-Months',Lang::get('labels.months'),['class'=>'col-md-3 control-label']) !!}--}}
                                {{--<div class="col-sm-6">--}}
                                    {{--{!! Form::number('Months',null,['class'=>'form-control','id'=>'clientRate-Months','required'=>'required']) !!}--}}
                                {{--</div>--}}
                                {{--<div class="col-sm-3">--}}
                                    {{--<p class="help-block"></p>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                                {{--{!! Form::hidden('SalaryGroup_Id',$sg->Id) !!}--}}
                            {{--<div class="btn-toolbar">--}}
                                {{--{!! Form::submit(strtoupper(Lang::get('labels.save')),['class'=> 'btn btn-orange btn-label form-control']) !!}--}}
                            {{--</div>--}}
                        {{--</form>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}

            </div>


        </div>
    </div>
@stop