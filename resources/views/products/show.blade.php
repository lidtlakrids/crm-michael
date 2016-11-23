@extends('layout.main')
@section('page-title',Lang::get('labels.product')." : ".$product->Name)

@section('scripts')
@include('scripts.dataTablesScripts')
<script>

$(document).ready(function () {

    var table = $('#table-list').DataTable({
        responsive:true,
        stateSave:true,
        bFilter:false,
        bPaginate:false,
        bSort:false,
        "oLanguage": {
            "sProcessing":   Lang.get('labels.processing'),
            "sLengthMenu":   Lang.get('labels.length-menu'),
            "sZeroRecords":  Lang.get('labels.zero-records'),
            "sInfo":         Lang.get('labels.info'),
            "sInfoEmpty":    Lang.get('labels.info-empty'),
            "sInfoFiltered": Lang.get('labels.info-filtered'),
            "sInfoPostFix":  "",
            "sSearch":       Lang.get('labels.search'),
            "sUrl":          "",
            "oPaginate": {
                "sFirst":    Lang.get('labels.first'),
                "sPrevious": Lang.get('labels.previous'),
                "sNext":     Lang.get('labels.next'),
                "sLast":     Lang.get('labels.last')
            }
        },
        "lengthMenu": [[20,50,100], [20,50,100]],
        //aaSorting:[[0,"desc"]], // shows the newest items first
        "sPaginationType": "full_numbers",
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": api_address+"OptimizeRules?$expand=TaskList($select=Title,Id)",
        'filter': "Id ne null and Product_Id eq "+getModelId(),
        "select": "Id",
        "aoColumns": [
            {mData:"Size","sType":"string",mRender:function(size,unused,obj){
                return "<span class='pseudolink editOptimizeRule' role='link' data-optimize-rule-id='"+obj.Id+"'>"+size+"</span>";
            }
            },
            {mData:"OptimizeInterval",sType:"number"

            },
            {mData:null,oData:"TaskList/Title",sType:"string",mRender:function(obj){
                    return obj.TaskList == null ? "" : obj.TaskList.Title;
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

})

</script>


@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Product',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $product->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-gray">
            <div class="panel-heading">
                <h4>@lang('labels.product')</h4>
                <div class="options">
                    @if(isAllowed('Products','patch'))
                        <a href="{{url('products/edit',$product->Id)}}" title="@lang('labels.edit')"><i class="fa fa-pencil"></i></a>
                    @endif
                </div>
            </div>
            <div class="panel-body">
                <div class="col-md-4">
                    <h4>@lang('labels.product-info')</h4>
                    <dl class="dl-horizontal">
                        <dt>@lang('labels.name')</dt>
                        <dd>{{$product->Name or "---"}}</dd>

                        <dt>@lang('labels.sale-price')</dt>
                        <dd>{{$product->SalePrice or "---"}}</dd>

                        <dt>@lang('labels.recommended-price')</dt>
                        <dd>{{$product->RecommendedPrice or "---"}}</dd>

                        <dt>@lang('labels.cost-price')</dt>
                        <dd>{{$product->CostPrice or "---"}}</dd>

                        <dt>@lang('labels.description')</dt>
                        <dd><pre>{{$product->Description or "---"}}</pre></dd>

                        <dt>@lang('labels.active')</dt>
                        <dd>{{($product->Active)?Lang::get('labels.yes'):Lang::get('labels.no')}}</dd>

                        <dt>@lang('labels.commission')</dt>
                        <dd>{{$product->ProductCommission or "---"}}</dd>

                        <dt>@lang('labels.product-department')</dt>
                        <dd>{{$product->ProductDepartment->Name or "---"}}</dd>

                        <dt>@lang('labels.product-type')</dt>
                        <dd>{{$product->ProductType->Name or "---"}}</dd>
                    </dl>
                </div>

                <div class="col-md-4">
                    <h4>
                        @lang('labels.optimize-rules') - <i class='fa fa-plus addOptimizeRule' data-product-id='{{$product->Id}}' title='@lang('labels.create-optimize-rule')'></i>
                    </h4>

                    <table class="table table-condensed" id="table-list">
                        <thead>
                            <tr>
                                <th>@lang('labels.size')</th>
                                <th>@lang('labels.optimize-interval')</th>
                                <th>@lang('labels.task-template')</th>
                            </tr>
                        </thead>
                    </table>

                </div>

            </div>
        </div>
    </div>
</div>
@stop