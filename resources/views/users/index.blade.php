@extends('layout.main')
@section('page-title',Lang::get('labels.users'))

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){

        var customFilters = '';

        var table = $('.datatables').DataTable(
                {
                    'responsive':true,
                    "language": {
                        "url": "datatables-"+locale+'.json',
                    },
                    "oLanguage": {
                        "sSearch":       "",
                        "sSearchPlaceholder": Lang.get('labels.search')
                    },
                    "lengthMenu": [[25,50, 100], [25,50,100]],
                    aaSorting:[[0,"desc"]], // shows the newest items first
                    "sPaginationType": "full_numbers",
                    "filter":"Id ne null"+customFilters,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": api_address+"Users?$expand=Roles($select=Id;$expand=Role($select=Name)),Title",
                    "aoColumns": [
                        {mData:"Id","sType":"numeric","width":"7%",mRender:function(id,unused,object,c){
//                            return '<a href="users/show/'+ id+'" title="'+Lang.get('labels.see-client')+'">'+object.UserName+'</a>';
                        return "";
                        }
                        },
                        {"mData":"UserName",mRender:function(userName,unused,object,c){
                          return  '<a href="users/show/'+ object.Id+'">'+userName+'</a>'
                            }
                        },
                        {"mData":"FullName"
                        },
                        {"mData":"Email"
                        },
                        {mData:"EmployeeLocalNumber",'sType':'date'

                        },
                        {mData:'Birthdate',searchable:false,mRender:function (date) {
                            var da = new Date(date);
                            return da.toDate();
                        }

                        },
                        {mData:"Active",sType:"date"

                        },
                        {mData:null,oData:"Title/Name",sType:"string",mRender:function(obj){
                            if(obj.Title != null){
                                return obj.Title.Name;
                            }else{return "";}

                            }
                        },
                        {"mData":null,"sType":"date","oData":"Name ","orderable":false,mRender:function(row){
                                var roleNames= "";
                                row.Roles.forEach(function(role){
                                    roleNames += role.Role.Name+"/";
                                });
                                return roleNames;
                            }
                        },
                        {"mData":null,"sortable":false,sType:"date","oData":null,mRender: function (obj) {
                            var links;
                            links = "<a href='"+base_url+"/users/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a> / ";
                            links += "<a href='"+base_url+"/users/changePassword/"+obj.Id+"'><i class='fa fa-lock' title='"+Lang.get('labels.change-password')+"'></i></a> /";
                            links += "<a href='"+base_url+"/payments/"+obj.Id+"'><i class='fa fa-money' title='"+Lang.get('labels.payments')+"'></i></a> /";
                            links += "<a href='"+base_url+"/missing-payments/"+obj.Id+"'><i class='fa fa-question-circle' title='"+Lang.get('labels.missing-payments')+"'></i></a>";
                            return links;
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

            var filters = $('.userFilters');

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
        })
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-user"></i> @lang('labels.users')</h4>
                        <div class="options">
                            <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                            <a href="{{ url('/users/create') }}" title="@lang('labels.create-user')"><i class="fa fa-plus"></i></a>
                        </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="">
                                <div class="form-group-sm col-md-3">
                                    {!! Form::select('User_Id',withEmpty($activeFilter,Lang::get('labels.select-status')),null,['class'=>'form-control userFilters']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <table class="table table-list cell-border datatables dtr-inline" style="width:100%" id="table-list">
                        <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th style="width: 3%">@lang('labels.username')</th>
                                <th>@lang('labels.name')</th>
                                <th>@lang('labels.email')</th>
                                <th>@lang('labels.employee-local-number')</th>
                                <th>Birth date</th>
                                <th>@lang('labels.active')</th>
                                <th>@lang('labels.title')</th>
                                <th>@lang('labels.roles')</th>
                                <th>@lang('labels.actions')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection