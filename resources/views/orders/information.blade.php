@extends('layout.main')
@section('page-title',Lang::get('labels.information'))

@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function () {

            $('#loadLastInfoScheme').click(function (event) {
                event.preventDefault();
                var button = $(event.target);
                button.prop('disabled',true);
                // find the last information scheme for the same product type
                var typeId = $('#information-typeId').val();
                var clientAliasId = $('#information-clientAliasId').val();

                $.get(api_address+'/InformationSchemes?$expand=FieldValue($expand=OrderField($expand=OrderFieldOption))&$filter=Type_Id eq '+typeId+" and Contract/ClientAlias_Id eq "+clientAliasId+"&$orderby=Id desc&$top=1")
                    .success(function (data) {
                        if(data.value.length > 0){
                            var values = data.value[0].FieldValue;
                            $.each(values,function (a,b) {
                                // find the field with same id
                                if(b.OrderField.OrderFieldType == "Radio"){
                                    $('input:radio[name="'+b.OrderField_Id+'"][value="'+b.value+'"]').prop('checked',true);
                                }else if(b.OrderField.OrderFieldType== "CheckBox"){
                                    $('input:checkbox[name="'+b.OrderField_Id+'"][value="'+b.value+'"]').prop('checked',true);
                                }
                                $('#field\\['+b.OrderField_Id+'\\]').val(b.value);
                            })
                        }else{
                            new PNotify({title:"The client does not have other information schemes for this type of product/package"});
                        }
                    });
                button.prop('disabled',false);
            });

            $('.addOrderPackage').on('click', function (event) {
                setPackage($(event.target).closest('.addOrderPackage').data('product-id'));
                return false;
            });

            /**
             * gets the product information and adds it to the order
             *
             * @param id
             */
            function setProduct(id) {
                $.ajax({
                    url: api_address + "Products(" + id + ")",
                    type: 'GET',
                    success: function (data) {
                        $("#orderProductsList").loadTemplate(base_url + "/templates/orderProduct.html",
                                {
                                    "label-Name": Lang.get('labels.name'),
                                    "Name": data.Name,
                                    "label-Price": Lang.get('lables.sale-price'),
                                    "Price": data.SalePrice,
                                    "label-Runlength": "Runleght",
                                    "Runlegth": 3,
                                    "label-Discount": Lang.get('lables.discount'),
                                    "Discount": 0
                                },
                                {
                                    prepend: true
                                }
                        )
                    }
                });
            }

            //sets a package to the order
            function setPackage(package_id) {

                var defaultRunLength = 6; //TODO get from settings or product info
                var defaultPaymentTerms = "Quarterly"; //TODO get from settings or product info

                //payment terms come as object, so make them an array
                var PaymentTerms= $.map(paymentTerms, function(value, index) {
                    return [value];
                });

                //get the package
                $.ajax({
                    url: api_address + "ProductPackages(" + package_id + ")?$expand=Product",
                    type: 'GET',
                    data: JSON.stringify($("#createOrder").serialize()),
                    async: false,
                    success: function (data) {
                        //open the default modal
                        var modal = getDefaultModal();

                        modal.find('.modal-header').append(Lang.get('labels.add-package') + " : " + data.Product.Name);
                        modal.find('.modal-body').loadTemplate(
                                base_url + '/templates/orders/orderProductForm.html',
                                {
                                    PriceLabel:Lang.get('labels.sale-price'),
                                    ProductPrice:data.Product.SalePrice,
                                    RunLengthLabel:Lang.get('labels.runlength'),
                                    RunLength:defaultRunLength,
                                    PaymentTerms:PaymentTerms,
                                    PaymentTermsLabel:Lang.get('labels.payment-terms'),
                                    DiscountLabel:Lang.get('labels.discount'),
                                    SaveLabel:Lang.get('labels.save'),
                                    DefaultTerms:defaultPaymentTerms,
                                    ProductName:data.Product.Name,
                                    PackageId:data.Id,
                                    CountryLabel:Lang.get('labels.country'),
                                    DomainLabel:Lang.get('labels.homepage')
                                },{
                                    overwriteCache:true,
                                    success:function(){
                                        var countrySelect = $('#orderPP-Country');
                                        for( var prop in countries){
                                            countrySelect.append($("<option></option>")
                                                    .attr("value",prop)
                                                    .text(countries[prop]));
                                        }
                                    }
                                });
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            }

            /// add a package to the order. if the fields were changed, add special fields to it
            $('body').on('submit','#orderProductPackage',function(event){
                event.preventDefault();
                var formData = convertSerializedArrayToHash($(this).serializeArray());
                addToOrder(formData);
            });

            /// add an addon  to the package.
            $('body').on('submit','#orderProductPackageAddons',function(event){
                //get the placeholder for the products
                var addonsPlaceholder = $('.AppendAddonsTo').siblings('.PackageAddons');
                addonsPlaceholder.empty();
                event.preventDefault();

                var addons = $(this).find('input:checkbox:checked').toArray();

                var products = $.map(addons,function(value,index){
                    return {ProductId : $(value).val(),ProductName:$(value).parent().text().trim(),Disabled:"return false;",Checked:"checked"}
                });
                addonsPlaceholder.loadTemplate(base_url+"/templates/orders/addonSelect.html",products,{prepend:true,overwriteCache:true});

                $('.AppendAddonsTo').removeClass('AppendAddonsTo');
                closeDefaultModal();

            });

            /**
             * adds the package or product ot the order
             * renders a element which will be used when submitting the order
             * @param data
             */
            function addToOrder(data){
                $("#orderProductsList").loadTemplate(base_url + "/templates/orders/orderProduct.html",
                    {
                        "NameLabel": Lang.get('labels.product'),
                        "Name": data.ProductName+" - "+Number(data.ProductPrice).format()+" DKK",
                        "PackageId":data.PackageId,
                        ProductPrice:data.ProductPrice,
                        Discount:data.Discount,
                        RunLength:data.RunLength,
                        PaymentTerms:data.PaymentTerms,
                        DeleteLabel:Lang.get('labels.delete'),
                        Domain:data.ppDomain,
                        Country_Id:data.Country_Id,
                        CountryName:countries[data.Country_Id],
                        CountryLabel:Lang.get('labels.country')
                    },
                    {
                        prepend: true,
                        overwriteCache:true,
                        success:function(){
                            //find the newly created product placeholder
                            var pp_placeholder = $('#orderProductsList:first-child').children().first('.productPlaceholder');

                            // check if there are any differences between the default values and the typed ones and add special fields, if yes
                            if(data.OriginalPrice !== data.ProductPrice){
                                pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Price changed from "+data.OriginalPrice+" to "+data.ProductPrice},{append:true,overwriteCache:true})
                            }
                            if(data.DefaultRunLength !== data.RunLength){
                                pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Runlegth changed from "+data.DefaultRunLength+" to "+data.RunLength},{append:true,overwriteCache:true})
                            }
                            if(data.DefaultTerms !== data.PaymentTerms){
                                pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Payment terms changed from "+data.DefaultTerms+" to "+data.PaymentTerms},{append:true,overwriteCache:true})
                            }
                            if(data.Discount !== ""){
                                pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Discount applied "+data.Discount+"%"},{append:true,overwriteCache:true})
                            }

                            closeDefaultModal();
                        }
                    }
                )
            }

            /**
             * delete a product from the order
             */
            $('body').on('click','.deleteOrderProduct',function(event){
                //get the product placeholder,
                $(event.target).closest('.orderProductPackage').remove();

            });

            /**
             * Put Addons in package
             */
            $('body').on('click','.addAddonToPackage',function(event){
                //get the package Id
                event.preventDefault();
                var packageId = $(event.target).closest('.orderProductPackage').data('package-id');
                $(event.target).addClass('AppendAddonsTo');
                // get the allowed addons
                $.ajax({
                    method: 'GET',
                    url: api_address + 'ProductPackages('+packageId+')?$select=AddonCount&$expand=Products($expand=Product($select=Name,Id))',
                    success: function (data) {

                        //make array with all allowed products
                        var allowedProducts = $.map(data.Products,function(value,index){
                            return {ProductId:value.Product.Id,ProductName:value.Product.Name}
                        });

                        //open the default modal
                        var modal = getDefaultModal();

                        modal.find('.modal-title').text(Lang.get('labels.add-ons-count')+" : "+data.AddonCount);
                        modal.find('.modal-body').loadTemplate(
                                base_url + '/templates/orders/addonsToPackageForm.html',
                                {
                                    MaxAddons:data.AddonCount,
                                    SaveLabel:Lang.get('labels.save')
                                },{overwriteCache:true,
                                    success:function(){
                                        $('#orderProductPackageAddons').loadTemplate(base_url+"/templates/orders/addonSelect.html",allowedProducts,{prepend:true}
                                        )
                                    }
                                });
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });

            $('body').on('change','.PackageAddon', function(evt) {
                var limit = $(evt.target).closest('#orderProductPackageAddons').find('.MaxAddons').val();
                if($(this).closest('#orderProductPackageAddons').find('.PackageAddon:checked').length > limit) {
                    this.checked = false;
                    new PNotify({
                        title: 'Max number of Addons is ' + limit,
                        'type': 'error'
                    });
                }
            });

            var form = $('#InformationSchemeForm');
            var btn = $(form).find(':submit');
            form.on("submit", function (event) {
                event.preventDefault();

                //remove error classes (from previous submit)
                clearErrors();
                //  validate if required checkboxes are selected
                var required = $('.required').toArray();

                if(required.length > 0){
                    var grouped = [];
                    //group them by names
                    required.forEach(function(elem,index){
                        if(!isInArray($(elem).prop('name'),grouped)){
                            grouped.push($(elem).prop('name'))
                        }
                    });

                    if(grouped.length > 0){
                        var error = false;
                        //go through the grouped inputs and check if any is selected
                        grouped.forEach(function(id,index){
                            var selected = $('input[name='+id+']:checked').toArray();

                            if(selected.length== 0){ // if the input is empty, we need to show error
                                error = true;

                                //find the label for that field
                                var label = $("label[for='field["+id+"]']");
                                    //add error classes on all inputs with error
                                    label.closest('.form-group').addClass('has-error');
                            }
                        });
                        if(error) {
                            //set focus on the first element with error
                            $('html, body').animate({
                                scrollTop: $("label[for='field[" + grouped[0] + "]']").offset().top
                            }, 2000);
                            btn.prop('disabled',false);
                            return false;
                        }
                    }
                }
                var formData= {};
                // get all fields and transform them in a way the backend can understand
                var fields = $('.orderField').serializeArray();
                var orderFieldValues = $.map(fields, function(value, index) {
                    if(value.value != ""){
                        return {value:value.value,OrderField_Id:value.name}
                    }
                });
                var hasLanding = $('.adslanding');
                if(hasLanding.length > 0){
                    $.each(hasLanding,function (index,element) {


                    var landingId = $(element).find('input[name="AdsLandingId"]').val();
                    var links = $(element).find('.adgroupUrls').serializeArray();
                    var names = $(element).find('.adgroupNames').serializeArray();
                    if(links.length > 0){
                        var values ='';
                        $.each(links,function (index,value) {
                            if(value.value !== ""){
                                values += names[index].value + ' - '+value.value +"\n"
                            }
                        });
                        if(values !=''){
                            orderFieldValues.push({value : values,OrderField_Id :landingId});
                        }
                    }
                    })
                }

                var hasGoals = $('.campaignGoal');
                if(hasGoals.length > 0){
                    $.each(hasGoals,function (index,element) {

                        var goalID = $(element).find('input[name="CampaignGoal"]').val();
                        var values = $(element).find('.campGoals').filter(function() {
                            return this.value;
                        }).serializeJSON();
                        orderFieldValues.push({value : JSON.stringify(values),OrderField_Id :goalID});

//                        var names = $(element).find('.adgroupNames').serializeArray();
//                        if(links.length > 0){
//                            var values ='';
//                            $.each(links,function (index,value) {
//                                if(value.value != ''){
//                                    values += names[index].value + ' - '+value.value +"\n"
//                                }
//                            });
//                            orderFieldValues.push({value : values,OrderField_Id :landingId});
//                        }
                    })
                }

                if (orderFieldValues.length > 0) {
                    formData.FieldValue = orderFieldValues;
                }

                //get the addons for the contract
                //find the container for the addons
                var addons = $('.PackageAddons').find('.PackageAddon').toArray();

                if(addons.length > 0){
                    var packageAddons = $.map(addons,function(val){
                        return {Product_Id:$(val).val()};
                    });
                    formData.Products = packageAddons;
                }

                //add the user to the information scheme
                formData.User_Id = $('#user-Id').val();

                //add the type to the information scheme
                formData.Type_Id = $('#information-typeId').val();
                btn.prop('disabled',true);

                //add the contract id
                formData.Contract_Id = $('#contractId').val();
                if(typeof formData.Products == 'undefined' && !inRole('Sales')){
                    bootbox.confirm("Send information scheme without addons to the package. Are you sure..?", function(result)
                    {
                        if(result){
                            sendForm(formData)
                        }else{
                            btn.prop('disabled',false);
                        }
                    });
                }else{
                    sendForm(formData);
                }
            });
            //sends the data from the form
            function sendForm(data){
                $.ajax({
                    method: 'POST',
                    url: api_address + 'InformationSchemes',
                    data: JSON.stringify(data),
                    success: function (data) {
                        window.location= base_url+'/information/show/'+data.Id;
                    },
                    error: function (error) {
                        btn.prop('disabled',false);
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            }
            if(adwordsId){
                $('.adwordsIdInput').val(adwordsId);
            }


        $('.AddAdGroup').on('click',function (event) {
            event.preventDefault();
            var placeholder = $(event.target).closest('.adslanding');
            $('<div class="form-group">' +
            '<div class="col-md-6"> ' +
            '<input type="text" name="AdgroupName" placeholder="AdGroup Name" class="form-control adgroupNames"> ' +
            '</div> ' +
            '<div class="col-md-6"> ' +
            '<input type="text" name="AdgroupUrl" placeholder="url" class="form-control adgroupUrls"> ' +
            '</div></div>').insertBefore(placeholder.find('.AddAdGroup'));
        })

        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-order">
                <div class="panel-heading">
                    <h4><i class="fa fa-info-circle"></i> @lang('labels.information-scheme') </h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                            <button id="loadLastInfoScheme">@lang('labels.load-last-info-scheme')</button>
                    </div>
                    <div class="row">
                        <div class="form-horizontal">
                            @if(isset($contract))
                            {!! Form::open(['id'=>'InformationSchemeForm']) !!}
                            {!! Form::hidden('Contract_Id',$contract->Id,['id'=>'contractId']) !!}
                            {!! Form::hidden('Type_Id',$contract->Product->ProductType->Id,['id'=>'information-typeId']) !!}
                            {!! Form::hidden('ClientAlias_Id',$contract->ClientAlias_Id,['id'=>'information-clientAliasId']) !!}

                            <div class="col-md-6">
                                <div class="row">
                                    <h4>Order Information</h4>
                                    <hr />
                                    @if(isset($orderFields))
                                        @foreach($orderFields as $field)
                                            <div class="form-group">
                                                {!! $field['label']  or "---"!!}
                                                <div class="col-sm-12">
                                                    {!! $field['element'] or "---"!!}
                                                </div>

                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5 col-md-offset-1">
                                <div class="row">
                                    <div class="col-md-10">

                                        <div class="row">
                                            <h4>@lang('labels.client-info')</h4>
                                            <hr />
                                            <dl class="dl-horizontal-row-2">
                                                <dt>@lang('labels.name')</dt>
                                                <dd>
                                                    @if($contract->ClientAlias != null)
                                                        <a href="{{url('clientAlias/show',$contract->ClientAlias_Id)}}">{{$contract->ClientAlias->Name or "-"}}</a>
                                                    @endif
                                                </dd>
                                                @if($contract->Product)
                                                    <dt>@lang('labels.product')</dt>
                                                    <dd>{{$contract->Product->Name or "-"}}</dd>
                                                @endif
                                            </dl>
                                        </div>
                                        @if(isset($order) && $order != null)
                                            <div class="row" id="orderProductsList">
                                                <h4>@lang('labels.package-info')</h4>
                                                @foreach($order->OrderProductPackage as $package)
                                                    @if($package->ProductPackage_Id !== $contract->ProductPackage_Id || $contract->Country_Id != $package->Country_Id) @continue @endif
                                                    <div class="row orderProductPackage"
                                                         data-package-id="{{$package->ProductPackage_Id}}" data-product-price="{{($package->ProductPrice > 0)? $package->ProductPrice : $product->SalePrice}}"
                                                         data-discount="{{$package->Discount}}" data-runlength="{{$package->RunLength}}" data-payment-terms="{{$package->PaymentTerms}}"
                                                         data-domain="{{$package->Domain}}" data-country-id="{{$package->Contract_Id}}">

                                                        <div class="col-md-12 productPlaceholder">
                                                            <dl class="dl-horizontal-row-2">
                                                                <dt>@lang('labels.name')</dt>
                                                                <dd>{{$contract->Product->Name}}</dd>
                                                                <dt>@lang('labels.country')</dt>
                                                                <dd>{{$package->Country->CountryCode or "--"}}</dd>
                                                                <!-- if different form main url -->
                                                                <dt>@lang('labels.homepage')</dt>
                                                                <dd><a target="_blank" href="{{addHttp($package->Domain)}}">{{$package->Domain}}</a></dd>
                                                                <!-- end if -->
                                                            </dl>
                                                            <hr/>
                                                        </div>

                                                        <div class="col-md-12 ">
                                                            <div class="form-horizontal PackageAddons">
                                                                @foreach($package->Products as $p)
                                                                    <div class="form-group">
                                                                        <div class="col-sm-12">
                                                                            <div class="checkbox block">
                                                                                <label>
                                                                                    <input class="PackageAddon" type="checkbox" name="PackageAddon" value="{{$p->Product->Id}}" onclick="return false;" checked="checked">
                                                                                    {{$p->Product->Name or "--"}}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <a href="#" class="btn btn-sm btn-green addAddonToPackage pull-right @if(inRoleNeutral('Sales')) hidden @endif">@lang('labels.add-ons')</a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="btn-toolbar" style="margin-top:10px;">
                                        {!! Form::submit(Lang::get('labels.send'),['class'=> 'btn btn-primary form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop



