@extends('layout.main')
@section('page-title',Lang::get('labels.create-salary-group'))
@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function(){

            $('#createSalaryGroup').on('submit', function (event) {
                event.preventDefault();
                var form = $(this);
                var formData = form.serializeJSON();
                delete(formData['_token']);

                // sets null for all empty input
                for (var prop in formData) {
                    if (formData[prop] === "") {
                        delete(formData[prop]);
                    }
                }

                $.ajax({
                    type: "POST",
                    url: api_address + 'SalaryGroups',
                    data: JSON.stringify(formData),
                    success: function (data) {
                        form[0].reset();
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: "Bonus created. See it <a href='"+base_url+"/salary-groups/show/"+data.Id+"'>here</a>",
                            type: 'success'
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
                <h4 class="panel-title">@lang('labels.create-salary-group')</h4>
            </div>
            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'createSalaryGroup'])!!}

                    <div class="form-group">
                        {!! Form::label('sg-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::text('Name',null,['class'=>'form-control','id'=>'sg-Name','required'=>'required']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('sg-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::textarea('Description',null,['class'=>'form-control','id'=>'sg-Description']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('sg-Salary',Lang::get('labels.salary'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('Salary',null,['class'=>'form-control','id'=>'sg-Salary','required'=>'required','min'=>'0']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>


                    <div class="form-group">
                        {!! Form::label('sg-MinimumTurnover',Lang::get('labels.min-turnover'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('MinimumTurnover',null,['class'=>'form-control','id'=>'sg-MinimumTurnover','min'=>'0','required'=>'required']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('sg-BonusProcentage','Bonus %',['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('BonusProcentage',null,['class'=>'form-control','id'=>'sg-BonusProcentage','min'=>'0','step'=>'0.01','max'=>'100']) !!}
                        </div>
                        <div class="col-sm-3">
                            % , ex - 5 , 6 , 15</p>
                        </div>
                    </div>

                    <div class="btn-toolbar">
                        {!! Form::submit("SAVE",['class'=> 'btn btn-orange btn-label form-control']) !!}
                    </div>
                    {!! Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>

@stop