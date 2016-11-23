@extends('layout.main')
@section('page-title',Lang::get('labels.country')." : ".$country->Name)
@section('content')
{{dd($country)}}
@stop