@extends('layout.main')
@section('page-title',Lang::get('labels.create-partner'))
@section('scripts')
    <script>
        $(document).ready(function () {
            $('#createPartnerForm').on('submit',function (event) {
                event.preventDefault();
                var data = $(this).find(':input').filter(function () {
                    return $.trim(this.value).length > 0
                }).serializeJSON();

                if(!validateUrl(addhttp(data.Homepage))){
                    new PNotify({title:Lang.get('messages.homepage-invalid'),type:"error"});
                }else{
                    data.Homepage = addhttp(data.Homepage);
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
                        error: handleError,
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
                }
            });
        });
    </script>
@stop
@section('content')
    <div class="row">
        <form id="createPartnerForm">
        <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h4><i class="fa fa-suitcase"></i> @lang('labels.create-partner')</h4>
            </div>
            <div class="panel-body">
                <div class="form-horizontal">
                    <div class="form-group">
                        {!! Form::label('Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Name',null,['class'=>'form-control','required'=>'required']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('Homepage',Lang::get('labels.homepage'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Homepage',null,['class'=>'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('Address',Lang::get('labels.address'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Address',null,['class'=>'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('zip',Lang::get('labels.zip'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::number('zip',null,['class'=>'form-control','min'=>0,'step'=>1]) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('City',Lang::get('labels.city'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('City',null,['class'=>'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('PhoneNumber',Lang::get('labels.phone'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('PhoneNumber',null,['class'=>'form-control','pattern'=>'[+]?\d*']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('EMail',Lang::get('labels.email'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::email('EMail',null,['class'=>'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('User_Id',Lang::get('labels.user'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::select('User_Id',withEmpty($users),null,['class'=>'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('Country_Id',Lang::get('labels.country'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::select('Country_Id',withEmpty($countries),null,['class'=>'form-control']) !!}
                        </div>
                    </div>
                    <div class="btn-toolbar">
                        {!! Form::submit(Lang::get('labels.create-partner'),['class'=> 'btn btn-primary form-control']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    </form>
</div>
@stop