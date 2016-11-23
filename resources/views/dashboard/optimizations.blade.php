@extends('layout.main')
@section('page-title',Lang::get('labels.my-optimizations'))

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
            var today = moment();
            var table =
                $('.datatables').DataTable(
                    {
                        responsive:true,
                        stateSave:true,
                        deferLoading: currentFilters.length > 0? 0:null,
                        "lengthMenu": [[50,75,100], [50,75,100]],
                        aaSorting:[[2,"asc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "oLanguage": {
                            "sSearch":       "",
                            "sSearchPlaceholder": Lang.get('labels.search')
                        },
                        "bServerSide": true,
                        "sAjaxSource": api_address+"Contracts?$expand=Product($select=Name),User($select=UserName,FullName),Manager($select=FullName,Id),ContractType($select=Name),ClientAlias($select=Name,Id)",
                        'filter' : "Status eq 'Active' and NeedInformation eq false and NextOptimize ne null "+
                                "and ((ProductPackage_Id ne null and Parent_Id ne null and Parent/Status eq 'Active') or (Parent_Id ne null and ProductPackage_Id eq null) or (Parent_Id eq null and ProductPackage_Id eq null) or " +
                        "(Parent_Id eq null and ProductPackage_Id ne null and (Product/ProductType_Id eq 3 or Product/ProductType_Id eq 18 or Product/ProductType_Id eq 20))) "
                        +customFilters,
                        'select':'ProductPackage_Id,Parent_Id',
                        "fnRowCallback": function (nRow, aaData) {
                            var dueDate = moment(aaData.NextOptimize);
//                            var diff = today.diff(dueDate,'days');
//                            console.log(diff);
                            if(dueDate.isAfter(today,'day')){
                                $(nRow).addClass('success');
                            }else if(today.isAfter(dueDate,'day')){
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
                                    var teamurl = isInArray(obj.ContractType.Name.toLowerCase(),['seo','social','local','google+']) ? 'seo':'adwords';

                                    return '<a href="'+base_url+'/'+teamurl+'/show/' + obj.Id + '">' + obj.ClientAlias.Name + '</a>';
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
                    <h4><i class="fa fa-gears"> </i> @lang('labels.my-optimizations')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="form-group-sm col-md-3">
                            {!! Form::select('Created',$periods,$selected,['class'=>'form-control optimizationFilters']) !!}
                        </div>

                        <div class="form-group-sm col-md-3">
                            {!! Form::select('Manager_Id',$managers,"Manager_Id eq '".Auth::user()->externalId."'",['class'=>'form-control optimizationFilters','disabled'=>isAdmin()?null:'disabled']) !!}
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