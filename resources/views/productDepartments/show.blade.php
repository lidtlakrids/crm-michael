@extends('layout.main')
@section('page-title',Lang::get('labels.product-department')." : ".$department->Name)

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','ProductDepartments',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $department->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-gray">
                <div class="panel-heading">
                    <h4>@lang('labels.product-department')</h4>
                    <div class="options">
                        <a href="{{url('productDepartments/edit',$department->Id)}}"><i class="fa fa-pencil"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <dt>@lang('labels.name')</dt>
                        <dd>{{$department->Name or "---"}}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@stop