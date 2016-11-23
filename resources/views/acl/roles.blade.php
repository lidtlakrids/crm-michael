@extends('layout.main')
@section('page-title',Lang::get('labels.roles'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        var table = $('.datatables').DataTable(
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
                    "lengthMenu": [[10,20, 50], [10,20, 50]],
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": api_address+"Roles",
                    "aoColumns": [
                        {mData:"Id","sType":"numeric","width":"7%",mRender:function(id,unused,object,c){
                            return "";
                            }
                        },
                        {mData:"Name",mRender:function(data,unused,obj){
                            var link;
                            link = "<a href='"+base_url+"/acl/roles/show/"+obj.Id+"'>"+data+"</a>";
                            return link;

                            }
                        },
                        {mData:"Description"

                        },
                        {mData:"Default"

                        },
                        {mData:null,oData:null,sortable:false,sType:"date",mRender:function(obj){
                            var links;
                            links = "<a href='"+base_url+"/acl/roles/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>";

                            return links;
                            }
                        }

                    ],
                    "fnServerData": fnServerOData,
                    "iODataVersion": 4,
                    "bUseODataViaJSONP": false

                });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-acl">
                <div class="panel-heading">
                    <h4><i class="fa fa-user"></i> @lang('labels.roles')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{ url('acl/roles/create') }}" title="@lang('labels.create-role')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-condensed datatables table-list" id="example">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.name')</th>
                            <th>@lang('labels.description')</th>
                            <th>@lang('labels.default')</th>
                            <th>@lang('labels.actions')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

@stop