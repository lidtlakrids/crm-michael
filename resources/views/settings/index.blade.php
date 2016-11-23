@extends('layout.main')
@section('page-title',Lang::get('labels.settings'))

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
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": api_address + "Settings",
                        "aoColumns": [
                            {
                                mData: "Id", "sType": "numeric", "width": "7%", mRender: function (id) {
                                return '<a href="' + base_url + '/settings/show/' + id + '" title="' + Lang.get('labels.see') + '">' + id + '</a>';
                            }
                            },
                            {
                                mData: "Model", "sType": "string"
                            },
                            {
                                mData: "Name"
                            },
                            {
                                mData: "Value"
                            },
                            {
                                mData: "Description"
                            },
                            {
                                mData: "Active"
                            },
                            {
                                mData: null, sType: "date", sortable: false, mRender: function (obj) {
                                return "<a href='" + base_url + "/settings/edit/" + obj.Id + "'><i class='fa fa-pencil'></i></a>"
                            }
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    });

            $('#updateMetadata').on('click', function (event) {

                $(event.target).prop('disabled', true);

                $.get(base_url + '/settings/updateMetadata').success(function (data) {
                    console.log(data);
                    $(event.target).prop('disabled', false);

                }).error(function (error) {
                    console.log(error);
                    $(event.target).prop('disabled', false);
                })

            });
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h4><i class="fa fa-gears"></i> @lang('labels.settings')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="{{ url('/settings/create') }}" title="@lang('labels.create-setting')"><i
                                    class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-12">
                         <button class="btn btn-sm btn-primary" id="updateMetadata"> Update metadata </button>
                        </div>
                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-list cell-border datatables dtr-inline">
                        <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.model')</th>
                                <th>@lang('labels.setting')</th>
                                <th>@lang('labels.value')</th>
                                <th>@lang('labels.description')</th>
                                <th>@lang('labels.active')</th>
                                <th>@lang('labels.actions')</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
@stop