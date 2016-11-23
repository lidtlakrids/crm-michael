@extends('layout.main')
@section('page-title',Lang::get('labels.ci-numbers'))
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $('.datatables').DataTable(
                {
//                  "processing": true,
                    "serverSide": true,
                    "ajax": "/clients/getClients",
                    "oLanguage": {
                        "sProcessing":   Lang.get('labels.processing'),
                        "sLengthMenu":   Lang.get('labels.length-menu'),
                        "sZeroRecords":  Lang.get('labels.zero-records'),
                        "sInfo":         Lang.get('labels.info'),
                        "sInfoEmpty":    Lang.get('labels.info-empty'),
                        "sInfoFiltered": Lang.get('labels.info-filtered'),
                        "sInfoPostFix":  "",
                        "sSearch":       Lang.get('labels.search'),
                        "sUrl":          "",
                        "oPaginate": {
                            "sFirst":    Lang.get('labels.first'),
                            "sPrevious": Lang.get('labels.previous'),
                            "sNext":     Lang.get('labels.next'),
                            "sLast":     Lang.get('labels.last')
                        }
                    },
                    "colums":[
                        {"data":"CINumber","fnCreateCell":function(nTd,oData){$(ntd).html("<a href='clients/show/'"+oData.id)}},
                        {"data":"Created"}

                    ]

                });
    </script>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h4><i class="fa fa-group"></i> @lang('labels.client-cvr')</h4>
                <div class="options">
                    <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                    <a href="{{url('clients/create')}}" title="@lang('labels.create-client')"><i class="fa fa-plus"></i></a>
                </div>
            </div>
            <div class="panel-body collapse in">
                <table id="example" class="table table table-hover datatables">
                    <thead>
                        <tr>
                            <th>@lang('labels.ci-number')</th>
                            <th>@lang('labels.salesman')</th>
                        </tr>
                    </thead>
                    {{--<tbody>--}}
                        {{--@if(isset($clients))--}}
                            {{--@foreach($clients as $client)--}}
                        {{--<tr>--}}
                            {{--<td><a href="{{url('clients/showClient',$client->Id)}}">{{ $client->CINumber }}</a></td>--}}
                            {{--<td>Salesman </td> <!-- todo get Salesman   -->--}}
                        {{--</tr>--}}
                            {{--@endforeach--}}
                        {{--@endif--}}
                    {{--</tbody>--}}
                </table>
            </div>
        </div>
    </div>
</div>
@stop



 