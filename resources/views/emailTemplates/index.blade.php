@extends('layout.main')
@section('page-title',Lang::get('labels.templates'))
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            $('#example').dataTable({
                responsive:true,
                saveState: true,
                "language": {
                    "url": "datatables-"+locale+'.json',
                },
                "oLanguage": {
                    "sSearch": "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                },
                "lengthMenu": [[20,50,100], [20,50,100]],
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": api_address+"Templates",
                "aoColumns": [
                    {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){

                        return '<a href="'+base_url+'/emailTemplates/show/'+ id+'">'+id+'</a>';
                    }},
                    { "mData": "Model", "oData": "Model","sName":"Model" },
                    {
                        mData:"TemplateData",oData:"TemplateData"
                    },
                    {mData:null,oData:null,sortable:false,mRender:function(obj){
                        return "<a href='"+base_url+"/emailTemplates/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>"

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

    {!! Form::hidden('Model','Template',['id'=>'Model']) !!}

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-barcode"></i> @lang('labels.templates')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('emailTemplates/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-striped datatables" id="example">
                            <thead>
                                <tr>
                                    <th>@lang('labels.number')</th>
                                    <th>@lang('labels.name')</th>
                                    <th>@lang('labels.description')</th>
                                    <th>@lang('labels.actions')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

