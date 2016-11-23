@extends('layout.main')
@section('page-title',Lang::get('labels.active-clients'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')


    <script>
        $(document).ready(function(){
            $('#table-list').DataTable({
                bPaginate: false
            })
        })

    </script>
@stop

@section('content')
    <div class="row">
        <div class="panel panel-green">
            <div class="panel-heading">
                <h4>@lang('labels.active-clients')</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed" id="table-list">
                        <thead>
                        <tr>
                            <th>@lang('labels.name')</th>
                            <th>@lang('labels.phone')</th>
                            <th>@lang('labels.email')</th>
                            <th>@lang('labels.address')</th>
                            <th>@lang('labels.contact')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($alias as $a)
                            <tr>
                                <td>
                                    <a href="{{url('clientAlias/show',$a->Id)}}">{{$a->Name or "--"}}</a>
                                </td>
                                <td>{{$a->Phone or ""}}</td>
                                <td>{{$a->EMail or ""}}</td>
                                <td>{{$a->Address or ""}} {{$a->City or ""}} {{ $a->zip }}</td>
                                <td>{{$a->Contact[0]->Name or ""}} {{$a->Contact[0]->Email or ""}} {{ $a->Contact[0]->Phone}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
@stop