@extends('layout.main')
@section('page-title',Lang::get('labels.edit-profile')." : ".strtoupper($user->FullName))

@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    <script>
        $(document).ready(function () {


            $( "#user-Birthdate" ).datetimepicker({
                changeMonth: true,
                changeYear: true,
                format: "Y-m-d",
                maxDate: new Date
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

                if(itemsToSubmit.Birtdate){
                    itemsToSubmit.Birthdate = new Date(itemsToSubmit.Birthdate);
                }
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
    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">@lang('labels.edit-profile')</div>

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
                        {!! Form::label('user-Birthdate',Lang::get('labels.birthdate'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            <?php if($user->Birthdate !== null){ $birthdate = Carbon::parse($user->Birthdate)->format('Y-m-d');}else{$birthdate=null;} ?>
                            {!! Form::input('date','Birthdate',$birthdate,['class'=>'form-control','id'=>'user-Birthdate','required'=>'required','autocomplete'=>'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-Address',Lang::get('labels.address'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('Address',$user->Address,['class'=>'form-control','id'=>'user-Address']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('user-PrivatePhone',Lang::get('labels.phone'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::number('PrivatePhone',$user->PrivatePhone,['class'=>'form-control','id'=>'user-PrivatePhone','min'=>'0']) !!}
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