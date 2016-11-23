@extends('layout.main')
@section('page-title',Lang::get('labels.teams'))
@section('styles')
@stop

@section('scripts')
@include('scripts.dataTablesScripts')
<script>
    $(document).ready(function () {
        var table =
            $('.datatables').DataTable(
                {
                    "language": {
                        "url": "datatables-" + locale + '.json',
                    },
                    "oLanguage": {
                        "sSearch":       "",
                        "sSearchPlaceholder": Lang.get('labels.search')
                    },
                    "lengthMenu": [[10, 20, 50], [10, 20, 50]],
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    'responsive':true,
                    "sAjaxSource": api_address + "ManagerTeams?$expand=Users($select=UserName)",
                    "aoColumns": [
                        {
                            mData: "Id", "sType": "numeric", "width": "7%", mRender: function (id) {
                            return '<a href="teams/show/' + id + '" title="' + Lang.get('labels.see-team') + '">' + id + '</a>';
                        }
                        },
                        {
                            mData: "Name"
                        },
                        {
                            mData: null,
                            oData: "ManagerTeams/Users",
                            orderable: false,
                            sType: "string",
                            sClass:'show-more-container',
                            mRender: function (data) {
                                var users = "";
                                data.Users.forEach(function (user) {
                                    users += user.UserName + ' / ';
                                });
                                return users;
                            }
                        },
                        {
                            mData: "TeamType", sType: 'date'
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
    });
</script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i> @lang('labels.teams')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('/teams/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <table cellpadding="0" cellspacing="0" border="0" style="width: 100%" class="table table-list cell-border datatables dtr-inline" id="example">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.name')</th>
                            <th>@lang('labels.users')</th>
                            <th>@lang('labels.type')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop