@extends('layout.main')
@section('page-title',Lang::get('labels.create-country'))

@section('scripts')
    <script>
        $('#createCountry').on('submit', function (event) {
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
                url: api_address + 'Countries',
                data: JSON.stringify(formData),
                success: function (data) {
                    console.log(data);
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

    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">@lang('labels.create-country')</div>

            <div class="panel-body">
                {!! Form::open(['url'=>'countries/store','class'=>'form-horizontal','id'=>'createCountry']) !!}

                <div class="form-group">
                    {!! Form::label('CountryCode',Lang::get('labels.country-code'),['class'=>'col-md-4 control-label','required'=>'required']) !!}
                    <div class="col-md-3">
                        {!! Form::text('CountryCode',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('Name',Lang::get('labels.name'),['class'=>'col-md-4 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('Name',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('PhoneExtension',Lang::get('labels.phone-code'),['class'=>'col-md-4 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('PhoneExtension',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('VatRate',Lang::get('labels.vat'),['class'=>'col-md-4 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::number('VatRate',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="btn-toolbar">
                    {!! Form::submit(Lang::get('labels.create'),['class'=> 'btn btn-primary form-control']) !!}
                </div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>

@stop