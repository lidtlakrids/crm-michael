@extends('layout.main')
@section('page-title',Lang::get('labels.upgrade').'&nbsp'.(isset($contract->ProductPackage->Product)?$contract->ProductPackage->Product->Name: (isset($contract->Product))?$contract->Product->Name:""))
@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function () {
            //get packages from the localstorage
//            localStorage.clear();
            if(localStorage.getItem('contractUpgrade') != null && JSON.parse(localStorage.getItem('contractUpgrade')).length > 0 ){

                var packages = JSON.parse(localStorage.getItem('contractUpgrade'));
                packages.forEach(function (pack) {
                    if(pack != null){ addToOrder(pack,true)}
                });
            }

            $('.addOrderPackage').on('click', function (event) {
                setPackage($(event.target).closest('.addOrderPackage').data('product-id'));
                return false;
            });

            /**
             * gets the product information and adds it to the order
             *
             * @param id
             */
            //sets a package to the order
            function setPackage(package_id) {
                //payment terms come as object, so make them an array
                var PaymentTerms= $.map(paymentTerms, function(value, index) {
                    return [value];
                });

                //get the package
                $.ajax({
                    url: api_address + "ProductPackages(" + package_id + ")?$expand=Product",
                    type: 'GET',
                    data: JSON.stringify($("#createOrder").serialize()),
                    success: function (data) {

                        var defaultRunLength =(data.DefaultRunlength != null ? data.DefaultRunlength : 6);
                        var defaultPaymentTerms = (data.DefaultPaymentTerm != null ? data.DefaultPaymentTerm:"Quarerly");
                        var defaultCreationFee = data.CreationFee || 0;
                        var defaultAdministrationFee  = data.AdministrationFee;
                        if(data.Product.ProductType_Id != contractTypeId){
                            var adminSplittable = !data.AdministrationFeeSplitable;
                            var creationSplittable = !data.CreationFeeSplitable;
                        }else{
                            adminSplittable = true;
                            creationSplittable = true;
                        }
                        //set the defaut runlenth in the runlength input
                        $('#runlegth').val(defaultRunLength);

                        //open the default modal
                        var modal = getDefaultModal();

                        modal.find('.modal-title').empty().append(Lang.get('labels.add-package') + " : " + data.Product.Name);
                        modal.find('.modal-body').loadTemplate(
                            base_url + '/templates/orders/orderProductForm.html',
                            {
                                PackageTypeId:data.Product.ProductType_Id,
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
                                DomainLabel:Lang.get('labels.homepage'),
                                CreationFee : defaultCreationFee,
                                AdministrationFee: defaultAdministrationFee,
                                SplitAdminFeeHidden : adminSplittable ? 'hidden':null,
                                SplitCreationFeeHidden : creationSplittable ? 'hidden':null
                            },{
                                overwriteCache:true,
                                success:function(){
                                    //create a countries select
                                    var countrySelect = $('#orderPP-Country');
                                    //countries come from the controller
                                    for( var prop in countries){
                                        countrySelect.append($("<option></option>")
                                                .attr("value",prop)
                                                .text(countries[prop]));
                                    }
                                    $('#orderPP-PaymentTerms').val(defaultPaymentTerms);
                                    // disable some inputs while renewing
//                                        $('#orderPP-Country').prop('disabled',true);
                                    $('#orderPP-RunLength').prop('disabled',true);
                                    $('#orderPP-Domain').prop('disabled',true);
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
//                if(formData.ppDomain !== ""){
//                    if(!validateUrl(addhttp(formData.ppDomain))){
//                        new PNotify({
//                            title: 'Invalid url',
//                            'type': 'error'
//                        });
//                        return false;
//                    }
//                }

                //save this to the local storage
                addToOrder(formData,false);
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
             *@param fromLocalStorage if set to false, will not try to add it to localstorage again
             */
            function addToOrder(data,fromLocalStorage){

                data.ppDomain = addhttp(domain); // comes from php
//                data.Country_Id = country_Id; // comes from php
                if(!fromLocalStorage){
                    var saved = addToLocalStorage(data);
                }
                // save in localstorage

                if((typeof saved != 'undefined' && saved == true) || fromLocalStorage == true){
                    var product = {
                        "NameLabel": Lang.get('labels.product'),
                        "Name": data.ProductName+" - "+Number(data.ProductPrice).format()+" DKK",
                        "PackageId":data.PackageId,
                        ProductPrice:data.ProductPrice,
                        Discount:data.Discount,
                        RunLength:data.RunLength,
                        PaymentTerms:data.PaymentTerms,
                        DeleteLabel:Lang.get('labels.delete'),
                        Country_Id:data.Country_Id,
                        CountryName:countries[data.Country_Id],
                        CountryLabel:Lang.get('labels.country'),
                        PackageTypeId:data.PackageTypeId,
                        CreationFee : data.CreationFee,
                        AdministrationFee:data.AdministrationFee !== '' ? data.AdministrationFee : null,
                        SplitFee: data.CreationFeeSplit ? 'true':'false',
                        SplitAdminFee: data.AdministrationFeeSplit ? 'true':'false'
                    };
                    // don't allow sellers to add Addons
                    if(inRole('Sales')){
                        product.AllowedAddons = "hidden";
                    }
                        product.UrlLabel = Lang.get('labels.homepage');
                        product.ClientUrlLink = data.ppDomain;
                        product.ClientUrl =data.ppDomain;
                        product.Domain = data.ppDomain;
                    $("#orderProductsList").loadTemplate(base_url + "/templates/orders/orderProduct.html",product,
                            {
                                prepend: true,
                                overwriteCache:true,
                                success:function(){
                                    //find the newly created product placeholder
                                    var pp_placeholder = $('#orderProductsList').children().first('.productPlaceholder');

                                    // check if there are any differences between the default values and the typed ones and add special fields, if yes
                                    if(parseFloat(data.OriginalPrice) !== parseFloat(data.ProductPrice)){
                                        pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Pris ændret fra "+Number(data.OriginalPrice).format(true)+" DKK til "+Number(data.ProductPrice).format(true)+ " DKK på "+data.ProductName},{append:true,overwriteCache:true})
                                    }
                                    if(data.DefaultTerms !== data.PaymentTerms){
                                        pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Betalingsbetingelser ændret  fra "+paymentTermsDK[data.DefaultTerms]+" til "+paymentTermsDK[data.PaymentTerms]+ " måneder på "+data.ProductName},{append:true,overwriteCache:true})
                                    }
                                    if(parseFloat(data.DefaultRunLength) > parseFloat(data.RunLength)){
                                        pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Bindingsperiode er ændret  fra "+data.DefaultRunLength+" til "+data.RunLength+ " måneder på "+data.ProductName},{append:true,overwriteCache:true})
                                    }
                                    if(parseFloat(data.CreationFee) < parseFloat(data.DefaultCreationFee)){
                                        pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Oprettelse gebyr ændret fra "+ Number(data.DefaultCreationFee).format(true)+ " DKK til "+ Number(data.CreationFee).format(true)+' DKK på '+data.ProductName},{append:true,overwriteCache:true})
                                    }
                                    if(data.Discount !== ""){
                                        pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Rabat på "+data.Discount+"%"+ " på "+data.ProductName},{append:true,overwriteCache:true})
                                    }
                                    if(parseFloat(data.AdministrationFee) < parseFloat(data.DefaultAdministrationFee)){
                                        pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Administration gebyr ændret fra "+ Number(data.DefaultAdministrationFee).format(true)+ " DKK til "+ Number(data.AdministrationFee).format(true)+' DKK på '+name},{append:true,overwriteCache:true})
                                    }

                                    closeDefaultModal();
                                }
                            })
                }
            }
            /**
             * delete a product from the order
             */
            $('body').on('click','.deleteOrderProduct',function(event){
                //get the product placeholder,
                $(event.target).closest('.orderProductPackage').remove();
                var packageInfo = $(event.target).closest('.orderProductPackage').data();

                //show the packages again
                //also remove the package from the localstorage
                removeFromLocalStorage(packageInfo);

            });

            function removeFromLocalStorage(packageInfo){
                if(localStorage.getItem('contractUpgrade') != null){
                    var packages = JSON.parse(localStorage.getItem('contractUpgrade'));
                    // find package with same country, domain and id and remove it
                    packages.forEach(function (a,index) {
                        if(a !== null){
                            if(Number(a.PackageId) == Number(packageInfo.packageId) && Number(a.Country_Id) == Number(packageInfo.countryId) && a.ppDomain == packageInfo.domain){
                                packages.splice(index,1)
                            }
                        }
                    });
                    //if there are any packages left
                    if(packages.length >= 0){
                        localStorage.setItem('contractUpgrade',JSON.stringify(packages));
                    }
                }
            }

            /**
             * Put Addons in package
             */
            $('body').on('click','.addAddonToPackage',function(event){
                //get the package Id
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

                        modal.find('.modal-title').empty().append(Lang.get('labels.add-ons-count')+" : "+data.AddonCount);
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

            /**
             * saves the package in local storage and also checks if it exists already
             * @param productPackage
             */
            function  addToLocalStorage(productPackage){

                //look for existing aray of packages
                if(localStorage.getItem('contractUpgrade') == null){
                    var contractUpgrade = [];
                    contractUpgrade.push(productPackage);
                    localStorage.setItem('contractUpgrade',JSON.stringify(contractUpgrade));
                    return true;
                }else{
                    //check if the package is not in the order already with the same country and domain
                    var contractUpgrade = JSON.parse(localStorage.getItem('contractUpgrade'));
                    var saved = true;
                    contractUpgrade.forEach(function(a){
                        if(Number(a.PackageId) == Number(productPackage.PackageId) && a.ppDomain == productPackage.ppDomain && Number(a.Country_Id) == Number(productPackage.Country_Id)){
                            new PNotify({
                                title: 'Same package for the same country and domain exists.',
                                'type': 'error'
                            });
                            saved = false;
                        }
                    });
                    if(saved){
                        //add the package to the existing array
                        contractUpgrade.push(productPackage);
                        localStorage.setItem('contractUpgrade',JSON.stringify(contractUpgrade));
                        return saved;
                    }else{ return saved}
                }
            }

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

            $(".submitUpgrade").on("click", function (event) {
                event.preventDefault();
                var btn = $(event.target);
                btn.prop('disabled',true);

                //initialize the data sent
                var formData = {
                    "User": {
                        "Id": $('#upgrade-User_Id').val()
                    },
                    "Domain": addhttp($('input[name=Domain]').val()),
                    "ClientAlias_Id": $('input[name=ClientAlias_Id]').val()
                };

                // get all fields and transform them in a way the backend can understand
                var fields = $('input.orderField').toArray();
                var orderFieldValues = $.map(fields, function(value, index) {

                    return {value:$(value).val(),OrderField:{Id:$(value).prop('name')}}
                });
                if (orderFieldValues.length > 0) {
                    formData.OrderFieldValue = orderFieldValues;
                }

                // get the product packages
                var packages = $('div.orderProductPackage').toArray();
                if(packages.length == 0){
                    new PNotify({
                        title: 'Please, select a Product',
                        'type': 'error'
                    });
                    return false;
                }else{
                    var productPackages = $.map(packages, function(value) {

                        var pack = {
                            ProductPackage:
                                {
                                    Id:$(value).data('package-id')
                                },
                            ProductPrice:$(value).data('product-price'),
                            PaymentTerms:$(value).data('payment-terms'),
                            Country_Id:country_Id,
                            Domain: addhttp(domain), // comes from php , same as contract domain
                            CreationFee:$(value).data('creation-fee'),
                            CreationFeeSplit : $(value).data('split-fee'),
                            AdministrationFeeSplit : $(value).data('split-admin-fee')
                        };

                        if($(value).data('administration-fee')){
                            pack.AdministrationFee  = $(value).data('administration-fee');
                        }

                        if($(value).data('discount') != ""){
                            pack.Discount = $(value).data('discount');
                        }
                        //find the container for the addons
                        var addons = $(value).find('.PackageAddon').toArray();

                        if(addons.length > 0){
                            var packageAddons = $.map(addons,function(val){
                                return {Product_Id:$(val).val()};
                            });
                            pack.Products = packageAddons;
                        }
                        //add the old contract Id to the package, so we know
                        //it's an upgrade. The var comes from php directly
                        if($(value).data('package-type-id') == contractTypeId){
                            pack.Contract_Id = contractId;
                        }

                        // determine the runlength depenind on the checkbox
                          if($('#align').prop('checked')){
                            pack.RunLength = 0;
                            }else if($('#extend').prop('checked')){
                              if($('#runlegth').val() == ""|| $('#runlegth').val() < 3) {
                                 new PNotify({ title:Lang.get('messages.min-runlength-3-months'),type:"error"});
                                  return false;
                              }else{
                                  pack.RunLength = $('#runlegth').val()
                              }
                          }

                        return pack;
                    });
                    //add the packages to the order
                    formData.OrderProductPackage = productPackages;
                }

                //send the request
                $.ajax({
                    method: 'POST',
                    url: api_address + 'Orders',
                    data: JSON.stringify(formData),
                    success: function (data) {
                        window.location = base_url + '/orders/show/' + data.Id;
                        localStorage.clear();
                    },
                    error: function (err) {
                        btn.prop('disabled',false);
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });

            $('#align').on('change',function(event){
                var alignCheckbox = $(event.target);
                var runlengthBox = $('#runlegth');
                var extendCheckbox = $('#extend');

                if(!alignCheckbox.prop('checked')){
                    runlengthBox.prop('disabled',false);
                    extendCheckbox.prop('checked',true);
                }else{
                    runlengthBox.prop('disabled',true);
                    extendCheckbox.prop('checked',false);
                }
            });

            $('#extend').on('change',function(event){
                var extendCheckbox = $(event.target);
                var runlengthBox = $('#runlegth');
                var alligncheckbox = $('#align');

                if(extendCheckbox.prop('checked')){
                    runlengthBox.prop('disabled',false);
                    alligncheckbox.prop('checked',false);
                    alligncheckbox.prop('disabled',false);
                }else{
                    runlengthBox.prop('disabled',true);
                    alligncheckbox.prop('checked',true);
                    alligncheckbox.prop('disabled',true);
                }
            });
        });
    </script>
    <style>
        #orderProductsList .dl-horizontal{
            border-bottom: solid 1px #ccc;
            padding:5px;
        }
    </style>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-order">
                <div class="panel-heading">
                    <h4><i class="fa fa-level-up"></i> @lang('labels.upgrade-contract') </h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3 productPackages">
                            <h4>@lang('labels.select-new-package')</h4>
                            @if(isset($packages))
                                @if(empty($packages))
                                    @lang('messages.no-packages-of-same-type')
                                @else
                                    @foreach($packages[$productType] as $package)
                                        @if($package->Product->Id == $contract->Product_Id)
                                            <?php continue; ?>
                                        @endif
                                        <a title="@lang('labels.add-to-order')" class="list-group-item addOrderPackage" data-product-id="{{$package->Id}}" href="#">
                                            {{$package->Product->Name}}
                                            <span class="pull-right"> {{formatMoney($package->Product->SalePrice)." ".config('gcm.money-code-short')}}</span>
                                        </a>
                                    @endforeach
                                    <?php unset($packages[$productType]) ?>
                                @endif
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h4>@lang('labels.extra-products')</h4>
                            <div id="accordioninpanel" class="accordion-group">
                                <?php $i = 1;?>
                                @foreach($packages as $type=>$packs)
                                    <div class="accordion-item">
                                        <a class="accordion-title collapsed" data-toggle="collapse" data-parent="#accordioninpanel" href="#collapsein{{$i}}"><h4>{{$type}}</h4></a>
                                        <div id="collapsein{{$i}}" class="collapse" style="height: 0px;">
                                            <div class="accordion-body">
                                                <div class="list-group">
                                                    @foreach($packs as $pack)
                                                        <a title="@lang('labels.add-to-order')" class="list-group-item addOrderPackage" data-product-id="{{$pack->Id}}" href="#">
                                                            {{$pack->Product->Name}}
                                                            <span class="pull-right"> {{formatMoney($pack->Product->SalePrice)." kr."}}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $i++; ?>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4 extendLogic">
                            <h4>@lang('labels.products-in-cart')</h4>
                            <div id="orderProductsList"></div>
                            <div>
                                <div class="form-horizontal">
                                    <div class="form-group">
                                        <div class="col-sm-6">
                                            <div class="checkbox block">
                                                <label>
                                                    <input id="align" name="align" type="checkbox">
                                                    @lang('labels.align')
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-6">
                                            <div class="checkbox block">
                                                <label>
                                                    <input id="extend" name="extend" type="checkbox" checked="checked">
                                                    @lang('labels.extend')
                                                </label>
                                                <input type="number" min=3 class="form-control col-md-3" id="runlegth" value="6" name="RunLength">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-3">
                                                {!! Form::label('User_Id',Lang::get('labels.seller'),['class'=>'control-label']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            {!! Form::select('User_Id',$users,Auth::user()->externalId,['class'=>'form-control','id'=>"upgrade-User_Id",'required'=>'required']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::hidden('Domain',$contract->Domain) !!}
                            {!! Form::hidden('ClientAlias_Id',$contract->ClientAlias->Id) !!}
                            <div class="btn-toolbar" style="margin-top:10px;">
                                {!! Form::submit(Lang::get('labels.upgrade'),['class'=>'btn btn-primary form-control submitUpgrade']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop