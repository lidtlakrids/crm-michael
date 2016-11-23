@extends('layout.main')
@section('page-title',Lang::get('labels.edit').'&nbsp;'.Lang::get('labels.ci-number').": ".$client->CINumber);

@section('scripts')
<script>
    $(document).ready(function(){


        var form = $('#editClient');
        // turn the form into hash map
        var startItems = convertSerializedArrayToHash(form.serializeArray());

        // fired when form is submitted
        form.on('submit', function(event){
            //stop the default action
            event.preventDefault();

            // find eventual changes
            var currentItems = convertSerializedArrayToHash(form.serializeArray());
            var itemsToSubmit = hashDiff( startItems, currentItems);

            //send request only if something changed
            if(!$.isEmptyObject(itemsToSubmit)){

            // sets null for all empty input
            for (var prop in itemsToSubmit) {
                if (itemsToSubmit[prop] === "") {
                    itemsToSubmit[prop] = null;
                }
            }

            var ClientId = $('#ModelId').val();
            $.ajax({
                type     : "PATCH",
                url      : api_address+'Clients('+ClientId+')',
                data     : JSON.stringify(itemsToSubmit),
                success  : function(data) {
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('messages.update-was-successful'),
                        type: 'success'
                    });
                },
                error    : function(err)
                {
                    new PNotify({
                        title: Lang.get('labels.error'),
                        text: Lang.get(err.statusText),
                        type: 'error'
                    });
                },
                beforeSend: function (request)
                {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
            }
            event.preventDefault(); //STOP default action
        });
    });
    </script>
@stop

@section('content')

{{--hidden fields for tasks--}}
{!! Form::hidden('Model','Client',['id'=>'Model']) !!}
{!! Form::hidden('ModelId', $client->Id,['id'=>'ModelId']) !!}
{{--hidden fields for tasks--}}

<div class="row">
    <div class="col-md-4">
        <div class="panel panel-grape">
            <div class="panel-heading">
                @lang('labels.edit-client')
                <div class="options">
                    <a href="{{url('clients/show',$client->Id)}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                </div>
            </div>
            <div class="panel-body">
                {!! Form::open(['method'=>'POST','action'=>['ClientAliasController@update',$client->Id],'class'=>'form-horizontal','id'=>'editClient']) !!}

                <div class="form-group">
                    {!! Form::label('CINumber',Lang::get('labels.name'),['class'=>'col-md-3 control-label','required'=>'required']) !!}
                    <div class="col-md-6">
                        {!! Form::text('CINumber',$client->CINumber,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('User_Id',Lang::get('labels.seller'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-6">
                        {!! Form::select('User_Id',withEmpty($sellers),$client->User_Id,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('ClientManager_Id',Lang::get('labels.client-manager'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-6">
                        {!! Form::select('ClientManager_Id',withEmpty($managers),$client->ClientManager_Id,['class'=>'form-control']) !!}
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