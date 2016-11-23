@extends('layout.main')
@section('page-title',Lang::get('labels.optimize-rules'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')

    <script>
        $(document).ready(function () {
            var customFilters = '';
            var table =  $('#table-list').DataTable({
                responsive:true,
                stateSave:true,
                "language": {
                    "url": "datatables-"+locale+'.json',
                },
                "oLanguage": {
                    "sSearch":       "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                },
                "lengthMenu": [[20,50,100], [20,50,100]],
                //aaSorting:[[0,"desc"]], // shows the newest items first
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": api_address+"Products?$expand=OptimizeRule($expand=TaskList($select=Title))",
                'filter': "Id ne null"+customFilters,
                "select": "Id",
                "aoColumns": [
                    {mData:"Name","sType":"string",mRender:function(product,unused,obj){
                        return '<a href="'+base_url+'/products/show/'+ obj.Id+'" title="'+Lang.get('labels.see-product')+'">'+product+'</a>';
                        }
                    },
                    {mData:"OptimizeInterval",sType:"number"

                    },
                    {mData:null,oData:null,sType:"number",sortable:false,mRender:function (obj) {
                        var links = '';
                        $.each(obj.OptimizeRule,function(a,b){
                            links += "<span class='pseudolink editOptimizeRule' role='link' data-optimize-rule-id='"+b.Id+"'>"+b.Size+" - "+b.OptimizeInterval+" "+Lang.get('labels.days') +"</span><br/>";
                        });
                        return links;
                        }
                    },
                    {mData:null,oData:null,sortable:false,mRender:function (obj) {
                        return "<i class='fa fa-plus addOptimizeRule' data-product-id='"+obj.Id+"' title='"+Lang.get('labels.create-optimize-rule')+"'></i>"
                        }
                    }
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            });

            $('body').on('submit','#createOptimizeRuleForm',function (event) {
                event.preventDefault();
                var data = $(this).find(':input').filter(function () {
                    return $.trim(this.value).length > 0
                }).serializeJSON();
                $.ajax({
                    type: "POST",
                    url: api_address + 'OptimizeRules',
                    data: JSON.stringify(data),
                    success: function (data) {
                        table.draw();
                        closeDefaultModal();
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });


            $('body').on('submit','#editOptimizeRuleForm',function (event) {
                event.preventDefault();
                var data = $(this).find(':input').filter(function () {
                    return $.trim(this.value).length > 0
                }).serializeJSON();
                var optimizeRuleId = data.OptimizeRuleId;
                delete(data.OptimizeRuleId);
                $.ajax({
                    type: "PATCH",
                    url: api_address + 'OptimizeRules('+optimizeRuleId+")",
                    data: JSON.stringify(data),
                    success: function () {
                        table.draw();
                        closeDefaultModal();
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
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
                    <h4><i class="fa fa-barcode"></i> @lang('labels.optimize-rules')</h4>
                    <div class="options">
                        @if(isAllowed('optimizeRules','post'))<a href="{{url('optimize-rules/create')}}" title="@lang('labels.create-product-package')"><i class="fa fa-plus"></i></a>@endif
                    </div>
                </div>
                <div class="panel-body">
                    <table cellpadding="0" cellspacing="0" border="0" style="width:100%" class="table table-hover datatables" id="table-list">
                        <thead>
                        <tr>
                            <th>@lang('labels.product')</th>
                            <th>@lang('labels.default-optimize-interval')</th>
                            <th>@lang('labels.optimize-rules')</th>
                            <th>@lang('labels.options')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

@stop