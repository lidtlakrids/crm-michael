@extends('layout.main')
@section('page-title',"Contract fields")
@section('scripts')
    @include('scripts.dataTablesScripts')

    <script>
        $(document).ready(function () {
            var customFilters = '';

            var table = $('#table-list').DataTable(
                {
                    responsive: true,
                    stateSave: true,
                    "language": {
                        "url": "datatables-" + locale + '.json'
                    },
                    "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                    aaSorting: [[0, "desc"]], // shows the newest items first
                    "sPaginationType": "full_numbers",
                    'filter': "Created ne null" + customFilters,
                    "sAjaxSource": api_address + "ContractFields?$expand=FieldOption,FieldLinks($expand=ProductType($select=Name))",
                    "bProcessing": true,
                    "bServerSide": true,
                    "fnRowCallback": function (nRow, aaData) {
                        if (aaData.Special && aaData.Active) {
                            $(nRow).addClass('success');
                        } else if (!aaData.Active) {
                            $(nRow).addClass('danger');
                        }
                    },
                    "aoColumns": [
                        {
                            "mData": "Id",
                            "oData": "Id",
                            "sType": "numeric",
                            "width": "5%",
                            mRender: function (id) {

                                return '<a href="order-fields/show/' + id + '" title="See Order">' + id + '</a>';
                            }
                        },
                        {
                            "mData": "DisplayName", sType: "string"
                        },
                        {
                            "mData": "ValueName", sType: "string"
                        },
                        {
                            "mData": "Description", sType: "string", "sClass": "show-more-container"
                        },
                        {
                            "mData": "Active", mRender: function (active) {
                            return trueOrFalseIcon(active, {title: Lang.get('labels.active')});
                        }
                        },
                        {
                            "mData": "FieldType", sType: "date"
                        },
                        {
                            "mData": null, oData: null, sType: 'date', mRender: function (obj) {
                            if (obj.FieldOption != null) {
                                return obj.FieldOption.map(function(elem){
                                    return elem.DisplayName;
                                }).join("<br>");
                            } else {
                                return 0
                            }
                        }
                        },
                        {
                            mData: null, oData: null, sortable: false, sType: "date", mRender: function (obj) {
                            return "<a href='" + base_url + "/contract-fields/edit/" + obj.Id + "'><i class='fa fa-pencil'></i></a>";
                        }
                        },
                        {   mData:null,oData:null,sortable:false,searchable:false,mRender:function (obj) {
                                if(obj.FieldLinks.length > 0){
                                    var text = '';
                                    $.each(obj.FieldLinks,function (index,val) {
                                        text += "<span class='contractFieldLink'>"+val.ProductType.Name +'<i class="fa fa-times removeContractFieldLink" title="Remove from this contract type" data-type-id="'+val.ProductType_Id+'" data-field-id="'+val.ContractField_Id+'"></i></span><br>'
                                    });
                                    return text;
                                }else{
                                    return '';
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
                    length: 40, ellipsisText: ' ...',
                    moreText: '<i class="fa fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
                });
            });

            // get original filters
            var settings = table.settings();
            var originalFilter = settings[0].oInit.filter;
            var filters = $('.orderFieldFilters');
            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function () {
                return this.value;
            });

            if (currentFilters.length > 0) {
                var oldFilters = $.map(currentFilters, function (obj) {
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
                if (customFilters.length > 0) {
                    settings[0].oInit.filter += " and " + customFilters;
                }
                //redraw the table
                table.draw();
                return false;
            });


            $('body').on('click','.removeContractFieldLink',function (event) {
                var target = $(event.target);
                var typeId = target.data('type-id');
                var fieldId = target.data('field-id');
                $.ajax({
                    url: api_address + "ContractFields("+fieldId+")/RemoveFrom",
                    type: "POST",
                    data: JSON.stringify({"ProductType_Id": typeId}),
                    success: function () {
                        target.closest('.contractFieldLink').remove();
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

            })

        });
    </script>
@stop
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-tasks"></i> @lang('labels.fields')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{ url('/contract-fields/create') }}" title="Create contract field"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <ol class="breadcrumb">
                        @if(isset($ordertypes))
                            @foreach($ordertypes as $id=>$name)
                                    <li> <a href="{{url('contract-types/show',$id)}}">{{$name}}</a></li>
                            @endforeach
                        @endif
                    </ol>
                    <hr>
                    <table id="table-list" cellpadding="0" cellspacing="0" border="0" style="width: 100%;" class="table table-condensed table-hover" >
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.display-name')</th>
                            <th>@lang('labels.value')</th>
                            <th>@lang('labels.description')</th>
                            <th>@lang('labels.active')</th>
                            <th>@lang('labels.type')</th>
                            <th>@lang('labels.options')</th>
                            <th>@lang('labels.actions')</th>
                            <th>Linked to</th>
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