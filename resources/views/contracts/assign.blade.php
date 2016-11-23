@extends('layout.main')
@section('page-title',Lang::get('labels.assign-contracts'))
@section('scripts')
    @include('scripts.dataTablesScripts')
<script>
$(document).ready(function(){
    var customFilters = '';

//    customFilters += checkOwnership('Contract');
    var filters = $('.assignFilters');

    //check if there are already selected filters / for example if we refresh the page
    var currentFilters = filters.filter(function(){
        return this.value;
    });

    var table =
    $('.datatables').DataTable(
    {
        responsive:true,
        stateSave:true,
        deferLoading: currentFilters.length > 0? 0:null,
        "stateDuration": 300, // 5 minutes
        "lengthMenu": [[20,50,100], [20,59,100]],
        "oLanguage": {
            "sSearch":       "",
            "sSearchPlaceholder": Lang.get('labels.search')
        },
        aaSorting:[[0,"desc"]], // shows the newest items first
        "sPaginationType": "full_numbers",
        "bProcessing": true,
        "bServerSide": true,
        'filter' : "Manager eq null and Status eq 'Active' and NeedInformation eq false"+customFilters,
        "sAjaxSource": api_address+"Contracts?$expand=ClientAlias($select=Id,Name),Country,Product($select=Id,Name,Description),User($select=UserName),Manager($select=FullName),ContractType",
        "aoColumns": [
            {mData:"Id","sType":"numeric","width":"7%",mRender:function(id,type,data){
                if(data.ContractType == null) return "no contract type";
                return '<a href="'+base_url+'/'+data.ContractType.Name.toLowerCase()+'/show/'+ id+'" title="'+Lang.get('labels.see-contract')+'">'+id+'</a>';
                }
            },
            {"sType":"string","mData":null,"oData":"ClientAlias/Name",mRender:function(data){
                if(data.ContractType == null) return "no contract type";
                var teamUrl = (data.ContractType.Name.toLowerCase() == 'adwords' || data.ContractType.Name.toLowerCase() == "seo") ? data.ContractType.Name.toLowerCase() : "contracts";
                return '<a href="'+base_url+'/'+teamUrl+'/show/'+ data.Id +'" title="'+Lang.get('labels.see-contract')+'">'+data.ClientAlias.Name+'</a>';
                }
            },
            {"mData":null,"sType":"date","oData":"Country/CountryCode",mRender:function(data){
                if(data.Country != null) {return data.Country.CountryCode }else {return "----"}
                }
            },
            {"mData":null,"sType":"string","oData":"Product/Name",mRender:function(data){
                if(data.Product != null) {return '<a href="'+base_url+'/products/show/'+ data.Product.Id +'" title="'+Lang.get('labels.see-product')+'">'+data.Product.Name+'</a>';
                }
                else {return "----"}
               }
            },
            {"mData":"Status","sType":"numeric"
            },
            {"mData": "Created", "sType": "date", mRender: function (data)
            {if(data != null) {
                var date = new Date(data);
                return date.toDateTime();
            }
            else{ return "---"}
            }

            },
            {"mData": "StartDate", "sType": "date", mRender: function (data)
                {if(data != null) {
                    var date = new Date(data);
                    return date.toDateTime();
                }
                else{ return "---"}
                    }
            },
            {"mData": "EndDate", "sType": "date", mRender: function (data)
            {if(data != null) {
                var date = new Date(data);
                return date.toDateTime();
            }
            else{ return "---"}
            }
            },
            { "sType":"string","mData": null,"oData":"User/UserName" ,mRender:function(data){
                if(data.User != null){return data.User.UserName}else{ return "---"}
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
            <div class="panel panel-adwords">
                <div class="panel-heading">
                    <h4><i class="fa fa-sign-out"></i> @lang('labels.assign-contracts')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('/contracts/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="form-group-sm col-md-2 col-md-offset-10">
                            {!! Form::select('Type_Id',withEmpty($types,'Select Product Type'),null,['class'=>'form-control assignFilters']) !!}
                        </div>
                    </div>
                    <hr>
                    <table id="table-list" cellpadding="0" cellspacing="0" border="0" style="width:100%" class="table datatables">
                        <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.client')</th>
                                <th>@lang('labels.country')</th>
                                <th>@lang('labels.product')</th>
                                <th>@lang('labels.status')</th>
                                <th>Created</th>
                                <th>@lang('labels.start-date')</th>
                                <th>@lang('labels.end-date')</th>
                                <th>@lang('labels.seller')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop