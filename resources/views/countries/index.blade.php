@extends('layout.main')
@section('page-title',Lang::get('labels.countries'))

@section('scripts')

    @include('scripts.dataTablesScripts')
    <script>
        var table =
            $('.datatables').DataTable(
                {
                    stateSave: true,
                    responsive: true,
                    "language": {
                        "url": "datatables-" + locale + '.json'
                    },
                    "oLanguage": {
                        "sSearch":       "",
                        "sSearchPlaceholder": Lang.get('labels.search')
                    },
                    "lengthMenu": [[10, 20, 50], [10, 20, 50]],
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": api_address + "Countries",
                    "aoColumns": [
                        {
                            mData: "Id",
                            "sType": "numeric",
                            "width": "7%",
                            mRender: function (id, unused, object, c) {
                                return '<a href="' + base_url + '/countries/show/' + id + '" title="' + Lang.get('labels.see-lead') + '">' + id + '</a>';
                            }
                        },
                        {mData: "CountryCode"},
                        {mData: "Name"},
                        {mData: "PhoneExtension"},
                        {mData: "VatRate", "sType": "date"},
                        {
                            mData: null, sType: "date", sortable: false, mRender: function (data) {
                            return "<a href='" + base_url + "/countries/edit/" + data.Id + "'><i class='fa fa-pencil'></i></a>"
                        }
                        }
                    ],
                    "fnServerData": fnServerOData,
                    "iODataVersion": 4,
                    "bUseODataViaJSONP": false

                });
    </script>
@stop


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i> @lang('labels.countries')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        {{--<a href="{{url('/countries/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a> countries cannot be created for now--}}
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="table-responsive">
                        <table cellpadding="0" cellspacing="0" border="0" class="table table-striped datatables" id="example">
                            <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.country-code')</th>
                                <th>@lang('labels.name')</th>
                                <th>@lang('labels.phone-extension')</th>
                                <th>@lang('labels.vat')</th>
                                <th>@lang('labels.actions')</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop