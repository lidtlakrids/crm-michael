@extends('layout.main')
@section('page-title',Lang::get('labels.move-leads'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){
            var   $userQuery = (filters.user_id =="null")?" and User_Id eq null":" and User_Id eq '"+filters.user_id+"'";

            var customFilters = '';

            var lfilters = $('.leadFilters');
            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = lfilters.filter(function(){
                return this.value;
            });

            var table =
                $('.datatables').DataTable(
                    {
                        "language": {
                            "url": base_url+"datatables-"+locale+'.json'
                        },
                        responsive:true,
                        "lengthMenu": [[20,50,100], [20,50,100]],
                        aaSorting:[[0,"desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": api_address+"Leads?$expand=User($select=UserName),Phone",
                        'filter' : "Status eq webapi.Models.LeadStatus'"+filters.lead_type+"'"+$userQuery+customFilters,
                        "aoColumns": [
                            {mData:"Id","sType":"numeric","width":"7%",mRender:function(id,unused,object,c){
                                return '<a href="'+base_url+'/leads/show/'+ id+'" title="'+Lang.get('labels.see-lead')+'">'+id+'</a>';
                                }
                            },
                            {mData:"Company",mRender:function(company,type,obj){
                                return '<a href="'+base_url+'/leads/show/'+ obj.Id+'" title="'+Lang.get('labels.see-lead')+'">'+company ? company :"" +'</a>';
                                }
                            },
                            {mData:"Website"
                            },
                            {mData:"City"},
                            {mData:null,oData:"Phone/Number",sType:"string",mRender:function(data){
                                if(data.Phone != null)
                                {
                                    return data.Phone.Number;
                                }else {return ""}
                            }},
                            {mData:"Status","sType":"date"}
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
            lfilters.on('change', function (event) {
                settings[0].oInit.filter = originalFilter;
                customFilters = '';//clear old filters
                var newFilters = $.map(lfilters.toArray(), function (obj) {
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

            $('.datatables tbody').on( 'click', 'tr', function () {
                $(this).toggleClass('active');
            });

            $('.selectAllOnPage').click(function (event) {
                event.preventDefault();
                if($(event.target).hasClass('deselect')){
                    $(event.target).text(Lang.get('labels.select-all')).removeClass('deselect');
                    $(table.rows().nodes()).removeClass('active');
                    return false;
                }
                $(event.target).text(Lang.get('labels.deselect-all'));
                $(event.target).addClass('deselect');
                $(table.rows().nodes()).addClass('active');
                return false;
            });

            $('#moveLeadsForm').submit( function (event) {
                event.preventDefault();
                var ids = $.map(table.rows('.active').data(),function(item){
                    return item.Id;
                });
                var newUserId = $('select[name=User_Id]').val();
                if(newUserId == ""){
                    newUserId = null;
                }                var newStatus = $('select[name=Status]').val();
                var data = {User_Id : newUserId};
                if(newStatus != '') data.Status = newStatus;
                ids.forEach(function(id){
                    $.ajax({
                        url: api_address+"Leads("+id+")",
                        type: "PATCH",
                        data: JSON.stringify(data),
                        success : function(data)
                        {
                            table.draw();
                        },
                        beforeSend: function (request)
                        {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
                });
                if($('.selectAllOnPage').hasClass('deselect')){
                    $('.selectAllOnPage').removeClass('deselect').text(Lang.get('labels.select-all'));
                }
            });
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-lead">
                <div class="panel-heading">
                    <h4><i class="fa fa-bullhorn"> </i> Leads @lang('labels.dashboard')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Move Leads from : {{$user->FullName or "-"}}</h4>
                           Move to:
                            <br />
                            {!! Form::open(['id'=>'moveLeadsForm']) !!}
                            <div class="form-inline">
                                {!!  Form::select('User_Id',withEmpty($users), null, ['class' => 'form-control']) !!}
                                {!!  Form::select('Status',withEmpty($statuses['LeadStatus'],'Select Status'), null, ['class' => 'form-control','required'=>'required']) !!}

                                <input class="btn btn-green form-control" id="moveLeads" type="submit" value="Move">
                                {!! Form::close() !!}
                                <button class="btn btn-green selectAllOnPage">@lang('labels.select-all')</button>
                            </div>
                        </div>
                    </div>
                    <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%" class="table datatables table-list table-condensed">
                                    <thead>
                                    <tr>
                                        <th class="td-id">ID</th>
                                        <th class="td-company">@lang('labels.company-name')</th>
                                        <th class="td-company">@lang('labels.homepage')</th>
                                        <th class="td-city">@lang('labels.city')</th>
                                        <th class="td-phone">@lang('labels.phone')</th>
                                        <th class="td-status">@lang('labels.status')</th>
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