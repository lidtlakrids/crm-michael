@extends('layout.main')
@section('page-title',Lang::get('labels.create-role'))

@section('scripts')
    <script>
        $('#createRole').on('submit', function (event) {
            event.preventDefault();

            var formData = convertSerializedArrayToHash($(this).serializeArray());

            delete(formData['_token']);

            // sets null for all empty input
            for (var prop in formData) {
                if (formData[prop] === "") {
                    delete(formData[prop]);
                }
            }
            formData.Default = (formData.Default)?true:false;
            $.ajax({
                type: "POST",
                url: api_address + 'Roles',
                data: JSON.stringify(formData),
                success: function (data) {
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('messages.update-was-successful'),
                        type: 'success'
                    });
                },
                error: function (err) {
                    new PNotify({
                        title: Lang.get('labels.error'),
                        text: Lang.get(err.statusText),
                        type: 'error'
                    });
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });
    </script>
@stop


@section('content')
    <div class="col-md-12">
        <div class="panel panel-acl">
            <div class="panel-heading">
                <h4><i class="fa fa-plus-square"></i> @lang('labels.create-role')</h4>
            </div>

            <div class="panel-body">
                 <div class="form-horizontal">
                    {!! Form::open(['id'=>'createRole']) !!}
                    <div class="form-group">
                        {!! Form::label('role-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::text('Name',null,['class'=>'form-control','id'=>'role-Name','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('role-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::textarea('Description',null,['class'=>'form-control']) !!}
                        </div>
                    </div>
                    <div class="btn-toolbar">
                        {!! Form::submit('Create role',['class'=> 'btn btn-primary form-control']) !!}
                    </div>
                    {!! Form::close() !!}

                </div>

            </div>


        </div>
    </div>
@endsection