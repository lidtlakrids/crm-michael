@extends('layout.main')
@section('page-title',Lang::get('labels.edit-partner'))
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            $('#updatePartner').on('submit',function (event) {
                event.preventDefault();
                var data = $(this).serializeJSON();

                $.each(data,function (a,b) {
                    if(b == ""){
                        data[a] = null;
                    }
                });

                if(data.Homepage){
                    data.Homepage = addhttp(data.Homepage);
                    if(!validateUrl(data.Homepage)){
                        new PNotify({title:Lang.get('messages.homepage-invalid'),type:"error"});
                        return;
                    }
                }
                $.ajax({
                    type: "POST",
                    url: api_address + 'Partners',
                    data: JSON.stringify(data),
                    success: function (msg) {
                        new PNotify({
                            title: Lang.get('labels.success'),
                            type: 'success'
                        });
                        window.location = base_url+'/partners/show/'+msg.Id
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });

            $('#partnerLeads').DataTable({
                searching: false,
                paginate : false,
                responsive : true,
                stateSave : true,
                language:{url:'/datatables-'+locale+'.json'},
                "lengthMenu": [[25,50, 100], [25,50,100]],
                aaSorting:[[0,"desc"]], // shows the newest items first
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": api_address+"Leads",
                "filter"     : "Partner_Id eq "+getModelId(),
                'select'     : "Id",
                "aoColumns": [
                    {mData:"Company","sType":"string",sSorting:"desc","width":"7%",mRender:function(id,a,obj){
                        return '<a target="_blank" href="'+base_url+'/leads/show/'+ obj.Id+'">'+Name+'</a>';
                        }
                    },
                    {"sType":"string","mData":"Homepage",mRender:function(data){
                            return '<a target="blank" href="'+ Homepage +'" title="'+Lang.get('labels.see-contract')+'">'+data.ClientAlias.Name+'</a>';

                    }
                    }
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            });
        });
    </script>
@stop
@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Partner',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $partner->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-suitcase"></i>@lang('labels.create-partner')</h4>
                </div>
                <div class="panel-body">
                    <div class="col-md-4">
                        <div class="form-horizontal">
                            <h4>@lang('labels.update-info')</h4>
                            <form id="updatePartner">
                                <div class="form-group">
                                    {!! Form::label('Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::text('Name',$partner->Name,['class'=>'form-control','required'=>'required']) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('Homepage',Lang::get('labels.homepage'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::text('Homepage',$partner->Homepage,['class'=>'form-control']) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('Address',Lang::get('labels.address'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::text('Address',$partner->Address,['class'=>'form-control']) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('zip',Lang::get('labels.zip'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::number('zip',$partner->zip,['class'=>'form-control','min'=>0,'step'=>1]) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('City',Lang::get('labels.city'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::text('City',$partner->City,['class'=>'form-control']) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('PhoneNumber',Lang::get('labels.phone'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::text('PhoneNumber',$partner->PhoneNumber,['class'=>'form-control','pattern'=>'[+]?\d*']) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('EMail',Lang::get('labels.email'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::email('EMail',$partner->EMail,['class'=>'form-control']) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('User_Id',Lang::get('labels.user'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::select('User_Id',withEmpty($users),$partner->User_Id,['class'=>'form-control']) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('Country_Id',Lang::get('labels.country'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::select('Country_Id',withEmpty($countries),$partner->Country_Id,['class'=>'form-control']) !!}
                                    </div>
                                </div>
                                <div class="btn-toolbar">
                                    {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4>@lang('labels.leads')</h4>
                        <div class="table-responsive">
                            <table id="partnerLeads" class="datatables table table-condensed">
                                <thead>
                                    <tr>
                                        <td>@lang('labels.name')</td>
                                        <td>@lang('labels.homepage')</td>
                                        <td>@lang('labels.phone')</td>
                                        <td>@lang('labels.actions')</td>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4>@lang('labels.clients')</h4>
                        <div class="table-responsive">
                            <table id="partnerClients" class="datatables table table-condensed">
                                <thead>
                                <tr>
                                    <td>@lang('labels.name')</td>
                                    <td>@lang('labels.ci-number')</td>
                                    <td>@lang('labels.address')</td>
                                    <td>@lang('labels.phone')</td>
                                    <td>@lang('labels.actions')</td>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop