@extends('layout.main')
@section('page-title',Lang::get('labels.products'))
@section('scripts')
@include('scripts.dataTablesScripts')
<script>
        $(document).ready(function () {
            var customFilters = '';
            var filters = $('.productFilters');

            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function(){
                return this.value;
            });

            var table = $('#table-list').DataTable({
                "lengthMenu": [[20,50,100], [20,50,100]],
                "oLanguage": {
                    "sSearch":       "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                },
                aaSorting:[[0,"desc"]], // shows the newest items first
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                'responsive' : true,
                'deferLoading':currentFilters.length > 0 ? 0:null,
                "sAjaxSource": api_address+"Products?$expand=ProductType,ProductDepartment",
                filter: 'Id ne null',
                "aoColumns": [
                    {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){

                        return '<a href="'+base_url+'/products/show/'+ id+'" title="'+Lang.get('labels.see-product')+'">'+id+'</a>';
                    }},
                    { "mData": "Name", "oData": "Name","sName":"Name" },
                    { "mData": null,"sType":"string", "oData": "ProductType/Name",mRender:function(obj)
                        {
                            if(obj.ProductType != null){
                                return obj.ProductType.Name;
                            } else {return "-"}
                        }
                    },
                    {"mData":"Active","oData":"Active","sType":"date",mRender:function (active) {
                        return trueOrFalseIcon(active);
                    }
                    },
                    {mData:"SalePrice",searchable:false,mRender:function(price){
                            return price.format();
                        }

                    },
                    {mData:"ProductCommission"

                    },
                    {mData:'Number',sType:'number'

                    },
                    {mData:null,sortable:false,searchable:false,sType:"date",mRender:function(obj){
                        return "<a href='"+base_url+"/products/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>"
                        }
                    }
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
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
                    <h4><i class="fa fa-barcode"></i> @lang('labels.products')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('products/create')}}" title="@lang('labels.create-product')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="form-inline">
                                <select class="form-control productFilters">
                                    <option value="">@lang('labels.select-status')</option>
                                    <option selected="selected"  value="Active eq true">@lang('labels.active')</option>
                                    <option value="Active eq false">@lang('labels.inactive')</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <table cellpadding="0" cellspacing="0" border="0" style="width: 100%;" class="table datatables" id="table-list">
                        <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.name')</th>
                                <th>@lang('labels.type')</th>
                                <th>@lang('labels.active')</th>
                                <th>@lang('labels.sale-price')</th>
                                <th>Commission</th>
                                <th>Economic ID</th>
                                <th>@lang('labels.actions')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

 