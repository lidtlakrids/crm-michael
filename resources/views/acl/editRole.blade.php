@extends('layout.main')
@section('page-title',Lang::get('labels.edit-role'))

@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function() {

            var form = $('#editProduct');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            $(form).on('submit', function (event) {
                event.preventDefault();

                var productId = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());

                var itemsToSubmit = hashDiff( startItems, currentItems);

                // sets null for all empty input
                for (var prop in itemsToSubmit) {
                    if (itemsToSubmit[prop] === "") {
                        itemsToSubmit[prop] = null;
                    }
                }

                //best one-liner ever to exist.
                itemsToSubmit.Default = $('#role-Default').is(':checked')? true:false;

                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'Roles('+role_id+')',
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
    <div class="col-md-12">
        <div class="panel panel-acl">
            <div class="panel-heading">
                <h4><i class="fa fa-edit "></i> @lang('labels.edit-role')</h4>
            </div>

            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'editRole']) !!}
                    <div class="form-group">
                        {!! Form::label('role-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::text('Name',$role->Name,['class'=>'form-control','id'=>'role-Name','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('role-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-3">
                            {!! Form::textarea('Description',$role->Description,['class'=>'form-control','id'=>'role->Description']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('role-Default',Lang::get('labels.default'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::checkbox('Default',$role->Default,['class'=>'form-control','id'=>'role-Default']) !!}
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