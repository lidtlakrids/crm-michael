@extends('layout.main')
@section('page-title',Lang::get('labels.notifications'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            var customFilters = "";
            var filters = $('.notificationFilters');

            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function(){
                return this.value;
            });

            var table = $('.datatables').DataTable(
                {
                    deferLoading: currentFilters.length > 0? 0:null,
                    "language": {
                        "url": "datatables-" + locale + '.json'
                    },
                    "oLanguage": {
                        "sSearch":       "",
                        "sSearchPlaceholder": Lang.get('labels.search')
                    },
                    "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                    aaSorting: [[0, "desc"]], // shows the newest items first
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": api_address + "Notifications?$expand=Creator($select=FullName),Recipient($select=FullName)",
                    "filter"     : "Created ne null" + customFilters,
                    "fnRowCallback": function (nRow, aaData) {
                        if (aaData.Value) {
                            $(nRow).addClass('crossed-through');
                        } else {
                            $(nRow).removeClass('crossed-through');
                        }
                        if(aaData.Model && aaData.ModelId && aaData.Model != 'TaskList' && aaData.Model != "Salary") {

                            $.when(getCompanyName(aaData.Model,aaData.ModelId))
                                    .then(function (name) {
                                        var clientName = "View";
                                        if(name.value != 'Undefined')clientName = name.value;

                                        $(nRow).find('td:nth-child(4)').html("<a href='"+linkToItem(aaData.Model,aaData.ModelId,true)+"' target='_blank'>"+clientName+"</a>");
                                    });
                        }
                    },
                    "aoColumns": [
                        {
                            mData: "Id", "oData": "Id", "sType": "numeric", "width": "7%", mRender: function (id) {
                            return '<a href="notifications/show/' + id + '" title="See notification">' + id + '</a>';
                        }
                        },
                        {
                            "mData": "Content", "sClass": "show-more-container", sType: "string"
                        },
                        {
                            mData: "Created", sType: "date", mRender: function (created) {
                            var date = new Date(created);
                            return date.toDateTime();
                        }
                        },
                        {
                            mData: "Model"
                        },
                        {
                            mData: "ModelId"
                        },
                        {mData:null,oData:"Creator/FullName",mRender:function (obj) {
                            if(obj.Creator != null){
                                return obj.Creator.FullName;
                            }
                            return ''
                        }
                        },
                        {
                            mData: null, oData: "Recipient/FullName", mRender: function (obj) {
                            if (obj.Recipient != null) {
                                return obj.Recipient.FullName;
                            }
                            return ''
                        }
                        },
                        {
                            mData: "Read", "sType": "date", "width": "10%", mRender: function (data) {
                            if (data == null) {
                                return "Not seen yet";
                            }
                            var date = new Date(data);
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
                    length: 100, ellipsisText: ' ...',
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

            if(currentFilters.length>0){
                var oldFilters =$.map(currentFilters, function (obj) {
                    return $(obj).val();
                });

                customFilters = oldFilters.join(' and ');
                // add the extra filters to the existing ones
                settings[0].oInit.filter += " and " + customFilters;
                table.draw();
            }
            //if a notification filter is clicked, apply a corresponding string
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
        <div class="col-xs-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-exclamation"></i> Notifications</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('notifications/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="form-inline  pull-right">
                                {!! Form::select('Creator_Id',withEmpty($creators,"Select creator"),null,['class'=>'form-control notificationFilters']) !!}
                                {!! Form::select('Recipient_Id',withEmpty($recipients,"Select recipient"),"Recipient_Id eq '".Auth::user()->externalId."'",['class'=>'form-control notificationFilters']) !!}
                                {!! Form::select('ReadStatus',withEmpty($readStatus,"Seen status"),null,['class'=>'form-control notificationFilters']) !!}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <table id="table-list" class="table table-list table-hover datatables">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.content')</th>
                            <th>@lang('labels.created-at')</th>
                            <th>@lang('labels.item')</th>
                            <th>@lang('labels.item-nr')</th>
                            <th>Creator</th>
                            <th>Recipient</th>
                            <th>Seen</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

@stop

