@extends('layout.main')
@section('page-title',Lang::get('labels.edit-user')." : ".strtoupper($user->UserName))

@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    <script>
        $(document).ready(function () {
            $( "#user-Birthdate" ).datepicker({
                yearRange: "-80:+0", // last hundred years
                changeYear:true,
                changeMonth:true,
                dateFormat:"yy-mm-dd",
                maxDate: new Date()
            });

            var form = $('#editUser');
            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
            delete(startItems['_token']);

            $(form).on('submit', function (event) {
                event.preventDefault();

                var userId = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());

                delete(currentItems['_token']);

                var itemsToSubmit = hashDiff(startItems, currentItems);

                // sets null for all empty input
                for (var prop in itemsToSubmit) {
                    if (itemsToSubmit[prop] === "") {
                        itemsToSubmit[prop] = null;
                    }
                }

                //best one-liner ever to exist.
                itemsToSubmit.Active = $('#user-Active').is(':checked')? true:false;

                if(itemsToSubmit.Email){
                    if(!validateEmail(itemsToSubmit.Email)){
                        new PNotify({
                            title: Lang.get('labels.error'),
                            text: Lang.get('Email is not valid'),
                            type: 'error'
                        });
                        return;
                    }
                }
                //send request only if something changed
                if (!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + "Users('"+ userId + "')",
                        data: JSON.stringify(itemsToSubmit),
                        success: function (data) {
                            new PNotify({
                                title: Lang.get('labels.success'),
                                text: Lang.get('messages.update-was-successful'),
                                type: 'success'
                            });
                            $.post(base_url+"/users/update-current/"+getModelId());

                            window.location = base_url+'/users/show/'+getModelId();
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
    {{--hidden fields for tasks--}}
    {!! Form::hidden('ModelId', $user->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">@lang('labels.edit-user')</div>

            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'editUser']) !!}

                    <div class="form-group">
                        {!! Form::label('user-FullName',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('FullName',$user->FullName,['class'=>'form-control','required'=>'required','id'=>'user->FullName']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-UserName',Lang::get('labels.username'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('UserName',$user->UserName,['class'=>'form-control','id'=>'user-UserName','disabled'=>'disabled']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-EMail',Lang::get('labels.email'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Email',$user->Email,['class'=>'form-control','required'=>'required','id'=>'user-EMail']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-EmployeeLocalNumber',Lang::get('labels.employee-local-number'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::number('EmployeeLocalNumber',$user->EmployeeLocalNumber,['class'=>'form-control','id'=>'user-EmployeeLocalNumber','min'=>0]) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-MyFoneUserName',"MyFone ".Lang::get('labels.username'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('MyFoneUserName',$user->MyFoneUserName,['class'=>'form-control','id'=>'user-MyFoneUserName']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-MyFonePassword',"MyFone ".Lang::get('labels.password'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::password('MyFonePassword',['class'=>'form-control','id'=>'user-MyFonePassword']) !!}
                        </div>
                    </div>


                    <div class="form-group">
                        {!! Form::label('user-Device',Lang::get('labels.device'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::number('Device',$user->Device,['class'=>'form-control','id'=>'user-Device']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Only for more then 1 device"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-Title_Id',Lang::get('labels.title'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::select('Title_Id',withEmpty($titles),$user->Title_Id,['class'=>'form-control','id'=>'user-Title_Id']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('user-SalaryGroup_Id',Lang::get('labels.salary-group'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::select('SalaryGroup_Id',withEmpty($salaryGroups),$user->SalaryGroup_Id,['class'=>'form-control','id'=>'user-SalaryGroup_Id']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-Active',Lang::get('labels.active'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::checkbox('Active',$user->Active,$user->Active,['class'=>'form-control','id'=>'user-Active']) !!}
                        </div>
                    </div>


                    <div class="form-group">
                        {!! Form::label('user-Birthdate',Lang::get('labels.birthdate'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            <?php if($user->Birthdate !== null){ $birthdate = Carbon::parse($user->Birthdate)->format('Y-m-d');}else{$birthdate=null;} ?>
                            {!! Form::text('Birthdate',$birthdate,['class'=>'form-control','id'=>'user-Birthdate','required'=>'required','autocomplete'=>'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-Address',Lang::get('labels.address'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Address',$user->Address,['class'=>'form-control','id'=>'user-Address']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-PrivatePhone','Private Phone' ,['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('PrivatePhone',$user->PrivatePhone,['class'=>'form-control','id'=>'user-PrivatePhone','pattern'=>'[+]?\d*']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-NearestRelatives',Lang::get('labels.nearest-relatives'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::textarea('NearestRelatives',$user->NearestRelatives,['class'=>'form-control','id'=>'user-NearestRelatives']) !!}
                        </div>
                    </div>

                    <div class="btn-toolbar">
                        {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
                    </div>
                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>
</div>
@endsection