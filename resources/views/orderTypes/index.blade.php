@extends('layout.main')
@section('page-title',Lang::get('labels.order-types'))
@section('scripts')
    @include('scripts.dataTablesScripts')

    <script>
        $(document).ready(function () {
            var customFilters = '';

            var table = $('#table-list').DataTable(
                    {
                        responsive: true,
                        stateSave: true,
                         "oLanguage": {
                            "sSearch":       "",
                            "sSearchPlaceholder": Lang.get('labels.search')
                        },
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        aaSorting: [[0, "desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        'filter': "Type_Id ne null" + customFilters,
                        "sAjaxSource": api_address + "OrderTypes?$expand=OrderTypeOrderField,Type",
                        "bProcessing": true,
                        "bServerSide": true,
                        "aoColumns": [
                            {
                                "mData": "Id",
                                "oData": "Id",
                                "sType": "numeric",
                                "width": "5%",
                                mRender: function (id) {
                                    return '<a href="ordertypes/show/' + id + '" title="' + Lang.get('labels.see') + '">' + id + '</a>';
                                }
                            },
                            {
                                "mData": "FormName", sType: "string"
                            },
                            {
                                "mData": null,oData:null,sType:'date',mRender:function(obj){
                                if(obj.OrderTypeOrderField != null){
                                    return obj.OrderTypeOrderField.length
                                }else {
                                    return 0
                                }
                                }
                            },
                            {mData:null,oData:"Type/Name",sType:"String",mRender: function (data) {
                                    return (data.Type != null? data.Type.Name : "--" );
                                }


                            },
                            {mData:null,oData:null,sortable:false,sType:"date",mRender:function (obj) {
                                return "<a href='"+base_url+"/ordertypes/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>";
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

            // get original filters
            var settings = table.settings();

            var originalFilter = settings[0].oInit.filter;

            var filters = $('.orderFieldFilters');

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
                table.draw();
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
                table.draw();
                return false;
            });
        });



    </script>



@stop


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-tasks"></i> @lang('labels.order-types')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{ url('/ordertypes/create') }}" title="@lang('labels.create-order-type')"><i class="fa fa-plus"></i></a>
                        <a href="{{ url('/order-fields') }}" title="@lang('labels.create-order-type')">Fields</a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <table id="table-list" cellpadding="0" cellspacing="0" border="0" class="table table-list cell-border datatables dtr-inline" >
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.name')</th>
                            <th>@lang('labels.fields')</th>
                            <th>@lang('labels.type')</th>
                            <th>@lang('labels.actions')</th>
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