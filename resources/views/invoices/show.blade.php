@extends('layout.main')
@section('page-title',Lang::get('labels.'.strtolower($invoice->Type))." : ".$invoice->InvoiceNumber)
@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
    @include('scripts.x-editable')
<script>
    $(document).ready(function () {

        $('.createReminder').on('click',function(event){

            var target = $(event.target);
            var aliasId = $('#AliasId').val();
            if(typeof aliasId == "undefined" && aliasId == "") {
                new PNotify({
                    title: "Error",
                    text: Lang.get('labels.client-not-set'),
                    type: 'error'
                });
                return false;
            }
            target.css('pointer-events','none');

            $.post(api_address + "ClientAlias(" + aliasId + ")/action.CreateReminder")
                .success(function (data) {
                    window.location = base_url + '/drafts/show/' + data.Id;
                })
        });

        var invoiceId = $('#ModelId').val();

        $('.markInvoicePaid').on('click', function (event) {
            $(event.target).prop('disabled',true);
            $.ajax({
                type: "POST",
                url: api_address + "Invoices("+ invoiceId + ")/action.Pay",
                success: function (data) {
                    window.location.reload();
                },
                error: handleError,
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
            return false;
        });

        $('.sendToDebtCollection').on('click', function (event) {
            $(event.target).prop('disabled',true);

            bootbox.confirm("Are you sure?", function(result)
            {
                if(result){
                    $.ajax({
                        type: "POST",
                        url: api_address + "Invoices("+ invoiceId + ")/SendToDebtCollection",
                        success: function (data) {
                            new PNotify({
                                title:"Sent to debt collection. Refreshing...",
                                type:'success'
                            });
                            window.location.reload(true);
                        },
                        error: function (err) {
                            $(event.target).prop('disabled',false);
                        },
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });

                }
            });
            return false;
        });

        $('.escalateReminder').on('click', function (event) {
            $(event.target).prop('disabled',true);
            $.ajax({
                type: "POST",
                url: api_address + "Invoices("+ invoiceId + ")/Escalate",
                success: function (data) {
                    new PNotify({
                        title:"Reminder was escalated... Redirecting",
                        type:'success'
                    });
                    window.location = base_url+'/drafts/show/'+data.Id;
                },
                error: handleError,
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });

            return false;
        });


        $('#resendInvoice').on('click',function(event){
            event.preventDefault();
            var button = $(event.target);
            button.prop('disabled',true);

            $.post(api_address+'Invoices('+invoiceId+')/action.SendInvoice').success(function(){
                new PNotify({
                    title:Lang.get('labels.invoice-was-sent')
                });
                button.prop('disabled',false);

            }).error(function(xhr,error,status){
                handleError(xhr,error,status);
                button.prop('disabled',false);
            });

        });

        // create credit note draft and redirect to it
        $('.makeCreditNote').on('click',function(event){

            //disable the button. prevent doubleclick
            $(event.target).prop('disabled',true);

            $.post(api_address+'Invoices('+getModelId()+")/action.Flip")
                    .success(function(data){
                window.location= base_url+"/drafts/show/"+data.Id;
            })
        });

        $('.overrideCommission').on('click',function(event){
            event.preventDefault();

            // get the invoice lines
            $.get(api_address+'Invoices('+invoiceId+')/InvoiceLine')
                .success(function(data){
                    var modal = getDefaultModal();
                    var body  = modal.find('.modal-body');
                    body.loadTemplate(base_url+'/templates/invoices/lineCommissionForm.html',{},
                        {
                            success: function(){
                                var form = $('#invoiceLinesCommission');
                                //make array with all lines infomration for the templates
                                var lines = $.map(data.value,function(line){
                                    var l = {
                                        Description:line.Description,
                                        NetAmount : line.Quantity+" * "+Number(line.UnitPrice).format() +" = "+
                                                    Number((line.Quantity * line.UnitPrice)).format()+
                                                    ( line.Discount != 0 ? "("+line.Discount+"% discount)":""),
                                        CommissionLabel : Lang.get('labels.commission'),
                                        Commission:line.Commission,
                                        InputName: "InvoiceLine["+line.Id+"][Commission]"
                                    };
                                    return l;

                                });

                                form.loadTemplate(base_url+'/templates/invoices/lineCommissionOverwrite.html',lines,{prepend:true,overwriteCache:true})
                            }
                        })
                });

            $('body').on('submit','#invoiceLinesCommission',function(event){
                event.preventDefault();
                var commissions =$(this).serializeJSON();
                var patches = $.map(commissions.InvoiceLine,function(a,b){
                    console.log(a);
                    console.log(b);
                    if(a.Commission !== ""){
                        $.ajax({
                            type:"Patch",
                            url: api_address+"InvoiceLines("+b+")",
                            data:JSON.stringify({Commission:a.Commission}),
                            error:function(){
                                return a;
                            },
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });
                    }
                });
                closeDefaultModal();
            })
        });

        $('#status').editable({
            mode:'inline',
            url:api_address+"Invoices("+getModelId()+")",
            ajaxOptions:{
                type:"patch",
                dataType: 'application/json',
                beforeSend: function (request)
                {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            },
            params: function(params) {
                var data = {};
                data['Status'] = params.value;
                return JSON.stringify(data);
            },
            source: statuses
        });

    })
</script>

@stop

@section('content')

    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Invoice',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $invoice->Id,['id'=>'ModelId']) !!}
    @if(isset($invoice->ClientAlias->Id)){!! Form::hidden('AliasId', $invoice->ClientAlias->Id,['id'=>'AliasId']) !!}@endif
    {{--hidden fields for tasks--}}

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-invoice"><!-- if paid make bgcolor green, overdue yellow and huge overdue red --->
                <div class="panel-heading">
                    <h4><i class="fa fa-barcode"></i> @lang('labels.invoice') </h4>
                    <div class="options">
                        <a href="#" title="@lang('labels.edit-invoice')"><i class="fa fa-edit"></i></a>
                        <a href="#" title="@lang('labels.print-invoice')"><i class="fa fa-mail-forward"></i></a>
                        <a href="#" title="@lang('labels.resend-invoice')"><i class="fa fa-file-pdf-o"></i></a>
                        <a href="#" title="@lang('labels.resend-invoice')"><i class="fa fa-print"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="panel-body">
                                @unless($invoice->Payed == null)
                                    <div class="clearfix">
                                        <div class="alert alert-dismissable alert-success"> <!-- OR alert alert-warning if overdue -->
                                            <strong>@lang('labels.paid')!</strong> - @lang('messages.invoice-was-paid') {{Carbon::parse($invoice->Payed)->format('d-m-Y H:i')}}.
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                        </div>
                                    </div>
                                @endunless
                                <div class="clearfix">
                                    <div class="pull-left">
                                        <address>
                                            <dl>
                                                <dt></dt>
                                                <dd>
                                                    <strong>@if(isset($invoice->ClientAlias))
                                                            <a href="{{url('clientAlias/show',$invoice->ClientAlias->Id)}}">
                                                                {{$invoice->ClientAlias->Name or "----"}}
                                                            </a>@else --- @endif
                                                    </strong>
                                                </dd>

                                                <dt></dt>
                                                <dd>{{$invoice->Address or ""}}</dd>

                                                <dt></dt>
                                                <dd>{{$invoice->ZipCode or ""}} {{$invoice->City or ""}}</dd>

                                                <dt></dt>
                                                <dd>{{$invoice->ClientAlias->Country->CountryCode or ""}}</dd>

                                                <dt></dt>
                                                <dd>{{$invoice->ClientAlias->Client->CINumber or "---"}} </dd><!-- debtorCI -->
                                            </dl>
                                        </address>
                                    </div>
                                    <div class="pull-right">
                                        <dl class="dl-horizontal">
                                            <dt>@lang('labels.invoice'):</dt>
                                            <dd>@if(isset($invoice->InvoiceNumber)) {{$invoice->InvoiceNumber}} @else --- @endif </dd>

                                            <dt>@lang('labels.created-date')</dt>
                                            <dd>{{date('d-m-Y H:i',strtotime($invoice->Created))}}</dd>

                                            <dt>@lang('labels.due-date') </dt>
                                            <dd>{{date('d-m-Y H:i',strtotime($invoice->Due))}}</dd>

                                            <dt>@lang('labels.customer-number')</dt>
                                            <dd>{{$invoice->ClientAlias->Id or "--"}}</dd>

                                            <dt>@lang('labels.our-reference')</dt>
                                            <dd>{{$invoice->User->FullName or "---"}}</dd>
                                        </dl>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    @if(isset($invoice->InvoiceLine ))
                                        <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered scroll-table" id="example">
                                            <thead>
                                                <tr>
                                                    <th>@lang('labels.product-description')</th>
                                                    <th>@lang('labels.quantity')</th>
                                                    <th>@lang('labels.unit-net-price')</th>
                                                    <th>@lang('labels.discount')</th>
                                                    <th>@lang('labels.total-net-amount')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($invoice->InvoiceLine as $line)
                                                <tr>
                                                    <td>@if(isset($line->Contract_Id))Contract #{{$line->Contract_Id}} @endif <span class="invoice product-name">{{$line->Product->Name or ""}}</span><br /><span class="invoice product-description">{{ $line->Description or "--" }}</span></td><!-- add product name and description under it-->
                                                    <td>{{ $line->Quantity }}</td>
                                                    <td>{{ number_format($line->UnitPrice,2,',','.')}}</td>
                                                    <td>{{$line->Discount}}%</td>
                                                    <td>{{ number_format(calculateLineDiscount($line),2,',','.')}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                                <div class="clearfix">
                                    <div class="col-md-3 col-md-offset-9">
                                        <p class="text-right">Subtal: @if(isset($invoice->NetAmount)) {{number_format($invoice->NetAmount,2,',','.')}} @else ---  @endif</p>
                                        <p class="text-right">@lang('labels.tax'): @if(isset($invoice->ClientAlias->Country)){{($invoice->ClientAlias->Country->VatRate)*100 }} @else 25 @endif %</p>
                                        <p class="text-right">Total: {{formatMoney($invoice->VatAmount)}}</p>
                                        <hr>
                                        <h3 class="text-right">{{config('gcm.money-code')}} {{ number_format($invoice->NetAmount+$invoice->VatAmount,2,',','.')}}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="panel-body">
                                <h4>Invoice Options</h4>
                                <dl class="dl-horizontal">
                                    <dt>@lang('labels.status')</dt>
                                    <dd style="color:@if($invoice->Status == "Paid") green @elseif($invoice->Status == "Sent") orange @else red @endif;">
                                        @if(isAllowed('invoices','patch'))
                                            <a href="#" id="status" data-type="select" data-pk="{{$invoice->Id}}" data-title="Select status">{{$invoice->Status}}</a>
                                        @else
                                            {{$invoice->Status}}
                                        @endif

                                    </dd>
                                    <dt>@lang('labels.type')</dt>
                                    <dd>{{$invoice->Type}}</dd>

                                    @if($invoice->Type == 'Reminder')
                                    <dt>Reminder Type</dt>
                                    <dd>{{$invoice->ReminderType}}</dd>
                                    @endif

                                    <dt>@lang('labels.pay-date'):</dt>
                                    <dd>@if($invoice->Payed != null) {{ date("d-m-Y",strtotime($invoice->Payed)) }} @endif</dd>
                                    @if($invoice->Status != "Paid")
                                        @if(date('c',strtotime('today')) > $invoice->Due)
                                            <dt>@lang('labels.days-overdue')</dt>
                                            <dd><span class="color-red">
                                                    <?php
                                                    $due = new Carbon($invoice->Due);
                                                    $now = Carbon::now();
                                                    $difference = ($due->diff($now)->days < 1)
                                                            ? 'today'
                                                            : $due->diffForHumans($now);
                                                    ?>
                                                    {{$difference}}
                                                </span>
                                            </dd>
                                        @endif
                                    @endif
                                </dl>
                                <hr />
                                <div class="btn-group-horizontal invoice-options">
                                    @if(isAllowed('invoiceLines','patch'))
                                        <button class="btn btn-warning btn-label overrideCommission"><i class="fa fa-pencil"></i> @lang('labels.edit-commission')</button>
                                    @endif

                                    @if($invoice->Payed == null && $invoice->Status != "Paid" && isAllowed('invoices','pay'))
                                        <button class="btn btn-success btn-label markInvoicePaid"><i class="fa fa-money"></i> @lang('labels.set-paid')</button>
                                    @endif

                                    @if($invoice->Type != "CreditNote" && isAllowed('invoices','pay'))<button class="btn btn-inverse btn-label makeCreditNote"><i class="fa fa-minus-square"></i> @lang('labels.creditnote')</button>@endif
                                    @if(isset($invoice->ClientAlias->EMail) && isAllowed('invoices','sendInvoice'))
                                        <button id="resendInvoice" class="btn btn-default btn-label"><i class="fa fa-mail-forward"></i> @lang('labels.send')</button>
                                    @endif
                                    {{--<button class="btn btn-default btn-label"><i class="fa fa-print"></i> Print</button>--}}
                                    @if($invoice->Type == "Invoice" && isAdmin() && $invoice->Status != "Paid")
                                        <button class="btn btn-default btn-label createReminder" title="@lang('labels.create-reminder')">
                                            <i class="fa fa-bomb"></i> @lang('labels.reminder')
                                        </button>
                                    @endif

                                    @if($invoice->Type == 'Reminder' && $invoice->Status == 'Overdue' && isAllowed('invoices','pay'))
                                        <button class="btn btn-danger btn-label sendToDebtCollection" title="Send to debt collection">
                                            <i class="fa fa-exclamation"></i> Send to Debt Collection
                                        </button>
                                    @endif


                                    @if($invoice->Type == 'Reminder' && isAllowed('invoices','pay') && $invoice->ReminderType == 'First')
                                        <button class="btn btn-danger btn-label escalateReminder" title="Escalate reminder">
                                            <i class="fa fa-level-up"></i> Escalate
                                        </button>
                                    @endif

                                    @if($invoice->HashCode != null)
                                       <a href="{{url('invoices/invoicePdf',$invoice->HashCode)}}" target="_blank"><button class="btn btn-default btn-label"><i class="fa fa-file-pdf-o"></i> PDF</button></a>
                                    @endif
                                    <!-- add send reminder if overdue -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:25px;">
                        <!-- TABS -->
                        <div class="col-md-12">
                            <div class="panel">
                                @include('layout.tabs-section',
                                ['files'      =>true,
                                 'appointments' => true,
                                 'admin' => true,
                                 'information'=>$invoice->ClientAlias
                                ])
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
    @stop
