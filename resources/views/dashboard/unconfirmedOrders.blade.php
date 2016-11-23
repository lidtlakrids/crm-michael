@extends('layout.main')
@section('page-title',"Unconfirmed orders")

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){

            var $userQuery = "User_Id eq '"+$('#user-Id').val()+"'";
            if(isAdmin()){
                $userQuery = "User_Id ne null";
            }
            var customFilters= "";
            var table =
                $('.datatables').DataTable(
                    {
                        "oLanguage": {
                            "sProcessing":   Lang.get('labels.processing'),
                            "sLengthMenu":   Lang.get('labels.length-menu'),
                            "sZeroRecords":  Lang.get('labels.zero-records'),
                            "sInfo":         Lang.get('labels.info'),
                            "sInfoEmpty":    Lang.get('labels.info-empty'),
                            "sInfoFiltered": Lang.get('labels.info-filtered'),
                            "sInfoPostFix":  "",
                            "sSearch":       Lang.get('labels.search'),
                            "sUrl":          "",
                            "oPaginate": {
                                "sFirst":    Lang.get('labels.first'),
                                "sPrevious": Lang.get('labels.previous'),
                                "sNext":     Lang.get('labels.next'),
                                "sLast":     Lang.get('labels.last')
                            }
                        },
                        responsive:true,
                        "lengthMenu": [[20,50,100], [20,50,100]],
                        aaSorting:[[0,"desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": api_address+"Orders?$expand=User($select=UserName,FullName),OrderProductPackage($expand=ProductPackage),ClientAlias",
                        'filter' : "ConfirmedDate eq null and ArchivedDate eq null and "+$userQuery,
                        "aoColumns": [
                            {
                                mData: "Id", "oData": "Id", "sType": "numeric", "width": "5%", mRender: function (id) {

                                return '<a href="'+base_url+'/orders/show/' + id + '" title="' + Lang.get('labels.see-order') + '">' + id + '</a>';
                            }
                            },
                            {"mData": null, "sType": "string", "oData": "ClientAlias/Name",
                                mRender: function (data) {
                                    if (data.ClientAlias != null) {
                                        return '<a href="'+base_url+'/orders/show/' + data.Id + '" title="' + Lang.get('labels.see-order') + '">' + data.ClientAlias.Name + '</a>';
                                    } else {
                                        return "----";
                                    }
                                }
                            },
                            {
                                "mData": null,
                                "sType": "string",
                                "oData": "ClientAlias/PhoneNumber",
                                mRender: function (data) {
                                    if (data.ClientAlias != null) {
                                        return data.ClientAlias.PhoneNumber;
                                    } else {
                                        return "----";
                                    }
                                }
                            },
                            {
                                "mData": "Created", "sType": "date", mRender: function (CreatedDate) {
                                var date = new Date(CreatedDate);
                                return date.toLocaleString();
                            }
                            },
                            {
                                "mData": null, "oData": "User/UserName", mRender: function (data) {
                                if (data.User != null) {
                                    return data.User.UserName
                                } else {
                                    return "---"
                                }
                            }
                            },
                            {
                                "orderable": false, "mData": null, "sType": "date", mRender: function (data) {
                                return data.Id + 'todo add links'; // todo add links
                            }
                            }

                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false

                    });
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-users"> </i>@lang('labels.appointments')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="col-md-3">
                        <label>
                            <input class="" id="showAll" type="checkbox">
                            @lang('labels.show-all')
                        </label>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table cellpadding="0" cellspacing="0" border="0" class="table datatables table-list table-condensed">
                                <thead>
                                <tr>
                                    <th>@lang('labels.number')</th>
                                    <th>@lang('labels.company-name')</th>
                                    <th>@lang('labels.phone')</th>
                                    <th>@lang('labels.submission-date')</th>
                                    <th>@lang('labels.salesman')</th>
                                    <th>@lang('labels.options')</th>
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