@extends('layout.main')
@section('page-title',"Clients to call")

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){

            var customFilters = "";
            var userId = $('#user-Id').val();
            var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);
            if(!admin){
                customFilters += ' and (Booker_Id eq \''+userId+'\' or User_Id eq \''+userId+'\')';
            }
            var filters = $('.appointmentFilters');
            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function(){
                return this.value;
            });
            var today = moment();

            appointmentsListTable =
                $('.datatables').DataTable(
                    {
                        deferLoading: currentFilters.length > 0? 0:null,
                        "stateDuration": 300, // 5 minutes
                        responsive:true,
                        stateSave:true,
                        "lengthMenu": [[20,50,100], [20,50,100]],
                        aaSorting:[[7,"asc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": api_address+"CalendarEvents?$expand=User($select=UserName,FullName),Booker($select=UserName,FullName)",
                        'filter' : "Created ne null and EventType eq 'FollowUp' and not Activity/any(d:d/ActivityType eq 'Completed' or d/ActivityType eq 'Cancel')" + customFilters,
                        'select' : 'Model,ModelId',
                        "fnRowCallback": function (nRow, aaData) {
                            if(aaData.Model && aaData.ModelId) {
                                $.when(getCompanyName(aaData.Model,aaData.ModelId))
                                    .then(function (name) {
                                        if(name.value !== 'Undefined'){
                                            var td  = $(nRow).find('td:nth-child(4)');
                                            td.find('a').text(name.value);
                                        }
                                    });
                            }

                            var dueDate = moment(aaData.Start);
//                            var diff = today.diff(dueDate,'days');
//                            console.log(diff);
                            if(dueDate.isAfter(today,'day')){
                                $(nRow).addClass('success');
                            }else if(today.isAfter(dueDate,'day')){
                                $(nRow).addClass('danger');
                            }else{
                                $(nRow).addClass('warning');
                            }

                        },
                        "aoColumns": [
                            {mData:"Id","sType":"numeric","width":"7%",mRender:function(id,unused,object,c){
                                return '<a href="'+base_url+'/appointments/show/'+ id+'">'+id+'</a>';
                            }
                            },
                            {
                                mData: "Summary", mRender: function (company, type, obj) {
                                return '<a href="' + base_url + '/appointments/show/' + obj.Id + '" title="See appointment">' + company + '</a>';
                            }
                            },
                            {
                                mData: "Description","sClass": "show-more-container", sType: "string"
                            },
                            {mData:null,oData:"null",sortable:false,searchable:false,mRender: function (obj) {
                                if(obj.Model){
                                    return '<a href="'+linkToItem(obj.Model,obj.ModelId,true)+'">View</a>';
                                }else{
                                    return "--"
                                }
                            }
                            },
                            {mData:null,oData:"Booker/FullName",sType:"string",mRender:function(obj){
                                return (obj.Booker != null)? obj.Booker.FullName: "";
                            }
                            },
                            {
                                mData: null, oData: "User/FullName", sType: "string", mRender: function (obj) {
                                return (obj.User != null) ? obj.User.FullName : "";
                            }
                            },
                            {mData:"Start",sType:"date",mRender: function (startDate) {
                                var date = new Date(startDate);
                                return date.toDateTime();
                            }
                            },
                            {mData:"End",sType:"date",mRender: function (endDate) {
                                if(endDate != null){
                                    var date = new Date(endDate);
                                    return date.toDateTime();
                                }else{
                                    return "--";
                                }
                            }
                            },
                            {
                                mData: null,
                                sType: "date",
                                "orderable": false,
                                searchable: false,
                                mRender: function (obj) {
                                    return "<i class='fa fa-pencil updateAppointment' data-calendarevent-id='" + obj.Id + "'></i>";
                                }
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    }).on('draw.dt', function () {
                    //initiate the more container after the table has loaded
                    $('.show-more-container').more({
                        length: 40, ellipsisText: ' ...',
                        moreText: '<i class="fa fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
                    });
                });

            // get original filters
            var settings = appointmentsListTable.settings();
            var originalFilter = settings[0].oInit.filter;

            if(currentFilters.length>0){
                var oldFilters =$.map(currentFilters, function (obj) {
                    return $(obj).val();
                });
                customFilters = oldFilters.join(' and ');
                // add the extra filters to the existing ones
                settings[0].oInit.filter += " and " + customFilters;
                appointmentsListTable.draw();
            }
            //if a contract filter is clicked, apply a corresponding string
            filters.on('change', function (event) {
                updateFilters();
            });


            function updateFilters(){

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
                appointmentsListTable.draw();
                return false;
            }

        });
    </script>
@stop

@section('content')
    {!! Form::hidden('Model','CalendarEvent',['id'=>'Model']) !!}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-calendar-o"></i> @lang('labels.appointments')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8 col-xs-12 text-right">
                            <div class="form-inline">
                                {{--<div class="form-group">--}}
                                    {{--{!! Form::select('Booker_Id',withEmpty($bookers,Lang::get('labels.select-booker')),null,['class'=>'form-control appointmentFilters']) !!}--}}
                                {{--</div>--}}
                                @if(isAdmin())
                                    <div class="form-group">
                                        {!! Form::select('User_Id',withEmpty($users,Lang::get('labels.select-user')),"User_Id eq '".Auth::user()->externalId."'",['class'=>'form-control appointmentFilters','id'=>'UserFilter']) !!}
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="table-responsive">
                            <div class="col-md-12">
                                <table cellpadding="0" cellspacing="0" border="0" class="table datatables table-list table-condensed table-hover" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>@lang('labels.title')</th>
                                            <th>@lang('labels.description')</th>
                                            <th>@lang('labels.company-name')</th>
                                            <th>@lang('labels.booker')</th>
                                            <th>@lang('labels.for-who')</th>
                                            <th>@lang('labels.start-time')</th>
                                            <th>@lang('labels.end-time')</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop