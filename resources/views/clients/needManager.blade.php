@extends('layout.main')
@section('page-title',"Need manager")

@section('styles')
@stop
@section('scripts')
    @include('scripts.dataTablesScripts')
    @include("scripts.x-editable")
    <script>
$(document).ready(function(){


    var customFilters = "";
//    var userId = $('#user-Id').val();
//    var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);
//
//    if(!admin){
//        roles.forEach(function(role){
//            switch (role){
//                case "Client Manager":
//                    customFilters += " and ClientManager_Id eq '"+userId+"' or ClientManager_Id eq null";
//                    break;
////                case "Adwords":
////                    customFilters += " and ClientAlias/any(d:d/User_Id eq '"+userId+"')";
////                    break;
//                case "Sales":
//                    customFilters += " and User_Id eq '"+userId+"'";
//                    break;
//                default:
//                    break;
//            }
//        });
//    }

  var table =  $('.datatables').DataTable(
    {
        stateSave:true,
        responsive:true,
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
        "lengthMenu": [[20,50,100], [20,50,100]],
        aaSorting:[[0,"desc"]], // shows the newest items first
        "sPaginationType": "full_numbers",
        "bProcessing": true,
        "bServerSide": true,
        select: "ClientManager_Id",
        'filter':"Id ne null and ClientManager_Id eq null and ClientAlias/any(d:d/Contract/any(q:q/Status eq 'Active'))"+customFilters,
        "sAjaxSource": api_address+"Clients?$expand=User($select=UserName),ClientAlias($select=Name),ClientManager($select=FullName)",
        "aoColumns": [
            {mData:"Id","oData":"Id","sType":"numeric","width":"7%",mRender:function(id){

                return '<a href="'+base_url+'/clients/show/'+ id+'" title="'+Lang.get('labels.see-client')+'">'+id+'</a>';
            }},
            { "mData": "CINumber","oData":"CINumber",mRender:function(CINumber,type,object,c){

                return '<a href="'+base_url+'/clients/show/'+object.Id+'" title="'+Lang.get('labels.see-client')+'">'+CINumber+'</a>';
            }},
            { "mData": "Created","sType":"date",mRender:function(CreatedDate){
                    var date = new Date(CreatedDate);
                    return date.toDateTime();
                }
            },
            {mData:null,oData:null,sType:"integer",sortable:false,mRender:function(obj){
                var clients = "";
                    $.each(obj.ClientAlias,function(a,b){
                        clients += b.Name +(obj.ClientAlias.length > 1 ? "<br>":"");
                    });
                return clients;
                }
            },
                // For expanding properties to sort, we need to set mdata to null and odata to the property path, for ordering
            { "mData": null, "oData":"User/UserName",mRender:function(data){
                if(data.User != null){return data.User.UserName}else{ return "---"}
                }
            },
            {"mData":null, "oData":"ClientManager/FullName","sType":"string",mRender:function(obj){
                return '<a href="#" id="status" class="clientManagerSelect" data-type="select" data-pk="'+obj.Id+'" data-title="Select status">'+Lang.get('labels.select-client-manager')+'</a>';
                }
            }
        ],
        "fnServerData": fnServerOData,
        "iODataVersion": 4,
        "bUseODataViaJSONP": false
    });

    $('body').on('click','.clientManagerSelect',function(event){
        // find the row id
        var id = $(this).data('pk');
        event.preventDefault();
        $(event.target).editable({
            source: managers,
            ajaxOptions:{
                type:"patch",
                dataType: 'application/json',
                beforeSend: function (request)
                {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            },
            params: function(params) {
                var data = {};
                data['ClientManager_Id'] = params.value;
                return JSON.stringify(data);
            },
            url:api_address+"Clients("+id+")",
            success: function() {
                table.draw();
            }
        }).removeClass('clientManagerSelect');
        setTimeout(function(){
            $(event.target).click();
        },500)
    });

});
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-group"></i> Assign Client Manager</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('clients/create')}}" title="@lang('labels.create-client')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <table id="table-list" cellpadding="0" cellspacing="0" border="0" class="table table table-condensed datatables" width="100%">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.ci-number')</th>
                            <th>@lang('labels.created-date')</th>
                            <th>@lang('labels.clientalias')</th>
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



 