@extends('layout.main')
@section('page-title',Lang::get('labels.orders'))
@section('scripts')
@include('scripts.dataTablesScripts')
<script>
    $(document).ready(function () {
        var customFilters = '';

        var userId = $('#user-Id').val();
        var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);
        if(!admin && userId != '34'){
            roles.forEach(function(role){
                switch (role){
                    case "Client Manager":
                        customFilters += " and ClientAlias/Client/ClientManager/Id eq '"+userId+"' or ClientAlias/Client/ClientManager/Id eq null";
                        break;
                    case "Adwords":
                    case "SEO":
                        customFilters += " and Contracts/any(d:d/Manager_Id eq '"+userId+"')";
                        break;
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
                    responsive: true,
                    'stateSave': true,
                    "stateDuration": 60 * 5, // 5 minutes
                    "language": {
                        "url": "datatables-"+locale+'.json'
                    },
                    "oLanguage": {
                        "sSearch":       "",
                        "sSearchPlaceholder": Lang.get('labels.search')
                    },
                    "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                    aaSorting: [[0, "desc"]], // shows the newest items first
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    "deferLoading": 0, // don't draw before the filters are applied
                    'select' : "ApprovedDate,Domain,ConfirmedIP",
                    "filter": "Id ne null" + customFilters,
                    "sAjaxSource": api_address + "Orders?$expand=ClientAlias($select=Id,PhoneNumber,Name),User($select=FullName)",
                    "fnRowCallback": function (nRow, aaData) {
                       if(aaData.ConfirmedDate != null && aaData.ArchivedDate == null){
                           $(nRow).addClass('success');
                       }else if(aaData.ConfirmedData == null && aaData.ApprovedDate != null && aaData.ArchivedDate == null){
                           $(nRow).addClass('warning');
                       }else{
                           $(nRow).addClass('danger');
                       }
                    },
                    "aoColumns": [
                        {
                            mData: "Id", "oData": "Id", "sType": "numeric", "width": "5%", mRender: function (id) {

                            return '<a href="' + base_url + '/orders/show/' + id + '" title="' + Lang.get('labels.see-order') + '">' + id + '</a>';
                        }
                        },
                        {
                            "mData": null, "sType": "string", "oData": "ClientAlias/Name",
                            mRender: function (data) {
                                if (data.ClientAlias != null) {
                                    return '<a href="' + base_url + '/orders/show/' + data.Id + '" title="' + Lang.get('labels.see-order') + '">' + data.ClientAlias.Name + '</a>';
                                } else {
                                    return Lang.get('messages.client-not-set');
                                }
                            }
                        },
                        {mData:"Domain",mRender:function (domain) {
                            if(domain != null){
                            return '<a target="_blank" href="'+addhttp(domain)+'">'+domain+'</a>'
                            }else{
                                return ''
                            }
                        }

                        },
                        {
                            "mData": null,
                            "sType": "string",
                            "oData": "ClientAlias/PhoneNumber",
                            mRender: function (data) {
                                if (data.ClientAlias != null) {
                                    if(canCall()){
                                        return "<span class='pseudolink flexfoneCallOut'>"+data.ClientAlias.PhoneNumber+"</span>";
                                    }else{
                                        return "<a href='tel:"+data.ClientAlias.PhoneNumber+"'>"+data.ClientAlias.PhoneNumber+'</a>';
                                    }
                                } else {
                                    return ""
                                }
                            }
                        },
                        {
                            "mData": "Created", "sType": "date", mRender: function (CreatedDate) {
                            var date = new Date(CreatedDate);
                            return date.toDateTime();
                        }
                        },
                        {
                            "mData": null, "oData": "User/FullName", mRender: function (data) {
                            return data.User != null ? data.User.FullName : "";
                        }
                        },
                        {
                            "mData": "ConfirmedDate", "sType": "date", mRender: function (data,display,obj) {
                                if (data != null) {
                                    var date = new Date(data);
                                    return date.toLocaleString() +
                                            (admin ? // sorry about this
                                                    obj.ConfirmedIP ? " <a target='_blank' href='http://ip-tracker.org/locator/ip-lookup.php?ip="+obj.ConfirmedIP+"'>"+obj.ConfirmedIP+"</a>" || "" : "" : "");
                                }
                                return "";
                            }
                        },
                        {
                            "mData": "ArchivedDate", "sType": "date", mRender: function (data) {
                            if (data != null) {
                                var date = new Date(data);
                                return date.toLocaleString();
                            }
                            return "";
                         }
                        }
                    ],
                    "fnServerData": fnServerOData,
                    "iODataVersion": 4,
                    "bUseODataViaJSONP": false
                });
        // get original filters
        var settings = table.settings();

        var originalFilter = settings[0].oInit.filter;

        var filters = $('.orderFilters');

        //check if there are already selected filters / for example if we refresh the page
        var currentFilters = filters.filter(function(){
           return this.value;
        });

        if(currentFilters.length>0){
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
    <div class="col-md-12">
        <div class="panel panel-order">
            <div class="panel-heading">
                <h4><i class="fa fa-reorder"></i> @lang('labels.orders')</h4>
                <div class="options">
                    <a href="{{ url('/orders/create') }}" title="Create new order"><i class="fa fa-plus"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="alert alert-danger">
                            Needs approval
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="alert alert-warning">
                            Waiting confirmation
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="alert alert-success">
                            Confirmed
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-inline  pull-right">
                            {!! Form::select('User_Id',withEmpty($users,Lang::get('labels.select-seller')),"User_Id eq '".Auth::user()->externalId."'",['class'=>'form-control orderFilters']) !!}
                            {!! Form::select('Created',withEmpty($orderDates,Lang::get('labels.select-period')),null,['class'=>'form-control orderFilters']) !!}
                            {!! Form::select('Status',$orderStatus,null,['class'=>'form-control orderFilters']) !!}
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="table-responsive">
                        <table id="table-list" class="table table-striped datatables" width="100%">
                            <thead>
                                <tr>
                                    <th>@lang('labels.number')</th>
                                    <th>@lang('labels.company-name')</th>
                                    <th>Homepage</th>
                                    <th>@lang('labels.phone')</th>
                                    <th>@lang('labels.submission-date')</th>
                                    <th>@lang('labels.salesman')</th>
                                    <th>@lang('labels.confirmed-date')</th>
                                    <th>@lang('labels.archived')</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop



 