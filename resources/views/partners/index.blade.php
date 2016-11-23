@extends('layout.main')
@section('page-title',Lang::get('labels.partners'))
@section('scripts')
    @include('scripts.dataTablesScripts')
<script>
$(document).ready(function () {

    var table = $('.datatables').DataTable(
    {
        stateSave:true,
        responsive:true,
        searching:true,
        "language": {
            "url": "datatables-"+locale+'.json'
        },
        "oLanguage": {
            "sSearch":       "",
            "sSearchPlaceholder": Lang.get('labels.search')
        },
        "lengthMenu": [[20,50, 100], [20,50, 100]],
        aaSorting:[[0,"desc"]], // shows the newest items first
        "sPaginationType": "full_numbers",
        "bServerSide": true,
        "bProcessing": true,
        filter:"Id ne null",
        "sAjaxSource": api_address+"Partners?$expand=User($select=FullName),ClientAlias($select=Name),Leads($select=Company),Country($select=CountryCode)",
        "aoColumns": [
            {mData:"Id","sType":"numeric",mRender:function(id){
                    return '<a href="'+base_url+'/partners/show/'+ id+'" title="'+Lang.get('labels.see-partner')+'">'+id+'</a>';
                }
            },
            { "mData": "Name",mRender:function(Name,unused,object,c){
                    return '<a href="'+base_url+'/partners/show/'+object.Id+'" title="'+Lang.get('labels.see-partner')+'">'+Name+'</a>';
            }},
            { "mData": "City"},
            { "mData": "PhoneNumber",mRender:function(number){
                    return createCallingLink(canCall,number);
            }},
            {"mData": null, "oData":"Country/CountryCode" ,mRender:function(data){
                    if(data.Country != null){return data.Country.CountryCode}else{ return "---"}
                }
            },
            {"mData": null, "oData":"User/FullName" ,mRender:function(data){
                    if(data.User != null){return data.User.FullName}else{ return "---"}
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
    {!! Form::hidden('Model','Partner',['id'=>'Model']) !!}

    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-suitcase"></i> @lang('labels.partners')</h4>
                    <div class="options">
                        @if(isAllowed('partners','post'))
                            <a href="{{url('partners/create')}}"><i class="fa fa-plus" title="@lang('labels.create-partner')"></i></a>
                        @endif
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-xs-12">
                            <table  id="table-list"  style="width: 100%;" class="table table-hover datatables">
                                <thead>
                                    <tr>
                                        <th>@lang('labels.number')</th>
                                        <th>@lang('labels.name')</th>
                                        <th>@lang('labels.city')</th>
                                        <th>@lang('labels.phone')</th>
                                        <th>@lang('labels.country')</th>
                                        <th>@lang('labels.seller')</th>
                                    </tr>
                                </thead>
                                <tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop



 