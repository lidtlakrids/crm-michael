@extends('layout.main')
@section('page-title',Lang::get('labels.overdue-invoices'))
@section('scripts')
    @include('scripts.dataTablesScripts')


    <script>
        $(document).ready(function(){
            $('#table-list').DataTable({
                bPaginate: false
            })
        })

    </script>

@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-contract">
                <div class="panel-heading">
                    <h4><i class="fa fa-money"></i> @lang('labels.overdue-invoices')</h4>
                    <div class="info-bar"></div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-12">
                            <div style="margin: 0 auto;">
                                <div class="alert alert-warning col-md-3">@lang('messages.reminder-or-overdue-by-less-than-month')</div>
                                <div class="alert alert-danger col-md-3">@lang('messages.debt-collection-or-overdue-by-more-than-month')</div>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="table-list" class="table table table-hover datatables" width="100%">
                            <thead>
                            <tr>
                                <th>@lang('labels.invoice-number')</th>
                                <th>@lang('labels.name')</th>
                                <th>@lang('labels.status')</th>
                                <th>@lang('labels.created-date')</th>
                                <th>@lang('labels.due-date')</th>
                                <th>@lang('labels.total-net-amount')</th>
                                <th>@lang('labels.commission')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($overdues as $inv)
                                <tr class="{{$inv->Class}}">
                                    <td><a href="{{url('invoices/show',$inv->Invoice_Id)}}">{{$inv->InvoiceNumber or "--"}}</a></td>
                                    <td><a href="{{url('clientAlias/show',$inv->ClientAlias_Id)}}">{{$inv->DebtorName or "--"}}</a></td>
                                    <td>{{$inv->Status or "-"}}</td>
                                    <td>{{date('d-m-Y',strtotime($inv->Created))}}</td>
                                    <td>{{date('d-m-Y',strtotime($inv->DueDate))}}</td>
                                    <td>{{formatMoney($inv->NetValue)}}</td>
                                    <td>{{formatMoney($inv->Commission)}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

