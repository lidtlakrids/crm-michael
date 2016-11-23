@extends('layout.main')
@section('page-title',Lang::get('labels.product-departments'))
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            $('#example').dataTable({
                "language": {
                    "url": "datatables-"+locale+'.json',
                },
                "oLanguage": {
                    "sSearch":       "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                },
                "lengthMenu": [[10,20, 50], [10,20, 50]],
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": api_address+"ProductDepartments",
                "aoColumns": [
                    {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){

                        return '<a href="productDepartments/show/'+ id+'">'+id+'</a>';
                    }},
                    { "mData": "Name", "oData": "Name","sName":"Name" }
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
                    <h4><i class="fa fa-barcode"></i> @lang('labels.product-departments')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('productDepartments/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <table cellpadding="0" cellspacing="0" border="0" class="table datatables" id="example">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.name')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

