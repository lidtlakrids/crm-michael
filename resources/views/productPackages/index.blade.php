@extends('layout.main')
@section('page-title',Lang::get('labels.product-packages'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            var customFilters = '';
            var filters = $('.ppFilters');

            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function(){
                return this.value;
            });

            var table = $('#table-list').DataTable({
                "oLanguage": {
                    "sProcessing":   Lang.get('labels.processing'),
                    "sLengthMenu":   Lang.get('labels.length-menu'),
                    "sZeroRecords":  Lang.get('labels.zero-records'),
                    "sInfo":         Lang.get('labels.info'),
                    "sInfoEmpty":    Lang.get('labels.info-empty'),
                    "sInfoFiltered": Lang.get('labels.info-filtered'),
                    "sInfoPostFix":  "",
                    "sSearch":       "",
                    "sUrl":          "",
                    "oPaginate": {
                        "sFirst":    Lang.get('labels.first'),
                        "sPrevious": Lang.get('labels.previous'),
                        "sNext":     Lang.get('labels.next'),
                        "sLast":     Lang.get('labels.last')
                    },
                    "sSearchPlaceholder": Lang.get('labels.search')
                },
                "lengthMenu": [[20,50,100], [20,50,100]],
                "language": {
                    "searchPlaceholder": Lang.get('labels.search')
                },
                aaSorting:[[0,"desc"]], // shows the newest items first
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                'responsive':true,
                'deferLoading':currentFilters.length > 0 ? 0:null,
                "sAjaxSource": api_address+"ProductPackages?$expand=Product($expand=ProductType)",
                'select':'Name',
                'filter': 'Id ne null'+customFilters,
                "aoColumns": [
                    {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){

                        return '<a href="'+base_url+'/product-packages/show/'+ id+'" title="'+Lang.get('labels.see-product-package')+'">'+id+'</a>';
                        }
                    },
                    {mData:null,oData:"Product/Name",sType:"string",mRender:function(obj){
                        if(obj.Product != null){
                            return obj.Product.Name;
                        }else{
                            return obj.Name;
                        }
                    }
                    },
                    {mData:"Active"

                    },
                    {mData:null,oData:"Product/ProductType/Name",sType:"string",mRender:function(obj) {
                            if (obj.Product != null) {
                                if (typeof obj.Product.ProductType.Name != "undefined") {
                                    return obj.Product.ProductType.Name;
                                } else {
                                    return "";
                                }
                            } else {
                                return "no product"
                            }
                        }
                    },
                    {mData:"DefaultRunlength"

                    },
                    {mData:"MaxBudget"

                    },
                    {mData:null,oData:"Product/SalePrice",sType:'number',mRender:function(obj){
                            if(obj.Product!=null){
                                return obj.Product.SalePrice.toLocaleString();
                            }else{
                                return "";
                            }
                        }
                    },
                    {mData:"CreationFee",mRender:function(creationFee){
                        return creationFee ? creationFee.toLocaleString() : "";
                        }
                    },
                    {mData:"AddonCount"

                    },
                    {mData:null,sortable:false,sType:"date",mRender:function(obj){
                        return "<a href='"+base_url+"/product-packages/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>"
                        }

                    }
                ],
                "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                    if(aData.Active){
                        $(nRow).addClass('success');
                    }else{
                        $(nRow).addClass('error');

                    }

                },
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
                    <h4><i class="fa fa-barcode"></i> @lang('labels.product-packages')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('product-packages/create')}}" title="@lang('labels.create-product-package')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="form-inline">
                                <select class="form-control ppFilters">
                                    <option value="">@lang('labels.select-status')</option>
                                    <option selected="selected"  value="Active eq true">@lang('labels.active')</option>
                                    <option value="Active eq false">@lang('labels.inactive')</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table cellpadding="0" cellspacing="0" border="0" style="width: 100%" class="table table-hover datatables" id="table-list">
                            <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.name')</th>
                                <th>@lang('labels.active')</th>
                                <th>@lang('labels.type')</th>
                                <th>Default Runlegth</th>
                                <th>@lang('labels.max-budget')</th>
                                <th>@lang('labels.cost-price')</th>
                                <th>Starting Fee</th>
                                <th>@lang('labels.add-ons-count')</th>
                                <th>@lang('labels.actions')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop