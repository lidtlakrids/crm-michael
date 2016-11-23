@extends('layout.main')
@section('page-title',Lang::get('labels.appointments'))

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
                            responsive:true,
                            "lengthMenu": [[20,50,100], [20,50,100]],
                            aaSorting:[[6,"asc"]], // shows the newest items first
                            "sPaginationType": "full_numbers",
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": api_address+"CalendarEvents?$expand=User($select=UserName,FullName),Booker($select=UserName,FullName)",
                            'filter' : "Id ne null " + customFilters,
                            "aoColumns": [
                                {mData:"Id","sType":"numeric","width":"7%",mRender:function(id,unused,object,c){
                                    return '<a href="'+base_url+'/appointments/show/'+ id+'">'+id+'</a>';
                                 }
                                },
                                {
                                    mData: "Summary", mRender: function (company, type, obj) {
                                    return '<a href="' + base_url + '/appointments/show/' + obj.Id + '" title="' + Lang.get('labels.see-lead') + '">' + company + '</a>';
                                    }
                                },
                                {
                                    mData: "Description","sClass": "show-more-container", sType: "string"

                                },
                                {mData:"EventType",searchable:false

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
                                    var date = new Date(endDate);
                                    return date.toDateTime();
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
                    /**
                     * function that makes a text appear multiline when we click "Show more"
                     */
                    $('.more-link').on('click', function () {
                        if ($(this).closest('.is-more').hasClass('multiline')) {
                            $(this).closest('.is-more').removeClass('multiline');
                        } else {
                            $(this).closest('.is-more').addClass('multiline');
                        }
                    });
                });
            // get original filters
            var settings = table.settings();
            var originalFilter = settings[0].oInit.filter;

            //if a contract filter is clicked, apply a corresponding string
            $('.appointmentFilters').on('click',function(event){
                var clickedFilter = $(event.target);

                // clear past filters
                var customFilters = '';

                //clear other filters by applying the original and removing all active classes
                clickedFilter.siblings().removeClass('active btn-adwords-alt');
                clickedFilter.siblings().addClass('btn-default');
                settings[0].oInit.filter = originalFilter;

                //check if the filter is active.
                if(clickedFilter.hasClass('active')){
                    //filter is already active and has been clicked, so disable it and apply original filters
                    clickedFilter.removeClass('active btn-adwords-alt');
                    clickedFilter.addClass('btn-default');
                    settings[0].oInit.filter = originalFilter;
                    //redraw the table
                    table.draw();
                }else
                {    // add active class
                    clickedFilter.addClass('active btn-adwords-alt');
                    var today = new Date();

                    // apply corresponding filter
                    switch(clickedFilter.attr('id')){
                        case "showFutureAppointments":
                            // apply custom filters
                            customFilters += "and Start ge "+today.toISOString();
                            break;
                        case "showPastAppointments":
                            customFilters += "and Start le "+today.toISOString();
                            break;
                        default :
                            break;
                    }

                    // add the extra filters to the existing ones
                    settings[0].oInit.filter += customFilters;
                    //redraw the table
                    table.draw();
                }

                return false;
            });




        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-users"> </i>@lang('labels.appointments')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="btn-group">
                            <a href="#" id="showFutureAppointments" class="btn btn-default appointmentFilters">@lang('labels.future-appointments')</a>
                            <a href="#" id="showPastAppointments" class="btn btn-default appointmentFilters">@lang('labels.past-appointments')</a>
                        </div>
                    </div>
                    <div class="col-md-3">

                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table cellpadding="0" cellspacing="0" border="0" style="width: 100%" class="table datatables table-list table-condensed">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>@lang('labels.title')</th>
                                    <th>@lang('labels.description')</th>
                                    <th>Type</th>
                                    <th>@lang('labels.booker')</th>
                                    <th>@lang('labels.for-who')</th>
                                    <th>@lang('labels.start-time')</th>
                                    <th>@lang('labels.end-time')</th>
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
@stop