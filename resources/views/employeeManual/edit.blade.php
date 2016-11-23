@extends('layout.main')
@section('page-title',Lang::get('labels.edit')." : ".$manual->Title)


@section('scripts')

    <script>
        $(document).ready(function () {

            $('#editManualForm').on('submit',function(event){
                event.preventDefault();

                var formData = $(this).serializeJSON();

                delete(formData._token);

                console.log(formData);

                $.ajax({
                    type: "PATCH",
                    url: api_address + 'EmployeeManuals(' + getModelId() + ')',
                    data: JSON.stringify(formData),
                    success: function (data) {
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get('messages.update-was-successful'),
                            type: 'success'
                        });
                        window.location = base_url+'/employee-manual'
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
            })
        });
    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','EmployeeManual',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $manual->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-gray">
                <div class="panel-heading">
                    <h4>@lang('labels.edit') @lang('labels.employee-manual')</h4>
                    <div class="options">
                        <a href="{{URL::previous()}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <div class="form-horizontal">
                        {!! Form::open(['id'=>'editManualForm'])!!}

                            <div class="form-group">
                                {!! Form::label('Title',Lang::get('labels.title'),['class'=>'col-md-3 control-label']) !!}
                                <div class="col-md-6">
                                    {!! Form::text('Title',$manual->Title,['class'=>'form-control','required'=>'required']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                                <div class="col-md-6">
                                    {!! Form::text('Description',$manual->Description,['class'=>'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('Content',Lang::get('labels.content'),['class'=>'col-md-3 control-label']) !!}
                                <div class="col-md-6">
                                    {!! Form::textarea('Content',$manual->Content,['class'=>'form-control']) !!}
                                </div>
                            </div>

                            <div class="btn-toolbar">
                                {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
                            </div>
                        {{Form::close()}}


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop