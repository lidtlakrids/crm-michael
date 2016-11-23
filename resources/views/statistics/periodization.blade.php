@extends('layout.main')
@section('page-title',"Periodization")

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script type="text/javascript">
        $(document).ready(function () {
            var date = new Date();
            var month = date.toISOString();
            periodizationByMonth(month)
        });

        $('#periodizationDate').datepicker( {
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'MM yy',
            onClose: function(dateText, inst) {
                var placeholder = $('.periodizationTablePlaceholder');
                placeholder.empty();
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                periodizationByMonth(getIsoDate(new Date(inst.selectedYear,inst.selectedMonth,2,0,0,0)))
            }
        });

        function periodizationByMonth(month) {
            var placeholder = $('.periodizationTablePlaceholder');
            placeholder.addClass('spinner');
            placeholder.loadTemplate(base_url+'/templates/periodization/table.html',{},{success:function () {
                $.ajax({
                    type: "POST",
                    url: api_address + 'Statistics/Periodization',
                    data:JSON.stringify({Date:month}),
                    success: function (data) {
                        $('#periodizationTable').DataTable({
                            responsive:true,
                            "lengthMenu": [[20,50,100,-1], [20,50,100,'all']],
                            aaSorting:[[4,"asc"]], // shows the newest items first
                            data:data.value,
                            "columns": [
                                { "data": "DebtorName" },
                                { "data": "ProductName" },
                                { "data": "InvoiceNumber" },
                                { "data": "Date",mRender:function (date) {
                                    return moment(date).format('YYYY-MM-DD');
                                } },
                                { "data": "Amount",mRender:function (amount) {
                                    return Number(amount).toFixed(2);
                                }},
                                {"data":"DaysLeft"
                                }
                            ],createdRow: function( row, data, dataIndex ) {
                                $( row ).find('td:eq(4)').attr('data-order',data.Amount);
                            }
                        });
                        placeholder.removeClass('spinner');
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            }})

        }
    </script>
@stop

@section('content')
    <div class="row">
        <div class="panel panel-sales">
            <div class="panel-heading">
                <h4>Periodization</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <!--<label for="periodizationDate">Date :</label>-->
                        <input name="periodizationDate" id="periodizationDate" class="date-picker form-control" placeholder="Date" />
                    </div>
                </div>
                <hr />
                <div class="periodizationTablePlaceholder"></div>
            </div>
        </div>
    </div>
@stop