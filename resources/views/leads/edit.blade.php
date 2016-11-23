@extends('layout.main')
@section('page-title',Lang::get('labels.edit-lead')." : ".$lead->Company)
@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function(){
            var form = $('#editLead');

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

                if(itemsToSubmit['Website']) {
                    //validate the url and convert it
                    itemsToSubmit['Website'] = addhttp(itemsToSubmit['Website']);
                }
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
                        url: api_address + 'Leads('+leadId+')',
                        data: JSON.stringify(itemsToSubmit),
                        success: function (data) {
                            new PNotify({
                                title: Lang.get('labels.success'),
                                text: Lang.get('messages.update-was-successful'),
                                type: 'success'
                            });

                            window.location = base_url+'/leads/show/'+getModelId();
                        },
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
                }

            });


            $("#taxonomySearch").autocomplete({
                source: function (request, response) {
                    var str = request.term;
                    $.get(api_address + "Taxonomies?$filter=contains(tolower(Name),'" + str + "')", {},
                            function (data) {
                                response($.map(data.value, function (el) {
                                            return {id: el.Id, label: el.Name};
                                        })
                                );
                            });
                },
                minLength: 2,
                select: function (event, ui) {
                    setTaxonomyId(ui)
                }
            });

            function setTaxonomyId(data) {
                $('input[name=Taxonomy_Id]').val(data.item.id);
            }


        });
    </script>


@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Lead',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $lead->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-lead">
                <div class="panel-heading">
                    <h4><i class="fa fa-plus-square"> </i> @lang('labels.edit-lead')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="{{url('leads/show',$lead->Id)}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>

                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4><i class="fa fa-reorder"></i>Lead Company Info</h4>
                            <div class="form-horizontal">
                                {!! Form::open(['id'=>'editLead']) !!}
                                <div class="form-group">
                                    {!! Form::label('lead-Company',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('Company',$lead->Company,['class'=>'form-control','id'=>'lead-Company','placeholder'=>Lang::get('labels.enter-company-name'),'required'=>'required']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-Website',Lang::get('labels.homepage'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('Website',addHttp($lead->Website),['class'=>'form-control','id'=>'lead-Website','placeholder'=>Lang::get('labels.enter-homepage'),'required'=>'required']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>


                                <div class="form-group">
                                    {!! Form::label('lead-Email',Lang::get('labels.email'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('Email',$lead->Email,['class'=>'form-control','type'=>'email','id'=>'lead-Email','placeholder'=>Lang::get('labels.enter-email')]) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-Phone',Lang::get('labels.phone'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('PhoneNumber',$lead->PhoneNumber,['class'=>'form-control','id'=>'lead-PhoneNumber']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-CINumber',Lang::get('labels.ci-number'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('CINumber',$lead->CINumber,['class'=>'form-control','id'=>'lead-CINumber']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-City',Lang::get('labels.city'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('City',$lead->City,['class'=>'form-control','id'=>'lead-City','placeholder'=>Lang::get('labels.enter-city')]) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-ContactPerson',Lang::get('labels.contact-person'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('ContactPerson',$lead->ContactPerson,['class'=>'form-control','id'=>'lead-ContactPerson']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-ContactPhone',Lang::get('labels.contact-phone'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('ContactPhone',$lead->ContactPhone,['class'=>'form-control','id'=>'lead-ContactPhone','pattern'=>'[+]?\d*']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-ContactEmail',Lang::get('labels.contact-email'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::email('ContactEmail',$lead->ContactEmail,['class'=>'form-control','type'=>'email','id'=>'lead-ContactEmail']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>


                                <div class="form-group">
                                    {!! Form::label('lead-City',Lang::get('labels.country'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('Country_Id',$countries,$lead->Country_Id,['class'=>'form-control','id'=>'lead-Country']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-Type',Lang::get('labels.type'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('Type',$leadTypes,findEnumNumber($leadTypes,$lead->Type),['class'=>'form-control','id'=>'lead-Type','required'=>'required']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="taxonomySearch" class="col-md-3 control-label">@lang('labels.taxonomy')</label>
                                    <div class="col-sm-6">
                                        <input id="taxonomySearch" class="form-control" placeholder="Search category..." value="{{$lead->Taxonomy->Name or ""}}">
                                        <input type="hidden" name="Taxonomy_Id">
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-AdwordsId','AdWords ID',['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('AdwordsId',$lead->AdwordsId,['class'=>'form-control','id'=>'lead-AdwordsId','patter'=>'\b\d{3}[-]?\d{3}[-]?\d{4}\b']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-AnalyticsId','AnalyticsId ID',['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('AnalyticsId',$lead->AnalyticsId,['class'=>'form-control','id'=>'lead-AnalyticsId']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-Source',Lang::get('labels.source'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('Source',$leadSources,findEnumNumber($leadSources,$lead->Source),['class'=>'form-control','id'=>'lead-Source','required'=>'required']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-textSource',Lang::get('labels.source'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('TextSource',$lead->TextSource,['class'=>'form-control','id'=>'lead-textSource']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-Status',Lang::get('labels.status'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('Status',$leadStatuses,findEnumNumber($leadStatuses,$lead->Status),['class'=>'form-control','id'=>'lead-Status','required'=>'required']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-User_Id',Lang::get('labels.assigned-to'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('User_Id',withEmpty($users),$lead->User_Id,['class'=>'form-control','id'=>'lead-User_Id']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="lead-Partner" class="col-sm-3 control-label">@lang('labels.partner')</label>
                                    <div class="col-sm-6">
                                        {!! Form::select('Partner_Id',withEmpty($partners,Lang::get('labels.select-partner')),$lead->Partner_Id,['class'=>'form-control','id'=>"lead-Partner"]) !!}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>{{-- end of first row --}}

                    <div class="row">
                        <div class="col-sm-10" style="text-align: right;">
                            <hr />
                            <div class="btn-toolbar">
                                {!! Form::submit(strtoupper(Lang::get('labels.update')),['class'=> 'btn btn-orange btn-label form-control']) !!}
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop