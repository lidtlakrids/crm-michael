@extends('layout.main')
@section('page-title',"Contracts That Need Information")
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){
            var customFilters = "";
//            var userId = $('#user-Id').val();
//            var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);
//            if(!admin){
//                roles.forEach(function(role){
//                    switch (role){
//                        case "Client Manager":
//                            customFilters += " and ClientAlias/Client/ClientManager/Id eq '"+userId+"'";
//                            break;
//                        case "Adwords":
//                            customFilters += " and Manager_Id eq '"+userId+"'";
//                            break;
//                        case "Sales":
//                            customFilters += " and User_Id eq '"+userId+"'";
//                            break;
//                        default:
//                            break;
//                    }
//                });
//            }
            var table =
                $('.datatables').DataTable(
                    {
                        searching: true,
                        responsive : true,
                        stateSave : true,
                        "oLanguage": {
                            "sProcessing":   Lang.get('labels.processing'),
                            "sLengthMenu":   Lang.get('labels.length-menu'),
                            "sZeroRecords":  Lang.get('labels.zero-records'),
                            "sInfo":         Lang.get('labels.info'),
                            "sInfoEmpty":    Lang.get('labels.info-empty'),
                            "sInfoFiltered": Lang.get('labels.info-filtered'),
                            "sInfoPostFix":  "",
                            "sSearch":       "",
                            "sSearchPlaceholder": Lang.get('labels.search'),
                            "sUrl":          "",
                            "oPaginate": {
                                "sFirst":    Lang.get('labels.first'),
                                "sPrevious": Lang.get('labels.previous'),
                                "sNext":     Lang.get('labels.next'),
                                "sLast":     Lang.get('labels.last')
                            }
                        },
                        "lengthMenu": [[25,50, 100], [25,50,100]],
                        aaSorting:[[0,"desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": api_address+"Contracts?$expand=ClientAlias($select=Id,Name),Country,Product($select=Id,Name,Description),User($select=Id,FullName),Manager($select=Id,FullName),Children($select=Id;$expand=Product($select=Name))",
                        "filter"     : "(Status ne 'Suspended' and Status ne 'Completed' and Status ne 'Cancelled') and NeedInformation eq true" +
                                            " and ((Parent_Id eq null and ProductPackage_Id ne null) or (Parent_Id ne null and ProductPackage_Id eq null) or (Parent_Id eq null and ProductPackage_Id eq null))"+customFilters,
                        'select'  : "NeedInformation",
                        "aoColumns": [
                            {mData:"Id","sType":"numeric",sSorting:"desc","width":"7%",mRender:function(id){
                                return '<a href="'+base_url+'/contracts/show/'+ id+'" title="'+Lang.get('labels.see-contract')+'">'+id+'</a>';
                            }
                            },
                            {"sType":"string","mData":null,"oData":"ClientAlias/Name",mRender:function(data){
                                if(data.ClientAlias != null){
                                    return '<a href="'+base_url+'/contracts/show/'+ data.Id +'" title="'+Lang.get('labels.see-contract')+'">'+data.ClientAlias.Name+'</a>';
                                }else{return "---"}
                            }
                            },
                            {"mData":null,"sType":"date","oData":"Country/CountryCode",mRender:function(data){
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
                            },
                            { "sType":"date",mData:null,sortable:false,mRender:function(data){
                                    var children = data.Children.length;
                                    var title = "";
                                    if(children>0){
                                        data.Children.forEach(function(child){
                                            title += child.Product.Name + '\n' ;
                                        });
                                    }
                                    return children + " <i class='fa fa-question' title='" + title + "'></i>";
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

            var filters = $('.contractFilters');

            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function(){
                return this.value;
            });

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
            <div class="panel panel-contract">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i> @lang('labels.contracts')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('/contracts/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="form-group-sm col-md-3">
                                {!! Form::select('User_Id',withEmpty($sellers,Lang::get('labels.select-seller')),'User_Id eq \''.Auth::user()->externalId."'",['class'=>'form-control contractFilters']) !!}
                            </div>
                            <div class="form-group-sm col-md-3">
                                {!! Form::select('Manager_Id',withEmpty($managers,Lang::get('labels.select-assigned')),null,['class'=>'form-control contractFilters']) !!}
                            </div>
                            <div class="form-group-sm col-md-3">
                                {!! Form::select('TeamStatus',withEmpty($teamStatus,Lang::get('labels.team-status')),null,['class'=>'form-control contractFilters']) !!}
                            </div>
                            <div class="form-group-sm col-md-3">
                                {!! Form::select('Status',withEmpty($contractStatus,Lang::get('labels.contract-status')),null,['class'=>'form-control contractFilters']) !!}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table id="table-list" class="table table table-hover datatables" width="100%">
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
                                <th>@lang('labels.assigned-to')</th>
                                <th>@lang('labels.sub-contracts')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

