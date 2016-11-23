@extends('layout.main')
@section('page-title',"Accounting - Register Payments")

@section('styles')
    {!! Html::style(asset('css/dropzone.min.css')) !!}

    <style>
        .setInvoiceAsPaid{position:relative}
    </style>
@stop
@section('scripts')
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}

    <script>
        $(document).ready(function () {

            $('body').on('click','.setInvoiceAsPaid',function(event){
                event.preventDefault();
                var button = $(event.target);
                // the value of the checkbox
                var row = button.closest('tr');
                var checkbox =row.find('input.paymentCheckbox');
                var invoiceId = checkbox.val();
                button.addClass('spinner');
                button.css('pointer-events','none');

                $.when(
                setPaid(invoiceId)
                ).then(function(){
                    //remove the button
                    button.remove();
                    //remove the checkbox
                    checkbox.remove();
                    row.find('td.isPaid').html(Lang.get('labels.yes'));
                }).fail(function(){
                    button.css('pointer-events','');
                    button.removeClass('spinner');
                    console.log('fail');
                })
            });

            var dropzone = new Dropzone("#paymentFileUploadForm",{
                url:api_address+"FileStorages",
                acceptedFiles: ".csv",
                headers: {
                    'Authorization' : 'Bearer ' + $.cookie('auth'),
                    'Accept': 'application/json; charset=utf-8'
                }
            });

            dropzone.on("addedfile", function(file) {
                file.previewElement.addEventListener("click", function() {
                    dropzone.removeFile(file);
                });
            });

            dropzone.on("complete", function(file) {
                if(file.status == "error"){
                    new PNotify(
                        {
                            title:Lang.get('labels.error'),
                            text:Lang.get('labels.bad-file'),
                            type:'error'
                        }
                    );
                    dropzone.removeFile(file);
                    return false;
                }
                dropzone.removeFile(file);
                // get the placeholder for the payments
                var container = $('.paymentsTable > tbody');
                // put spinner
                container.addClass('spinner');

                // when the file is uploaded, we need to patch it with correct information
                var response = JSON.parse(file.xhr.response);
                var fileId = response.Id;

                $.get(api_address+'Payments('+fileId+')').success(function(data){
                    if(data.value.length > 0){
                        renderPayments(data.value);
                    }
                }).error(function () {
                    new PNotify(
                        {
                            title: Lang.get('labels.error'),
                            text: Lang.get('labels.bad-file'),
                            type: 'error'
                        });
                    container.removeClass('spinner');

                })
            });

            /**
             * removes the buttons and the checkboxes for each registered payment or put an error
             *
             */
            function registerPayments(payment){
                // find the tr which has this payment
                var row = $('.paymentsTable').find('tr[data-invoice-id="'+payment+'"]');
                var checkbox =row.find('input.paymentCheckbox');
                    checkbox.remove();
                    row.find('.setInvoiceAsPaid').remove();
                    row.find('.isPaid').html(Lang.get('labels.yes'));
                // check if we paid from modal
                if($('#defaultModal').is(':visible')){
                    new PNotify({
                        text:Lang.get('messages.invoice-set-as-paid',{Id:payment}),
                        type:'success'
                    });
                }



            }
            /**
             * shows an error on failed requests
             *
             */
            function registerFail(payment){
                // find the tr which has this payment
                var row = $('.paymentsTable').find('tr[data-invoice-id="'+payment+'"]');
                    row.addClass('danger');
            }


            /**
             * puts the payments into the table
             * @param payments
             */
            function renderPayments(payments){
                var container = $('.paymentsTable > tbody');
                var totalPaid = 0;
                //make payments array
                var a = $.map(payments,function(p){
                    var payment = {};
                        payment.InvoiceText = p.Text;
                    //check if we have found an invoice
                        if(p.Invoice != null){
                            if(p.Invoice.Invoice_Id != null){
                                payment.InvoiceId       = p.Invoice.Invoice_Id;
                                payment.InvoiceNumber   = p.Invoice.InvoiceNumber;
                                payment.ClientName      = p.Invoice.ClientName;
                                payment.InvoiceLink     = linkToItem('Invoice',p.Invoice.Invoice_Id,true);
                                payment.ClientAliasLink = (p.Invoice.ClientAlias_Id != null)?linkToItem('ClientAlias',p.Invoice.ClientAlias_Id,true) : null;
                                payment.ClientName      = p.Invoice.ClientName;
                                payment.Amounts         = "<span class='paidAmount'>Paid : "+p.Amount.format() +'kr. </span> / Invoice Amount :' + invoiceValue(p.Invoice,'kr.');
                                var created             = new Date(p.Invoice.Created);
                                var due                 = new Date(p.Invoice.DueDate);
                                payment.CreateDate      = created.toDate();
                                if(p.Invoice.PaymentDate == null){
                                    payment.PayButton = "<a href='#' class='form-control btn-green btn-xs setInvoiceAsPaid'>"+Lang.get('labels.paid')+'</a>';
                                    payment.PaymentCheckBox = "<input type='checkbox' value='"+p.Invoice.Invoice_Id+"' class='paymentCheckbox'>";
                                    payment.IsPaid = Lang.get('labels.no');
                                }else{ // it's paid, just say "Yes"
                                    payment.IsPaid = Lang.get('labels.yes');
                                }
                            }else{ // not paid and no invoice found
                                payment.SearchInvoiceClass = "searchInvoiceText";
                                payment.IsPaid  = Lang.get('labels.no');
                                payment.Amounts = "<span class='paidAmount'>"+p.Amount.format() + ' kr. </span>';
                            }
                        }else{  // if there is not invoice, add search invoice class to the invoice text
                            payment.SearchInvoiceClass = "searchInvoiceText";
                            payment.IsPaid  = Lang.get('labels.no');
                            payment.Amounts = "<span class='paidAmount'>"+p.Amount.format() + ' kr. </span>';
                        }

                        totalPaid += p.Amount;
                    return payment;
                });// end mapping
                container.loadTemplate(
                        base_url+'/templates/registerPayments/paymentTableRow.html',
                        a,
                        {
                            overwriteCache:true,
                            success: function () {
                                container.removeClass('spinner');
                                $('.totalPaid').html(totalPaid.format() +'DKK')
                            }
                        })
            }

            $('body').on('click','.paySelected',function(event){
                var button = $(event.target);
//                button.prop('disabled',true);
                //get all checked checkboxes
                var a = $.map($('input:checkbox.paymentCheckbox:checked'),function(el){
                    return $(el).val();
                });
                if(a.length>0){
                    $('.paymentsTable').addClass('spinner');
                    button.prop('disabled',true);
                    var count = a.length;
                    a.forEach(function (obj,index) {
                        $.when(setPaid(obj).then(function(){
                            registerPayments(obj);
                        }).fail(function () {
                            registerFail(obj);
                        }));
                        if(count -1 == index){
                            button.prop('disabled',false);
                            $('.paymentsTable').removeClass('spinner');
                        }
                    })

                }else{
                    new PNotify({
                        title:Lang.get('labels.error'),
                        text:Lang.get('messages.no-payments-selected'),
                        type:'error'
                    });
                }
            });

            /**
             * Opens a modal and searches for the text, to find an invoice with similar information
             *
             */
            $('body').on('click','.searchInvoiceText', function (event) {
                event.preventDefault();
                //get the text on the clicked item
                var text = $(event.target).text();
                //find the paid amount
                var amount = $(event.target).closest('tr').find('.paidAmount').html();

                    var modal = getDefaultModal();
                    modal.find('.modal-body').loadTemplate(base_url+'/templates/registerPayments/invoiceSearchModalBody.html',
                    {
                        SearchedInvoices: text,
                        SearchLabel : Lang.get('labels.search'),
                        DisplayingResults: Lang.get('labels.displaying-results-for')+ " "+ text
                    },{
                        overwriteCache: true,
                        success: function () {
                            search(text);
                            modal.find('.modal-footer').html('<input type="button" class="form-control btn-green btn-md paySelected" value="@lang('labels.pay-selected')" />')
                        }
                    });

            });


            //searches and appends results to the results placeholder
            function search(text){
                //get the modal container =
                var modal = $('#defaultModal');

                // find the results container
                var container = modal.find('.invoiceSearchResults');
                var query = "";
                // 4 or 5 digits regex to look for a invoice number
                var regex=/(?:^|\D)(\d{4,5})(?=\D|$)/g;
                var numbers = text.match(regex);
                console.log(numbers);

                if(numbers != null){
                    var invoiceNumbers = $.map(numbers, function (obj) {
                        return "indexof(cast(InvoiceNumber, 'Edm.String'),'"+obj.replace('&','').trim()+"') gt -1";
                    });
                    var InvoiceNumberQuery = invoiceNumbers.join(' or ');
                    query += InvoiceNumberQuery;
                }
                // find the text part and search for client names
                var textOnly = text.replace(/[0-9]/g, '');
                var words = textOnly.split(' ');
                    if(words.length>0){
                        var names = $.map(words, function (obj) {
                            if(obj != "" && obj.length > 2 && obj.toLowerCase() != 'aps'){
                                return "indexof(Name,'"+obj.trim()+"') gt -1";
                            }
                        });
                        var nameQuery = names.join(' or ');
                        if(nameQuery != "" && query != ""){
                            query += " or "+nameQuery;
                        }else if(nameQuery != "" && query == ""){
                            query = nameQuery;
                        }else if(nameQuery == "" && query != ""){
                            // do nothing just execute
                        }else{
                            container.html(Lang.get('labels.no-results'));
                            return false;
                        }
                    }


                $.get(api_address+"Invoices?$expand=ClientAlias($select=Name)&$filter="+query).success(function (data) {
                        if(data.value.length > 0) {
                            var results = $.map(data.value, function (obj) {
                                var inv = {};
                                var created = new Date(obj.Created);
                                // calculate the amount

                                var amount = invoiceValue(obj,"DKK");
                                inv.InvoiceId = obj.Id;
                                inv.InvoiceLink = linkToItem('Invoice', obj.Id, true);
                                if(obj.Status == "Paid"){
                                    inv.DisablePaid= "return false;"
                                }
                                inv.InvoiceAndAlias = obj.InvoiceNumber +
                                        (obj.ClientAlias != null ? " - " + obj.ClientAlias.Name : "") +
                                        " "+ created.toDate()+
                                        " "+ amount +
                                        (obj.Status == "Paid" ? "- <strong style='color:green'>" + Lang.get('labels.paid') + "</strong>" : "");

                                return inv;
                            });
                            container.loadTemplate(base_url + '/templates/registerPayments/invoiceResults.html', results, {overwriteCache: true});
                        }else{
                            container.html(Lang.get('labels.no-results'));
                        }
                    });
            }


            $('body').on('submit','#searchInvoiceForm', function (event) {
                event.preventDefault();
                //get the text from the input
                 var text =  $('.invoiceSearchString').val();
                search(text);
            });

            /**
             * sets invoice as paid a
             * @param invoiceId
             */
            function setPaid(invoiceId){
                return   $.ajax({
                    type: "POST",
                    url: api_address + "Invoices("+ invoiceId + ")/action.Pay",
                    success: function (data) {
                        return data;
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            }
        })
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-check-circle"></i>@lang('labels.register-payments')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="panel">
                            <div class="col-md-12">
                                <form id="paymentFileUploadForm" class="dz-clickable">
                                    <div class="dz-default dz-message">
                                        <span><i class="fa">@lang('labels.drop-here-to-upload') or Click</i></span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel">
                                <div class="table-responsive"  style="margin-top:20px; border-top: solid 1px #ccc;">
                                    <table class="table table-hover paymentsTable" style="width: 100%;">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Invoice</th>
                                            <th>Debitor</th>
                                            <th>Text</th>
                                            <th>Paid</th>
                                            <th>Amount</th>
                                            <th>Options</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th colspan="7" style="text-align: right ">Total Paid :<span class="totalPaid"></span> </th>
                                            <th></th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <input type="button" class="form-control btn-green btn-md paySelected" value="@lang('labels.pay-selected')" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="findInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title"><i class="fa fa-search"></i> Find Faktura / Kontrakt</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Vælg</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
@stop
