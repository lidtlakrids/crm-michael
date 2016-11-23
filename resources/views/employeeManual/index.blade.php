@extends('layout.main')
@section('page-title',Lang::get('labels.employee-manual'))
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            var table = $('#example').DataTable({
                responsive: true,
                saveState: true,
                "language": {
                    "url": "datatables-" + locale + '.json'
                },
                "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                'filter': "Published ne null",
                "sAjaxSource": api_address + "EmployeeManuals",
                select: "Id",
                "aoColumns": [
                    {
                        "mData": "Title", mRender: function (title, unused, obj) {
                        return "<a href='" + base_url + "/employee-manual/" + obj.Id + "'>" + title + "</a>";
                    }
                    },
                    {mData: "Description", "sClass": "show-more-container", sType: "string"}
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

            $('#createManual').click(function (event) {
                event.preventDefault();
                var modal = getDefaultModal();
                modal.find('.modal-title').empty().append(Lang.get('labels.create-employee-manual'));

                modal.find('.modal-body').loadTemplate(base_url + '/templates/employeeManual/addManualForm.html',
                        {
                            TitleLabel: Lang.get('labels.title'),
                            DescriptionLabel: Lang.get('labels.description'),
                            ContentLabel: Lang.get('labels.content'),
                            PublishedLabel: Lang.get('labels.should-be-published')
                        },
                        {
                            overwriteCache: true
                        })
            });

            $('body').on('submit', '#createManualForm', function (event) {
                event.preventDefault();
                var formData = $(this).find(':input').filter(function () {
                    return $.trim(this.value).length > 0
                }).serializeJSON();

                formData.Published = formData.Published ? new Date() : null;

                $.ajax({
                    type: "POST",
                    url: api_address + 'EmployeeManuals',
                    data: JSON.stringify(formData),
                    success: function (data) {
                        closeDefaultModal();
                        table.draw();
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

            })
        });
    </script>
@stop

@section('content')

    {!! Form::hidden('Model','EmployeeManual',['id'=>'Model']) !!}

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-book"></i>Employee Manual</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        @if(isAllowed('employeeManuals','post'))
                            <i class="fa fa-plus" id="createManual" title="@lang('labels.create')"></i>
                            {{--<a href="{{url('employee-manual/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>--}}
                        @endif
                    </div>
                </div>
                <div class="panel-body">
                    <div>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-striped datatables" id="example">
                            <thead>
                            <tr>
                                <th>@lang('labels.title')</th>
                                <th>@lang('labels.description')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

