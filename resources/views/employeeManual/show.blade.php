@extends('layout.main')
@section('page-title',Lang::get('labels.employee-manual')." : ".$manual->Title)


@section('scripts')

    <script>
        $(document).ready(function () {

        });
    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','EmployeeManual',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $manual->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-gray">
                <div class="panel-heading">
                    <h4>@lang('labels.employee-manual')</h4>
                    <div class="options">
                        <a href="{{URL::previous()}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                        @if(isAllowed('employeeManuals','patch'))
                            <a href="{{url('employee-manual/edit',$manual->Id)}}" title="@lang('labels.edit')"><i class="fa fa-pencil"></i></a>
                        @endif
                    </div>
                </div>
                <div class="panel-body">
                    @if(isset($prev))
                        <div class="col-md-3">
                            <h4>@lang('labels.previous')</h4>
                            <a href="{{url('employee-manual',$prev->Id)}}">{{$prev->Title}}</a>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <h4>{{$manual->Title}}</h4>
                        <blockquote>
                            <p>{{$manual->Description}}</p>
                            <small>
                                @if($manual->Modified == null)
                                @lang('labels.published') :
                                <cite>{{date("d-m-Y H:i",strtotime($manual->Published))}}</cite>
                                @else
                                    @lang('labels.modified') :
                                    <cite>{{date("d-m-Y H:i",strtotime($manual->Modified))}}</cite>

                                @endif


                            </small>
                        </blockquote>
                        <p class="multiline">{{$manual->Content}}</p>
                    </div>
                    @if(isset($next))
                        <div class="col-md-3">
                            <h4>@lang('labels.next')</h4>
                            <a href="{{url('employee-manual',$next->Id)}}">{{$next->Title}}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop