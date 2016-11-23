@extends('layout.main')
@section('page-title',Lang::get('labels.orders-for-approval'))

@section('scripts')
@include('scripts.dataTablesScripts')
    <script>
        $('#table-list').DataTable(
                {
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
                    responsive:true,
                    saveState:true,
                    "lengthMenu": [[20,50,100], [20,50,100]],
                    aaSorting:[[0,"desc"]], // shows the newest items first
                    "sPaginationType": "full_numbers",
                    "bFilter":false,
                    "bProcessing": true,
                    "bServerSide": true,
                    "filter" :"ApprovedDate eq null and ArchivedDate eq null",
                    "sAjaxSource": api_address+"Orders?$expand=ClientAlias($select=Name,PhoneNumber),User($select=UserName),OrderType($select=FormName)",
                    "aoColumns": [
                        {mData:"Id","oData":"Id","sType":"numeric","width":"5%",mRender:function(id){

                            return '<a href="'+base_url+'/orders/show/'+ id+'" title="'+Lang.get('labels.see-order')+'">'+id+'</a>';
                            }
                        },
                        {mData:null,oData:"ClientAlias/Name",sType:"string",mRender: function (object) {
                                if(object.ClientAlias != null){
                                    return object.ClientAlias.Name;
                                }else{return "No Client set"}
                            }
                        },
                        {mData:null,oData:"ClientAlias/PhoneNumber",sType:"string",mRender:function(object){
                            if(object.ClientAlias != null){
                                return object.ClientAlias.PhoneNumber;
                                }else{return ""}
                            }
                        },
                        {mData:"Created",sType:"date",mRender:function(obj){
                            var date = new Date(obj);
                                return date.toDateTime();
                            }
                        },
                        {mData:null,oData:"OrderType/FormName",sType:"string",mRender:function(obj){
                            if(obj.OrderType != null){
                                return obj.OrderType.FormName;
                                }else {return ""}
                            }
                        },
                        {mData:null,oData:"User/UserName",sType:"string",mRender:function(obj){
                            if(obj.User != null){
                                return obj.User.UserName.toUpperCase();
                                }else {return ""}
                            }
                        }
                    ],
                    "fnServerData": fnServerOData,
                    "iODataVersion": 4,
                    "bUseODataViaJSONP": false

                });
    </script>

@stop
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-order">
            <div class="panel-heading">
                <h4><i class="fa fa-reorder"></i> @lang('labels.orders-for-approval')</h4>
            </div>
            <div class="panel-body">
             <table id="table-list" class="table table-striped table-hover datatables" width="100%">
        <thead>
        <tr>
            <th>@lang('labels.number')</th>
            <th>@lang('labels.company-name')</th>
            <th>@lang('labels.phone')</th>
            <th>@lang('labels.submission-date')</th>
            <th>@lang('labels.order-type')</th>
            <th>@lang('labels.salesman')</th>
        </tr>
        </thead>
    </table>
            </div>  <!-- END panel body -->
        </div>  <!-- END Panel grape -->

    </div> <!-- End of col-md-12 -->
</div> <!-- End of Row -->
@stop



 