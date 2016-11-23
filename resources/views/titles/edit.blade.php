@extends('layout.main')
@section('page-title',Lang::get('labels.edit-title'))

@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function() {

            var form = $('#editRole');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            $(form).on('submit', function (event) {
                event.preventDefault();

                var titleId = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());

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
                        url: api_address + 'Titles('+titleId+')',
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
                                text: Lang.get(err.responseJSON.error.innererror.message),
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
    {!! Form::hidden('Model','Title',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $title->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}


    <div class="col-md-12">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h4>@lang('labels.edit-title')</h4>
            </div>

            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'editRole']) !!}
                    <div class="form-group">
                        {!! Form::label('role-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::text('Name',$title->Name,['class'=>'form-control','id'=>'role-Name','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('role-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::textarea('Description',$title->Description,['class'=>'form-control','id'=>'role->Description']) !!}
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