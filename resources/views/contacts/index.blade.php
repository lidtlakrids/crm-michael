@extends('layout.main')
@section('page-title',Lang::get('labels.contacts'))
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function(){

            var customFilters = '';
            var userId = $('#user-Id').val();
            var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);

            if(!admin){
                roles.forEach(function(role){
                    switch (role){
                        case "Sales":
                            customFilters += " and ClientAlias/User_Id eq '"+userId+"'";
                            break;
                        default:
                            break;
                    }
                });
            }
            var filters = $('.adwordsFilters');

            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function(){
                return this.value;
            });
            var caller = canCall();

            $('.datatables').DataTable(
                {
                    responsive:true,
                    stateSave :true,
                    "language": {
                        "url": "datatables-"+locale+'.json'
                    },
                    "oLanguage": {
                        "sSearch":       "",
                        "sSearchPlaceholder": Lang.get('labels.search')
                    },
                    "lengthMenu": [[20,50,100],[20,50,100]],
                    aaSorting:[[0,"desc"]], // shows the newest items first
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    'filter' : 'Id ne null '+customFilters,
                    "sAjaxSource": api_address+"Contacts?$expand=ClientAlias($select=Id,Name)",
                    "aoColumns": [
                        {mData:"Id","oData":"Id","sType":"numeric","width":"7%",mRender:function(id){

                            return '<a href="'+base_url+'/contacts/edit/'+ id+'" title="'+Lang.get('labels.edit-contact')+'">'+id+'</a>';
                        }},
                        { "mData": "Name"
                        },
                        { "mData": "Phone",sType:'numeric',mRender:function(number){

                            return createCallingLink(caller,number);

                            }
                        },
                        { "mData": "Email"
                        },
                        { "mData": "JobFunction"
                        },
                        {"mData":null, "oData":"ClientAlias/Name","sType":"string",mRender:function(data){
                            if(data.ClientAlias != null){return '<a href="'+base_url+'/clientAlias/show/'+data.ClientAlias.Id+'">'+data.ClientAlias.Name+'</a>'}else{ return "---"}
                        }

                        }
                    ],
                    "fnServerData": fnServerOData,
                    "iODataVersion": 4,
                    "bUseODataViaJSONP": false
                });
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-group"></i> @lang('labels.contacts')</h4>
                </div>
                <div class="panel-body collapse in">
                    <div class="table-responsive">
                        <table id="table-list" class="table table-hover datatables" width="100%">
                            <thead>
                            <tr>
                                <th>@lang('labels.number')</th>
                                <th>@lang('labels.name')</th>
                                <th>@lang('labels.phone')</th>
                                <th>@lang('labels.email')</th>
                                <th>@lang('labels.title')</th>
                                <th>@lang('labels.client')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop



 