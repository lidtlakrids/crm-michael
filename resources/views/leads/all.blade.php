@extends('layout.main')
@section('page-title',Lang::get('labels.leads'))

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            var customFilters = '';

            var filters = $('.leadFilters');
            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function(){
                return this.value;
            });
            var caller = canCall();
            var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);

            if(!admin){
                roles.forEach(function(role){
                    switch (role){
                        case "Meet Booking":
                           customFilters = " and (Booker_Id eq '"+getUserId()+"' or User_Id eq '"+getUserId()+"')";
                        default:
                            break;
                    }
                });
            }

            var table =
                $('.datatables').DataTable(
                    {
                        responsive: true,
                        stateSave: true,
                        deferLoading: currentFilters.length > 0? 0:null,
                        "language": {
                            "url": "datatables-"+locale+'.json'
                        },
                        "oLanguage": {
                            "sSearch":       "",
                            "sSearchPlaceholder": Lang.get('labels.search')
                        },
                        "stateDuration": 180, // 3 minutes
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        aaSorting: [[0, "desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "filter": "Created ne null" + customFilters,
                        "sAjaxSource": api_address + "Leads?$expand=User($select=FullName)",
                        "aoColumns": [
                            {
                                mData: "Id",
                                "sType": "numeric",
                                "width": "7%",
                                mRender: function (id, unused, object, c) {
                                    return '<a href="' + base_url + '/leads/show/' + id + '" title="' + Lang.get('labels.see-lead') + '">' + id + '</a>';
                                }
                            },
                            {
                                mData: "Company", mRender: function (company, type, obj) {
                                return '<a href="' + base_url + '/leads/show/' + obj.Id + '" title="' + Lang.get('labels.see-lead') + '">' + company ? company :"" + '</a>';
                            }
                            },
                            {mData: "Website",mRender:function(obj){
                                    if(obj != null){
                                        return "<a target='_blank' href='"+addhttp(obj)+"'>"+obj+"</a>"
                                    }else {
                                        return "-"
                                    }
                                }
                            },
                            {
                                mData: null, oData: "User/FullName", sType: "string", mRender: function (obj) {

                                return obj.User != null ? obj.User.FullName : "--";
                            }
                            },
                            {mData: "PhoneNumber",sType: "string", mRender: function (data) {
                                if (data != null) {
                                    if(caller){
                                        return "<span class='pseudolink flexfoneCallOut'>"+data+"</span>";
                                    }else{
                                        return "<a href='tel:"+data+"'>"+data+'</a>';
                                    }
                                } else {
                                        return ""
                                }
                            }
                            },
                            {mData:"ContactPerson"

                            },
                            {mData:"Status", "sType": "date"},
                            {mData:'Modified',sType:"date",mRender:function (modified) {
                                    if(modified){
                                        var date = new Date(modified);
                                        return date.toDateTime();
                                    }else{
                                        return '';
                                    }
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
            <div class="panel panel-lead">
                <div class="panel-heading">
                    <h4><i class="fa fa-bullseye"></i> @lang('labels.leads')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('leads/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="form-inline  pull-right">
                                {!! Form::select('User_Id',withEmpty($users,Lang::get('labels.select-assigned')),!isAdmin()?'User_Id eq \''.Auth::user()->externalId."'":null,['class'=>'form-control leadFilters']) !!}
                                {!! Form::select('Booker_Id',withEmpty($bookers,Lang::get('labels.select-booker')),null,['class'=>'form-control leadFilters']) !!}
                                {!! Form::select('LeadStatus',withEmpty($leadStatuses,Lang::get('labels.select-status')),null,['class'=>'form-control leadFilters']) !!}
                                {!! Form::select('Ads',withEmpty($adsFilters,"Ads filters"),null,['class'=>'form-control leadFilters']) !!}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table id="table-list" cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-list cell-border datatables dtr-inline">
                            <thead>
                            <tr>
                                <th class="td-id">ID</th>
                                <th class="td-company">@lang('labels.company-name')</th>
                                <th class="td-city">@lang('labels.homepage')</th>
                                <th class="td-city">@lang('labels.user')</th>
                                <th class="td-phone">@lang('labels.phone')</th>
                                <th class="td-phone">Contact person</th>
                                <th class="td-status">@lang('labels.status')</th>
                                <th class="td-status">Last update</th>
                                {{-- <th>@lang('labels.last-appointment')</th><!-- TO DO ADD ROW APPOINTMENT -->--}}
                                <!-- ADD WEBSITE URL -->
                                <!-- NICE TO HAVE : Make Checkboxes besides id so you can select those leads you want to call (like KRistian is opning all leads in new tabs )-->
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop