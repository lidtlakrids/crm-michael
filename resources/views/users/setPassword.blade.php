@extends('layout.main')

@section('page-title',Lang::get('labels.change-password'))

@section('scripts')
    <script>
        $(document).ready(function(){

            var userId = $('#ModelId').val();
            $('#changePasswordForm').on('submit', function (event) {
                event.preventDefault();

                var formData = convertSerializedArrayToHash($(this).serializeArray());

                // sets null for all empty input
                for (var prop in formData) {
                    if (formData[prop] === "") {
                        delete(formData[prop]);
                    }
                }
                console.log(formData);
                if(formData.NewPassword != formData.ConfirmPassword){
                    new PNotify({
                        title: Lang.get('labels.error'),
                        text: Lang.get('messages.passwords-do-not-match'),
                        type: 'error'
                    });
                    return false;
                }

                var data = {};
                data.SetPassword=formData;

                $.ajax({
                    type: "POST",
                    url: api_address + "Users('"+userId+"')/action.SetPassword",
                    data: JSON.stringify(data),
                    success: function (data) {
                        console.log(data);
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
            });
        });
    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','User',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $userId,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}

    <div class="row">
        <div class="col-md-6">
            <div class="form-horizontal">
                {!! Form::open(['id'=>'changePasswordForm']) !!}

                <div class="form-group">
                    {!! Form::label('user-Password',Lang::get('labels.password'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-sm-6">
                        {!! Form::password('NewPassword',['class'=>'form-control','id'=>'user-Password','required'=>'required']) !!}
                    </div>
                    <div class="col-sm-3">
                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('user-ConfirmPassword',Lang::get('labels.confirm-password'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-sm-6">
                        {!! Form::password('ConfirmPassword',['class'=>'form-control','id'=>'user-ConfirmPassword','required'=>'required']) !!}
                    </div>
                    <div class="col-sm-3">
                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                    </div>
                </div>

                <div class="btn-toolbar">
                    <hr />
                    {!! Form::submit(strtoupper(Lang::get('labels.save')),['class'=> 'btn btn-orange btn-label form-control']) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>

    </div>

@stop