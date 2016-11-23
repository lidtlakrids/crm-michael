@extends('layout.main')
@section('page-title',Lang::get('labels.ci-numbers'))
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
$(document).ready(function(){


    var customFilters = "";
    var userId = $('#user-Id').val();
    var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);

    if(!admin){
        roles.forEach(function(role){
            switch (role){
                case "Client Manager":
                    customFilters += " and ClientManager_Id eq '"+userId+"' or ClientManager_Id eq null";
                    break;
                case "Adwords":
                case "SEO":
                    customFilters += " and ClientAlias/any(d:d/Contract/any(a:a/Manager_Id eq '"+userId+"'))";
                    break;
                case "Sales":
                    customFilters += " and User_Id eq '"+userId+"'";
                    break;
                default:
                    break;
            }
        });
    }

  var table =  $('.datatables').DataTable(
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
        'filter':"Id ne null"+customFilters,
        "sAjaxSource": api_address+"Clients?$expand=User($select=FullName),ClientManager($select=FullName),ClientAlias($select=Name,Homepage)",
        "aoColumns": [
            {mData:"Id","oData":"Id","sType":"numeric","width":"7%",mRender:function(id){

                return '<a href="clients/show/'+ id+'" title="'+Lang.get('labels.see-client')+'">'+id+'</a>';
            }},
            { "mData": "CINumber","oData":"CINumber",sType:'string',mRender:function(CINumber,type,object,c){

                return '<a href="clients/show/'+object.Id+'" title="'+Lang.get('labels.see-client')+'">'+CINumber+'</a>';
            }},
            {mData:null,oData:"ClientAlias/Name",sType:'date',sortable:false,mRender:function (obj) {
                if(obj.ClientAlias && obj.ClientAlias.length > 0){
                    var clients = '';
                    $.each(obj.ClientAlias,function (a,b) {
                        clients += b.Name + ' - '+b.Homepage + "<br>";
                    });
                    return clients;
                }else{
                    return '';
                }
            }
            },
            { "mData": "Created","sType":"date",mRender:function(CreatedDate){
                var date = new Date(CreatedDate);
                return date.toDateTime();
            }},
                // For expanding properties to sort, we need to set mdata to null and odata to the property path, for ordering
            { "mData": null, "oData":"User/FullName",mRender:function(data){
                if(data.User != null){return data.User.FullName}else{ return "---"}
                }
            },
            {"mData":null, "oData":"ClientManager/FullName","sType":"string",mRender:function(data){
                if(data.ClientManager != null){return data.ClientManager.FullName}else{ return "---"}
            }

            }
        ],
        "fnServerData": fnServerOData,
        "iODataVersion": 4,
        "bUseODataViaJSONP": false
    });
});
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-group"></i> @lang('labels.client-cvrs')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('clients/create')}}" title="@lang('labels.create-client')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <table id="table-list" cellpadding="0" cellspacing="0" border="0" class="table table table-hover datatables" width="100%">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.ci-number')</th>
                            <th>@lang('labels.clients')</th>
                            <th>@lang('labels.created-date')</th>
                            <th>@lang('labels.salesman')</th>
                            <th>@lang('labels.client-manager')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop



 