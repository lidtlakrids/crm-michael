@extends('layout.main')
@section('page-title',Lang::get('labels.edit-salary-group').'&nbsp;'.$sg->Name)
@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function(){

            var form = $('#editSalaryGroup');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
            delete(startItems['_token']);

            $(form).on('submit', function (event) {
                event.preventDefault();

                var leadId = $('#ModelId').val();

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
                        url: api_address + 'SalaryGroups('+leadId+')',
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
    {!! Form::hidden('Model','SalaryGroup',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $sg->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4 class="panel-title">@lang('labels.edit-salary-group')</h4>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'editSalaryGroup'])!!}

                        <div class="form-group">
                            {!! Form::label('sg-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::text('Name',$sg->Name,['class'=>'form-control','id'=>'sg-Name','required'=>'required']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('sg-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::textarea('Description',$sg->Description,['class'=>'form-control','id'=>'sg-Description']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('sg-Salary',Lang::get('labels.salary'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('Salary',$sg->Salary,['class'=>'form-control','id'=>'sg-Salary']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>


                        <div class="form-group">
                            {!! Form::label('sg-MinimumTurnover',Lang::get('labels.min-turnover'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('MinimumTurnover',$sg->MinimumTurnover,['class'=>'form-control','id'=>'sg-MinimumTurnover','min'=>'0']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('sg-BonusProcentage','Bonus %',['class'=>'col-md-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::number('BonusProcentage',$sg->BonusProcentage,['class'=>'form-control','id'=>'sg-BonusProcentage','min'=>'0','step'=>'0.01','max'=>'100']) !!}
                            </div>
                            <div class="col-sm-3">
                                % , ex - 5 , 6 , 15</p>
                            </div>
                        </div>

                        <div class="btn-toolbar">
                            {!! Form::submit(strtoupper(Lang::get('labels.update')),['class'=> 'btn btn-orange btn-label form-control']) !!}
                        </div>
                        {!! Form::close()!!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop