@extends('layout.main')
@section('page-title',Lang::get('labels.fields'))
@section('scripts')
    @include('scripts.dataTablesScripts')

    <script>
        $(document).ready(function(){

            var typeId = getModelId();
            // the exiting fields come from php
            //initialize datatable
            var table = $('#orderFieldsTable').DataTable(
                    {
                        responsive: true,
                        stateSave: true,
                        "oLanguage": {
                            "sProcessing": Lang.get('labels.processing'),
                            "sLengthMenu": Lang.get('labels.length-menu'),
                            "sZeroRecords": Lang.get('labels.zero-records'),
                            "sInfo": Lang.get('labels.info'),
                            "sInfoEmpty": Lang.get('labels.info-empty'),
                            "sInfoFiltered": Lang.get('labels.info-filtered'),
                            "sInfoPostFix": "",
                            "sSearch": Lang.get('labels.search'),
                            "sUrl": "",
                            "oPaginate": {
                                "sFirst": Lang.get('labels.first'),
                                "sPrevious": Lang.get('labels.previous'),
                                "sNext": Lang.get('labels.next'),
                                "sLast": Lang.get('labels.last')
                            }
                        },
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        aaSorting: [[0, "desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        'filter': "Active eq true and not FieldLinks/any(d:d/ProductType_Id eq {{$id}})",
                        "sAjaxSource": api_address + "ContractFields?$expand=FieldOption",
                        "bProcessing": true,
                        "bServerSide": true,
                        "aoColumns": [
                            {
                                "mData": "Id",
                                "oData": "Id",
                                "sType": "numeric",
                                "width": "5%",
                                mRender: function (id) {

                                    return '<a href="order-fields/show/' + id + '" title="' + Lang.get('labels.see') + '">' + id + '</a>';
                                }
                            },
                            {
                                "mData": "DisplayName", sType: "string"
                            },
                            {
                                "mData": "ValueName", sType: "string"
                            },
                            {
                                "mData": "Description", sType: "string","sClass": "show-more-container"
                            },
                            {
                                "mData": "Active"
                            },
                            {
                                "mData": "FieldType",sType:"date"
                            },
                            {
                                "mData": null,oData:null,sType:'date',mRender:function(obj){
                                if(obj.FieldOption != null){
                                    return obj.FieldOption.length
                                }else {
                                    return 0
                                }
                            }
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    }).on('draw.dt', function () {
                //initiate the more container after the table has loaded
                $('.show-more-container').more({
                    length: 40, ellipsisText: ' ...',
                    moreText: '<i class="fa fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
                });
            });

            $('#orderFieldsTable tbody').on( 'click', 'tr', function () {
                var row = table.row( this ).node();
                var data = table.row( this ).data();
                $.ajax({
                    url: api_address+"ContractFields("+data.Id+')/AddTo',
                    type: "POST",
                    data: JSON.stringify({
                        ProductType_Id : typeId,
                    }),
                    success: function(data) {
                        row.remove();
                    },
                    error: function()
                    {
                        $('.alert-danger').innerHTML = "Error while adding field";
                    }
                });
            });
        })

    </script>
@stop


@section('content')

    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','ContractType',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-tasks"></i> @lang('labels.fields')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{ url('/contract-fields/create') }}" title="Create contract field"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <a class="btn btn-green" href="{{url('contract-types/show',$id)}}">@lang('labels.go-back')</a>
                    <hr>
                    <table id="orderFieldsTable" cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-condensed table-hover table-list" >
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.display-name')</th>
                            <th>@lang('labels.value')</th>
                            <th>@lang('labels.description')</th>
                            <th>@lang('labels.active')</th>
                            <th>@lang('labels.type')</th>
                            <th>@lang('labels.options')</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop