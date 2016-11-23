@extends('layout.main')
@section('page-title',"Server error")
@section('styles')
@stop

@section('scripts')
@stop

@section('content')
    <div class="row">
        <div class="alert alert-danger">@lang('messages.there-was-an-error') <a class="btn btn-green" href="{{url(URL::previous())}}">@lang('labels.back')</a>
        </div>
    </div>
@stop