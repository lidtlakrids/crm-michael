@extends('layout.main')
@section('page-title',Lang::get('labels.denied'))
@section('styles')
@stop

@section('scripts')
@stop

@section('content')
    <div class="row">
        <a class="btn btn-sm btn-green" href="{{url(URL::previous())}}">Back</a>
        <div class="alert alert-danger">Permission denied</div>
    </div>
@stop