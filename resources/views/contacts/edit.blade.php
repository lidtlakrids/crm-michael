@extends('layout.main')
@section('page-title',Lang::get('labels.edit-contact')." : ".$contact->Name)
@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function(){
            var form = $('#editContact');
            $('input.contact-birthdate:text').datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                yearRange: "-100:+0"
            });
            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            // delete the token
            delete(startItems['_token']);

            $(form).on('submit', function (event) {
                event.preventDefault();

                var contactId = $('#ModelId').val();

                // find eventual changes
                var currentItems = form.serializeJSON({checkboxUncheckedValue: "false"});
                delete(currentItems['_token']);
                var itemsToSubmit = hashDiff( startItems, currentItems);
                //send request only if something changed
                if(itemsToSubmit.Facebook){
                    itemsToSubmit.Facebook = addhttp(itemsToSubmit.Facebook);
                    if(!validateUrl(itemsToSubmit.Facebook)){
                        var fbField = $('#contact-Facebook');
                        fbField.focus();
                        fbField.closest('.form-group').addClass('has-error');
                        new PNotify({
                            title:"Enter a valid Facebook link",
                            type:"error"
                        });
                        return;
                    }
                }
                if(itemsToSubmit.LinkedIn){
                    itemsToSubmit.LinkedIn = addhttp(itemsToSubmit.LinkedIn);
                    if(!validateUrl(itemsToSubmit.LinkedIn)){
                        var linkedinField = $('#contact-LinkedIn');
                        linkedinField.focus();
                        linkedinField.closest('.form-group').addClass('has-error');
                        new PNotify({
                            title:"Enter a valid LinkedIn link",
                            type:"error"
                        });
                        return;
                    }
                }

                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'Contacts('+contactId+')',
                        data: JSON.stringify(itemsToSubmit),
                        success: function (data) {
                            new PNotify({
                                title: Lang.get('labels.success'),
                                text: Lang.get('messages.update-was-successful'),
                                type: 'success'
                            });
                            startItems = convertSerializedArrayToHash(form.serializeArray())
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
    {!! Form::hidden('ModelId', $contact->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-contract">
                <div class="panel-heading"><h4>@lang('labels.edit-contact')</h4>
                    <div class="options">
                        <a href="{{URL::previous()}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'editContact']) !!}
                        <div class="form-group">
                            {!! Form::label('contact-Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Name',$contact->Name,['class'=>'form-control','required'=>'required','id'=>'contact-Name']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('contact-Phone',Lang::get('labels.phone'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Phone',$contact->Phone,['class'=>'form-control','id'=>'contact-Phone']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('contact-Title',Lang::get('labels.title'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('JobFunction',$contact->JobFunction,['class'=>'form-control','id'=>'contact-Title']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('contact-Email',Lang::get('labels.email'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::email('Email',$contact->Email,['class'=>'form-control','id'=>'contact-Email']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact-Birthdate" class="col-sm-3 control-label">@lang('labels.birthdate')</label>
                            <div class="col-sm-6">
                                <input type="text" placeholder="@lang('labels.birthdate')" id="contact-Birthdate" value="@if($contact->Birthdate != null){{date('d-m-Y',strtotime($contact->Birthdate))}}@endif" name="Birthdate" class="form-control contact-birthdate">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact-Department" class="col-sm-3 control-label">@lang('labels.department')</label>
                            <div class="col-sm-6">
                                <select name="Department" id="contact-Department" class="form-control">
                                    <option value="">@lang('labels.select')</option>
                                    <option value="@lang('labels.owner')">@lang('labels.owner')</option>
                                    <option value="@lang('labels.sales')">@lang('labels.sales')</option>
                                    <option value="@lang('labels.management')">@lang('labels.management')</option>
                                    <option value="@lang('labels.accounting')">@lang('labels.accounting')</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('contact-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::textarea('Description',$contact->Description,['class'=>'form-control','id'=>'contact-Description']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact-Facebook"
                                   class="col-md-3 control-label">Facebook link</label>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" id="contact-Facebook"
                                       placeholder="Facebook" value="{{$contact->Facebook}}" name="Facebook" type="text" pattern="^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?" >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact-LinkedIn"
                                   class="col-md-3 control-label">LinkedIn link</label>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" id="contact-LinkedIn"
                                       placeholder="LinkedIn" value="{{$contact->LinkedIn}}" name="LinkedIn" type="text" pattern="^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?">
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="contact-MainContact"
                                   class="col-md-3 control-label">Main Contact <i class="fa fa-question" title="If you select this, other main contact will be updated and this one will become the main one."></i>
                            </label>
                            <div class="col-sm-6">
                                {!! Form::checkbox('MainContact','true',$contact->MainContact,['class'=>'form-control','id'=>'contact-MainContact']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact-ReceiveReports"
                                   class="col-md-3 control-label">Reporting person <i class="fa fa-question" title="Is this the person that should receive reports"></i>
                            </label>
                            <div class="col-sm-6">
                                {!! Form::checkbox('ReceiveReports','true',$contact->ReceiveReports,['class'=>'form-control','id'=>'contact-ReceiveReports']) !!}
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