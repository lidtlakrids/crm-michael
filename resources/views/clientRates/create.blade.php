@extends('layout.main')
@section('page-title',Lang::get('labels.create-client-rate'))
@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function(){

            $('#createClientRate').on('submit', function (event) {
                event.preventDefault();

                var formData = convertSerializedArrayToHash($(this).serializeArray());

                delete(formData['_token']);

                // sets null for all empty input
                for (var prop in formData) {
                    if (formData[prop] === "") {
                        delete(formData[prop]);
                    }
                }
                formData.Rate = formData.Rate/100;
                $.ajax({
                    type: "POST",
                    url: api_address + 'ClientRates',
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
        });
    </script>

@stop

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4 class="panel-title">@lang('labels.create-client-rate')</h4>
            </div>
            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'createClientRate'])!!}

                    <div class="form-group">
                        {!! Form::label('clientRate-Rate',Lang::get('labels.rate'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('Rate',null,['class'=>'form-control','id'=>'clientRate-Rate','required'=>'required','step'=>'0.01','min'=>0,'max'=>100]) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block">5%, 10 %...</p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('clientRate-Months',Lang::get('labels.months'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('Months',null,['class'=>'form-control','id'=>'clientRate-Months','required'=>'required']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>


                    <div class="form-group">
                        {!! Form::label('clientRate-SalaryGroup_Id',Lang::get('labels.salary-group'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::select('SalaryGroup_Id',withEmpty($salaryGroups),['class'=>'form-control','id'=>'clientRate-SalaryGroup_Id']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>


                    <div class="btn-toolbar">
                        {!! Form::submit(strtoupper(Lang::get('labels.save')),['class'=> 'btn btn-orange btn-label form-control']) !!}
                    </div>
                    {!! Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>

@stop