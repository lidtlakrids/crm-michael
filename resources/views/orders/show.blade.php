@extends('layout.main')
@section('page-title',Lang::get('labels.order').": ".$order->Id)
@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop

@section('scripts')
    @include('scripts.x-editable')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
<script>
    $(document).ready(function(){

        var model   = $('#Model').val();
        var modelId = $('#ModelId').val();

        $('.approveOrder').click(function()
        {
            var orderId = modelId;
            $.ajax({
                method: "POST",
                url: api_address+"Orders("+orderId+")/action.Approve",
                success: function( msg ) {
                    location.reload();
                },
                error:handleError,
                beforeSend: function (request)
                {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
            return false;
        });

        $('.dismissOrder').click(function()
        {
            var orderId = modelId;
            var datetimeNow = new Date().toISOString();
            var data = {ArchivedDate: datetimeNow};
            $.ajax({
                method: "PATCH",
                url: api_address+"Orders("+orderId+")",
                data: JSON.stringify(data),
                success: function( msg ) {
                    location.reload();
                },
                beforeSend: function (request)
                {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
            return false;
        });

        $('.renewOrder').click(function()
        {
            var orderId = modelId;
            var data = {ArchivedDate: null};
            $.ajax({
                method: "PATCH",
                url: api_address+"Orders("+orderId+")",
                data: JSON.stringify(data),
                success: function( msg ) {
                    location.reload();
                },
                beforeSend: function (request)
                {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });

            return false;
        });

        $('.resendOrder').on('click',function(event){
            event.preventDefault();
            $(event.target).css('pointer-events','none');

            $.post(api_address+'Orders('+modelId+')/action.Resend').success(function(){
                new PNotify({
                    title:Lang.get('labels.order-was-sent')
                });
                $(event.target).css('pointer-events',"");

            }).error(function(error){
                handleError(error);
                $(event.target).css('pointer-events',"");

            });
        });

        $('.confirmOrder').on('click',function(event){
            event.preventDefault();
            $(event.target).css('pointer-events','none');

            $.ajax({
                url: api_address+'Orders/Confirm',
                type: "POST",
                data:JSON.stringify({Hashcode : $('#HashCode').val(),IP:'194.239.255.14'}),
                success: function (data) {
                    new PNotify({
                        title: "Order was confirmed. Refreshing"
                    });
                    location.reload();
                },
                error: function (err) {
                    $(event.target).css('pointer-events',"");
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        //unlick the select. We need this link for the acl
        $('#ClientManager_Id_Select').change( function (event) {

            var userId = $(event.target).val();
            var clientId = $('#Client_Id').val();
            var userName = $(event.target).find('option:selected').text();
            $.ajax({
                url: api_address + "Clients("+clientId+')',
                type: "PATCH",
                data:JSON.stringify({ClientManager_Id : userId}),
                success: function (data) {
                    $('.orderClientManagerPlaceholder').html(userName);
                    $('#ClientManager_Id_Select').addClass('hidden');
                },
                error: handleError,
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        $('#editOrderClientManager').click(function(){
            $('#ClientManager_Id_Select').toggleClass('hidden');
        });

        $('.editOrderProduct').on('click',function (event) {
            var PaymentTerms= $.map(paymentTerms, function(value, index) {
                return [value];
            });
            event.preventDefault();
            var opid = $(event.target).data('opid');
            $.get(api_address+'OrderProducts('+opid+')?$expand=Product($select=Name)')
                .success(function (data) {

                    var modal = getDefaultModal();
                    modal.find('.modal-title').empty().append("Edit order product : " + data.Product.Name);
                    modal.find('.modal-body').loadTemplate(
                        base_url + '/templates/orders/orderProductEditForm.html',
                        {
                            OrderProductId:opid,
                            RunLength:data.RunLength,
                            PaymentTerms:PaymentTerms,
                            SaveLabel:Lang.get('labels.save'),
                            ProductName:data.Product.Name,
                            CountryLabel:Lang.get('labels.country')
                        },{
                            overwriteCache:true,
                        });
                })
        });

        $('body').on('submit','#editOrderProductForm',function (event) {
            event.preventDefault();
            var form = $(this);
            var data = form.serializeJSON();

            var id = data.opid;
            delete(data.opid);
            $.ajax({
                url: api_address + "OrderProducts("+id+')',
                type: "PATCH",
                data:JSON.stringify(data),
                success: function () {

                    new PNotify({
                        title: "Product was updated. Refreshing"
                    });
                    location.reload();
                },
                error: handleError,
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        if(isAdmin()){
            $( "#confirmIP" ).editable({
                url:api_address+"Orders("+getModelId()+")",
                params: function(params) {
                    var data = {};
                    data['ConfirmedIP'] = params.value == "" ? null:params.value;
                    return JSON.stringify(data);
                },
                ajaxOptions:{
                    type:"patch",
                    dataType: 'application/json',
                    beforeSend: function (request)
                    {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                }
            });
        }

        $('#linkInvoiceToOrder').on('submit',function (event) {
            event.preventDefault();
            var form = $(this);
            var btn = form.find(':submit');
            btn.prop('disabled',true);
            var data = form.serializeJSON();
            $.ajax({
                url: api_address + "Orders("+getModelId()+')',
                type: "PATCH",
                data:JSON.stringify(data),
                success: function (data) {
                    new PNotify({
                        title: 'Invoice associated. Refreshing...'
                    });
                    location.reload(true);
                },
                error: function (error) {
                    btn.prop('disabled',false);
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
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Order',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $order->Id,['id'=>'ModelId']) !!}
    {!! Form::hidden('HashCode', $order->HashCode,['id'=>'HashCode']) !!}
    @if(isset($order->ClientAlias))
        {!! Form::hidden('Client_Id',$order->ClientAlias->Client->Id,['id'=>'Client_Id']) !!}
    @endif
    {{--hidden fields for tasks--}}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-orange">
            <div class="panel-heading">
                <h4>{{mb_strtoupper(Lang::get('labels.order-confirmation'))}}</h4>
                {{--admin options--}}
                <div class="options">
                   @if($order->ApprovedDate == null && isAllowed('orders','patch') )
                        <a href="{{url('orders/edit',$order->Id)}}" title="@lang('labels.edit-order')"><i class="fa fa-edit"></i></a>
                    @endif
                        <a href="#" title="@lang('labels.view-client')"><i class="fa fa-info-circle"></i></a>
                   @if($order->ApprovedDate != null && isAllowed('orders','post') && isset($order->ClientAlias->EMail))
                        <a href="#" title="@lang('labels.resend-order')"><i class="fa fa-refresh resendOrder"></i></a>
                   @endif
                    <a href="#" title="@lang('labels.print-order')"><i class="fa fa-print"></i></a>
                </div>
                {{--end admin options--}}
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" id="OrderId" value="{{$order->Id}}">
                        @if(!$order->ApprovedDate)
                            <h3 style="background: #16a085; padding: 5px 10px; color: #fff; border-radius: 1px; margin: 20px 0 20px; text-align:center">@lang('messages.waiting-approval')</h3>
                        @elseif(!$order->ConfirmedDate && !$order->ArchivedDate)
                            <h3 style="background: #16a085; padding: 5px 10px; color: #fff; border-radius: 1px; margin: 20px 0 20px; text-align:center">@lang('messages.awaiting-confirmation')</h3>
                            @elseif($order->ConfirmedDate && !$order->ArchivedDate)
                                    <!-- or order confirmed -->
                            <h3 style="background: #85c744; padding: 5px 10px; color: #fff; border-radius: 1px; margin: 20px 0 20px; text-align:center">
                                ORDER CONFIRMED ON {{date('d-m-Y H:i',strtotime($order->ConfirmedDate))}} BY IP: <a id="confirmIP" data-pk="1">{{$order->ConfirmedIP or ""}}</a>
                            </h3>
                            @elseif($order->ArchivedDate)
                            <h3 style="background: #e73c3c; padding: 5px 10px; color: #fff; border-radius: 1px; margin: 20px 0 20px; text-align:center">ORDER DISMISSED BY: ADMIN</h3>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <dl class="dl-horizontal-row-2">
                            <dt>
                                Order ID
                            </dt>
                            <dd>
                                {{$order->Id}}
                            </dd>
                            <dt>@lang('labels.client')</dt>
                            <dd>
                                @if($order->ClientAlias != null)
                                <a href="{{url('clientAlias/show',$order->ClientAlias->Id)}}"> {{$order->ClientAlias->Name or "--"}}</a>
                                @else
                                    @lang('messages.client-not-set')
                                @endif
                            </dd>
                            <dt>@lang('labels.homepage')</dt>
                            <dd><a href="{{addHttp($order->ClientAlias->Homepage)}}" target="_blank">{{$order->ClientAlias->Homepage or "--"}}</a></dd>
                            <dt>Invoice Email</dt>
                            <dd> {{$order->ClientAlias->EMail or "--"}}</dd>
                            <dt>Company Email</dt>
                            <dd> {{$order->ClientAlias->CompanyEmail or "--"}}</dd>
                            <dt>Address</dt>
                            <dd>{{$order->ClientAlias->Address or "--"}},{{$order->ClientAlias->City or "--"}} {{$order->ClientAlias->zip or "--"}}</dd>
                            {{--<dt>@lang('labels.client-manager')</dt>--}}
                            {{--<dd>--}}
                                {{--@if(isAllowed('clients','patch'))--}}
                                        {{--<span class="orderClientManagerPlaceholder">--}}
                                        {{--{!! $order->ClientAlias->Client->ClientManager->FullName or Form::select('ClientManager_Id',withEmpty($clientManagers),null,['class'=>'form-control','id'=>'ClientManager_Id_Select'])!!}--}}
                                        {{--</span>--}}
                                        {{--{!! Form::select('ClientManager_Id',withEmpty($clientManagers),null,['class'=>'form-control hidden','id'=>'ClientManager_Id_Select']) !!}--}}
                                {{--@else--}}
                                    {{--{{$order->ClientAlias->Client->ClientManager->FullName or "--"}}--}}
                                {{--@endif--}}
                            {{--</dd>--}}
                            <dt>@lang('labels.payment-status')</dt>
                            <dd>
                            @if(isset($order->Invoice))
                                <strong style="color: @if($order->Invoice->Status =="Paid") green @else red @endif ;">{{$order->Invoice->Status or ''}}</strong>
                            @else
                                @if(isAdmin())
                                    <form id="linkInvoiceToOrder">
                                        <label for="orderInvoiceId">Invoice ID</label>
                                        <input id='orderInvoiceId' type="number" name="Invoice_Id" min="0" required="required">
                                        <button type="submit">Save</button>
                                    </form>
                                @else
                                    No payment info
                                @endif

                            @endif
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-4">
                        Hash :  {{$order->HashCode or ''}} <br>
                        Status : {{$order->Status or ''}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table style="width:99%;" id="table-list" class="table table-bordered table-striped table-condensed table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:3%;">@lang('labels.number')</th>
                                        <th>@lang('labels.product')</th>
                                        <th>@lang('labels.description')</th>
                                        <th>@lang('labels.country')</th>
                                        <th>@lang('labels.homepage')</th>
                                        <th>@lang('labels.terms')</th>
                                        <th>@lang('labels.runlength')</th>
                                        <th>@lang('labels.monthly-price')</th>
                                        <th>Creation Fee</th>
                                        <th>@lang('labels.discount')</th>
                                        <th>@lang('labels.total')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $orderTotal = 0; ?>
                                    @foreach($order->OrderProductPackage as $p)

                                        @if(isset($p->ProductPackage))
                                            <?php $orderTotal+= ($p->RunLength*$p->ProductPrice)- ($p->RunLength*$p->ProductPrice*($p->Discount/100)) + $p->CreationFee; ?>
                                            <tr>
                                                <td>{{$p->ProductPackage->Id}}</td>
                                                <td>{{$p->ProductPackage->Product->Name or "--"}}</td>
                                                <td class="multiline">{{$p->ProductPackage->Product->Description or ""}}</td>
                                                <td>{{$p->Country->CountryCode or "--"}}</td>
                                                <td><a href="{{addHttp($p->Domain)}}" target="_blank">{{$p->Domain or "--"}}</a></td>
                                                <td>{{$p->PaymentTerms}}</td>
                                                <td>{{$p->RunLength}}</td>
                                                <td>{{formatMoney($p->ProductPrice)}}</td>
                                                <td>{{formatMoney($p->CreationFee)}}</td>
                                                <td>{{$p->Discount}} %</td>
                                                <td class='orderProductPrice'>{{formatMoney(($p->RunLength*$p->ProductPrice) - ($p->RunLength*$p->ProductPrice*($p->Discount/100))  + $p->CreationFee)}}</td>
                                            </tr>
                                        @elseif($p->Product != null)
                                            <?php $orderTotal+= ($p->RunLength*$p->ProductPrice) - ($p->RunLength*$p->ProductPrice*($p->Discount/100)); ?>
                                            <tr>
                                                <td>
                                                    {{$p->Product->Id}}
                                                    {{--Qucik edit package--}}
                                                    @if(isAdmin())
                                                        <i data-opid="{{$p->Id}}" title="Edit Order Product" style="color:dodgerblue" class="fa fa-pencil editOrderProduct"></i>
                                                    @endif

                                                </td>
                                                <td>{{$p->Product->Name or "--"}}</td>
                                                <td class="multiline">{{$p->Product->Description}}</td>
                                                <td>{{$order->ClientAlias->Country->CountryCode or "--"}}</td>
                                                <td>{{$order->ClientAlias->Homepage or "--"}}</td>
                                                <td>{{$p->PaymentTerms}}</td>
                                                <td>{{$p->RunLength}}</td>
                                                <td>{{formatMoney($p->Product->SalePrice)}}</td>
                                                <td>0</td>
                                                <td>{{$p->Discount}} %</td>
                                                <td class='orderProductPrice'>{{formatMoney(($p->RunLength*$p->ProductPrice)- ($p->RunLength*$p->ProductPrice*($p->Discount/100)))}}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row" style="border-radius: 0px;">
                    <div class="col-md-9">
                        Seller : {{ $order->User->FullName or '' }}
                        <br />
                        @if($order->ConfirmedDate != null)@lang('labels.confirmed-date') {{date('d-m-Y H:i',strtotime($order->ConfirmedDate))}} by {{$order->ConfirmedIP or "----"}}@endif
                        <br />
                        @if($order->ApprovedDate)
                            @lang('labels.approved-by')&nbsp;<strong>{{$order->ApprovedBy->UserName or ""}}</strong>
                        @endif
                    </div>

                    <div class="col-md-3">
                        <p class="text-right">Sub-total: <span class="orderTotal"></span></p>
                        <p class="text-right">Discount: <span class="orderDiscount">0</span>%</p> <!-- todo discount -->
                        <hr>
                        <h3 class="text-right">{{config('gcm.money-code')}} <span class="orderTotal">{{formatMoney($orderTotal)}}</span></h3>
                    </div>
                </div>
                <div class="row noPrint">
                    <hr />
                    <div class="col-md-6">
                        <h4 >@lang('labels.order-info')</h4>
                        <table class="table table-condensed table-bordered">
                            <tbody>
                            @if(is_array($order->OrderFieldValue))
                                @foreach($order->OrderFieldValue as $value)
                                    <tr>
                                        <td>{{$value['DisplayName']}}: </td>
                                        <td>{!! nl2br($value['value']) !!}</td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="6">
                        <div class="pull-right">
                            <div class="btn-group-horizontal">
                                @if($order->Id < 8172)
                                    <a href="http://stat.gcm.nu/stat/get_old_form.php?orderId={{$order->Id}}" target="_blank" class="btn btn-magenta btn-label"><i class="fa fa-clock-o"></i> Old Form</a>
                                @endif
                                @if($order->Invoice != null)
                                    @if($order->Invoice->Type != "CreditNote" && $order->Invoice->Status == "Sent")
                                        <button class="btn btn-success btn-label" style="width: 117px;"><i class="fa fa-money"></i>@lang('labels.set-paid')</button>
                                    @endif
                                @endif

                                @if($order->ApprovedDate == null)
                                    {!! Form::button('<i class="fa fa-check"></i>'.Lang::get('labels.approve'), array('class' => 'approveOrder btn btn-success btn-label'))!!}
                                    <a href="{{url('orders/edit',$order->Id)}}" title="@lang('labels.edit-order')" class="btn btn-inverse btn-label" style="width: 135px;"><i class="fa fa-edit"></i>{{strtoupper(Lang::get('labels.edit'))}}</a>
                                @elseif($order->ApprovedDate != null && isAllowed('orders','post') && isset($order->ClientAlias->EMail) && $order->ArchivedDate == null)
                                    <a href="#" class="btn btn-inverse btn-label resendOrder"><i class="fa fa-envelope-o"></i>@lang('labels.resend-order')</a>
                                @endif
                                @if(isAdmin() && $order->ConfirmedDate == null)
                                    <a href="#" class="btn btn-green-alt btn-label confirmOrder"><i class="fa fa-check"></i>Confirm</a>
                                @endif

                                @if(!$order->ArchivedDate)
                                    <button class="dismissOrder btn btn-danger btn-label" style="width: 117px;"><i class="fa fa-times"></i>@lang('labels.dismiss')</button>
                                @else
                                    <button class="renewOrder btn btn-inverse btn-label" style="width: 117px;"><i class="fa fa-refresh"></i>@lang('labels.renew')</button>
                                @endif
                                <a href="https://dk.gcm.nu/FormSubmission/view/{{$order->Id}}" target="_blank" class="btn btn-inverse"><i class="fa fa-print"></i></a>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row noPrint">
                    @include('layout.tabs-section',
                    ['files'      =>true,
                     'admin' => true,
                     'invoices' => ($order->Invoice == null ?  []:[$order->Invoice]),
                     'contracts' => $order->Contracts,
                     'appointments' => true,
                     'appointmentEmail'=>$order->ClientAlias->EMail,
                     'appointmentInfo'=>['Type'=>'Appointment','Summary' => 'Aftale Målsætning & KPI','Description'=>"Formålet er at behovsafdække og få aftalt en konkret målsætning med relevante KPI'er så både vi og du arbejder mod en samlet målsætning for din online position og resultater."]

                    ])
                </div>
                <div class="panel-footer hidden-print">
                </div>
            </div>
        </div>
    </div>
    {{--END CONTRACT CONFIRMATION--}}
</div>

@stop
