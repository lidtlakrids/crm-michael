@extends('layout.main')
@section('page-title',Lang::get('labels.unpaid-invoices'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){

            var $userQuery = checkOwnership('Invoices');
            var customFilters= "";
            var table =
                $('.datatables').DataTable(
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
                        "lengthMenu": [[20,50,100], [20,50,100]],
                        aaSorting:[[5,"asc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        select:"Id",
                        "sAjaxSource": api_address+"Invoices?$expand=User($select=UserName,FullName)",
                        'filter' : "Type eq 'Invoice' and (Status eq webapi.Models.InvoiceStatus'Sent' or Status eq webapi.Models.InvoiceStatus'Overdue' or Status eq webapi.Models.InvoiceStatus'Reminder' or Status eq webapi.Models.InvoiceStatus'DebtCollection' ) and year(Due) eq 2016"+$userQuery,
                        "fnRowCallback": function (nRow, aaData) {
                            var dueDate = moment(aaData.Due);
                            var today = moment();
                            var diff = today.diff(dueDate,'days');

                            if(aaData.Status == "Sent"){
                                $(nRow).addClass('success');
                            } else if(aaData.Status == "Overdue"){
                                $(nRow).addClass( (diff < 30 ? "warning":"danger"))
                            }else if(aaData.Status == "Reminder"){
                                $(nRow).addClass('danger')
                            }else{
                                $(nRow).addClass('danger')
                            }
                        },
                        "aoColumns": [
                            {{---
                            {mData:"Id","sType":"numeric","oDAta":"Id",width:"5%",mRender:function(id,unused,object,c){

                                return '<a href="'+base_url+'/invoices/show/'+ id+'" title="'+Lang.get('labels.see-invoice')+'">'+id+'</a>';
                            }},
                            --}}
                            { "mData": "InvoiceNumber","sType":"numeric",width:"5%","oData":"InvoiceNumber",mRender:function(invoiceNumber,unused,object,c){

                                return '<a href="'+base_url+'/invoices/show/'+ object.Id+'" title="'+Lang.get('labels.see-invoice')+'">'+invoiceNumber+'</a>';
                            }},
                            { "mData": "Name","oData":"Name"},
                            { "mData": "Status","oData":"Name"},
                            { "mData": 'NetAmount',"sType":"numeric",mRender:function(obj){
                                return (obj != null) ? obj.format() : "";
                            }
                            },
                            { "mData": "Created","oData":"Created","sType":"date",mRender:function(CreatedDate){
                                var date = new Date(CreatedDate);
                                return date.toDateTime();
                            }},
                            { "mData": "Due","oData":"Due","sType":"date",mRender:function(Due){
                                var date = new Date(Due);
                                return date.toDateTime();
                            }},
                            { "sType":"string","mData": null, "oData":"User/UserName",
                                mRender:function(data){ if(data.User != null){return "<span title='"+data.User.FullName+"'>"+data.User.UserName.toUpperCase()+"</span>"}else{ return "---"}}
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false

                    });
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-exclamation-triangle"></i> @lang('labels.unpaid-invoices')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div style="margin: 0 auto;">
                                <div class="alert alert-success col-md-3">@lang('messages.everything-ok')</div>
                                <div class="alert alert-warning col-md-3">@lang('messages.reminder-or-overdue-by-less-than-month')</div>
                                <div class="alert alert-danger col-md-3">@lang('messages.debt-collection-or-overdue-by-more-than-month')</div>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <table cellpadding="0" cellspacing="0" border="0" style="width: 100%" class="table datatables table-list table-condensed">
                                <thead>
                                <tr>
                                    {{-- <th>@lang('labels.id')</th> --}}
                                    <th>@lang('labels.invoice')</th>
                                    <th>@lang('labels.debtor-name')</th>
                                    <th>@lang('labels.status')</th>
                                    <th>@lang('labels.total-net-amount')</th>
                                    <th>@lang('labels.created-date')</th>
                                    <th>@lang('labels.due-date')</th>
                                    <th>@lang('labels.seller')</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop