@extends('layout.main')
@section('page-title',"Detailed logs")
@section('scripts')
    @include('scripts.dataTablesScripts')
    {!! Html::script( asset('js/lib/signalr.min.js')) !!}
    {!! Html::script( 'http://svn.crmtest.dk:8483/signalr/hubs') !!}
    <script>
        $(document).ready(function () {

             var LogsTable = $('#table-list').DataTable(
                    {
                        responsive: true,
                        stateSave: true,
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        aaSorting: [[0, "desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "sAjaxSource": api_address + "DetailedLogs?$expand=User($select=FullName)",
                        "bProcessing": true,
                        "bServerSide": true,
                        'filter'     : 'Id ne null',
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
                                "mData": "Model", sType: "string"
                            },
                            {mData:"ModelId",'searchable':false,

                            },
                            {
                                "mData": "OriginalObject", sType: "string","sClass": "show-more-container multiline"
                            },
                            {
                                "mData": "Changes", sType: "string","sClass": "show-more-container multiline"
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
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    }).on('draw.dt', function () {
                //initiate the more container after the table has loaded
                $('.show-more-container').more({
                    length: 55, ellipsisText: ' ...',
                    moreText: '<i class="fa fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
                });
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
                        <a href="{{url('logs')}}">Error Logs</a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <table id="table-list" cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-condensed table-hover datatables" >
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.model')</th>
                            <th>Id</th>
                            <th>Original</th>
                            <th>Changes</th>
                            <th>User</th>
                            <th>Created</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop