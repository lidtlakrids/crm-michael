@extends('layout.main')
@section('scripts')

    <script>
        $(document).ready(function () {
            $('#ClientAliasForm').on('submit',function (event) {
                event.preventDefault();
                var form = $(event.target);
                var data = form.serializeJSON();
                delete(data['_token']);
                $.ajax({
                    method: 'POST',
                    url: api_address + 'Clients('+data.Client_Id+')/ClientAlias',
                    data: JSON.stringify(data),
                    success: function (data) {
                        window.location = base_url + '/clientAlias/show/' + data.Id;
                    },
                    error: function(xhr,error,status){
                        // enable the submit button
                        $('#orderSubmitButton').prop('disabled',false);

                        handleError(xhr,error,status);
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            })
        })
    </script>


@stop
@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h4><i class="fa fa-group"></i> @lang('labels.create-alias')</h4>
            </div>
            <div class="panel-body">
                 <!-- TODO REFACTOR THIS -->
                {!! Form::open(['id'=>'ClientAliasForm','class'=>'form-horizontal']) !!}

                {!! Form::hidden('Client_Id',$ClientId) !!}
                <div class="form-group">
                    {!! Form::label('Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-6">
                        {!! Form::text('Name',null,['class'=>'form-control']) !!}
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
                        {!! Form::text('zip',null,['class'=>'form-control']) !!}
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
                    {!! Form::label('CompanyEmail',"Company Email",['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-6">
                        {!! Form::text('CompanyEmail',null,['class'=>'form-control','type'=>'email']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('EMail',"Invoice email",['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-6">
                        {!! Form::text('EMail',null,['class'=>'form-control','type'=>'email']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('CompanyEmail',"Company email",['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-6">
                        {!! Form::text('CompanyEmail',null,['class'=>'form-control','type'=>'email']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('User_Id',Lang::get('labels.seller'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-6">
                        {!! Form::select('User_Id',$users,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('Country_Id',Lang::get('labels.country'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-6">
                        {!! Form::select('Country_Id',$countries,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="btn-toolbar">
                    {!! Form::submit('Create client',['class'=> 'btn btn-primary form-control']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    {{--<div class="col-md-6">--}}
        {{--<div class="panel panel-grape">--}}
            {{--<div class="panel-heading">--}}
                {{--<h4><i class="fa fa-envelope"></i> @lang('labels.contacts')</h4>--}}
            {{--</div>--}}
            {{--<div class="panel-body">--}}
                {{--asd--}}
            {{--</div>--}}
        {{--</div>--}}

    {{--</div>--}}
</div>
@stop