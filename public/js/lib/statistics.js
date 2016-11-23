
function paidAmounts(months) {
    $.each(months,function (index,val) {
        paidAmountsByMonth(val);
    })
}

function sentAmounts(months) {
    $.each(months,function (index,val) {
        sentAmountsByMonth(val);
    })
}

function overdueAmounts(months) {
    $.each(months,function (index,val) {
        overdueAmountsByMonth(val);
    })
}

function debtCollectionAmounts(months) {
    $.each(months,function (index,val) {
        debtCollectionAmountsByMonth(val);
    })
}
function lostClients(months) {
    $.each(months,function (index,val) {
        lostClientsByMonth(val);
    })
}

function salesStats(months) {
    $.each(months,function (index,val) {
        newAndReSalesPerMonth(val);
    })
}

function expectedPayments() {
    $.get(base_url+'/statistics/expected-payments')
        .success(function(data){
            $.each(data,function (index,value) {
                if($('#expected-'+index).length > 0) {
                    var total = value.draftSum + value.invoiceSum;
                    var count = value.drafts.length + value.invoices.length;
                    $('#expected-'+index).html('<span>Count: '+count+'</span><br><span> Amount: '+Number(total).format()+'</span>');
                }
            });
        })
}

function expectedAmountsByMonth(month) {
    return $.get(base_url+'/statistics/expected-payments/'+month)
        .success(function(data){

            });
}

function meetingsStats() {
    $.get(base_url+'/statistics/meetings-by-year')
        .success(function (data) {
            $.each(data,function (index,value) {
                if($('#meetings-'+index).length > 0) {
                    $('#meetings-'+index).text(Number(value));
                }
            });
        });
}

function paidAmountsByMonth(month) {
    var date  = new Date(month), y = date.getFullYear(), m = date.getMonth();
    var start = getIsoDate(new Date(y, m, 1));
    var end   = getIsoDate(new Date(y, m + 1, 0));

    return $.get(api_address+"Invoices?$filter=Type eq 'Invoice' and Status eq 'Paid' and (Payed le "+end+' and Payed ge '+start+')&&$select=NetAmount,Id,Name,Created,Due,Type,Status,InvoiceNumber,Payed&$expand=User($select=FullName)')
        .success(function (data) {
            var count = data.value.length;
            var amount  = data.value.reduce(function (prev,current) {
                return prev+current.NetAmount;
            },0);
            $('#paid-'+month).html('<span>Count: '+count+'</span><br><span> Amount: '+Number(amount).format()+'</span>')
        })
}

function sentAmountsByMonth(month) {
    var date  = new Date(month), y = date.getFullYear(), m = date.getMonth();
    var start = getIsoDate(new Date(y, m, 1));
    var end   = getIsoDate(new Date(y, m + 1, 0));

   return $.get(api_address+"Invoices?$filter=(Type eq 'Invoice' or Type eq 'CreditNote') and (Status ne webapi.Models.InvoiceStatus'FakeInvoice') " +
        "and (Created le "+end+' and Created ge '+start+')&$select=NetAmount,Id,Name,Created,Due,Type,Status,InvoiceNumber,Payed&$expand=User($select=FullName)')
        .success(function (data) {
            var count = data.value.length;
            var amount  = data.value.reduce(function (prev,current) {
                return prev+current.NetAmount;
            },0);
            $('#invoiced-sent-'+month).html('<span>Count: '+count+'</span><br><span> Amount: '+Number(amount).format()+'</span>')
        })
}

function overdueAmountsByMonth(month) {
    var date  = new Date(month), y = date.getFullYear(), m = date.getMonth();
    var start = getIsoDate(new Date(y, m, 1));
    var end   = getIsoDate(new Date(y, m + 1, 0));

    return $.get(api_address+"Invoices?$filter=Type eq 'Invoice' and (Status eq 'Overdue' or Status eq 'Reminder') and (Created le "+end+' and Created ge '+start+')&$select=NetAmount,Id,Name,Created,Due,Type,Status,InvoiceNumber,Payed&$expand=User($select=FullName)')
        .success(function (data) {
            var count = data.value.length;
            var amount  = data.value.reduce(function (prev,current) {
                return prev+current.NetAmount;
            },0);

            $('#invoiced-overdue-'+month).html('<span>Count: '+count+'</span><br><span> Amount: '+Number(amount).format()+'</span>')
        })
}

function debtCollectionAmountsByMonth(month) {
    var date  = new Date(month), y = date.getFullYear(), m = date.getMonth();
    var start = getIsoDate(new Date(y, m, 1));
    var end   = getIsoDate(new Date(y, m + 1, 0));

    return $.get(api_address+"Invoices?$filter=Type eq 'Invoice' and Status eq 'DebtCollection' and (Created le "+end+' and Created ge '+start+')&$select=NetAmount')
        .success(function (data) {
            var count = data.value.length;
            var amount  = data.value.reduce(function (prev,current) {
                return prev+current.NetAmount;
            },0);

            $('#invoiced-debtcollection-'+month).html('<span>Count: '+count+'</span><br><span> Amount: '+Number(amount).format()+'</span>')
        })
}


function lostClientsByMonth(month) {
    var date  = new Date(month), y = date.getFullYear(), m = date.getMonth();
    var start = getIsoDate(new Date(y, m, 1));
    var end   = getIsoDate(new Date(y, m + 1, 0));

    $.get(api_address+"ClientDailyStats?$filter=State eq 'Lost'  and (DayOfStat le "+end+' and DayOfStat ge '+start+')')
        .success(function (data) {
            console.log(data);
        })

}

function ordersToday() {
    var today = moment().format('YYYY-MM-DD');
    return $.get(api_address+'Orders/$count?$filter=date(Created) eq '+today);
}

//todo
function callsToday(userId) {
    var today = moment.format('YYYY-MM-DD');
    var userQuery = userId ? "" : "";

    return $.get(api_address+'')
}

function goalsPerMonth(month) {
    var split = month.split('-');
    return $.get(api_address+'SellerGoals?$filter=Year eq '+split[0]+' and Month eq '+split[1]+"&$select=UpSalesGoal,ReSalesGoal,NewSalesGoal,NewSalesCountGoal,ReSalesCountGoal,UpSalesCountGoal")

}

function newAndReSalesPerMonth(month,process) {

    var date = moment(month).format('Y-MM-DD');
     return $.ajax({
        url: api_address + "Statistics/SaleStats",
        type: "POST",
        data: JSON.stringify({Month:date}),
        success: function (data) {
            if(!process){
                var newSalesCount = 0;
                var newSalesValue = 0;
                var ReSalesCount  = 0;
                var ReSalesValue  = 0;

                var newSalesCountGoal = 0;
                var newSalesValueGoal = 0;
                var ReSalesCountGoal  = 0;
                var ReSalesValueGoal  = 0;

                if(data.value){
                    $.each(data.value,function (index,val) {
                        newSalesCount += val.NewSalesCount;
                        newSalesValue += val.NewSalesValue;
                        ReSalesCount  += val.ReSalesCount + val.UpSaleCount;
                        ReSalesValue  += val.ReSalesValue + val.UpSaleValue;
                    });

                    $('#newsales-'+month).removeClass('spinner').html('<span>Count: '+newSalesCount+'</span><br><span> Amount: '+Number(newSalesValue).format()+'</span>');
                    $('#resales-'+month).removeClass('spinner').html('<span>Count: '+ReSalesCount+'</span><br><span> Amount: '+Number(ReSalesValue).format()+'</span>');
                    $('#total-sales-'+month).removeClass('spinner').html('<span>Count: '+(newSalesCount+ReSalesCount)+'</span><br><span> Amount: '+(Number(ReSalesValue) + Number(newSalesValue)).format()+'</span>');

                    $.when(goalsPerMonth(month))
                        .then(function (data) {
                            if(data.value){
                                $.each(data.value,function (index,val) {
                                    newSalesCountGoal += val.NewSalesCountGoal;
                                    newSalesValueGoal += val.NewSalesGoal;
                                    ReSalesCountGoal  += val.ReSalesCountGoal + val.UpSalesCountGoal;
                                    ReSalesValueGoal  += val.ReSalesGoal + val.UpSalesGoal;
                                });
                                $('#newsales-goal-'+month).removeClass('spinner')
                                    .html('<span>Count: '+newSalesCountGoal+'</span><br><span> Amount: '+Number(newSalesValueGoal).format()+'</span>');
                                $('#newsales-goal-diff-'+month).removeClass('spinner')
                                    .html('<span>Count: '+(newSalesCount - newSalesCountGoal)+'</span><br><span> Amount: '+(Number(newSalesValue) - Number(newSalesValueGoal)).format()+'</span>');
                                $('#resales-goal-'+month).removeClass('spinner')
                                    .html('<span>Count: '+ReSalesCountGoal+'</span><br><span> Amount: '+Number(ReSalesValueGoal).format()+'</span>');
                                $('#resales-goal-diff-'+month).removeClass('spinner')
                                    .html('<span>Count: '+(ReSalesCount - ReSalesCountGoal) +'</span><br><span> Amount: '+(Number(ReSalesValue) - Number(ReSalesValueGoal)).format()+'</span>');
                                $('#total-goals-'+month).removeClass('spinner')
                                    .html('<span>Count: '+(newSalesCountGoal+ReSalesCountGoal)+'</span><br><span> Amount: '+(Number(newSalesValueGoal) + Number(ReSalesValueGoal)).format()+'</span>');
                                $('#total-goals-diff-'+month).removeClass('spinner')
                                    .html('<span>Count: '+((newSalesCount+ReSalesCount) - (newSalesCountGoal+ReSalesCountGoal))+'</span><br><span> Amount: '+((Number(newSalesValue)+Number(ReSalesValue) - (Number(newSalesValueGoal) + Number(ReSalesValueGoal)))).format()+'</span>');

                            }
                        })


                }
            }
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });

}

function getStats (months) {
    sentAmounts(months);
    overdueAmounts(months);
    debtCollectionAmounts(months);
    paidAmounts(months);
    expectedPayments();
    meetingsStats();
    salesStats(months)
}
