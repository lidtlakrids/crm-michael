@extends('layout.main')
@section('page-title',Lang::get('labels.drafts'))
@section('scripts')
    @include('scripts.dataTablesScripts')
<script>
var table =
$('.datatables').DataTable(
{
    stateSave:true,
    responsive:true,
    "language": {
        "url": "datatables-"+locale+'.json'
    },
    "oLanguage": {
        "sSearch":       "",
        "sSearchPlaceholder": Lang.get('labels.search')
    },
    "lengthMenu": [[20,50,100], [20,50,100]],
    aaSorting:[[0,"desc"]], // shows the newest items first
    "sPaginationType": "full_numbers",
    "bProcessing": true,
    "bServerSide": true,
    "sAjaxSource": api_address+"Drafts?$expand=ClientAlias($expand=Country;$select=Id,Name),User($select=FullName),DraftLine",
    'filter':"(Status ne 'Invoice' and Status ne 'Deleted')",
    "aoColumns": [
        {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){
            return '<a href="'+base_url+'/drafts/show/'+ id+'" title="'+Lang.get('labels.see-draft')+'">'+id+'</a>';
            }
        },
        {"sType":"string","mData":null,"oData":"ClientAlias/Name",mRender:function(data){
            return '<a href="'+base_url+'/drafts/show/'+ data.Id +'" title="'+Lang.get('labels.see-draft')+'">'+data.ClientAlias.Name+'</a>';
            }
        },
        {"mData":null,"sType":"date",sortable:false,mRender:function(data){
            var  lineCount = Object.keys(data.DraftLine).length;
           return lineCount;
        }},
        {"mData": "Created", "sType": "date", mRender: function (data)
            {
                if(data) {
                    var date = new Date(data);
                    return date.toDateTime();
                } else{ return "---"}
            }
        },
        {"mData": "NoticeAccountant", "sType": "date", mRender: function (data)
        {
            if(data) {
                var date = new Date(data);
                return date.toDateTime();
            } else{ return "---"}
        }
        },
        {mData:"Type",sType:"date"

        },
        {"mData":"Status",sType:"date"
        },
        {mData:null,oData:"User/FullName",sType:"string",mRender:function(obj){
                return obj.User != null ? obj.User.FullName:"";
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
            <div class="panel panel-invoice">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i> @lang('labels.drafts')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('/contracts/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="table-responsive">
                        <table id="table-list" class="table table-hover datatables" width="100%">
                            <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.client')</th>
                                <th>@lang('labels.draft-lines')</th>
                                <th>@lang('labels.created-date')</th>
                                <th>@lang('labels.to-be-handled')</th>
                                <th>@lang('labels.type')</th>
                                <th>@lang('labels.status')</th>
                                <th>@lang('labels.user')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop