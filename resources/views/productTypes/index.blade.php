@extends('layout.main')
@section('page-title',Lang::get('labels.product-types'))
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            $('#table-list').dataTable({
                "language": {
                    "url": "datatables-"+locale+'.json',
                },
                 "oLanguage": {
                    "sSearch":       "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                },
                "lengthMenu": [[20,50,100],[20,50,100]],
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": api_address+"ProductTypes",
                "aoColumns": [
                    {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){
                        return '<a href="productTypes/show/'+ id+'">'+id+'</a>';
                    }},
                    { "mData": "Name", "oData": "Name","sName":"Name"},
                    { "mData": "EconomicProductGroup", sType:"numeric"}
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            })
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-barcode"></i> @lang('labels.product-types')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('productTypes/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table datatables" id="table-list">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.name')</th>
                            <th>Economic Product Group</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

