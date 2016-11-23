@extends('layout.main')
@section('page-title',Lang::get('labels.assign-contracts'))
@section('scripts')
    @include('scripts.dataTablesScripts')
<script>
$(document).ready(function(){
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
        "lengthMenu": [[20,50,100], [20,59,100]],
        aaSorting:[[0,"desc"]], // shows the newest items first
        "sPaginationType": "full_numbers",
        "bProcessing": true,
        "bServerSide": true,
        'filter' : "Manager eq null",
        "sAjaxSource": api_address+"Contracts?$expand=ClientAlias($select=Id,Name),Country,Product($select=Id,Name,Description),User($select=UserName),Manager($select=FullName)",
        "aoColumns": [
            {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){
                return '<a href="'+base_url+'/contracts/show/'+ id+'" title="'+Lang.get('labels.see-contract')+'">'+id+'</a>';
                }
            },
            {"sType":"string","mData":null,"oData":"ClientAlias/Name",mRender:function(data){
                return '<a href="'+base_url+'/contracts/show/'+ data.Id +'" title="'+Lang.get('labels.see-client')+'">'+data.ClientAlias.Name+'</a>';
                }
            },
            {"mData":null,"sType":"date","oData":"Country/CountryCode",mRender:function(data){
                if(data.Country != null) {return data.Country.CountryCode }else {return "----"}
                }
            },
            {"mData":null,"sType":"string","oData":"Product/Name",mRender:function(data){
                if(data.Product != null) {return '<a href="'+base_url+'/products/show/'+ data.Product.Id +'" title="'+Lang.get('labels.see-client')+'">'+data.Product.Name+'</a>';
                }
                else {return "----"}
               }
            },
            {"mData":"Status","sType":"numeric"
            },
            {"mData": "StartDate", "sType": "date", mRender: function (data)
                {if(data) {
                    var date = new Date(data);
                    return date.toDateTime();
                }
                else{ return "---"}
                    }
            },
            {"mData": "EndDate", "sType": "date", mRender: function (data)
            {if(data) {
                var date = new Date(data);
                return date.toDateTime();
            }
            else{ return "---"}
            }
            },
            { "sType":"string","mData": null,"oData":"User/UserName" ,mRender:function(data){
                if(data.User != null){return data.User.UserName}else{ return "---"}
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
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-adwords">
                <div class="panel-heading">
                    <h4><i class="fa fa-sign-out"></i> @lang('labels.assign-contracts')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('/contracts/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <table id="table-list" cellpadding="0" cellspacing="0" border="0" class="table table table-hover datatables">
                        <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.client')</th>
                                <th>@lang('labels.country')</th>
                                <th>@lang('labels.product')</th>
                                <th>@lang('labels.status')</th>
                                <th>@lang('labels.start-date')</th>
                                <th>@lang('labels.end-date')</th>
                                <th>@lang('labels.seller')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop