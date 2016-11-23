@extends('layout.main')
@section('page-title',Lang::get('labels.create-team'))
@section('styles')
@stop

@section('scripts')
<script>
    $('#createTeam').on('submit', function (event) {
        event.preventDefault();

       var formData = convertSerializedArrayToHash($(this).serializeArray());

        delete(formData['_token']);

        // sets null for all empty input
        for (var prop in formData) {
            if (formData[prop] === "") {
                delete(formData[prop]);
            }
        }

        $.ajax({
            type: "POST",
            url: api_address + 'ManagerTeams',
            data: JSON.stringify(formData),
            success: function (data) {
                console.log(data);
                new PNotify({
                    title: Lang.get('labels.success'),
                    text: Lang.get('messages.update-was-successful'),
                    type: 'success'
                });
                window.location=base_url+'/teams/show/'+data.Id;
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
    <div class="row">
        <div class="col-md-6">
            <div class="form-horizontal">
                {!! Form::open(['id'=>'createTeam']) !!}

                <div class="form-group">
                    {!! Form::label('Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('Name',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('TeamType',Lang::get('labels.type'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::select('TeamType',withEmpty($teamTypes),null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>
                <div class="btn-toolbar">
                    {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
                </div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>

@stop