@extends('layout.main')
@section('page-title',Lang::get('labels.edit-salary-group')." ".$clientRate->Rate)
@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function(){

            var form = $('#editClientRate');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
            delete(startItems['_token']);

            $(form).on('submit', function (event) {
                event.preventDefault();

                var clientRateId = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());
                delete(currentItems['_token']);
                var itemsToSubmit = hashDiff( startItems, currentItems);


                // sets null for all empty input
                for (var prop in itemsToSubmit) {
                    if (itemsToSubmit[prop] === "") {
                        itemsToSubmit[prop] = null;
                    }
                }
                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'ClientRates('+clientRateId+')',
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
    {!! Form::hidden('Model','ClientRate',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $clientRate->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4 class="panel-title">@lang('labels.create-client-rate')</h4>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'editClientRate'])!!}

                        <div class="form-group">
                            {!! Form::label('clientRate-Rate',Lang::get('labels.rate'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('Rate',$clientRate->Rate,['class'=>'form-control','id'=>'clientRate-Rate','required'=>'required','step'=>'0.001']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('clientRate-Months',Lang::get('labels.months'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('Months',$clientRate->Months,['class'=>'form-control','id'=>'clientRate-Months']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('clientRate-SalaryGroup_Id',Lang::get('labels.salary-group'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::select('SalaryGroup_Id',withEmpty($salaryGroups),$clientRate->SalaryGroup_Id,['class'=>'form-control','id'=>'clientRate-SalaryGroup_Id']) !!}
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