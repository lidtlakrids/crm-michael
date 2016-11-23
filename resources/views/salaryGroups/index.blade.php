@extends('layout.main')
@section('page-title',Lang::get('labels.salary-groups'))
@section('scripts')
@include('scripts.dataTablesScripts')

<script>
        $(document).ready(function () {
            $('#table-list').dataTable({
                "language": {
                    "url": "datatables-"+locale+'.json'
                },
                "oLanguage": {
                    "sSearch":       "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                },
                "lengthMenu": [[20,50,100], [20,50,100]],
                aaSorting:[[0,"desc"]], // shows the newest items first
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": api_address+"SalaryGroups",
                "aoColumns": [
                    {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){

                        return '<a href="'+base_url+'/salary-groups/show/'+ id+'">'+id+'</a>';
                    }},
                    { "mData": "Name", "oData": "Name","sName":"Name" },
                    {"mData":"Description"
                    },
                    {mData:"Salary",sType:"number"
                    },
                    {mData:"MinimumTurnover",sType:"number"
                    },
                    {mData:"BonusProcentage",sType:"number",'searchable':false

                    },
                    {mData:null,sortable:false,sType:"date",mRender:function(obj){
                        return "<a href='"+base_url+"/salary-groups/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>"
                        }

                    }
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            })

        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-barcode"></i> @lang('labels.salary-groups')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        @if(isAllowed('salaryGroups','post'))<a href="{{url('salary-groups/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>@endif
                    </div>
                </div>
                <div class="panel-body">
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped datatables" id="table-list">
                        <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.name')</th>
                                <th>@lang('labels.description')</th>
                                <th>@lang('labels.salary')</th>
                                <th>@lang('labels.min-turnover')</th>
                                <th>Bonus %</th>
                                <th>@lang('labels.actions')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

 