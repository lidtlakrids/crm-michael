@extends('layout.main')
@section('page-title',Lang::get('labels.create-order').'&nbsp'.(isset($order)?$order->FormName: ""))
@section('styles')
@stop

@section('scripts')
<script>
        $(document).ready(function () {
            //get packages from the localstorage
//            localStorage.clear();
            if(localStorage.getItem('orderPP') != null){
                var packages = JSON.parse(localStorage.getItem('orderPP'));
                packages.forEach(function (pack) {
                   if(pack != null){ addToOrder(pack,true)}
                })
            }

            var ciInput = $('#Client-CINumber');
            ciInput.on('keyup',function(){
                checkCI();
            });
            function checkCI(){
                if(ciInput.val().trim().length>=6){
                    // try to find a client with that ci numnber
                    setTimeout(function(){
                        $.ajax({
                            url: api_address+"Clients?$expand=ClientAlias($select=Name,Homepage,Id)&$filter=CINumber eq '" + ciInput.val().trim() + "'",
                            type: 'GET',
                            success: function (data) {

                                if(data.value.length>0){
                                    //show alias in this ci number
                                    var clients = $.map(data.value[0].ClientAlias,function(item,value){
                                        return {ClientAliasName:item.Name,Homepage:item.Homepage,Id:item.Id}
                                    });

                                    $('#Client-Id').val(data.value[0].Id);
                                    clients.push({ClientAliasName:Lang.get('labels.new'),Homepage:"",Id:0});
                                    var modal = getDefaultModal();
                                    modal.find('.modal-title').empty().append(Lang.get('labels.clients-with-same-ci'));
                                    modal.find('.modal-body').append('<div class="form-horizontal"></div>');
                                    modal.find('.modal-body > .form-horizontal').loadTemplate(base_url+'/templates/orders/clientAliasSelect.html',clients);
                                }else{
                                    //remove the id of the client, just in case
                                    $('#Client-Id').val("");
                                }
                            },
                            error: handleError,
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });

                    },2000)
                }
            }

            $('.createNewClient').on('click',function(event){
                event.preventDefault();
                //if they press new , clear the old alias Id
                unsetAlias();
                var placeholder = $('.clientInformationPlaceholder');
                placeholder.toggleClass('hidden');
                $(event.target).text((placeholder.hasClass('hidden')?Lang.get('labels.new'):Lang.get('labels.search')));
                $('.search-classic').toggleClass('hidden');

                // don't validate fields that are not visible
                $('.required').prop('required', function(){
                    return placeholder.is(':visible');
                });


            });

            // don't validate fields that are not visible
            $('.required').prop('required', function(){
                return  $(this).is(':visible');
            });


            $("#searchInput").autocomplete({
                source: function (request, response) {
                    var str = request.term;
                    $.get(api_address + "ClientAlias?$filter=indexof(tolower(Name), '" + str + "') ge 0 or indexof(tolower(Homepage), '" + str + "') ge 0", {},
                            function (data) {
                                response($.map(data.value, function (el) {
                                            return {id: el.Id, label: el.Name + "  " + el.Homepage, value: el.Name,homepage:el.Homepage};
                                        })
                                );
                            });
                },
                minLength: 2,
                select: function (event, ui) {
                    setAliasId(ui.item.id, ui.item.value,ui.item.homepage)
                }
            });

            $("#taxonomySearch").autocomplete({
                source: function (request, response) {
                    var str = request.term;
                    $.get(api_address + "Taxonomies?$filter=contains(tolower(Name),'" + str + "')", {},
                            function (data) {
                                response($.map(data.value, function (el) {
                                            return {id: el.Id, label: el.Name};
                                        })
                                );
                            });
                },
                minLength: 2,
                select: function (event, ui) {
                    setTaxonomyId(ui)
                }
            });

            function setTaxonomyId(data) {
                $('input[name=ClientAlias-Taxonomy_Id]').val(data.item.id);
            }
//            $("#productSearch").autocomplete({
//                source: function (request, response) {
//                    var str = request.term;
//                    $.get(api_address + "Products?$filter=indexof(tolower(Name), '" + str + "') ge 0 and Active eq true", {},
//                            function (data) {
//                                response($.map(data.value, function (el) {
//                                    return {id: el.Id, label: el.Name + "  " + el.SalePrice + " kr."};
//                                }));
//                            });
//                },
//                minLength: 2,
//                select: function (event, ui) {
//                    setProduct(ui.item.id)
//                }
//            });
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

                        var defaultRunLength    = (data.DefaultRunlength != null ? data.DefaultRunlength : 6);
                        var defaultPaymentTerms = (data.DefaultPaymentTerm != null ? data.DefaultPaymentTerm:"Quarerly");
                        var defaultCreationFee  = data.CreationFee || null;
                        var defaultAdministrationFee  = data.AdministrationFee|| null;
                        //open the default moda
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
                                CreationFee: defaultCreationFee,
                                AdministrationFee: defaultAdministrationFee,
                                SplitCreationFeeHidden : data.CreationFeeSplitable ? null : "hidden",
                                SplitAdminFeeHidden : data.AdministrationFeeSplitable ? null :"hidden"
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
                                    //set the default payment terms to quarterly
                                    $('#orderPP-PaymentTerms').val(defaultPaymentTerms);
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
                if(formData.ppDomain !== ""){
                    if(!validateUrl(addhttp(formData.ppDomain))){
                        new PNotify({
                            title: 'Invalid url',
                            'type': 'error'
                        });
                        return false;
                    }
                }
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
             * renders a element which will be used when submiting the order
             * @param data
             *@param fromLocalStorage if set to false, will not try to add it to localstorage again
            */
            function addToOrder(data,fromLocalStorage){

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
                    if(inRoleNeutral('Sales')){
                        product.AllowedAddons = "hidden";
                    }
                    if(data.ppDomain != $('#Domain').val() && data.ppDomain !== "" ){
                        var domain = addhttp(data.ppDomain);
                        product.UrlLabel = Lang.get('labels.homepage');
                        product.ClientUrlLink = domain;
                        product.ClientUrl =domain;
                        product.Domain = domain;
                    }else{
                        product.Domain = (data.ppDomain !== "")? addhttp(data.ppDomain):data.ppDomain;
                    }
                    $("#orderProductsList").loadTemplate(base_url + "/templates/orders/orderProduct.html",product,
                        {
                            prepend: true,
                            overwriteCache:true,
                            success:function(){
                                //find the newly created product placeholder
                                var pp_placeholder = $('#orderProductsList:first-child').children().first('.productPlaceholder');

                                // remove the numbers from the product name. Clients should not see if they are in 1 ,2 or 3 package number
                                var name = data.ProductName.replace(new RegExp("[0-9]", "g"), "");

                                // check if there are any differences between the default values and the typed ones and add special fields, if yes
                                if(parseFloat(data.OriginalPrice) > parseFloat(data.ProductPrice)){
                                    pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Pris ændret fra "+Number(data.OriginalPrice).format(true)+" DKK til "+ Number(data.ProductPrice).format(true)+ " DKK på "+name},{append:true,overwriteCache:true})
                                }
                                if(parseFloat(data.DefaultRunLength) > parseFloat(data.RunLength)){
                                    pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Bindingsperiode er ændret  fra "+data.DefaultRunLength+" til "+data.RunLength+ " måneder på "+name},{append:true,overwriteCache:true})
                                }
                                if(data.DefaultTerms !== data.PaymentTerms){
                                    pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Betalingsbetingelser ændret fra "+paymentTermsDK[data.DefaultTerms]+" til "+paymentTermsDK[data.PaymentTerms]+ " på "+name},{append:true,overwriteCache:true})
                                }
                                if(data.Discount !== ""){
                                    pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Rabat på "+data.Discount+"%"+ " på "+data.ProductName},{append:true,overwriteCache:true})
                                }
                                if(parseFloat(data.CreationFee) < parseFloat(data.DefaultCreationFee)){
                                    pp_placeholder.loadTemplate(base_url+'/templates/orders/specialField.html',{FieldText:"Oprettelse gebyr ændret fra "+ Number(data.DefaultCreationFee).format(true)+ " DKK til "+ Number(data.CreationFee).format(true)+' DKK på '+name},{append:true,overwriteCache:true})
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
                //also remove the package from the localstorage
                removeFromLocalStorage(packageInfo);
            });

            function removeFromLocalStorage(packageInfo){
                if(localStorage.getItem('orderPP') != null){
                    var packages = JSON.parse(localStorage.getItem('orderPP'));
                    // find package with same country, domain and id and remove it
                    packages.forEach(function (a,index) {
                        if(a !== null){
                            if(Number(a.PackageId) == Number(packageInfo.packageId) && Number(a.Country_Id) == Number(packageInfo.countryId) && addhttp(a.ppDomain) == addhttp(packageInfo.domain)){
                                packages.splice(index,1)
                            }
                        }
                    });
                    //if there are any packages left
                    if(packages.length >= 0){
                        localStorage.setItem('orderPP',JSON.stringify(packages));
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
                if(localStorage.getItem('orderPP') == null){
                    var orderPP = [];
                        orderPP.push(productPackage);
                    localStorage.setItem('orderPP',JSON.stringify(orderPP));
                    return true;
                }else{
                    //check if the package is not in the order already with the same country and domain
                    var orderPP = JSON.parse(localStorage.getItem('orderPP'));
                        var saved = true;
                        orderPP.forEach(function(a){
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
                    orderPP.push(productPackage);
                    localStorage.setItem('orderPP',JSON.stringify(orderPP));
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

            $("#createOrder").on("submit", function (event) {
                event.preventDefault();

                // disable the submit button
                $('#orderSubmitButton').prop('disabled','disabled');

                var aliasId = $('#ClientAlias_Id').val();

                if (Number(aliasId) == 0 || aliasId == "") {
                    //check if we have information for new client
                    var clientData = convertSerializedArrayToHash($(this).serializeArray());
                    if(clientData['ClientAlias-Name'] != ""){
                        //create an alias object
                         var ClientAlias = groupFormFields(clientData);
                            //if we have a client with same ci number, we just send the id of it
                            if(typeof ClientAlias.Client.Id !== 'undefined'){
                                delete(ClientAlias.Client.CINumber);
                            }

                        var ca = ClientAlias.ClientAlias;
                        ca.Homepage = addhttp($('#Domain').val());
                        ca.Client= ClientAlias.Client;
                        ca.Contact = [ClientAlias.Contact];
                        ca.Contact[0].MainContact = true;
                        ca.Subscribed = ca.Subscribed ? true:false;
                        aliasId = 'undefined';
                    }
                    else{
                    var notice = new PNotify({
                        title: 'Please, select a Client',
                        'type': 'error'
                    });
                    notice.get().click(function () {
                        notice.remove();
                    });
                        $('#orderSubmitButton').prop('disabled',false);

                        return false;
                    }
                }

                //initialize the data sent
                var formData = {
                    "User": {
                        "Id": $('select[name=User_Id]').val()
                    },
                    "Domain": addhttp($('input[name=Domain]').val())
                };

                // add the alias object or the alias id
                if(aliasId !== 'undefined'){
                    formData.ClientAlias_Id = aliasId;
                }else{
                    formData.ClientAlias = ca;
                }

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
                    $('#orderSubmitButton').prop('disabled',false);

                    return false;
                }else{
                    var productPackages = $.map(packages, function(value) {
                        var pack = {
                            ProductPackage:
                                {
                                    Id:$(value).data('package-id')
                                },
                            ProductPrice:$(value).data('product-price'),
                            RunLength:$(value).data('runlength'),
                            PaymentTerms:$(value).data('payment-terms'),
                            Country_Id:$(value).data('country-id'),
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
                        if($(value).data('domain') == ""){
                            pack.Domain = addhttp($('#Domain').val());
                        }else{
                            pack.Domain =addhttp($(value).data('domain'));
                        }

                        //find the container for the addons
                        var addons = $(value).find('.PackageAddon').toArray();

                        if(addons.length > 0){
                            var packageAddons = $.map(addons,function(val){
                                return {Product_Id:$(val).val()};
                            });

                            pack.Products = packageAddons;
                        }

                        return pack;
                        });

                    //add the packages to the order
                    formData.OrderProductPackage = productPackages;
                }

                // check if the selected alias already has these products for the same country and the same domain
                /// first, see if it's existing alias
                if(typeof formData.ClientAlias_Id !== 'undefined'){
                    // so we have existing alias, get it's contracts
                    $.when($.get(api_address+"ClientAlias("+formData.ClientAlias_Id+")/Contract?$expand=Country,Product&$filter=Parent_Id eq null and ProductPackage_Id ne null"))
                        .then(function(contracts){
                            var error = false;
                            //loop through each contract
                            contracts.value.forEach(function(contract){
                                formData.OrderProductPackage.forEach(function(pack){
                                    if(contract.Domain==pack.Domain && contract.Country_Id == pack.Country_Id &&
                                            contract.Product.ProductType_Id == $('.orderProductPackage[data-package-id="'+pack.ProductPackage.Id+'"]').data('package-type-id')){
                                        error = true;
                                        new PNotify({
                                            title:Lang.get('labels.error'),
                                            text:Lang.get('messages.contract-exists')+'\n'+contract.Domain+'\n'+countries[contract.Country_Id],
                                            type:"error"
                                        })
                                    }
                                })
                            });
                            if(!error){
                                sendForm(formData);
                            }else{
                                $('#orderSubmitButton').prop('disabled',false);
                            }
                        }).fail(function(error,status,code){
                        handleError(error,status,code);
                        $('#orderSubmitButton').prop('disabled',false);
                    });
                }else{
                    sendForm(formData);
                }
            });

            function sendForm(data) {

                $.ajax({
                    method: 'POST',
                    url: api_address + 'Orders',
                    data: JSON.stringify(data),
                    success: function (data) {
                        window.location = base_url + '/orders/show/' + data.Id;
                        localStorage.clear();
                    },
                    error: function(xhr,error,status){
                        // enable the submit button
                        $('#orderSubmitButton').prop('disabled',false);

                        handleError(xhr,error,status);
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            }

            checkCI();// check the ci number on load
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
                    <h4><i class="fa fa-shopping-cart"></i> Order </h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><i class="fa fa-plus-square"></i> @lang('labels.create-order') <span id="name">@if($aliasName) @lang('labels.for') : {{$aliasName}}@endif</span></h4>
                            @if($aliasId == null && !isset($lead))
                            <div class="row">
                                    <div class="col-xs-10">
                                        <div class="panel">
                                            <div class="search-classic">
                                                <input id="searchInput" class="form-control" placeholder="Search Client...">
                                            </div>
                                        </div>
                                    </div>
                                @if(isAdmin())
                                    <div class="col-xs-2">
                                        <button class="btn btn-md btn-green createNewClient">@lang('labels.new')</button>
                                    </div>
                                @endif
                            </div> {{-- end row for search --}}
                            @endif
                            <div class="row">
                                <div class="col-xs-12">
                                    {!! Form::open(['method'=>'POST','action'=>['OrdersController@store'],'class'=>'form-horizontal','id'=>'createOrder']) !!}
                                    {!! Form::hidden('ClientAlias_Id',$aliasId, array('id' => 'ClientAlias_Id')) !!}
                                    @if(isset($order)) {!! Form::hidden('FormType',$order->Id,['id'=>'FormType']) !!} @endif


                                    <div class="clientInformationPlaceholder @if(!isset($lead)) hidden @endif">
                                        <input type="hidden" name="Client-Id" value="" id="Client-Id">
                                        <div class="form-group">
                                            {!! Form::label('Domain',Lang::get('labels.homepage'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-md-6">
                                                {!! Form::text('Domain',(isset($url)?$url:null),['class'=>'form-control required','placeholder'=>'http://','required'=>'required']) !!}
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('ClientAlias-Name',Lang::get('labels.company-name'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::text('ClientAlias-Name',(isset($lead->Company)?$lead->Company:null),['class'=>'form-control required','required'=>'required','id'=>'ClientAlias-Name']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('Client-CINumber',Lang::get('labels.ci-number'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::text('Client-CINumber',(isset($lead->CINumber)?$lead->CINumber:null),['class'=>'form-control required','required'=>'required','id'=>'Client-CINumber']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('ClientAlias-Address',Lang::get('labels.company-address'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::text('ClientAlias-Address',(isset($lead->Address)?$lead->Address:null),['class'=>'form-control required','required'=>'required','id'=>'ClientAlias-Address']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('ClientAlias-zip',Lang::get('labels.post-number'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::text('ClientAlias-zip',(isset($lead->zip)?$lead->zip:null),['class'=>'form-control required','required'=>'required','id'=>'ClientAlias-zip']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('ClientAlias-City',Lang::get('labels.city'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::text('ClientAlias-City',(isset($lead->City)?$lead->City:null),['class'=>'form-control required','required'=>'required','id'=>'ClientAlias-City']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('ClientAlias-EMail',"Invoice email",['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::email('ClientAlias-EMail',(isset($lead->Email)?$lead->Email:null),['class'=>'form-control required','required'=>'required','id'=>'ClientAlias-EMail']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Where will the invoice be sent?"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('ClientAlias-CompanyEmail',"Client Email",['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::email('ClientAlias-CompanyEmail',(isset($lead->Email)?$lead->Email:null),['class'=>'form-control required','required'=>'required','id'=>'ClientAlias-CompanyEmail']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Where will we contact the client?"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('ClientAlias-PhoneNumber',Lang::get('labels.company-phone'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::text('ClientAlias-PhoneNumber',(isset($lead->PhoneNumber)?$lead->PhoneNumber:null),['class'=>'form-control required','required'=>'required','id'=>'ClientAlias-PhoneNumber','pattern'=>'[+]?\d*']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('Contact-Name',Lang::get('labels.contact-person'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::text('Contact-Name',(isset($lead->ContactPerson)?$lead->ContactPerson:null),['class'=>'form-control required','required'=>'required','id'=>'Contact-Name']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('Contact-Email',Lang::get('labels.contact-email'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::email('Contact-Email',(isset($lead->ContactEmail)?$lead->ContactEmail:null),['class'=>'form-control required','required'=>'required','id'=>'Contact-Email']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('Contact-Phone',Lang::get('labels.contact-phone'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-sm-6">
                                                {!! Form::text('Contact-Phone',(isset($lead->ContactPhone)?$lead->ContactPhone:null),['class'=>'form-control required','required'=>'required','id'=>'Contact-Phone','pattern'=>'[+]?\d*']) !!}
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="taxonomySearch" class="col-md-3 control-label">@lang('labels.taxonomy')</label>
                                            <div class="col-sm-6">
                                                <input id="taxonomySearch" class="form-control" placeholder="Search category..." @if(isset($lead->Taxonomy_Id)) value="{{$lead->Taxonomy->Name or ""}}" disabled="disabled" @endif>
                                                <input type="hidden" name="ClientAlias-Taxonomy_Id" value="{{$lead->Taxonomy_Id or ""}}">
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                            </div>
                                        </div>

                                        @if(isset($lead->Partner_Id))
                                            <div class="form-group">
                                                {!! Form::label('ClientAlias-Partner_Id',Lang::get('labels.partner'),['class'=>'col-md-3 control-label']) !!}
                                                <div class="col-md-6">
                                                    {!! Form::select('ClientAlias-Partner_Id',withEmpty($partners),(isset($lead->Parner_Id)? $lead->Partner_Id:null),['class'=>'form-control']) !!}
                                                </div>
                                            </div>
                                        @endif

                                        {!! Form::hidden('ClientAlias-Lead_Id',(isset($lead->Id)? $lead->Id:"" )) !!}
                                        {!! Form::hidden('ClientAlias-AdwordsId',(isset($lead->AdwordsId)? $lead->AdwordsId:"")) !!}
                                        {!! Form::hidden('ClientAlias-AnalyticsId',(isset($lead->AnalyticsId)? $lead->AnalyticsId:"" )) !!}

                                        <div class="form-group">
                                            {!! Form::label('ClientAlias-Country_Id',Lang::get('labels.country'),['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-md-6">
                                                {!! Form::select('ClientAlias-Country_Id',$countries,(isset($lead->Country_Id)? $lead->Country_Id:null),['class'=>'form-control']) !!}
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            {!! Form::label('ClientAlias-Subscribed',"Subscribe to newsletter",['class'=>'col-md-3 control-label']) !!}
                                            <div class="col-md-6">
                                                {!! Form::checkbox('ClientAlias-Subscribed',true,true,['class'=>'form-control']) !!}
                                            </div>
                                        </div>

                                    </div>

                                    @if(isset($orderFields))
                                        @foreach($orderFields as $field)
                                            <div class="form-group">
                                                {!! $field['label'] !!}
                                                <div class="col-md-6">
                                                    {!! $field['element'] !!}
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    <div class="form-group">
                                        {!! Form::label('User_Id',Lang::get('labels.seller'),['class'=>'col-md-3 control-label']) !!}
                                        <div class="col-md-6">
                                            {!! Form::select('User_Id',$users,Auth::user()->externalId,['class'=>'form-control']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                        <div class="col-md-12 ">
                                            <div class="row" id="orderProductsList">

                                            </div>
                                        </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="btn-toolbar" style="margin-top:10px;">
                                                {!! Form::submit('Submit order',['class'=> 'btn btn-primary form-control','id'=>'orderSubmitButton']) !!}
                                            </div>
                                        </div>
                                    </div>
                            </div>
                    </div>
                        <div class="col-md-1"></div>
                        <div class="col-md-5">
                            <h4><i class="fa fa-puzzle-piece"></i> PACKAGES</h4>

                            <div class="row">
                                <div id="accordioninpanel" class="accordion-group">
                                    <div class="accordion-item">
                                        <a class="accordion-title collapsed" data-toggle="collapse" data-parent="#accordioninpanel" href="#collapseinTwo"><h4>ADWORDS</h4></a>
                                        <div id="collapseinTwo" class="collapse" style="height: 0px;">
                                            <div class="accordion-body ">
                                                <div class="list-group">
                                                    @if(isset($packages['Adwords']))
                                                        @foreach($packages['Adwords'] as $package)
                                                            <a title="@lang('labels.add-to-order')" class="list-group-item addOrderPackage" data-product-id="{{$package->Id}}" href="#">
                                                                {{$package->Product->Name}}
                                                                <span class="pull-right"> {{formatMoney($package->Product->SalePrice)." kr."}}</span>
                                                            </a>
                                                        @endforeach
                                                        <?php unset($packages['Adwords'])?>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <a class="accordion-title collapsed" data-toggle="collapse" data-parent="#accordioninpanel" href="#collapseinThree"><h4>SEO</h4></a>
                                        <div id="collapseinThree" class="collapse" style="height: 0px;">
                                            <div class="accordion-body">
                                                <div class="list-group">
                                                    @if(isset($packages['SEO']))
                                                        @foreach($packages['SEO'] as $package)
                                                            <a title="@lang('labels.add-to-order')" class="list-group-item addOrderPackage" data-product-id="{{$package->Id}}" href="#">
                                                                {{$package->Product->Name}}
                                                                <span class="pull-right"> {{formatMoney($package->Product->SalePrice)." kr."}}</span>
                                                            </a>
                                                        @endforeach
                                                        <?php unset($packages['SEO']); ?>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <a class="accordion-title collapsed" data-toggle="collapse" data-parent="#accordioninpanel" href="#collapseinFour"><h4>Adwords Local</h4></a>
                                        <div id="collapseinFour" class="collapse" style="height: 0px;">
                                            <div class="accordion-body ">
                                                <div class="list-group">
                                                    @if(isset($packages['Adwords Local']))
                                                        @foreach($packages['Adwords Local'] as $package)
                                                            <a title="@lang('labels.add-to-order')" class="list-group-item addOrderPackage" data-product-id="{{$package->Id}}" href="#">
                                                                {{$package->Product->Name}}
                                                                <span class="pull-right"> {{formatMoney($package->Product->SalePrice)." kr."}}</span>
                                                            </a>
                                                        @endforeach
                                                        <?php unset($packages['Adwords Local'])?>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <a class="accordion-title collapsed" data-toggle="collapse" data-parent="#accordioninpanel" href="#collapseinFive"><h4>OTHER</h4></a>
                                        <div id="collapseinFive" class="collapse" style="height: 0px;">
                                            <div class="accordion-body">
                                                <div class="list-group">
                                                    @foreach($packages as $type=>$packages)
                                                        @foreach($packages as $package)
                                                            <a title="@lang('labels.add-to-order')" class="list-group-item addOrderPackage" data-product-id="{{$package->Id}}" href="#">
                                                                {{$package->Product->Name}}
                                                                <span class="pull-right"> {{formatMoney($package->Product->SalePrice)." kr."}}</span>
                                                            </a>
                                                        @endforeach
                                                    @endforeach

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{--<div class="row">--}}
                                        {{--<div class="col-md-12">--}}
                                            {{--<div class="search-classic1">--}}
                                                {{--<input autocomplete="off" id="productSearch" type="text" class="form-control" placeholder="@lang('labels.search') after products...">--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop