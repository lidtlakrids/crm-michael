@extends('layout.main')
@section('page-title',Lang::get('labels.edit-template')." : ".$template->Model)
@section('styles')
@stop

@section('scripts')
    {!! Html::script(asset('js/form-ckeditor/ckeditor.js')) !!}
    <script>
        $(document).ready(function(){

            var form = $('#editTemplate');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
            delete(startItems['_token']);

            $(form).on('submit', function (event) {
                event.preventDefault();

                var templateId = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());
                delete(currentItems['_token']);
                var itemsToSubmit = hashDiff( startItems, currentItems);
                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "Patch",
                        url: api_address + 'Templates('+templateId+')',
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
    {!! Form::hidden('Model','Template',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $template->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-brown">
                <div class="panel-heading"><h4>@lang('labels.edit-template')</h4>
                    <div class="options">
                        <a href="{{URL::previous()}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'editTemplate']) !!}
                        <div class="form-group">
                            {!! Form::label('Model',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-3">
                                {!! Form::text('Model',$template->Model,['class'=>'form-control','required'=>'required','id'=>'Model']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::textarea('TemplateData',$template->TemplateData,['class'=>'form-control','id'=>'templateEditor']) !!}
                        </div>



                        <div class="btn-toolbar">
                            {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
                        </div>
                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop