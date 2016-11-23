@extends('layout.main')
@section('page-title', 'White Lists')
@section('styles')
@stop
@section('scripts')
    <script>
        $('#createWhiteList').on('submit', function (event) {
            event.preventDefault();

            var formData = convertSerializedArrayToHash($(this).serializeArray());

            delete(formData['_token']);

            // sets null for all empty input
            for (var prop in formData) {
                if (formData[prop] === "") {
                    delete(formData[prop]);
                }
            }

            formData.Permanent = $('#wl-permanent').prop("checked");
            formData.Active = $('#wl-active').prop('checked');

            function validateIpAddress(ip){
                var regex = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]|[%])$/;
                return regex.test(ip);
            }

            if(formData.ipaddress){
                if(!validateIpAddress(formData.ipaddress)){
                    new PNotify({
                        title: Lang.get('labels.error'),
                        text: "The Ip Address is not valid",
                        type: 'error'
                    });
                    return;
                }
            }

            $.ajax({
                type: "POST",
                url: api_address + 'Whitelists',
                data: JSON.stringify(formData),
                success: function (data) {
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('messages.update-was-successful'),
                        type: 'success'
                    });
                    window.location = base_url+'/white-lists';
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });
    </script>
    @stop
@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-grape">
                <div class="panel-heading"><h4><i class="fa fa-list-ul"></i> Create white list</h4>
                    <div class="options">
                        {{--<a href="{{url('taskTemplates/show',($taskTemplate->ParentTaskListId == null)? $taskTemplate->Id : $taskTemplate->ParentTaskListId)}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>--}}
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'createWhiteList']) !!}
                        <div class="form-group">
                            {!! Form::label('wl-ipaddress',"IP address",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('ipaddress',null,['class'=>'form-control','required'=>'required','id'=>'wl-ipaddress']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('wl-permanent',"Permanent",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-4">
                                {!! Form::checkbox('Permanent',null,null,['class'=>'form-control','id'=>'wl-permanent']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('wl-active',"Active",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-4">
                                {!! Form::checkbox('Active',null,null,['class'=>'form-control','id'=>'wl-active']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('wl-user',"User",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::select('User[Id]', withEmpty($users), null, ['class' => 'form-control', 'id'=>'wl-user']) !!}
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