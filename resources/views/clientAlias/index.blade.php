@extends('layout.main')
@section('page-title',Lang::get('labels.clients'))
@section('scripts')
    @include('scripts.dataTablesScripts')
<script>
$(document).ready(function () {
    var customFilters = "";
    var userId = $('#user-Id').val();
    var admin = isInArray('Administrator',roles) || isInArray('Developer',roles) || isInArray('Accounting',roles);

    if(!admin &&  userId !== '34'){ // 34 Mikker should see all too
        roles.forEach(function(role){
            switch (role){
                case "Client Manager":
                    customFilters += " and Client/ClientManager_Id eq '"+userId+"'";
                    break;
                case "Adwords":
                    customFilters += " and Contract/any(d:d/ContractType_Id eq 4 or d/ContractType_Id eq 19)";
                    break;
                case "SEO":
                    customFilters += " and Contract/any(d:d/ContractType_Id eq 3)";
                    break;
                case "Sales":
                    customFilters += " and User_Id eq '"+userId+"'";
                    break;
                default:
                    customFilters += " and User_Id eq '"+userId+"'";
                    break;
            }
        });
    }
    var caller = canCall();

    var filters = $('.clientAliasFilters');

    //check if there are already selected filters / for example if we refresh the page
    var currentFilters = filters.filter(function(){
        return this.value;
    });

    var table = $('.datatables').DataTable(
    {
        stateSave:true,
        responsive:true,
        deferLoading: currentFilters.length > 0? 0:null,
        "lengthMenu": [[20,50, 100,-1], [20,50, 100,'All']],
        "oLanguage": {
            "sSearch":       "",
            "sSearchPlaceholder": Lang.get('labels.search')
        },
        aaSorting:[[0,"desc"]], // shows the newest items first
        "sPaginationType": "full_numbers",
        "bServerSide": true,
        "bProcessing": true,
        filter:"Id ne null"+customFilters,
        "sAjaxSource": api_address+"ClientAlias?$expand=User($select=FullName),Country($select=CountryCode),Contract($select=Status;$filter=Status eq 'Active' and Parent_Id eq null)",
        "aoColumns": [
            {mData:"Id","sType":"numeric",mRender:function(id){

                return '<a href="'+base_url+'/clientAlias/show/'+ id+'" title="'+Lang.get('labels.see-client')+'">'+id+'</a>';
            }},
            { "mData": "Name",mRender:function(Name,unused,object,c){

                return '<a href="'+base_url+'/clientAlias/show/'+object.Id+'" title="'+Lang.get('labels.see-client')+'">'+Name+'</a>';
            }},
            { "mData": "City" },
            { "mData": "PhoneNumber",mRender:function(number){

                return createCallingLink(caller,number);

            }},
            {"mData": null, "oData":"Country/CountryCode" ,mRender:function(data){
                if(data.Country != null){return data.Country.CountryCode}else{ return ""}
            }
            },
            {"mData": null, "oData":"User/FullName" ,mRender:function(data){
                if(data.User != null){return data.User.FullName}else{ return ""}
                }
            },
            {"mData": "Created",sType:"date",mRender:function(created){
                if(created != null){
                    var date = new Date(created);
                    return date.toDate();
                }else{ return "---"}
            }
            },
            {mData:null,oData:null,searchable:false,sortable:false,mRender:function (obj) {
                    var actions ='';
                        actions += "<i title='Quick comment' data-client-id='"+obj.Id+"' class='fa fa-comment quickClientComment'></i>";
                        actions += " - "+obj.Contract.length+' a. contr.';
                    return actions;
                }
            }
        ],
        "fnServerData": fnServerOData,
        "iODataVersion": 4,
        "bUseODataViaJSONP": false
    });

    // get original filters
    var settings = table.settings();

    var originalFilter = settings[0].oInit.filter;

    if(currentFilters.length>0){
        var oldFilters =$.map(currentFilters, function (obj) {
            return $(obj).val();
        });

        customFilters = oldFilters.join(' and ');
        // add the extra filters to the existing ones
        settings[0].oInit.filter += " and " + customFilters;
        table.draw();
    }
    //if a contract filter is clicked, apply a corresponding string
    filters.on('change', function (event) {
        settings[0].oInit.filter = originalFilter;
        customFilters = '';//clear old filters
        var newFilters = $.map(filters.toArray(), function (obj) {
            if ($(obj).val() != "") {
                return $(obj).val();
            }
        });

        customFilters = newFilters.join(' and ');
        // add the extra filters to the existing ones
        if(customFilters.length > 0){
            settings[0].oInit.filter += " and " + customFilters;
        }
        //redraw the table
        table.draw();
        return false;
    });


});

    </script>
@endsection

@section('content')
    {!! Form::hidden('Model','ClientAlias',['id'=>'Model']) !!}

    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-group"></i> @lang('labels.clients')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    @if(isAdmin() || Auth::user()->externalId == '34') <!-- 34 Mikker -->
                        <div class="row">
                            <div class="col-md-6 col-md-offset-6">
                                <div class="form-inline  pull-right">
                                    {!! Form::select('User_Id',withEmpty($sellers,Lang::get('labels.select-seller')),"User_Id eq '".Auth::user()->externalId."'",['class'=>'form-control clientAliasFilters']) !!}
                                    {!! Form::select('Status',withEmpty($statuses,"SelectStatus"),"Contract/any(d:d/Status eq 'Active' or d/Status eq 'Standby')",['class'=>'form-control clientAliasFilters']) !!}
                                </div>
                            </div>
                        </div>
                        <hr>
                    @endif
                    <div class="row">
                        <div class="col-xs-12">
                                <table  id="table-list"  style="width: 100%;" class="table table-hover datatables">
                                    <thead>
                                    <tr>
                                        <th>@lang('labels.number')</th>
                                        <th>@lang('labels.name')</th>
                                        <th>@lang('labels.city')</th>
                                        <th>@lang('labels.phone')</th>
                                        <th>@lang('labels.country')</th>
                                        <th>@lang('labels.seller')</th>
                                        <th>@lang('labels.created-date')</th>
                                        <th>@lang('labels.actions')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop



 