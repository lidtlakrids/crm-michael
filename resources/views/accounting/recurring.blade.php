@extends('layout.main')
@section('page-title',Lang::get('labels.recurring-payments'))
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        
    $(document).ready(function () {
            
        var customFilters = '';

        var table =
                $('.datatables').DataTable(
                        {
                            responsive:true,
                            stateSave:true,
                            "lengthMenu": [[20,50,100], [20,50,100]],
                            "oLanguage": {
                                "sSearch": "",
                                "sSearchPlaceholder": Lang.get('labels.search')
                            },
                            aaSorting:[[4,"asc"]], // shows the newest items first
                            "sPaginationType": "full_numbers",
                            "bProcessing": true,
                            "bServerSide": true,
                            'filter' : "(Status ne 'Invoice' and Status ne 'Deleted')" + customFilters,
                            "sAjaxSource": api_address+"Drafts?$expand=ClientAlias($expand=Country;$select=Id,Name),DraftLine",
                            "fnRowCallback": function (nRow, aaData) {
                                // difference between today and the next invoice date
                                var today = moment().utc().startOf('day');
                                var toBeHandled = moment(aaData.NoticeAccountant).utc().startOf('day');
                                console.log(toBeHandled);
                                var diffDays = toBeHandled.diff(today,'days');
                                var cssClass = "";
                                if(diffDays < 7 && diffDays > 0){
                                    cssClass = "warning";
                                }else if(diffDays >= 7){
                                    cssClass = "success";
                                }else if(diffDays <= 0){
                                    cssClass = 'danger';
                                }
                                $(nRow).addClass(cssClass);

                            },
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
                                    var lineCount = Object.keys(data.DraftLine).length;
                                    return lineCount;
                                }},
                                {"mData": "Created", "sType": "date", mRender: function (data)
                                    {if(data) {
                                        var date = new Date(data);
                                        return date.toDate();
                                    } else{ return "---"}
                                 }
                                },
                                {"mData": "NoticeAccountant", "sType": "date", mRender: function (data)
                                {if(data) {
                                    var date = new Date(data);
                                    return date.toDate();
                                } else{ return "---"}
                                }
                                },
                                {"mData":"Status",sType:"date"
                                }
                            ],
                            "fnServerData": fnServerOData,
                            "iODataVersion": 4,
                            "bUseODataViaJSONP": false
                        });
        // get original filters
        var settings = table.settings();

        var originalFilter = settings[0].oInit.filter;


        //if a contract filter is clicked, apply a corresponding string
        $('.draftFilters').on('click',function(event) {
            var clickedFilter = $(event.target);

            // clear past filters
            var customFilters = '';

            //clear other filters by applying the original and removing all active classes
            clickedFilter.siblings().removeClass('active btn-adwords-alt');
            clickedFilter.siblings().addClass('btn-default');
            settings[0].oInit.filter = originalFilter;

            //check if the filter is active.
            if (clickedFilter.hasClass('active')) {
                //filter is already active and has been clicked, so disable it and apply original filters
                clickedFilter.removeClass('active btn-adwords-alt');
                clickedFilter.addClass('btn-default');
                settings[0].oInit.filter = originalFilter;
                //redraw the table
                table.draw();
            } else {    // add active class
                clickedFilter.addClass('active btn-adwords-alt');
                // apply corresponding filter
                var today = new Date();
                switch (clickedFilter.attr('id')) {

                    case "week":
                        var nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
                        // apply custom filters
                        customFilters += "and NoticeAccountant lt "+nextWeek.toISOString();
                        break;

                    case "month":
                        var nextMonth = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
                        customFilters += "and NoticeAccountant lt "+nextMonth.toISOString();
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
            <div class="panel panel-invoice">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i>Recurring Payments</h4>
                    <div class="info-bar"></div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-12">
                            <div style="margin: 0 auto;">
                                <div class="alert alert-success col-md-3">More than 7 days to handle</div>
                                <div class="alert alert-warning col-md-3">Should be handled next 7 days</div>
                                <div class="alert alert-danger col-md-3">Should have been handled already</div>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-md-offset-3">
                        <div class="btn-group">
                            <a href="#" id="showAllContracts" class="btn btn-adwords-alt active draftFilters">Show All</a >
                            <a href="#" id="week" class="btn btn-default draftFilters">1 week</a>
                            <a href="#" id="month" class="btn btn-default draftFilters">1 month</a>
                        </div>
                    </div>

                    <table id="table-list" class="table table-hover datatables" style="width: 100%">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.client')</th>
                            <th>@lang('labels.draft-lines')</th>
                            <th>@lang('labels.created-date')</th>
                            <th>Handling date</th>
                            <th>@lang('labels.status')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop