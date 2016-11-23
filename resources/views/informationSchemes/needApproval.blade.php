@extends('layout.main')
@section('page-title',Lang::get('labels.information-for-approval'))

@section('scripts')
@include('scripts.dataTablesScripts')
    <script>
    $(document).ready(function () {


        var customFilters='';
        var table =  $('#table-list').DataTable(
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
                saveState:true,
                "lengthMenu": [[20,50,100], [20,50,100]],
                aaSorting:[[0,"desc"]], // shows the newest items first
                "sPaginationType": "full_numbers",
                "bFilter":false,
                "bProcessing": true,
                "bServerSide": true,
                "filter" :"Created ne null"+customFilters,
                "sAjaxSource": api_address+"InformationSchemes?$expand=Contract($expand=ClientAlias,ContractType),User($select=UserName),Type($select=FormName)",
                "aoColumns": [
                    {mData:"Id","oData":"Id","sType":"numeric","width":"5%",mRender:function(id){

                        return '<a href="'+base_url+'/information/show/'+ id+'" title="'+Lang.get('labels.see-order')+'">'+id+'</a>';
                        }
                    },
                    {mData:null,oData:"Contract/ClientAlias/Name",sType:"string",mRender: function (object) {
                            if(object.Contract.ClientAlias != null){
                                return object.Contract.ClientAlias.Name;
                            }else{return ""}
                        }
                    },
                    {mData:null,oData:"Contract/Id",sType:"string",mRender:function(object){
                        if(object.Contract != null){
                            return object.Contract.Id;
                            }else{return ""}
                        }
                    },
                    {mData:"Created",sType:"date",mRender:function(obj){
                        var date = new Date(obj);
                            return date.toDateTime();
                        }
                    },
                    {mData:null,oData:"Contract/ContractType/Name",sType:"string",mRender:function(obj){
                        if(obj.Contract.ContractType != null){
                            return obj.Contract.ContractType.Name;
                            }else {return ""}
                        }
                    },
                    {mData:null,oData:"User/UserName",sType:"string",mRender:function(obj){
                        if(obj.User != null){
                            return obj.User.UserName.toUpperCase();
                            }else {return ""}
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
                <h4><i class="fa fa-reorder"></i> @lang('labels.information-for-approval')</h4>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-6 col-md-offset-6">
                        <div class="form-inline  pull-right">
                            {!! Form::select('User_Id',withEmpty($users,Lang::get('labels.select-seller')),null,['class'=>'form-control orderFilters']) !!}
                            {!! Form::select('Created',withEmpty($orderDates,Lang::get('labels.select-period')),null,['class'=>'form-control orderFilters']) !!}
                            {!! Form::select('Type_Id',withEmpty($types,Lang::get('labels.select-type')),null,['class'=>'form-control orderFilters']) !!}
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table-list" class="table table-striped table-condensed table-hover datatables">
                        <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.company-name')</th>
                                <th>@lang('labels.contract')</th>
                                <th>@lang('labels.submission-date')</th>
                                <th>@lang('labels.order-type')</th>
                                <th>@lang('labels.created-by')</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>  <!-- END panel body -->
        </div>  <!-- END Panel grape -->

    </div> <!-- End of col-md-12 -->
</div> <!-- End of Row -->
@stop



 