@extends('layout.main')
@section('page-title',Lang::get('labels.client-rates'))
@section('scripts')
@include('scripts.dataTablesScripts')

<script>
    $(document).ready(function () {

        $('body').on('click','.deleteClientRate',function (event) {
            var id = $(event.target).data('clientrateid');
            var row = $(event.target).closest('tr');
            bootbox.confirm("Are you sure?", function(result) {
                if(result){
                    $.ajax({
                        type: "DELETE",
                        url: api_address + 'ClientRates('+id+')',
                        success: function (data) {
                            $(row).remove();
                        },
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
                }
            });
        });

        $('#table-list').DataTable({
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
            "sAjaxSource": api_address+"ClientRates?$expand=SalaryGroup",
            "aoColumns": [
                {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){

                    return '<a href="'+base_url+'/client-rates/show/'+ id+'">'+id+'</a>';
                }},
                { "mData": "Rate",sType:"number"},
                {"mData":"Months",sType:"number"
                },
                {mData:null,oData:"SalaryGroup/Name",sType:"string",mRender: function (obj) {
                    if(obj.SalaryGroup != null){
                        return obj.SalaryGroup.Name
                    }else{
                        return "";
                    }
                }
                },

                {mData:null,sortable:false,sType:"date",mRender:function(obj){
                    return "<a href='"+base_url+"/client-rates/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a> / <i class='fa fa-times deleteClientRate' data-clientRateId='"+obj.Id+"'>"
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
                    <h4><i class="fa fa-barcode"></i> @lang('labels.client-rates')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        @if(isAllowed('clientRates','post'))<a href="{{url('client-rates/create')}}" title="@lang('labels.create-client-rate')"><i class="fa fa-plus"></i></a>@endif
                    </div>
                </div>
                <div class="panel-body">
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped datatables" id="table-list">
                        <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.rate')</th>
                                <th>@lang('labels.months')</th>
                                <th>@lang('labels.salary-group')</th>
                                <th>@lang('labels.actions')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

 