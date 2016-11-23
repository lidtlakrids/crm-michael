@extends('layout.main')
@section('page-title',Lang::get('labels.payments'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')


<script>
    $(document).ready(function(){

        $('#table-list').DataTable({
            "lengthMenu": [[25,50, 100], [25,50,100]],
            "bPaginate":false,
            "oLanguage": {
                "sSearch":       "",
                "sSearchPlaceholder": Lang.get('labels.search')
            }
        });

        var statsRow = $('.netAndCommission').clone();
        $('#table-list').find('thead').prepend(statsRow);

        $('#changePaymentPeriod').on('submit',function(event){
            event.preventDefault();
            var period = $('#PeriodSelect option:selected').text();
            var uId = $('#UserSelect option:selected').val() || '';
            window.location = base_url+'/payments/'+uId+"?period="+period;
        })
    })
</script>
@stop

@section('content')
    <div class="row">
        <div class="panel panel-green">
            <div class="panel-heading">
                <h4>@lang('labels.payments') @if($user !== null) : {{$user->FullName}} @endif</h4>
            </div>

            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>@lang('labels.period') {{$period['start']}} -> {{$period['end']}}</h4>
                        <br />
                        {!! Form::open(['id'=>'changePaymentPeriod']) !!}
                        <div class="form-inline">
                            {!!  Form::select('Period',withEmpty($periods),$period, ['class' => 'form-control','required'=>'required','id'=>'PeriodSelect']) !!}
                            @if(isAdmin())
                                {!!  Form::select('User',withEmpty($sellers),null, ['class' => 'form-control','id'=>'UserSelect']) !!}
                            @endif
                            <input class="btn btn-green form-control" id="changePeriod" type="submit" value="Go">
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table datatables" id="table-list">
                        <thead>
                            <tr>
                                <th>@lang('labels.invoice-number')</th>
                                <th>@lang('labels.client')</th>
                                <th>@lang('labels.total-net-amount')</th>
                                <th>@lang('labels.commission')</th>
                                <th>@lang('labels.created-date')</th>
                                <th>@lang('labels.due-date')</th>
                                <th>@lang('labels.pay-date')</th>
                                @if(isset($data)) <th>Seller</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $totalNet = 0;
                            $totalCommission = 0;
                            $totalPayments = 0;
                        ?>
                        @if(isset($payments))
                                @foreach($payments as $payment)
                                    <?php
                                        $totalPayments++;
                                        $totalCommission += $payment->Commission;
                                        $totalNet        += $payment->NetValue;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="{{url('invoices/show',$payment->Invoice_Id)}}">{{$payment->InvoiceNumber or "--"}}</a>
                                        </td>
                                        <td>
                                            <a href="{{url('clientAlias/show',$payment->ClientAlias_Id)}}">{{$payment->DebtorName or "--"}}</a>
                                        </td>
                                        <td data-order="{{$payment->NetValue}}">{{formatMoney($payment->NetValue)}}</td>
                                        <td data-order="{{$payment->Commission}}">{{formatMoney($payment->Commission)}}</td>
                                        <td>{{date('d-m-Y',strtotime($payment->Created))}}</td>
                                        <td>{{date('d-m-Y',strtotime($payment->DueDate))}}</td>
                                        <td>{{date('d-m-Y',strtotime($payment->PayDate))}}</td>
                                    </tr>
                                @endforeach
                            @elseif(isset($data))
                                @foreach($data as $uId=>$payments)
                                    @foreach($payments as $payment)
                                        <?php
                                        $totalPayments++;
                                        $totalCommission += $payment->Commission;
                                        $totalNet        += $payment->NetValue;
                                        ?>

                                        <tr>
                                            <td>
                                                <a href="{{url('invoices/show',$payment->Invoice_Id)}}">{{$payment->InvoiceNumber or "--"}}</a>
                                            </td>
                                            <td>
                                                <a href="{{url('clientAlias/show',$payment->ClientAlias_Id)}}">{{$payment->DebtorName or "--"}}</a>
                                            </td>
                                            <td data-order="{{$payment->NetValue}}">{{formatMoney($payment->NetValue)}}</td>
                                            <td data-order="{{$payment->Commission}}">{{formatMoney($payment->Commission)}}</td>
                                            <td>{{date('d-m-Y',strtotime($payment->Created))}}</td>
                                            <td>{{date('d-m-Y',strtotime($payment->DueDate))}}</td>
                                            <td>{{date('d-m-Y',strtotime($payment->PayDate))}}</td>
                                            <td>{{$sellers[$uId]}}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="netAndCommission">
                                <td>
                                    @lang('labels.total') : {{$totalPayments or 0}}
                                </td>
                                <td></td>
                                <td>
                                    @lang('labels.total-net-amount') : {{formatMoney($totalNet)}}
                                </td>
                                <td>
                                    @lang('labels.total') @lang('labels.commission') : {{formatMoney($totalCommission)}}
                                </td>
                                @if($user != null && $user->SalaryGroup != null)
                                    <td>
                                    @if($totalCommission < $user->SalaryGroup->MinimumTurnover)
                                        Commission needed for a bonus : {{formatMoney($user->SalaryGroup->MinimumTurnover - $totalCommission)}}
                                    @else
                                        This months bonus : {{calculateSalaryBonus($totalCommission,$user->SalaryGroup)}}
                                    @endif
                                    </td>
                                @endif

                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop