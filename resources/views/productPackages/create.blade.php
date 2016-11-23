@extends('layout.main')
@section('page-title',Lang::get('labels.create-product-package'))
@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function(){

            // we validate the first form
            $('#formSubmits').on('click',function(event){

                if($('#createProductPackage')[0].checkValidity()){
                    $('#createProductPackage').submit();
                }else{
                    $('#productPackageSubmit').click();
                }
            });

            /// Nastiest piece of code i've wrote

            $('#createProductPackage').on('submit', function (event) {
                event.preventDefault();

                // we valdiate the second form form

                if(!$('#createPackage')[0].checkValidity()){

                    $('#packageSubmit').click();
                    return;
                }


                //get package data
                var packageData = convertSerializedArrayToHash($('#createPackage').serializeArray());
                packageData.Active = (packageData.Active)?true:false;

                packageData.Product_Id = 1;

                //get all allowed products

                var allowedProducts=[];
                $("input:checkbox[name=Product]:checked").each(function(){
                    allowedProducts.push($(this).val());
                });

                //get product data
                var productData = convertSerializedArrayToHash($(this).serializeArray());

                // sets null for all empty input
                for (var prop in productData) {
                    if (productData[prop] === "") {
                        delete(productData[prop]);
                    }
                }
                productData.Active = (packageData.Active)?true:false;

                //make the optimizeRules object
                var optimizeRules = {};
                optimizeRules.Size = packageData.Size;
                optimizeRules.Task_Id = productData.Task_Id || null ;
                delete(productData.Task_Id);

                $.ajax({
                    type: "POST",
                    url: api_address + 'Products',
                    data: JSON.stringify(productData),
                    success: function (data) {
                        // add the product ID to the optimizeRule
                        optimizeRules.Product_Id = data.Id;
                        //Once the product is created, create the package with the Product_Id
                        packageData.Product_Id = data.Id;
                        //sales price same for product and package
                        packageData.CostPrice = data.SalePrice;
                        //the product is added just by Id
                        delete(packageData.Product);
                        $.ajax({
                            type: "POST",
                            url: api_address + 'ProductPackages',
                            data: JSON.stringify(packageData),
                            success: function (data) {
                                //add all allowed products to the package
                                allowedProducts.forEach(function(Product_Id){
                                    $.ajax({
                                        type: "POST",
                                        url: api_address + 'ProductPackages('+data.Id+')/action.AddProduct',
                                        data: JSON.stringify({Product_Id : Number(Product_Id)}),
                                        success: function (data) {

                                        },
                                        error: function (err) {
                                            new PNotify({
                                                title: Lang.get('labels.error'),
                                                text: Lang.get(err.statusText),
                                                type: 'error'
                                            });
                                        },
                                        beforeSend: function (request) {
                                            request.setRequestHeader("Content-Type", "application/json");
                                        }
                                    });

                                });

                                new PNotify({
                                    title: Lang.get('labels.success'),
                                    text: Lang.get('labels.package-created')+" Redirecting...",
                                    type: 'success'
                                });
                                setTimeout(function () {
                                    window.location = base_url+'/product-packages'

                                },1000);
                            },
                            error: function (err) {
                                new PNotify({
                                    title: Lang.get('labels.error'),
                                    text: Lang.get(err.statusText),
                                    type: 'error'
                                });
                            },
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });

                    },
                    error: function (err) {
                        new PNotify({
                            title: Lang.get('labels.error'),
                            text: Lang.get(err.statusText),
                            type: 'error'
                        });
                    },
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
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4 class="panel-title">@lang('labels.create-product-package')</h4>
            </div>
            <div class="panel-body">

                <div class="col-md-6">
                    <h4>@lang('labels.product-info')</h4>
                    <div class="form-horizontal">
                        {!!  Form::open(['id'=>'createProductPackage'])!!}
                        <div class="form-group">
                                {!! Form::label('product-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                            {!! Form::text('Name',null,['class'=>'form-control','id'=>'product-Name','required'=>'required']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-Number',Lang::get('labels.number'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('Number',null,['class'=>'form-control','id'=>'product-Number','min'=>0,'step'=>'1']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-SalePrice',"Sales price (Monthly)",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('SalePrice',null,['class'=>'form-control','id'=>'product-SalePrice','required'=>'required']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-RecommendedPrice',Lang::get('labels.recommended-price'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('RecommendedPrice',null,['class'=>'form-control','id'=>'product-RecommendedPrice']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-CostPrice',Lang::get('labels.cost-price'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('CostPrice',null,['class'=>'form-control','id'=>'product-CostPrice']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::textarea('Description',null,['class'=>'form-control','id'=>'product-Description']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-ProductCommission',Lang::get('labels.commission'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('ProductCommission',null,['class'=>'form-control','id'=>'product-ProductCommission','min'=>0,'max'=>1,'step'=>'0.01',]) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block">format : 0.4 <i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-ProductDepartment_Id',Lang::get('labels.department'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                              {!! Form::select('ProductDepartment_Id',withEmpty($departments),null,['class'=>'form-control','id'=>'product-ProductDepartment_Id']) !!}
                            </div>
                            <div class="col-sm-3">
                             <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-ProductType_Id',Lang::get('labels.type'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                              {!! Form::select('ProductType_Id',withEmpty($types),null,['class'=>'form-control','id'=>'product-ProductType_Id','required'=>'required']) !!}
                            </div>
                            <div class="col-sm-3">
                              <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-TimeAllowance',"Support time (minutes)",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('TimeAllowance',null,['class'=>'form-control','id'=>'product-TimeAllowance','min'=>0]) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-OptimizeInterval',"Optimize Interval (days)",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('OptimizeInterval',null,['class'=>'form-control','id'=>'product-OptimizeInterval','required'=>'required']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                            <input type="submit" id="productPackageSubmit" class="hidden">
                        </div>
                    </div>
                </div>
                {!! Form::close()!!}

                <div class="col-md-6">
                    <h4>@lang('labels.package-info')</h4>
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'createPackage']) !!}
                        <div class="form-group">
                            {!! Form::label('productPackage-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                            {!! Form::text('Name',null,['class'=>'form-control','id'=>'productPackage-Name','required'=>'required']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('productPackage-Active',Lang::get('labels.active'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                            {!! Form::checkbox('Active',null,true,['class'=>'form-control','id'=>'productPackage-Active']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('productPackage-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                            {!! Form::textarea('Description',null,['class'=>'form-control','id'=>'productPackage-Description']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('productPackage-Size',Lang::get('labels.size'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::select('Size',withEmpty($sizes),null,['class'=>'form-control','id'=>'productPackage-Size','required'=>'required']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('productPackage-DefaultRunlength',Lang::get('labels.runlength'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('DefaultRunlength',null,['class'=>'form-control','min'=>1,'required'=>'required','id'=>'productPackage-DefaultRunlength']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('productPackage-DefaultPaymentTerm',Lang::get('labels.payment-terms'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::select('DefaultPaymentTerm',withEmpty($paymentTerms),null,['class'=>'form-control','id'=>'productPackage-DefaultPaymentTerm','required'=>'required']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('productPackage-MaxBudget',"Max daily budget",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                            {!! Form::number('MaxBudget',null,['class'=>'form-control','id'=>'productPackage-MaxBudget','required'=>'required']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('productPackage-CreationFee',Lang::get('labels.creation-fee'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('CreationFee',null,['class'=>'form-control','id'=>'productPackage-CreationFee','min'=>'0','required'=>'required']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-CreationFeeText',Lang::get('labels.creation-fee-text'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::textarea('CreationFeeText',null,['class'=>'form-control','id'=>'product-CreationFee']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('productPackage-AdministrationFee',Lang::get('labels.administration-fee'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('AdministrationFee',null,['class'=>'form-control','id'=>'productPackage-AdministrationFee','required'=>'required']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-AdministrationFeeText',"Administration Fee Text",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::textarea('AdministrationFeeText',null,['class'=>'form-control','id'=>'product-AdministrationFeeText']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('productPackage-AddonCount',Lang::get('labels.max-add-ons'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                            {!! Form::number('AddonCount',null,['class'=>'form-control','id'=>'productPackage-AddonCount','required'=>'required']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('product-AddonCount',Lang::get('labels.allowed-products'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6" style="max-height:300px;overflow: scroll;">
                                @foreach($products as $k=>$val)
                                    <div class="form-group">
                                        <div class="col-sm-6">
                                            <div class="checkbox block">
                                                <label for="Product[{{$k}}]">
                                                    <input id="Product[{{$k}}]" value="{{$k}}" name="Product" type="checkbox">
                                                    {{$val}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="submit" id="packageSubmit" class="hidden">
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="btn-toolbar">

                    {!! Form::submit(strtoupper(Lang::get('labels.save')),['class'=> 'btn btn-orange btn-label form-control','id'=>'formSubmits']) !!}

                </div>

                <hr/>

            </div>
        </div>
    </div>
</div>

@stop