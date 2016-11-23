@extends('layout.main')
@section('page-title',Lang::get('labels.edit-team')." : ".$team->Name)
@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function(){
            var form = $('#editTeam');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
                delete(startItems['_token']);
            $(form).on('submit', function (event) {
                event.preventDefault();

                var teamId = $('#ModelId').val();
                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());
                delete(currentItems['_token']);
                var itemsToSubmit = hashDiff( startItems, currentItems);
                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'ManagerTeams('+teamId+')',
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
    {!! Form::hidden('Model','ManagerTeam',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $team->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-brown">
                <div class="panel-heading"><h4>@lang('labels.edit-team')</h4>
                    <div class="options">
                        <a href="{{url('teams/show',$team->Id)}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'editTeam']) !!}
                        <div class="form-group">
                            {!! Form::label('Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-3">
                                {!! Form::text('Name',$team->Name,['class'=>'form-control','required'=>'required']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('TeamType',Lang::get('labels.seller'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-3">
                                {!! Form::select('TeamType',($teamTypes),findEnumNumber($teamTypes,$team->TeamType),['class'=>'form-control','required'=>'required']) !!}
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
    </div>
@stop