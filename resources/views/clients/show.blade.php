@extends('layout.main')
@section('styles')
<style>
</style>
@stop
@section('page-title',Lang::get('labels.ci-number').": ".$client->CINumber)
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4><i class="fa fa-briefcase"></i> <span>@lang('labels.ci-number') : {{$client->CINumber}}</span></h4>
                <div class="options">
                    <a href="{{url('clients/edit',$client->Id)}}" title="@lang('labels.edit')"><i class="fa fa-edit"></i></a>
                    <a href="{{url('clientAlias/create',$client->Id)}}" title="@lang('labels.create-alias')"><i class="fa fa-plus"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="col-md-12">
                    @foreach($client->ClientAlias as $alias)
                        <div class="col-md-3">
                            <div class="panel" >
                                <div class="panel-heading">
                                    <h4><a href="{{url('clientAlias/show',$alias->Id)}}">{{$alias->Name or "--"}}</a></h4>
                                    <div class="options">
                                        <a href="{{url('clientAlias/edit',$alias->Id)}}"><i class="fa fa-pencil"></i></a>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <dl style="min-height: 100px;">
                                        <dt>@lang('labels.homepage')</dt>
                                        <dd>{!! Html::link(addHttp($alias->Homepage)) !!}</dd>

                                        <dt>@lang('labels.address')</dt>
                                        <dd>{{$alias->Address or "---"}},{{$alias->zip or ""}},{{$alias->City or ""}}</dd>

                                        <dt>@lang('labels.country')</dt>
                                        <dd>{{$alias->Country->CountryCode or "---"}}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@stop