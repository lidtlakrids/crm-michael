@extends('layout.main')
@section('page-title','Log')
@section('content')
    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">
            </div>
            <div class="panel-body">
               <dl class="dl-horizontal">
                   <dt>@lang('labels.model')</dt>
                   <dd>{{$log->Module or "--"}}</dd>
                   <dt>Id</dt>
                   <dd>{{$log->ItemId or "--"}}</dd>
                   <dt>@lang('labels.error')</dt>
                   <dd><pre>{{$log->Error or "--"}}</pre></dd>
                   <dt>@lang('labels.created-date')</dt>
                   <dd>{{date('d-m-Y H:i',strtotime($log->Created))}}</dd>
                   <dt>@lang('labels.seen')</dt>
                   <dd><i class="fa @if($log->Seen) fa-check @else fa-times @endif"></i></dd>
               </dl>
            </div>
        </div>
    </div>



@endsection
