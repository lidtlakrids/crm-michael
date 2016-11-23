@extends('layout.main')

@section('page-title',Lang::get('labels.task-templates'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {


            var customFilters = "";
            var userId = $('#user-Id').val();
            var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);

            if(!admin){
                customFilters += ' and (AssignedTo_Id eq \''+userId+'\' or Author_Id eq \''+userId+'\')';
            }

            var table = $('#example').DataTable({
                "language": {
                    "url": "datatables-"+locale+'.json',
                },
                "oLanguage": {
                    "sSearch":       "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                },
                "lengthMenu": [[10,20, 50], [10,20, 50]],
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": api_address+"TaskListTemplates?$expand=Author($select=FullName),AssignedTo($select=FullName)",
                'filter': 'Id ne null'+customFilters,
                "aoColumns": [
                    {mData:"Id","sType":"numeric","width":"7%",mRender:function(id){

                        return '<a href="taskTemplates/show/'+ id+'">'+id+'</a>';
                        }
                    },
                    {mData:"Title"

                    },
                    {mData:"Description",sClass:"multiline"
                    },
                    {mData:"Created",sType:"date",mRender:function(data){
                        var date = new Date(data);
                        return date.toDate();
                        }

                    },
                    {mData:null,oData:"Author/FullName",mRender: function (data) {
                        if(data.Author){
                            return data.Author.FullName;
                        }else{
                            return "--";
                        }
                      }
                    },
                    {mData:null,oData:"AssignedTo/FullName",mRender: function (data) {
                        if(data.AssignedTo){
                            return data.AssignedTo.FullName;
                        }else{
                            return "--";
                        }
                    }
                    },
                    {mData:null,sortable:false,sType:"date",mRender: function (obj) {
                            var links;
                            links = "<a href='"+base_url+"/taskTemplates/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>";

                        return links;
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

    {!! Form::hidden('Model','TaskListTemplate',['id'=>'Model']) !!}

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-barcode"></i> @lang('labels.templates')</h4>
                    <div class="options">
                        <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                        <a href="{{url('taskTemplates/create')}}" title="@lang('labels.create')"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-list cell-border datatables dtr-inline" id="example">
                        <thead>
                        <tr>
                            <th>@lang('labels.number')</th>
                            <th>@lang('labels.name')</th>
                            <th>@lang('labels.description')</th>
                            <th>@lang('labels.created-at')</th>
                            <th>@lang('labels.created-by')</th>
                            <th>@lang('labels.assigned-to')</th>
                            <th>@lang('labels.actions')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop