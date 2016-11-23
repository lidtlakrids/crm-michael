@extends('layout.main')
@section('page-title',Lang::get('labels.create-title'))

@section('scripts')
    <script>
        $('#createTitle').on('submit', function (event) {
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
                url: api_address + 'Titles',
                data: JSON.stringify(formData),
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
        });
    </script>
@stop


@section('content')
    <div class="col-md-12">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h4>@lang('labels.create-title')</h4>
            </div>

            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'createTitle']) !!}
                    <div class="form-group">
                        {!! Form::label('title-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::text('Name',null,['class'=>'form-control','id'=>'title-Name','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('title-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::textarea('Description',null,['class'=>'form-control','id'=>'title-Description']) !!}
                        </div>
                    </div>
                    <div class="btn-toolbar">
                        {!! Form::submit(Lang::get('labels.save'),['class'=> 'btn btn-primary form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection