@extends('layout.main')
@section('page-title','Stat '.$type)

@section('styles')
@stop

@section('scripts')
    @include('scripts.statistics-scripts')
    @include('scripts.dataTablesScripts')
    <script type="text/javascript">
        $(document).ready(function () {
            switch (type) {
                case "invoiced-sent":
                    $.when(sentAmountsByMonth(month))
                        .then(function (data) {
                            loadInvoicesResult(data);
                        });
                    break;
                case "paid":
                    $.when(paidAmountsByMonth(month))
                        .then(function (data) {
                            loadInvoicesResult(data);
                        });
                    break;
                case "invoiced-overdue":
                    $.when(overdueAmountsByMonth(month))
                            .then(function (data) {
                                loadInvoicesResult(data);
                            });
                    break;

                case 'newsales':
                    $.when(newAndReSalesPerMonth(month,false))
                        .then(function (data) {
                            var result = {};
                            if(data.value){
                                result.orderIds = [];
                                result['New Sales Value'] = 0;
                                result['New Sales Count'] = 0;

                                $.each(data.value,function (index,val) {
                                    $.merge(result.orderIds,val.NewSalesOrderId);
                                    result['New Sales Value'] += val.NewSalesValue;
                                    result['New Sales Count'] += val.NewSalesCount;
                                })
                            }

                            loadOrdersResult(result);
                        });
                break;

                case 'resales':
                    $.when(newAndReSalesPerMonth(month,false))
                            .then(function (data) {
                                var result = {};
                                if(data.value){
                                    result.orderIds      = [];
                                    result['Resales Value'] = 0;
                                    result['Resales Count'] = 0;

                                    $.each(data.value,function (index,val) {
                                        $.merge(result.orderIds,val.ReSalesOrderId);
                                        $.merge(result.orderIds,val.UpSaleOrderId);
                                        result['Resales Value'] += (val.ReSalesValue + val.UpSaleValue);
                                        result['Resales Count'] += (val.ReSalesCount + val.UpSaleCount);

                                    })
                                }
                                loadOrdersResult(result);
                            });
                    break;

                case 'total-sales':
                    $.when(newAndReSalesPerMonth(month,false))
                            .then(function (data) {
                                var result = {};
                                if(data.value){
                                    result.orderIds      = [];
                                    result['Resales Value'] = 0;
                                    result['Resales Count'] = 0;
                                    result['New Sales Value'] = 0;
                                    result['New Sales Count'] = 0;

                                    $.each(data.value,function (index,val) {
                                        $.merge(result.orderIds,val.ReSalesOrderId);
                                        $.merge(result.orderIds,val.UpSaleOrderId);
                                        $.merge(result.orderIds,val.NewSalesOrderId);
                                        result['Resales Value'] += (val.ReSalesValue + val.UpSaleValue);
                                        result['Resales Count'] += (val.ReSalesCount + val.UpSaleCount);
                                        result['New Sales Value'] += val.NewSalesValue;
                                        result['New Sales Count'] += val.NewSalesCount;
                                    })
                                }

                                loadOrdersResult(result);
                            });
                    break;

//                case  'expected':
//                    $.when(expectedAmountsByMonth(month))
//                            .then(function (data) {
//                                console.log(data);
//                                var grouped =
//                                //loadInvoicesResult(data);
//                            });
//                    break;
                default:
                    break;

            }

        });
        resultsPlaceholder = $('.results-placeholder');

        function loadOrdersResult(data) {

            var query =  '( Id eq '+data.orderIds.join(' or Id eq ')+")";
            delete(data.orderIds);

            resultsPlaceholder.loadTemplate(base_url + '/templates/statistics/ordersTable.html', {}, {
                success: function () {
                    var table2 = $('#ordersStatsTable').DataTable({
                        "bPaginate": false,
                        'bInfo': false,
                        'bFilter': false,
                        "iDisplayLength": "All",
                        responsive: true,
                        stateSave: true,
                        aaSorting: [[2, "desc"]], // shows the newest items first
                        "bProcessing": true,
                        "bServerSide": true,
                        "deferRender": true, // testing if speed is better with this
                        "sAjaxSource": api_address+"Orders?$expand=User($select=Id,FullName),ClientAlias($select=Name,Id),OrderProduct($select=Id;$expand=Product($select=Name)),OrderProductPackage($select=Id;$expand=ProductPackage($select=Name))",
                        'filter' : "Created ne null and "+query,
                        "select":"ArchivedDate",
                        "fnRowCallback":function (row,data) {
                            if(data.ArchivedDate){
                                $(row).addClass('danger').prop('title','Order is archived');
                            }
                        },
                        "aoColumns": [
                            {
                                mData: 'Id', mRender: function (obj) {
                                return "<a target='_blank' href='" + linkToItem('Order', obj, true) + "'>" + obj + '</a>';
                            }
                            },
                            { mData:null,oData:"ClientAlias/Name",mRender:function (obj) {
                                return "<a target='_blank' href='"+base_url+"/clientAlias/show/"+obj.ClientAlias.Id+"'>"+obj.ClientAlias.Name+"</a>";
                            }
                            },{
                                mData:"Domain"
                            },
                            {
                                mData: null, oData:"User/FullName", mRender: function(obj){
                                return "<a target='_blank' href='" + linkToItem('User', obj.User.Id, true) + "'>" + obj.User.FullName + '</a>';
                            }
                            },
                            {
                                mData: "Created", sType: 'date', mRender: function (obj) {
                                return new Date(obj).toDateTime();
                            }
                            },
                            {
                                mData: "ConfirmedDate", sType: 'date', mRender: function (obj) {
                                if (obj != null) {
                                    return new Date(obj).toDateTime();
                                } else {
                                    return '';
                                }
                            }
                            },
                            {
                                mData: null, oData: null, mRender: function (obj) {
                                var products = '';
                                var prPackages = '';

                                if(obj.OrderProduct.length > 0){
                                    $.each(obj.OrderProduct, function(index, element) {

                                        products += element.Product.Name + "</br>";
                                    });
                                }
                                if (obj.OrderProductPackage.length > 0){
                                    $.each(obj.OrderProductPackage, function(index, element) {
                                        prPackages += element.ProductPackage.Name + "</br>";
                                    });
                                }
                                return products + " " + prPackages;
                            }
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    })
                }
            });

            $.each(data,function (index,value) {
                console.log(index,value);
                    var info ={Total:Number(value).format(true)};
                    switch (index){
                        case 'New Sales Value':
                            info.Message = 'New Sales Value';
                            info.Classes = 'alert alert-success col-md-3';
                            break;
                        case 'New Sales Count':
                            info.Message = 'New Sales Count';
                            info.Classes = 'alert alert-success col-md-3';
                            break;
                        case 'Resales Value':
                            info.Message = 'Re Sales Value';
                            info.Classes = 'alert alert-success col-md-3';
                            break;
                        case 'Resales Count':
                            info.Message = 'Re Sales Count';
                            info.Classes = 'alert alert-success col-md-3';
                            break;
                        default:
                            info.Message = 'no idea';
                            info.Classes = 'alert alert-success col-md-3';
                            break;
                    }
                    $('.totalsResults').loadTemplate(base_url+'/templates/statistics/totalsBox.html',info,{overwriteCache:true,append:true})

            });

                resultsPlaceholder.removeClass('spinner')

        }


        function loadInvoicesResult(data) {
            totals = {'success':0,'warning':0,'danger':0,'info':0};

            resultsPlaceholder.loadTemplate(base_url+'/templates/statistics/invoicesStatsTable.html',{},{
                success:function () {

                    $('#invoicesStatsTable').DataTable(
                        {
                            responsive:true,
                            "lengthMenu": [[20,50,100,-1], [20,50,100,'all']],
                            aaSorting:[[4,"asc"]], // shows the newest items first
                            data:data.value,
                            columns: [
                                { mRender:function (a,display,obj) {
                                    return "<a target='_blank' href='"+linkToItem('Invoice',obj.Id,true)+"'>"+obj.InvoiceNumber+"</a>";
                                } },
                                {mRender:function (a,b,obj) {
                                    return obj.Name;
                                }},
                                {mRender:function (a,b,obj) {
                                    return Number(obj.NetAmount).toFixed(2)
                                }},
                                {mRender:function (a,b,obj) {
                                    var date = new Date(obj.Created);
                                    return date.toDate();
                                }},
                                {mRender:function (a,b,obj) {
                                    var date = new Date(obj.Due);
                                    return date.toDate();
                                }},
                                {mRender:function (a,b,obj) {
                                    if(obj.Payed){
                                        var date = new Date(obj.Payed);
                                        return date.toDate();
                                    }else{
                                        return '';
                                    }
                                }},
                                {mRender:function (a,b,obj) {
                                    return obj.Status;
                                }},
                                {mRender:function (a,b,obj) {
                                    var user = obj.User? obj.User.FullName : "";
                                    return user;
                                }}
                            ],
                            createdRow: function( row, data, dataIndex ) {
                                var today = moment().utc().startOf('day');
                                var toBeHandled = moment(data.Due).utc().startOf('day');
                                var $daysoverdue = toBeHandled.diff(today,'days');
                                var cssClass = "";
                                //determine the color of the payment
                                switch (data.Status){
                                    case "Sent":
                                    case "Created":
                                        cssClass = $daysoverdue < 0 ? "warning":"success";
                                        break;
                                    case "Overdue" :
                                        cssClass = $daysoverdue < -30 ? "danger": "warning";
                                        break;
                                    case "Paid":
                                        cssClass = 'success';
                                        break;
                                    case  "Reminder" :
                                    case "DebtCollection":
                                        cssClass='danger';
                                        break;
                                    default:
                                        cssClass = "warning";
                                        break;
                                }
                                totals[cssClass] += Number(data.NetAmount);
                                $(row).addClass(cssClass);
                                $( row ).find('td:eq(2)').attr('data-order',data.NetAmount);
                                $( row ).find('td:eq(3)').attr('data-order',moment(data.Created));
                                $( row ).find('td:eq(4)').attr('data-order',moment(data.Due));
                                $( row ).find('td:eq(5)').attr('data-order',moment(data.Payed));
                            },
                            "initComplete": function(settings, json) {
                                $.each(totals,function (index,value) {
                                    if(parseInt(value) > 0){
                                        var info ={Total:Number(value).format(true)};
                                        switch (index){
                                            case 'success':
                                                info.Message = 'Everything ok';
                                                info.Classes = 'alert alert-success col-md-3';
                                                break;
                                            case 'danger':
                                                info.Message = 'Debt collection and/or overdue by more than one month';
                                                info.Classes = 'alert alert-danger col-md-3';
                                                break;
                                            case 'warning':
                                                info.Message = 'Reminder and/or overdue by less than a month';
                                                info.Classes = 'alert alert-warning col-md-3';
                                                break;
                                            default:
                                                info.Message = 'no idea';
                                                info.Classes = 'alert alert-info col-md-3';
                                                break;
                                        }
                                        $('.totalsResults').loadTemplate(base_url+'/templates/statistics/totalsBox.html',info,{overwriteCache:true,append:true})
                                    }
                                })
                            }
                        });
                    resultsPlaceholder.removeClass('spinner')
                }
            })
        }

    </script>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-orange">
            <div class="panel-heading">
                <h4>Results</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12 totalsResults">

                        <div style="clear: both;"></div>
                    </div>
                </div>
                <div class="results-placeholder spinner">

                </div>
            </div>
        </div>
    </div>
</div>

@stop