@extends('layout.main')
@section('page-title',Lang::get('labels.missing-payments')." : ".(isset($user->FullName)? $user->FullName : ""))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')


    <script>
        $(document).ready(function(){

            $('#table-list').DataTable({
                "lengthMenu": [[25,50, 100], [25,50,100]],
                "oLanguage": {
                    "sSearch":       "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                },
                "bPaginate":false,
                'responsive':true,
                aaSorting:[[5,"asc"]] // shows the newest items first
            });

            $('#changePaymentPeriod').on('submit',function(event){
                event.preventDefault();
                var period = $('#PeriodSelect option:selected').text();
                window.location = window.location.href.split('?')[0]+"?period="+period;
            });

            var caller = canCall();

            $('.clientPhone').each(function () {
                var td = $(this);
                var clientid = td.data('client-alias-id');
                $.get(api_address+'ClientAlias('+clientid+')?$select=PhoneNumber')
                    .success(function (data) {
                      td.html( caller ? "<span class='flexfone pseudolink'>"+data.PhoneNumber+"</span>"  : "<a href='tel:"+data.PhoneNumber+"'>"+data.PhoneNumber+"</a>"     )
                    })
            });

            var statsRow = $('.netAndCommission').clone();
            $('#table-list').find('thead').prepend(statsRow);

            $('.alert-success').append('<br>'+Number($('#successPayments').val()).format(true) +' DKK');
            $('.alert-warning').append('<br>'+Number($('#warningPayments').val()).format(true) +' DKK');
            $('.alert-danger').append('<br>'+Number($('#dangerPayments').val()).format(true) +' DKK');

            $('.ResendInvoice').on('click',function(event){
                event.preventDefault();
                var button = $(event.target);
                button.css('pointer-events','none');
                var id = button.data('invoice-id');
                    $.post(api_address+'Invoices('+id+')/action.SendInvoice').success(function(){
                        new PNotify({
                            title:Lang.get('labels.invoice-was-sent')
                        });
                        button.css('pointer-events','');

                    }).error(function(xhr,error,status){
                        button.css('pointer-events','');
                    });
            });

            $('.invoiceAppointment').on('click',function(event){
                event.preventDefault();
                var button = $(event.target);
                var id = button.data('invoice-id');
                var modal = getDefaultModal();
                modal.find('.modal-title').append(Lang.get('labels.create-event'));
                modal.find('.modal-body').loadTemplate(
                    base_url+"/templates/calendar/addEventForm.html",
                    {
                        Model:"Invoice",
                        ModelId:id,
                        TypeLabel  : Lang.get('labels.type'),
                        EventTypes : ['Payment',"FollowUp",'Appointment','Meeting'],
                        SummaryLabel : Lang.get('labels.summary'),
                        DescriptionLabel:Lang.get('labels.description'),
                        TimeLabel : Lang.get('labels.time'),
                        AttendeesLabel: Lang.get('labels.attendees'),
                        CreateLabel:Lang.get('labels.create'),
                        OptionsLabel:Lang.get('labels.options'),
                        NotifyAttendeesLabel:Lang.get('labels.notify-attendees'),
                        CreateOnGoogleLabel:Lang.get('labels.create-on-google')
                    },
                    {
                        overwriteCache:true,
                        success:function(){
                            $('#event-time').daterangepicker(
                                {
                                    minDate:moment(),
                                    timePicker: true,
                                    "timePicker24Hour": true,
                                    locale: {
                                        format: 'YYYY-MM-DD H:mm'
                                    }
                                }
                            );
                        }
                    }
                );
            });

            $('#User_Id').on('change',function (event) {
                var val = $(event.target).val();
                window.location = base_url+'/statistics/debt-collection/'+val;
            })
        })
    </script>
@stop

@section('content')
    <div class="row">
        <div class="panel panel-green">
            <div class="panel-heading">
                <h4>@lang('labels.missing-payments') : {{$user->FullName or ""}}</h4>
            </div>

            <div class="panel-body">
                <div class="row">
                   <!-- <div class="col-md-12">
                        <div style="margin: 0 auto;">-->
                            <div class="col-md-3">
                                <div class="alert alert-danger">@lang('messages.debt-collection-or-overdue-by-more-than-month')</div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('User_Id','Select Seller',['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::select('User_Id',withEmpty($users,'See All'),$userId,['class'=>'form-control']) !!}
                                    </div>
                                </div>
<!--                            </div>
                        </div>
                        <div style="clear: both;"></div>-->
                    </div>
                </div>
                <table class="table datatables table-hover" width="100%" id="table-list">
                        @if(isset($payments))
                            <thead>
                            <tr>
                                <th>@lang('labels.invoice-number')</th>
                                <th>@lang('labels.client')</th>
                                <th>Phone</th>
                                <th>@lang('labels.total-net-amount')</th>
                                <th>@lang('labels.commission')</th>
                                <th>@lang('labels.created-date')</th>
                                <th>@lang('labels.due-date')</th>
                                <th>@lang('labels.status')</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                $totalNet = 0;
                                $totalCommission = 0;
                                $totalPayments = 0;
                                $success = 0;
                                $warning = 0;
                                $danger  = 0;

                            ?>
                                @foreach($payments as $payment)
                                    <?php
                                        $totalPayments++;
                                        $totalCommission += $payment->Commission;
                                        $totalNet        += $payment->NetValue;
                                        switch ($payment->Class){
                                            case "success":
                                                $success += $payment->NetValue;
                                                break;
                                            case "warning":
                                                $warning += $payment->NetValue;
                                                break;
                                            case "danger":
                                                $danger += $payment->NetValue;
                                                break;
                                            default:
                                                break;
                                        }

                                    ?>

                                    <tr class="{{$payment->Class}}">
                                        <td>
                                            <a href="{{url('invoices/show',$payment->Invoice_Id)}}">{{$payment->InvoiceNumber or "--"}}</a>
                                        </td>
                                        <td>
                                            <a href="{{url('clientAlias/show',$payment->ClientAlias_Id)}}">{{$payment->DebtorName or "--"}}</a>
                                        </td>
                                        <td class="clientPhone" data-client-alias-id="{{$payment->ClientAlias_Id}}">
    {{--                                        {{$payment->PhoneNumber or "Missing phone"}}--}}
                                        </td>
                                        <td data-order="{{$payment->NetValue}}">{{formatMoney($payment->NetValue)}}</td>
                                        <td data-order="{{$payment->Commission}}">{{formatMoney($payment->Commission)}}</td>
                                        <td data-order="{{strtotime($payment->Created)}}">{{date('d-m-Y',strtotime($payment->Created))}}</td>
                                        <td data-order="{{strtotime($payment->DueDate)}}">{{date('d-m-Y',strtotime($payment->DueDate))}}</td>
                                        <td>{{$payment->Status}}</td>
                                        <td>
                                            <i title='Quick comment' data-client-id='{{$payment->ClientAlias_Id}}' class='fa fa-comment quickClientComment'></i>
                                            /
                                            <i title='Resend invoice' data-invoice-id='{{$payment->Invoice_Id}}' class='fa fa-envelope ResendInvoice'></i>
                                            /
                                            <i title='Create appointment for this payment' data-invoice-id='{{$payment->Invoice_Id}}' class='fa fa-calendar invoiceAppointment'></i>
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif(isset($total))
                                    <thead>
                                    <tr>
                                        <th>@lang('labels.invoice-number')</th>
                                        <th>@lang('labels.client')</th>
                                        <th>Phone</th>
                                        <th>@lang('labels.total-net-amount')</th>
                                        <th>@lang('labels.created-date')</th>
                                        <th>@lang('labels.due-date')</th>
                                        <th>@lang('labels.status')</th>
                                        <th>Invoice Seller</th>
                                        <th>Client Seller</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                            <?php
                            $totalNet = 0;
                            $totalPayments = 0;
                            $success =0;
                            $warning = 0;
                            $danger  = 0;

                            ?>
                            @foreach($total as $payment)
                                <?php
                                    $totalPayments++;
                                    $totalNet += $payment->NetAmount;
                                    switch ($payment->Class){
                                        case "success":
                                            $success += $payment->NetAmount;
                                        break;
                                        case "warning":
                                            $warning += $payment->NetAmount;
                                            break;
                                        case "danger":
                                            $danger += $payment->NetAmount;
                                            break;
                                        default:
                                            break;
                                    }
                                ?>

                                <tr class="{{$payment->Class}}">
                                    <td>
                                        <a href="{{url('invoices/show',$payment->Id)}}">{{$payment->InvoiceNumber or "--"}}</a>
                                    </td>
                                    <td>
                                        <a href="{{url('clientAlias/show',$payment->ClientAlias_Id)}}">{{$payment->ClientAlias->Name or $payment->Name}}</a>
                                    </td>
                                    <td class="clientPhone" data-client-alias-id="{{$payment->ClientAlias_Id}}">
                                    </td>
                                    <td data-order="{{$payment->NetAmount}}">{{formatMoney($payment->NetAmount)}}</td>
                                    <td data-order="{{strtotime($payment->Created)}}">{{date('d-m-Y',strtotime($payment->Created))}}</td>
                                    <td data-order="{{strtotime($payment->Due)}}">{{date('d-m-Y',strtotime($payment->Due))}}</td>
                                    <td>{{$payment->Status}}</td>
                                    <td>{{$payment->User->FullName or ""}}</td>
                                    <td>{{$payment->ClientAlias->User->FullName or ""}}</td>
                                    <td>
                                        <i title='Quick comment' data-client-id='{{$payment->ClientAlias_Id}}' class='fa fa-comment quickClientComment'></i>
                                        /
                                        <i title='Resend invoice' data-invoice-id='{{$payment->Id}}' class='fa fa-mail-forward ResendInvoice'></i>
                                        /
                                        <i title='Create appointment for this payment' data-invoice-id='{{$payment->Id}}' class='fa fa-calendar invoiceAppointment'></i>
                                    </td>
                                </tr>
                            @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="netAndCommission">
                                <td>
                                    @lang('labels.total') : {{$totalPayments}}
                                </td>
                                <td></td>
                                <td></td>
                                <td>
                                    @lang('labels.total-net-amount') : {{formatMoney($totalNet)}}
                                </td>
                                <td>
                                    @if(isset($totalCommission))
                                        @lang('labels.total') @lang('labels.commission') : {{formatMoney($totalCommission)}}
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <input  type="hidden" id="warningPayments" value="{{$warning or 0}}">
                    <input  type="hidden" id="dangerPayments" value="{{$danger or 0}}">
                    <input  type="hidden" id="successPayments" value="{{$success or 0}}">
                </div>
        </div>
    </div>
@stop