@extends('layout.main')
@section('page-title',Lang::get('labels.win-back'))
@section('scripts')
    @include('scripts.dataTablesScripts')


    <script>
        $(document).ready(function(){
            $('#table-list').DataTable({
                bPaginate: false,
                "order": [[ 4, "asc" ]],
                "oLanguage": {
                    "sSearch":       "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                }
            })
        })

    </script>

@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-contract">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i> @lang('labels.contracts')</h4>
                    <div class="info-bar"></div>
                </div>
                <div class="panel-body collapse in">
                    <div class="table-responsive">
                        <table id="table-list" class="table table-hover datatables" width="100%">
                            <thead>
                            <tr>
                                <th>@lang('labels.client')</th>
                                <th>@lang('labels.product')</th>
                                <th>@lang('labels.country')</th>
                                <th>@lang('labels.start-date')</th>
                                <th>@lang('labels.end-date')</th>
                                <th>@lang('labels.assigned-to')</th>
                                <th>@lang('labels.client-manager')</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($endedContracts as $c)
                                    <tr>
                                        <td><a href="{{url('contracts/show',$c->Id)}}">{{$c->ClientAlias->Name or "--"}}</a></td>
                                        <td>{{$c->Product->Name or "-"}}</td>
                                        <td>{{$c->Country->CountryCode or "-"}}</td>
                                        <td data-order="{{strtotime($c->StartDate)}}">{{date('d-m-Y',strtotime($c->StartDate))}}</td>
                                        <td data-order="{{strtotime($c->EndDate)}}">{{date('d-m-Y',strtotime($c->EndDate))}}</td>
                                        <td>{{$c->Manager->FullName or "-"}}</td>
                                        <td>{{$c->ClientAlias->Client->Manager->FullName or "-"}}</td>
                                    </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

