@extends('layout.main')
@section('page-title','Missing drafts check')

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>

        $(document).ready(function () {
            var table =
                $('.datatables').DataTable(
                    {
                        bPaginate:false,
                        "iDisplayLength": "All",
                        aaSorting:[[0,"desc"]], // shows the newest items first
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": api_address+"Contracts?$expand=ClientAlias($select=Id,Name),Manager($select=FullName),User($select=FullName),Product($select=Name),InvoiceLines($select=Invoice_Id)",
                        "filter"     : "(Status ne 'Completed' and Status ne 'Cancelled') and year(Created) ge 2015 and ((ProductPackage_Id ne null and Parent_Id eq null) or (Parent_Id eq null and ProductPackage_Id eq null) or (Parent_Id eq null and ProductPackage_Id eq null))",
                        'select'  : "RunLength,PaymentTerm",
                        "fnRowCallback": function (nRow, aaData) {
                            $.get(api_address+'Drafts/$count?$filter=DraftLine/any(d:d/Contract_Id eq '+aaData.Id+' and (Status ne \'Deleted\' and Status ne \'Invoice\'))')
                                .success(function (data) {
                                    var $row = $(nRow);
                                    if(terms[aaData.PaymentTerm]){
                                        var shouldHave = aaData.RunLength / terms[aaData.PaymentTerm];
                                        var invoices = aaData.InvoiceLines.length;
                                        var drafts = data;
                                        var missing = shouldHave - (invoices + drafts);
                                        if(missing > 0){
                                            $row.find('.result_'+aaData.ClientAlias.Id).html( '<span style="color:red">Missing ' + missing+'</span>');
                                        }else{
                                            $row.remove();
                                        }
                                    }else{
                                        $row.find('.result_'+aaData.ClientAlias.Id).text( 'No payment Terms ')
                                    }
                                })
                        },
                        "aoColumns": [
                            {mData:"Id","sType":"numeric",sSorting:"desc","width":"7%",mRender:function(id){
                                return '<a href="'+base_url+'/contracts/show/'+ id+'" title="'+Lang.get('labels.see-contract')+'">'+id+'</a>';
                            }

                            },
                            {"sType":"string","mData":null,"oData":"ClientAlias/Name",mRender:function(data){
                                if(data.ClientAlias != null){
                                    return '<a href="'+base_url+'/contracts/show/'+ data.Id +'" title="'+Lang.get('labels.see-contract')+'">'+data.ClientAlias.Name+'</a> <span class="result_'+data.ClientAlias.Id+'"></span>';
                                }else{return "---"}
                            }
                            },
                            {"mData":null,"sType":"string","oData":"Product/Name",mRender:function(data){
                                if(data.Product != null) {return '<a href="'+base_url+'/products/show/'+ data.Product.Id +'" title="'+Lang.get('labels.see-client')+'">'+data.Product.Name+'</a>'; }else {return "----"}
                            }
                            },
                            {"mData":"Status","sType":"numeric"
                            },
                            {"mData": "StartDate", "sType": "date", mRender: function (data)
                            {
                                if(data != null) {
                                    var date = new Date(data);
                                    return date.toDate();
                                }
                                else{ return "---"}
                            }
                            },
                            {"mData": "EndDate", "sType": "date", mRender: function (data)
                            {if(data != null) {
                                var date = new Date(data);
                                return date.toDate();
                            }
                            else{ return "---"}
                            }
                            },
                            { "sType":"string","mData": null,"oData":"User/FullName" ,mRender:function(data){
                                if(data.User != null){return data.User.FullName}else{ return "---"}
                            }
                            },
                            { "sType":"string","mData": null,"oData":"Manager/FullName" ,mRender:function(data){
                                if(data.Manager != null){return data.Manager.FullName}else{ return "---"}
                            }
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
    <table id="table-list" class="table table-condensed datatables" width="100%">
        <thead>
        <tr>
            <th>@lang('labels.number')</th>
            <th>@lang('labels.client')</th>
            <th>@lang('labels.product')</th>
            <th>@lang('labels.status')</th>
            <th>@lang('labels.start-date')</th>
            <th>@lang('labels.end-date')</th>
            <th>@lang('labels.seller')</th>
            <th>@lang('labels.assigned-to')</th>
        </tr>
        </thead>
    </table>


@stop