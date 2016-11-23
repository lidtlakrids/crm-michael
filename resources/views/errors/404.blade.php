@extends('layout.main')
@section('page-title',"Server error")
@section('styles')
@stop

@section('scripts')
@stop

@section('content')
    <div class="row">
        <div class="alert alert-danger">
            @lang('messages.resource-not-found')
            <a class="btn btn-green" href="{{url(URL::previous())}}">Back</a>
        </div>
    </div>
@stop