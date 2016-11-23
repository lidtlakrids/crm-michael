@extends('layout.main')
@section('page-title',"Logs")
@section('scripts')
    @include('scripts.dataTablesScripts')
    {!! Html::script( asset('js/lib/signalr.min.js')) !!}
    {!! Html::script( 'http://svn.crmtest.dk:8483/signalr/hubs') !!}
    <script>
        $(document).ready(function () {
            var customFilters = '';

             var LogsTable = $('#table-list').DataTable(
                    {
                        responsive: true,
                        stateSave: true,
                        "deferLoading": 0, // don't draw before the filters are applied
                        "language": {
                            "url": "datatables-"+locale+'.json'
                        },
                        "oLanguage": {
                            "sSearch":       "",
                            "sSearchPlaceholder": Lang.get('labels.search')
                        },
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        aaSorting: [[0, "desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "sAjaxSource": api_address + "Logs?$expand=User($select=FullName)",
                        "bProcessing": true,
                        "bServerSide": true,
                        'filter'     : 'Id ne null'+customFilters,
                        "aoColumns": [
                            {
                                "mData": "Id",
                                "oData": "Id",
                                "sType": "numeric",
                                "width": "5%",
                                mRender: function (id) {
                                    return '<a href="logs/show/' + id + '" title="' + Lang.get('labels.see') + '">' + id + '</a>';
                                }
                            },
                            {
                                "mData": "Module", sType: "string"
                            },
                            {mData:"ItemId",'searchable':false,

                            },
                            {
                                "mData": "Error", sType: "string","sClass": "show-more-container multiline",width:"65%"
                            },
                            {mData:null,oData:"User/FullName",sType:'string',mRender:function (obj) {
                                return obj.User != null ? obj.User.FullName : "";
                                }
                            },
                            {mData:"Created",sType:"date",mRender:function(obj){
                                    if(obj != null){
                                        var date = new Date(obj);
                                        return date.toDateTime();
                                    }else{
                                        return "";
                                    }
                                }
                            },
                            {mData:'Seen',sType:"date",mRender:function (seen) {
                                return seen ? "<i class='fa fa-recycle' title='"+Lang.get('labels.renew')+"'><i/>" : "<i class='fa fa-recycle' title='"+Lang.get('labels.seen')+"'><i/>"

                            }
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    }).on('draw.dt', function () {
                //initiate the more container after the table has loaded
                $('.show-more-container').more({
                    length: 150, ellipsisText: ' ...',
                    moreText: '<i class="fa fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
                });
            });

            // get original filters
            var settings = LogsTable.settings();

            var originalFilter = settings[0].oInit.filter;

            var filters = $('.LogFilters');

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
                LogsTable.draw();
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
                LogsTable.draw();
                return false;
            });

            $('.datatables tbody').on( 'click', '.fa', function () {
                var row = $(this).closest('tr');
                var $row = LogsTable.row( row ).node();
                var data = LogsTable.row( row ).data();
                var id = data.Id;

                $.ajax({
                    url: api_address+"Logs("+id+")",
                    type: "GET",
                    success : function()
                    {
                        $($row).remove();
                    },
                    beforeSend: function (request)
                    {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });

            $('#markAllLogSeen').on('click',function (event) {

                $.get(api_address+"Logs/MarkAllSeen")
                    .success(function (data) {
                        LogsTable.draw();
                        new PNotify({
                            title:"Cleared "+data.value+' logs'
                        });
                    });
//                table.rows().every( function ( ) {
//                    var row = table.row(this).node();
//                    var data = this.data();
//                    if(!data.Seen){
//                        $.ajax({
//                            url: api_address+"Logs("+data.Id+")",
//                            type: "GET",
//                            success : function(data)
//                            {
//                                $(row).remove();
//                            },
//                            beforeSend: function (request)
//                            {
//                                request.setRequestHeader("Content-Type", "application/json");
//                            }
//                        });
//                    }
//                });
            });


            var connection = $.hubConnection("http://svn.crmtest.dk:8483/signalr", { useDefaultPath: false });
            var contosoChatHubProxy = connection.createHubProxy('Hubs');
            contosoChatHubProxy.on('addNewMessageToPage', function (name, message) {
               LogsTable.draw();
            });

            connection.start({jsonp:true}).done(function () {
            });
        });

    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-tasks"></i>Logs</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="{{url('detailed-logs')}}">Detailed Logs</a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-3">
                            <div  class="alert alert-info">
                                <p>Click on the icon on each row, to change the status</p>
                            </div>
                            <button id="markAllLogSeen">Mark everything as "Seen"</button>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control LogFilters">
                                <option value="Seen eq false">New</option>
                                <option value="Seen eq true">Old</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table id="table-list" cellpadding="0" cellspacing="0" border="0" class="table table-hover datatables" >
                            <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.model')</th>
                                <th>Id</th>
                                <th>@lang('labels.error')</th>
                                <th>@lang('labels.user')</th>
                                <th>@lang('labels.created-date')</th>
                                <th>@lang('labels.seen')</th>
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
@stop