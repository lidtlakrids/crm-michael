@extends('layout.main')
@section('page-title',Lang::get('labels.create-user'))

@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    <script>
        $(document).ready((function() {
            $('input#user-Birthdate').datepicker({
                dateFormat:"yy-mm-dd",
                changeYear:true,
                yearRange: "-100:+0"
            });

            $('#createUser').on('submit', function (event) {
                event.preventDefault();

                var formData = convertSerializedArrayToHash($(this).serializeArray());

                delete(formData['_token']);

                // sets null for all empty input
                for (var prop in formData) {
                    if (formData[prop] === "") {
                        delete(formData[prop]);
                    }
                }

                formData.Active = (formData.Active)?true:false;

                formData.Birthdate = new Date(formData.Birthdate);

                $.ajax({
                    type: "POST",
                    url: api_address + 'Users',
                    data: JSON.stringify(formData),
                    success: function (data) {
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get('messages.user-created-successfully'),
                            type: 'success'
                        });
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });
        }));
    </script>
@stop

@section('content')

<div class="col-md-12">
    <div class="panel panel-grape">
        <div class="panel-heading">@lang('labels.create-user')</div>

        <div class="panel-body">
            <div class="form-horizontal">
                {!! Form::open(['id'=>'createUser']) !!}

                    <div class="form-group">
                        {!! Form::label('user-FullName',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::text('FullName',null,['class'=>'form-control','required'=>'required','id'=>'user->FullName']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                    {!! Form::label('user-UserName',Lang::get('labels.username'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                         {!! Form::text('UserName',null,['class'=>'form-control','id'=>'user-UserName','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-EMail',Lang::get('labels.email'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::email('Email',null,['class'=>'form-control','required'=>'required','id'=>'user-EMail']) !!}
                        </div>
                    </div>
                <div class="form-group">
                    {!! Form::label('user-MyFoneUserName',"MyFone ".Lang::get('labels.username'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('MyFoneUserName',null,['class'=>'form-control','id'=>'user-MyFoneUserName']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('user-MyFonePassword',"MyFone ".Lang::get('labels.password'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('MyFonePassword',null,['class'=>'form-control','id'=>'user-MyFonePassword']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('user-Device',Lang::get('labels.device'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::number('Device',null,['class'=>'form-control','id'=>'user-Device']) !!}
                    </div>
                    <div class="col-sm-3">
                        <p class="help-block"><i class=" fa fa-info-circle" title="Only for more then 1 device"></i></p>
                    </div>
                </div>
                    <div class="form-group">
                        {!! Form::label('user-Title_Id',Lang::get('labels.title'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::select('Title_Id',withEmpty($titles),null,['class'=>'form-control','id'=>'user-Title_Id']) !!}
                        </div>
                    </div>
                <div class="form-group">
                    {!! Form::label('user-SalaryGroup_Id',Lang::get('labels.salary-group'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::select('SalaryGroup_Id',withEmpty($salaryGroups),null,['class'=>'form-control','id'=>'user-SalaryGroup_Id']) !!}
                    </div>
                </div>


                <div class="form-group">
                        {!! Form::label('user-Password',Lang::get('labels.password'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::password('Password',null,['class'=>'form-control','id'=>'user-Password','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-Active',Lang::get('labels.active'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::checkbox('Active',null,['class'=>'form-control','id'=>'user-Active']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-Birthdate',Lang::get('labels.birthdate'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::input('date','Birthdate',null,['class'=>'form-control','id'=>'user-Birthdate']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-Address',Lang::get('labels.address'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::text('Address',null,['class'=>'form-control','id'=>'user-Address']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-PrivatePhone',Lang::get('labels.phone'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::number('PrivatePhone',null,['class'=>'form-control','id'=>'user-PrivatePhone','min'=>'0']) !!}
                        </div>
                    </div>


                    <div class="form-group">
                        {!! Form::label('user-NearestRelatives',Lang::get('labels.nearest-relatives'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-4">
                            {!! Form::textarea('NearestRelatives',null,['class'=>'form-control','id'=>'user-NearestRelatives']) !!}
                        </div>
                    </div>

                    <div class="btn-toolbar">
                        {!! Form::submit('Add user',['class'=> 'btn btn-primary form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
        </div>
    </div>
</div>
@endsection