@extends('layout.main')
@section('page-title',"Seo")
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
    $(document).ready(function(){
        var customFilters = '';
//        var userId = $('#user-Id').val();
//        var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);
//
//        if(!admin){
//            roles.forEach(function(role){
//                switch (role){
//                    case "Client Manager":
//                        customFilters += " and ClientAlias/Client/ClientManager/Id eq '"+userId+"'";
//                        break;
//                    case "Adwords":
//                    case "SEO":
//                        customFilters += " and Manager_Id eq '"+userId+"'";
//                        break;
//                    case "Sales":
//                        customFilters += " and User_Id eq '"+userId+"'";
//                        break;
//                    default:
//                        break;
//                }
//            });
//        }

        var filters = $('.seoFilters');

        //check if there are already selected filters / for example if we refresh the page
        var currentFilters = filters.filter(function(){
            return this.value;
        });


        var table =
            $('.datatables').DataTable(
                {
                    responsive:true,
                    "stateDuration": 300, // 5 minutes
                    "stateSave":true,
                    deferLoading: currentFilters.length > 0? 0:null,
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
                    "deferRender": true, // testing if speed is better with this
                    'filter' : "NeedInformation eq false and (ContractType/Name eq 'SEO' or ContractType/Id eq 18 or ContractType/Id eq 20 or ContractType/Id eq 8) and Manager_Id ne null" + customFilters,
                    "sAjaxSource": api_address+"Contracts?$expand=ClientAlias($select=Id,Name,Homepage),Country,Product($select=Id,Name,Description),User($select=FullName),Manager($select=FullName)",
                    "aoColumns": [
                        {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){
                            return '<a href="'+base_url+'/seo/show/'+ id+'" title="'+Lang.get('labels.see-contract')+'">'+id+'</a>';
                            }
                        },
                        {"sType":"string","mData":null,"oData":"ClientAlias/Name",mRender:function(data){
                            return '<a href="'+base_url+'/seo/show/'+ data.Id +'" title="'+Lang.get('labels.see-client')+'">'+data.ClientAlias.Name+'</a>';
                            }
                        },
                        {mData:"Domain",sType:"string",mRender:function (domain,unused,obj) {
                               var url = domain || obj.ClientAlias.Homepage;
                               return "<a target='_blank' href='"+addhttp(url)+"'>"+url+" <i class='fa fa-link'></i></a>";
                            }

                        },
                        {"mData":null,"sType":"string","oData":"Country/CountryCode",mRender:function(data){
                            if(data.Country != null) {return data.Country.CountryCode }else {return "----"}
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
                            if(data) {
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
                        {"mData": "NextOptimize", "sType": "date", mRender: function (data)
                            {if(data) {
                                var date = new Date(data);
                                return date.toDateTime();
                            }
                            else{ return "---"}
                            }
                        },
                        { "sType":"string","mData": null,"oData":"User/UserName" ,mRender:function(data){
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
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-seo">
                <div class="panel-heading">
                    <h4><i class="fa fa-search"></i> SEO @lang('labels.contracts')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="{{url('/contracts/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                        <i class="fa fa-question-circle"></i>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="form-inline  pull-right">
                                {!! Form::select('User_Id',withEmpty($sellers,Lang::get('labels.select-seller')),"User_Id eq '".Auth::user()->externalId."'",['class'=>'form-control seoFilters']) !!}
                                {!! Form::select('Manager_Id',withEmpty($managers,Lang::get('labels.select-assigned')),"Manager_Id eq '".Auth::user()->externalId."'",['class'=>'form-control seoFilters']) !!}
                                {!! Form::select('TeamStatus',withEmpty($teamStatus,Lang::get('labels.team-status')),null,['class'=>'form-control seoFilters']) !!}
                                {!! Form::select('Status',withEmpty($contractStatus,Lang::get('labels.contract-status')),"Status eq 'Active'",['class'=>'form-control seoFilters']) !!}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" id="table-list" class="table datatables">
                            <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.client')</th>
                                <th>Domain</th>
                                <th>@lang('labels.country')</th>
                                <th>@lang('labels.product')</th>
                                <th>@lang('labels.status')</th>
                                <th>@lang('labels.start-date')</th>
                                <th>@lang('labels.end-date')</th>
                                <th>@lang('labels.next-optimization')</th>
                                <th>@lang('labels.seller')</th>
                                <th>@lang('labels.assigned-to')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop