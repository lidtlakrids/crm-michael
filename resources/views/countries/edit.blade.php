@extends('layout.main')
@section('page-title',Lang::get('labels.country')." : ".$country->Id)
@section('scripts')
    <script>
        $(document).ready(function () {
            var form = $('#editCountry');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
            delete(startItems['_token']);

            $(form).on('submit', function (event) {
                event.preventDefault();

                var countryId = $('#ModelId').val();

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
                if(itemsToSubmit.VatRate){
                    console.log(1);
                }


                //send request only if something changed
                if (!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'Countries(' + countryId + ')',
                        data: JSON.stringify(itemsToSubmit),
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
                }

            });
        });
    </script>

@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Country',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $country->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">@lang('labels.create-country')</div>

            <div class="panel-body">
                {!! Form::open(['class'=>'form-horizontal','id'=>'editCountry']) !!}

                <div class="form-group">
                    {!! Form::label('CountryCode',Lang::get('labels.country-code'),['class'=>'col-md-4 control-label','required'=>'required']) !!}
                    <div class="col-md-3">
                        {!! Form::text('CountryCode',$country->CountryCode,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('Name',Lang::get('labels.name'),['class'=>'col-md-4 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('Name',$country->Name,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('PhoneExtension',Lang::get('labels.phone-code'),['class'=>'col-md-4 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('PhoneExtension',$country->PhoneExtension,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('VatRate',Lang::get('labels.vat'),['class'=>'col-md-4 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::number('VatRate',$country->VatRate,['class'=>'form-control','required'=>'required','step'=>'0.01']) !!}
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
@stop
