@extends('layout.main')
@section('page-title',Lang::get('labels.titles'))
@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){
            var table =
                $('.datatables').DataTable(
                    {
                        "language": {
                            "url": "datatables-"+locale+'.json'
                        },
                        "oLanguage": {
                            "sSearch":       "",
                            "sSearchPlaceholder": Lang.get('labels.search')
                        },
                        "lengthMenu": [[10,20, 50], [10,20, 50]],
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": api_address+"Titles",
                        "aoColumns": [
                            {mData: "Id", "sType": "numeric", "width": "7%", mRender: function (id) {
                                return '<a href="'+base_url+'/titles/show/' + id + '">' + id + '</a>';
                                }
                            },
                            {mData:"Name"
                            },
                            {mData:"Description"
                            },
                            {"mData":null,"sortable":false,sType:"date","oData":null,mRender: function (obj) {
                                var links;
                                links = "<a href='"+base_url+"/titles/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>";
                                return links;
                              }
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    });
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i> @lang('labels.titles')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        @if(isAllowed('titles','post'))<a href="{{url('/titles/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>@endif
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <table cellpadding="0" cellspacing="0" border="0" class="table table table-hover datatables" id="example">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.name')</th>
                            <th>@lang('labels.description')</th>
                            <th>@lang('labels.actions')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop