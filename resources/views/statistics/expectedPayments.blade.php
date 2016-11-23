@extends('layout.main')
@section('page-title','Expected Payments')

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){

            $('.expectedPaymentsResults').DataTable({
                'bFilter':false,
                bPaginate:false,
                bInfo:false,
            });

            $('#period-time').daterangepicker(
                {
                    timePicker: true,
                    "timePicker24Hour": true,
                    locale: {
                        format: 'YYYY-MM-DD H:mm'
                    }
                }
            );
            $('#expectedPayments').on('submit',function (event) {
                event.preventDefault();
            });

            $('.expectedPaymentsResultSwitch').on('click',function (event) {
                var data = $(event.target).closest('.expectedPaymentsResultSwitch').data();
//                if(data.period) window.open(base_url+'/statistics/expected-payments/'+data.period,'_blank');
                $('.expectedPaymentsResults').hide();
                $('#'+data.period).removeClass('hidden').show();
            })
        })
    </script>

@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-apple"></i>Stat</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p>Net Amounts, without MOMS, in Danish Kroner (DKK)</p> <br>
                            <p>Amounts for Drafts are taken based on the Notice Accountant Date</p><br>
                            <p>Amounts for Invoices are taken based on the Due Date</p>
                        </div>
                        <div class="col-md-4">
                            <form method="get">
                                <div class="form-group">
                                    <div class="col-md-4">
                                    {!! Form::select('user',withEmpty($sellers,'Select Seller'),$user,['class'=>'form-control']) !!}
                                    </div>
                                </div>
                                <div class="btn-toolbar">
                                    {!!  Form::submit('Go',['class'=>'btn btn-green'])!!}
                                </div>
                            </form>
                        </div>
                    </div>
                    <hr />

                    @if($month == null)
                    <div class="table-responsive">
                        <table class="table datatables" id="table-list">
                            <thead>
                                <tr>
                                    <th>Summary for next 12 months</th>
                                  @foreach($periods as $k=>$period)
                                      <th>{{date('Y-M',strtotime($k))}}</th>
                                  @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="summary section-one">
                                    <td>By month</td>
                                    @foreach($periods as $period=>$stat)
                                        <td>
                                            <span class="pseudolink expectedPaymentsResultSwitch"  data-period="{{$period}}" >
                                                <span>Drafts : {{formatMoney($stat['draftSum'])}} </span><br>
                                                <span>Invoices : {{formatMoney($stat['invoiceSum'])}} </span><br>
                                                <span>Total : {{formatMoney($stat['draftSum']+$stat['invoiceSum'])}}</span>
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        @foreach($periods as $period=>$values)
                            <div class="table-responsive">
                                <table id="{{$period}}" class="table table-condensed expectedPaymentsResults hidden" style="font-size:12px">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Company</th>
                                            <th>Amount</th>
                                            <th>Seller</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($values['drafts'] as $draft)
                                            <tr>
                                                <td><a target="_blank" href="{{url('drafts/show',$draft->Id)}}">Draft {{$draft->Id}}</a></td>
                                                <td>{{$draft->ClientAlias->Name or ''}}</td>
                                                <td data-order="{{draftLinesSum([$draft])}}">{{formatMoney(draftLinesSum([$draft]))}}</td>
                                                <td>{{$users[$draft->User_Id]}}</td>
                                                <td>{{toDate($draft->NoticeAccountant)}}</td>
                                            </tr>
                                        @endforeach
                                        @foreach($values['invoices'] as $invoice)
                                            <tr>
                                                <td><a target="_blank" href="{{url('invoices/show',$invoice->Id)}}">Invoice {{$invoice->Id}}</a></td>
                                                <td>{{$invoice->ClientAlias->Name or ''}}</td>
                                                <td data-order="{{$invoice->NetAmount}}">{{formatMoney($invoice->NetAmount)}}</td>
                                                <td>{{$users[$invoice->User_Id]}}</td>
                                                <td>{{toDate($invoice->Created)}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                    @else
                        <div class="row">
                            <div class="col-md-3">
                                  <span class="pseudolink" >
                                                <span>Drafts : {{formatMoney(end($periods)['draftSum'])}} </span><br>
                                                <span>Invoices : {{formatMoney(end($periods)['invoiceSum'])}} </span><br>
                                                <span>Total : {{formatMoney(end($periods)['draftSum']+end($periods)['invoiceSum'])}}</span>
                                        </span>
                            </div>
                        </div>

                        <div class="row">
                            @foreach($periods as $period=>$values)
                                <div class="table-responsive">
                                    <table id="{{$period}}" class="table table-condensed expectedPaymentsResults" style="font-size:12px">
                                        <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Company</th>
                                            <th>Amount</th>
                                            <th>Seller</th>
                                            <th>Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($values['drafts'] as $draft)
                                            <tr>
                                                <td><a target="_blank" href="{{url('drafts/show',$draft->Id)}}">Draft {{$draft->Id}}</a></td>
                                                <td>{{$draft->ClientAlias->Name or ''}}</td>
                                                <td data-order="{{draftLinesSum([$draft])}}">{{formatMoney(draftLinesSum([$draft]))}}</td>
                                                <td>{{$users[$draft->User_Id]}}</td>
                                                <td>{{toDate($draft->NoticeAccountant)}}</td>
                                            </tr>
                                        @endforeach
                                        @foreach($values['invoices'] as $invoice)
                                            <tr>
                                                <td><a target="_blank" href="{{url('invoices/show',$invoice->Id)}}">Invoice {{$invoice->Id}}</a></td>
                                                <td>{{$invoice->ClientAlias->Name or ''}}</td>
                                                <td data-order="{{$invoice->NetAmount}}">{{formatMoney($invoice->NetAmount)}}</td>
                                                <td>{{$users[$invoice->User_Id]}}</td>
                                                <td>{{toDate($invoice->Created)}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop




