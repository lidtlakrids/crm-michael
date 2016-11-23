@extends('layout.main')
@section('page-title',Lang::get('labels.edit-product-package')." : ".($package->Product != null? $package->Product->Name : Lang::get('labels.error-in-package')))
@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function () {

            var form = $('#editProductPackage');
            var packageId = $('#ModelId').val();

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());

            $(form).on('submit', function (event) {
                event.preventDefault();

                var packageId = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());

                var itemsToSubmit = hashDiff(startItems, currentItems);

                // sets null for all empty input
                for (var prop in itemsToSubmit) {
                    if (itemsToSubmit[prop] === "") {
                        itemsToSubmit[prop] = null;
                    }
                }
                delete(itemsToSubmit['Product']);

                //best one-liner ever to exist.
                itemsToSubmit.Active = $('#productPackage-Active').is(':checked') ? true : false;
                itemsToSubmit.CreationFeeSplitable = $('#productPackage-CreationFeeSplitable').is(':checked') ? true : false;
                itemsToSubmit.AdministrationFeeSplitable = $('#productPackage-AdministrationFeeSplitable').is(':checked') ? true : false;

                //send request only if something changed
                if (!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'ProductPackages(' + packageId + ')',
                        data: JSON.stringify(itemsToSubmit),
                        success: function (data) {
                            new PNotify({
                                title: Lang.get('labels.success'),
                                text: Lang.get('messages.update-was-successful'),
                                type: 'success'
                            });
                        },
                        error: function (err) {
                            new PNotify({
                                title: Lang.get('labels.error'),
                                text: Lang.get(err.responseJSON.error.innererror.message),
                                type: 'error'
                            });
                        },
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
                }
            });

            $('input[name=Product]').on('click', function (event) {

                var save = $(event.target).prop('checked');
                var product_Id = $(event.target).val();

                var action = save ? "AddProduct" : "RemoveProduct";

                $.ajax({
                    type: "POST",
                    url: api_address + 'ProductPackages(' + packageId + ')/action.' + action,
                    data: JSON.stringify({Product_Id: product_Id}),
                    success: function (data) {
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get('messages.update-was-successful'),
                            type: 'success'
                        });
                    },
                    error: function (err) {
                        new PNotify({
                            title: Lang.get('labels.error'),
                            text: Lang.get(err.responseJSON.error.innererror.message),
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

    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','ProductPackage',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $package->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4>@lang('labels.edit')</h4>
                <div class="options">
                    <a href="{{url('product-packages/show',$package->Id)}}" title="@lang('labels.back')"><i
                                class="fa fa-arrow-left"></i> @lang('labels.back')</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-horizontal">
                            {!! Form::open(['id'=>'editProductPackage']) !!}

                            <div class="form-group">
                                {!! Form::label('productPackage-Name',Lang::get('labels.name'),['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::text('Name',$package->Name,['class'=>'form-control','id'=>'productPackage-Name','required'=>'required']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('productPackage-Active',Lang::get('labels.active'),['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::checkbox('Active',$package->Active,$package->Active,['class'=>'form-control','id'=>'productPackage-Active']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('productPackage-Description',"Description for the Invoice",['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::textarea('Description',$package->Description,['class'=>'form-control','id'=>'productPackage-Description','rows'=>'2']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('productPackage-Size',Lang::get('labels.size'),['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::select('Size',withEmpty($sizes),findEnumNumber($sizes,$package->Size),['class'=>'form-control','id'=>'product-Product_Id','required'=>'required']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('productPackage-DefaultRunLength',Lang::get('labels.runlength'),['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::number('DefaultRunlength',$package->DefaultRunlength,['class'=>'form-control','id'=>'productPackage-DefaultRunLength','min'=>"1",'step'=>1,'required'=>'required']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('productPackage-DefaultPaymentTerm',Lang::get('labels.payment-terms'),['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::select('DefaultPaymentTerm',withEmpty($paymentTerms),findEnumNumber($paymentTerms,$package->DefaultPaymentTerm),['class'=>'form-control','id'=>'productPackage-DefaultPaymentTerm','required'=>'required']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('productPackage-MaxBudget',Lang::get('labels.max-budget'),['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::number('MaxBudget',$package->MaxBudget,['class'=>'form-control','id'=>'productPackage-MaxBudget','required'=>'required','min'=>0]) !!}
                                </div>
                            </div>

                            @if($package->Product != null)
                                <div class="form-group">
                                    {!! Form::label('productPackage-SalesPrice',Lang::get('labels.sale-price'),['class'=>'col-sm-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::number('SalesPrice',$package->Product->SalePrice,['class'=>'form-control','id'=>'productPackage-SalesPrice','required'=>'required','disabled'=>'disabled']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"> <a
                                                        href="{{url('products/edit',$package->Product->Id)}}">@lang('labels.edit')
                                                    &nbsp; {{$package->Product->Name}}</a></i></p>
                                    </div>
                                </div>
                            @else
                                <div class="form-group">
                                    <div class="col-sm-6 col-sm-offset-3"><span role="link"
                                                                                class="pseudolink setProductOnPackage">@lang('messages.no-product-on-the-package')</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('productPackage-Product_Id',Lang::get('labels.product'),['class'=>'col-sm-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('Product_Id',withEmpty($products),null,['class'=>'form-control','id'=>'product-Product_Id','required'=>'required']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group">
                                {!! Form::label('productPackage-CostPrice',Lang::get('labels.cost-price'),['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::number('CostPrice',$package->CostPrice,['class'=>'form-control','id'=>'productPackage-CostPrice','required'=>'required','min'=>'0']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('product-AddonCount',Lang::get('labels.add-ons-count'),['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::text('AddonCount',$package->AddonCount,['class'=>'form-control','id'=>'productPackage-AddonCount','required'=>'required']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('productPackage-AdministrationFee',Lang::get('labels.administration-fee'),['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::number('AdministrationFee',$package->AdministrationFee,['class'=>'form-control','id'=>'productPackage-AdministrationFee']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('product-AdministrationFeeText',"Administration fee text",['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::textarea('AdministrationFeeText',$package->AdministrationFeeText,['class'=>'form-control','id'=>'product-AdministrationFeeText','rows'=>'2']) !!}
                                </div>
                                <div class="col-sm-3">
                                    <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                </div>
                            </div>


                            <div class="form-group">
                                {!! Form::label('productPackage-AdministrationFeeSplitable',"Can split the administration fee?",['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::checkbox('AdministrationFeeSplitable',$package->AdministrationFeeSplitable,$package->AdministrationFeeSplitable,['class'=>'form-control','id'=>'productPackage-AdministrationFeeSplitable']) !!}
                                </div>
                                <div class="col-sm-3">
                                    <p class="help-block">Is it possible to split the Administration fee into 2 invoices</p>
                                </div>
                            </div>


                            <div class="form-group">
                                {!! Form::label('productPackage-CreationFee',"Starting Fee",['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::number('CreationFee',$package->CreationFee,['class'=>'form-control','id'=>'productPackage-CreationFee']) !!}
                                </div>
                                <div class="col-sm-3">
                                    <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('product-CreationFeeText',"Starting fee text",['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::textarea('CreationFeeText',$package->CreationFeeText,['class'=>'form-control','id'=>'product-CreationFee','rows'=>'2']) !!}
                                </div>
                                <div class="col-sm-3">
                                    <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('productPackage-CreationFeeSplitable',"Can split the starting fee?",['class'=>'col-sm-3 control-label']) !!}
                                <div class="col-sm-6">
                                    {!! Form::checkbox('CreationFeeSplitable',$package->CreationFeeSplitable,$package->CreationFeeSplitable,['class'=>'form-control','id'=>'productPackage-CreationFeeSplitable']) !!}
                                </div>
                                <div class="col-sm-3">
                                    <p class="help-block">Is it possible to split the starting fee into 2 invoices</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <h4>
                                {!! Form::label('product-AddonCount',Lang::get('labels.allowed-products'),['class'=>'col-sm-3 control-label']) !!}
                                </h4>
                                <div class="col-sm-6" style="height:300px; overflow: scroll; overflow-x: hidden; overflow-y: auto;">
                                    @if(isset($products))
                                        @foreach($products as $k=>$val)
                                            <div class="form-group">
                                                <div class="col-sm-6">
                                                    <div class="checkbox block">
                                                        <label for="Product[{{$k}}]">
                                                            <input id="Product[{{$k}}]" value="{{$k}}" name="Product"
                                                                   @if(in_array($k,$allowedProducts)) checked="checked"
                                                                   @endif type="checkbox">
                                                            {{$val}}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        @lang('labels.no-products-allowed')
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="row">
                    <hr class="col-sm-12 col-md-6">
                    </div>
                    <div class=" col-md-4 col-md-offset-1 col-sm-6 col-sm-offset-3">

                    <div class="btn-toolbar">
                        {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop