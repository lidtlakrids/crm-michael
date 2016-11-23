@extends('layout.main')
@section('page-title',"Old sellers")
@section('scripts')
    @include('scripts.dataTablesScripts')
<script>
$(document).ready(function(){
    var customFilters = "";
    var filters = $('.contractFilters');

    //check if there are already selected filters / for example if we refresh the page
    var currentFilters = filters.filter(function(){
        return this.value;
    });

    var table =
    $('.datatables').DataTable(
    {
        deferLoading: currentFilters.length > 0? 0:null,

        searching: true,
        responsive : true,
        stateSave : true,
        "lengthMenu": [[25,50, 100], [25,50,100]],
         aaSorting:[[0,"desc"]], // shows the newest items first
        "sPaginationType": "full_numbers",
        "bProcessing": true,
        "bServerSide": true,
        "deferRender": true, // testing if speed is better with this
        "sAjaxSource": api_address+"Contracts?$expand=ClientAlias($select=Id,Name;$expand=User($select=Id,FullName)),Country,Product($select=Id,Name,Description),User($select=Id,FullName),Children($select=Id;$expand=Product($select=Name))",
        "filter"     : "User/Active eq false"+customFilters,
        'select'  : "NeedInformation",
        "aoColumns": [
            {mData:"Id","sType":"numeric",sSorting:"desc","width":"7%",mRender:function(id){
                return '<a href="'+base_url+'/contracts/show/'+ id+'" title="'+Lang.get('labels.see-contract')+'">'+id+'</a>';
                }

            },
            {"sType":"string","mData":null,"oData":"ClientAlias/Name",mRender:function(data){
                if(data.ClientAlias != null){
                   return '<a href="'+base_url+'/contracts/show/'+ data.Id +'" title="'+Lang.get('labels.see-contract')+'">'+data.ClientAlias.Name+'</a>';
                }else{return "---"}
            }
            },
            {"mData":null,"sType":"date","oData":"Country/CountryCode",mRender:function(data){
                if(data.Country != null) {return data.Country.CountryCode }else {return "----"}
                }
            },
            {"mData":null,"sType":"string","oData":"Product/Name",mRender:function(data){
                if(data.Product != null) {return '<a href="'+base_url+'/products/show/'+ data.Product.Id +'" title="'+Lang.get('labels.see-client')+'">'+data.Product.Name+'</a>'; }else {return "----"}
               }
            },
            {"mData":"Status","sType":"numeric"
            },
            {"mData": "StartDate", "sType": "date", mRender: function (data)
                {
                    if(data != null) {
                    var date = new Date(data);
                    return date.toDate();
                }
                else{ return "---"}
                }
            },
            {"mData": "EndDate", "sType": "date", mRender: function (data)
                {if(data != null) {
                    var date = new Date(data);
                    return date.toDate();
                }
                else{ return "---"}
                }
            },
            {"mData": "NextOptimize", "sType": "date", mRender: function (data)
            {if(data != null) {
                var date = new Date(data);
                return date.toDate();
            }
            else{ return "---"}
            }
            },
            { "sType":"string","mData": null,"oData":"User/FullName" ,mRender:function(data){
                if(data.User != null){
                    return '<a href="#" title="Click to change draft type" class="quickEditContractSeller" data-type="select" data-pk="'+data.Id+'">'+data.User.FullName+'</a>'
                }else{ return "---"}
              }
            },
            { "sType":"string","mData": null,"oData":null,sortable:false,searchable:false ,mRender:function(data){
                if(data.ClientAlias != null){
                    return data.ClientAlias.User.FullName
                }else{ return "---"}
            }
            },
            { "sType":"date",mData:null,sortable:false,mRender:function(data){
                  var children = data.Children.length;
                  var title = "";
                  if(children>0){
                      data.Children.forEach(function(child){
                         title += child.Product.Name + '\n' ;
                      });
                  }
                  return children + " <i class='fa fa-question' title='" + title + "'></i>";
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
    $('body').on('click','.quickEditContractSeller',function(event){
        // find the row id

        var id = $(this).data('pk');
        event.preventDefault();
        $(event.target).editable({
            source: sellers, // comes from the controller
            ajaxOptions:{
                type:"patch",
                dataType: 'application/json',
                beforeSend: function (request)
                {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            },
            params: function(params) {
                var data = {};
                data['User_Id'] = params.value;
                return JSON.stringify(data);
            },
            url:api_address+"Contracts("+id+")"
        }).removeClass('quickEditContractSeller');
        setTimeout(function(){
            $(event.target).click();
        },200)
    });

});
</script>
@stop

@section('content') 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-contract">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i> @lang('labels.contracts')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('/contracts/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="form-inline  pull-right">
                                {!! Form::select('User_Id',withEmpty($userList,Lang::get('labels.select-seller')),"User_Id eq '".Auth::user()->externalId."'",['class'=>'form-control contractFilters']) !!}
                                {!! Form::select('Status',$contractStatus,null,['class'=>'form-control contractFilters']) !!}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="clearfix"></div>
                    <table id="table-list" class="table table-condensed datatables" width="100%">
                        <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.client')</th>
                                <th>@lang('labels.country')</th>
                                <th>@lang('labels.product')</th>
                                <th>@lang('labels.status')</th>
                                <th>@lang('labels.start-date')</th>
                                <th>@lang('labels.end-date')</th>
                                <th>@lang('labels.next-optimization')</th>
                                <th>@lang('labels.seller')</th>
                                <th>Client Seller</th>
                                <th>@lang('labels.sub-contracts')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

