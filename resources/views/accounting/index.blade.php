@extends('layout.main')
@section('page-title',Lang::get('labels.invoices'))
@section('scripts')
    @include('scripts.dataTablesScripts')

    <script>

        $(document).ready(function(){

            var customFilters = "";
         //   var tempFilters = " and (Status eq webapi.Models.InvoiceStatus'Reminder' or Status eq webapi.Models.InvoiceStatus'Overdue' or Status eq webapi.Models.InvoiceStatus'DebtCollection')";
            var userId = $('#user-Id').val();
            var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);

            if(!admin){
                roles.forEach(function(role){
                    switch (role){
                        case "Client Manager":
                            customFilters += " and ClientAlias/Client/ClientManager/Id eq '"+userId+"' or ClientAlias/Client/ClientManager/Id eq null";
                            break;
//                    case "Adwords":
//                        customFilters += " and Contract/any(d:d/Manager_Id eq '"+userId+"')";
//                        break;
                        case "Sales":
                            customFilters += " and User_Id eq '"+userId+"'";
                            break;
                        default:
                            break;
                    }
                });
            }
            var table = $('.datatables').DataTable(
                    {
                        responsive:true,
                        stateSave: true,
                        "language": {
                            "url": "datatables-"+locale+'.json'
                        },
                        "oLanguage": {
                            "sSearch":       "",
                            "sSearchPlaceholder": Lang.get('labels.search')
                        },
                        "lengthMenu": [[20, 50], [20, 50]],
                        aaSorting:[[0,"desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "filter"     : "Created ne null"+customFilters,
                        "sAjaxSource": api_address+"Invoices?$expand=User($select=UserName,FullName),ClientAlias($expand=Client($select=CINumber,ClientManager_Id))",
                        'createdRow':function(row,data){
                            var today = moment();
                            var due = moment(data.Due);
                            if(data.Status == "DebtCollection"){
                                $(row).addClass('danger');
                            }

                        },
                        "aoColumns": [
                            {mData:"Id","sType":"numeric","oDAta":"Id",width:"5%",mRender:function(id,unused,object,c){

                                return '<a href="'+base_url+'/invoices/show/'+ id+'" title="'+Lang.get('labels.see-invoice')+'">'+id+'<i class="fa fa-file-pdf-o"></i></a>';
                            }},
                            { "mData": "InvoiceNumber","sType":"numeric",width:"5%","oData":"InvoiceNumber",mRender:function(invoiceNumber,unused,object,c){

                                return '<a href="'+base_url+'/invoices/show/'+ object.Id+'" title="'+Lang.get('labels.see-invoice')+'">'+invoiceNumber+'</a>';
                            }},
                            { "mData": "Name","oData":"Name"},
                            { "mData": 'NetAmount',"sType":"numeric",mRender:function(obj){
                                return (obj != null) ? obj.format() : "";
                            }
                            },
                            { "mData": "Payed","oData":"Payed","sType":"date",mRender:function(Payed){
                                if(Payed != null) {
                                    var date = new Date(Payed);
                                    return date.toDateTime();
                                }else{return ""}
                            }},
                            {mData:"Status",sType:"date"

                            },
                            {mData:"Type",sType:"date"

                            },
                            { "mData": "Created","oData":"Created","sType":"date",mRender:function(CreatedDate){
                                var date = new Date(CreatedDate);
                                return date.toDateTime();
                            }},
                            { "mData": "Due","oData":"Due","sType":"date",mRender:function(Due){
                                var date = new Date(Due);
                                return date.toDateTime();
                            }},
                            { "sType":"string","mData": null, "oData":"User/UserName",
                                mRender:function(data){ if(data.User != null){return "<span title='"+data.User.FullName+"'>"+data.User.UserName.toUpperCase()+"</span>"}else{ return "---"}}
                            }

                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    });

            // get original filters
            var settings = table.settings();

            var originalFilter = settings[0].oInit.filter;

            var filters = $('.invoiceFilters');

            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function(){
                return this.value;
            });

            if(currentFilters.length>0){
                tempFilters='';
                var oldFilters =$.map(currentFilters, function (obj) {
                    return $(obj).val();
                });

                customFilters = oldFilters.join(' and ');
                // add the extra filters to the existing ones
                settings[0].oInit.filter += " and " + customFilters;
                table.draw();
            }

            //if a contract filter is clicked, apply a corresponding string
            filters.on('change', function (event) {
                settings[0].oInit.filter = originalFilter;
                customFilters = '';//clear old filters
                var newFilters = $.map(filters.toArray(), function (obj) {
                    if ($(obj).val() != "") {
                        return $(obj).val();
                    }
                });

                customFilters = newFilters.join(' and ');
                // add the extra filters to the existing ones
                if(customFilters.length > 0){
                    settings[0].oInit.filter += " and " + customFilters;
                }
                //redraw the table
                table.draw();
                return false;
            });
        });
    </script>
@stop
@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-invoice">
                <div class="panel-heading">
                    <h4><i class="fa fa-money"></i> @lang('labels.invoices')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{ url('/invoices/create') }}" title="@lang('labels.create-invoice')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="">
                                <div class="form-group-sm col-md-3">
                                    {!! Form::select('User_Id',withEmpty($sellers,Lang::get('labels.select-seller')),null,['class'=>'form-control invoiceFilters']) !!}
                                </div>
                                <div class="form-group-sm col-md-3">
                                    {!! Form::select('Status',$statuses,null,['class'=>'form-control invoiceFilters']) !!}
                                </div>
                                <div class="form-group-sm col-md-3">
                                    {!! Form::select('Type',withEmpty($types,Lang::get('labels.invoice-type')),null,['class'=>'form-control invoiceFilters']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table id="table-list" class="table table-hover datatables" width="100%">
                            <thead>
                            <tr>
                                <th>@lang('labels.id')</th>
                                <th>@lang('labels.invoice-number')</th>
                                <th>@lang('labels.debtor-name')</th>
                                <th>@lang('labels.total-net-amount')</th>
                                <th>@lang('labels.paid')</th>
                                <th>@lang('labels.status')</th>
                                <th>@lang('labels.type')</th>
                                <th>@lang('labels.created-date')</th>
                                <th>@lang('labels.due-date')</th>
                                <th>@lang('labels.seller')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

