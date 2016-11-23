@extends('layout.main')
@section('page-title', 'White Lists')
@section('styles')
    @stop
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            var customFilters = "";
            var filters = $('.taskFilters');
            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function () {
                return this.value;
            });
            table = $('.datatables').DataTable(
                    {
                        deferLoading: currentFilters.length > 0 ? 0 : null,
                        responsive: true,
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        "sPaginationType": "full_numbers",
                        'filter': "Created ne null " + customFilters,
                        "sAjaxSource": api_address + "Whitelists?$expand=User($select=FullName)",
                        "bProcessing": true,
                        "bServerSide": true,
                        "fnRowCallback": function (nRow, aaData) {
                            if (aaData.Value) {
                                $(nRow).addClass('crossed-through');
                            } else {
                                $(nRow).removeClass('crossed-through');
                            }
                        },
                        "aoColumns": [
                            {
                                "mData": "Id",
                                "oData": "Id",
                                "sType": "numeric",
                                mRender: function (id) {
                                    return '<a href="white-lists/show/' + id + '">' + id + '</a>';
                                }
                            },
                            {
                                "mData": null,
                                "oData": "User/FullName",
                                "sType": "string",
                                mRender: function (data) {
                                    if (data.User != null) {
                                        return data.User.FullName;
                                    } else {
                                        return "";
                                    }
                                }
                            },
                            {
                                 "mData": "Created",
                                "sType": "date",
                                mRender: function (data) {
                                if (data == null) {
                                    return "---";
                                }
                                var date = new Date(data);
                                return date.toDateTime();
                            }

                            },
                            {
                                "mData": "ipaddress",
                                mRender: function (obj) {
                                    if(obj != null){
                                        return "<a target='_blank' href='http://www.ip-tracker.org/locator/ip-lookup.php?ip="+obj+"'>"+obj+"</a>";
                                    }else{
                                        return "--";
                                    }
                                }
                            },
                            {
                                "mData": "Permanent",
                                "sType": "string",
                                searchable: false,
                                mRender: function (data) {
                                    if (data) {
                                        return "Yes";
                                    } else {
                                        return "No";
                                    }
                                }
                            },
                            {
                                "mData": "Active",
                                "sType": "string",
                                searchable: false,
                                mRender: function (data) {
                                    if (data) {
                                        return "Yes";
                                    } else {
                                        return "No";
                                    }
                                }
                            },
                            {
                                "mData": null,
                                "oData": null,
                                sType: "string",
                                "orderable": false,
                                searchable: false,
                                mRender: function (obj) {
                                    return "<a href='"+base_url+"/white-lists/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>";
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

            var settings = table.settings();
            var originalFilter = settings[0].oInit.filter;

            if (currentFilters.length > 0) {
                var oldFilters = $.map(currentFilters, function (obj) {
                    return $(obj).val();
                });

                customFilters = oldFilters.join(' and ');
                // add the extra filters to the existing ones
                settings[0].oInit.filter += " and " + customFilters;
                table.draw();
            }

            function updateFilters() {

                settings[0].oInit.filter = originalFilter;
                customFilters = '';//clear old filters
                var newFilters = $.map(filters.toArray(), function (obj) {
                    if ($(obj).val() != "") {
                        return $(obj).val();
                    }
                });

                customFilters = newFilters.join(' and ');
                // add the extra filters to the existing ones
                if (customFilters.length > 0) {
                    settings[0].oInit.filter += " and " + customFilters;
                }
                //redraw the table
                table.draw();
                return false;
            }

        });
    </script>
    @stop
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-list-ul"></i> White Lists</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{ url('/white-lists/create') }}" title="Create a new whielist"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <hr>
                    <div class="table-responsive">
                        <table cellpadding="0" cellspacing="0" border="0" width="100%"
                               class="table table-striped table-condensed datatables" id="table-white-lists">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>User</th>
                                <th>Created</th>
                                <th>IP address</th>
                                <th>Permanent</th>
                                <th>Active</th>
                                <th>@lang('labels.actions')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop