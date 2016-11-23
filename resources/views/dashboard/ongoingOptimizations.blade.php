@extends('layout.main')
@section('page-title',"Ongoing optimizations")

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){
            var customFilters= "";

            var filters = $('.optimizationFilters');
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
                        "lengthMenu": [[50,75,100], [50,75,100]],           
                        "oLanguage": {
                            "sSearch":       "",
                            "sSearchPlaceholder": Lang.get('labels.search')
                        },
                        aaSorting:[[2,"asc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": api_address+"Contracts?$expand=Product($select=Name),User($select=UserName,FullName),Manager($select=FullName,Id),ContractType($select=Name),ClientAlias($select=Name,Id)",
                        'filter' : 'Activity/any(d:d/ActivityType eq \'StartOptimize\')'+customFilters,
                        'select':'ProductPackage_Id,Parent_Id',
                        "fnRowCallback": function (nRow, aaData) {
                            var dueDate = moment(aaData.NextOptimize);
                            var today = moment();
                            var diff = today.diff(dueDate,'days');
                            if(diff < 0){
                                $(nRow).addClass('success');
                            }else if(diff > 0){
                                $(nRow).addClass('danger');
                            }else{
                                $(nRow).addClass('warning');
                            }
                        },
                        "aoColumns": [
                            {mData:"Id","oData":"Id","sType":"numeric","width":"5%",mRender:function(id,unused,obj) {
                                return '<a href="'+base_url+'/'+obj.ContractType.Name.toLowerCase()+'/show/' + id + '" title="' + Lang.get('labels.see-order') + '">' + id + '</a>';
                            }
                            },
                            {mData:null,oData:"ClientAlias/Name",mRender:function(obj){
                                return '<a href="'+base_url+'/'+obj.ContractType.Name.toLowerCase()+'/show/' + obj.Id + '">' + obj.ClientAlias.Name + '</a>';
                            }
                            },
                            {mData:'NextOptimize',sType:"date",mRender:function(date){
                                var d = new Date(date);
                                return d.toDate();
                            }
                            },
                            {mData:null,oData:"Product/Name",sType:"string",mRender:function(obj){
                                return obj.Product == null ? "--": obj.Product.Name;
                            }
                            },
                            {mData:'StartDate',sType:"date",mRender:function(date){
                                var d = new Date(date);
                                return d.toDate();
                            }
                            },
                            {mData:'EndDate',sType:"date",mRender:function(date){
                                var d = new Date(date);
                                return d.toDate();
                            }
                            },
                            {mData:null,oData:'Manager/FullName',mRender:function (obj) {
                                if(obj.Manager){
                                    return obj.Manager.FullName;
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

            if(currentFilters.length>0){
                var oldFilters =$.map(currentFilters, function (obj) {
                    return $(obj).val();
                });

                customFilters = oldFilters.join(' and ');
                // add the extra filters to the existing ones
                settings[0].oInit.filter += ' and '+customFilters;
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
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-clock-o"> </i> Ongoing optimizations</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="form-group-sm col-md-3">
                            {!! Form::select('User',withEmpty($users,'Select User'),"Manager_Id eq '".$userid."'",['class'=>'form-control optimizationFilters','disabled'=>$admin ? null : true]) !!}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%" class="table datatables table-list">
                                <thead>
                                <tr>
                                    <th>@lang('labels.number')</th>
                                    <th>@lang('labels.client')</th>
                                    <th>@lang('labels.next-optimization')</th>
                                    <th>@lang('labels.product')</th>
                                    <th>@lang('labels.start-date')</th>
                                    <th>@lang('labels.end-date')</th>
                                    <th>Manager</th>
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