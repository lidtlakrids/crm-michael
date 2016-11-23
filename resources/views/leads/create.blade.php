@extends('layout.main')
@section('page-title',Lang::get('labels.create-lead'))
@section('scripts')
<script>
    $(document).ready(function(){

        $('#createLead').on('submit', function (event) {
            event.preventDefault();
            var formData = $(this).find(':input').filter(function () {
                return $.trim(this.value).length > 0
            }).serializeJSON();
            delete(formData['_token']);
//            if(formData.Website){
//                if(!validateUrl(addhttp(formData['Website']))){
//                    new PNotify({
//                        title: "Invalid homepage",
//                        type: 'error'
//                    });
//                    return false;
//                }
//            }
            // check if the lead exists
            $.get(api_address+'Leads?$filter=contains(Website,'+"'"+formData.Website+"')"+
                    (formData.PhoneNumeber ? " or contains(PhoneNumber,'"+formData.PhoneNumber.replace('+','')+"')" : ""))
                .success(function(data){
                    if(data.value.length > 0){
                        // show the existing leads
                        var placeholder = $('.existingLeads');
                        var list = placeholder.find('.similarLeadsList');
                            $.each(data.value,function (a,lead) {
                                list.append("<li><a href='"+base_url+"/leads/show/"+lead.Id+"'>"+lead.Company+" - "+lead.Website+"</a>&nbsp;<a href='"+base_url+"/leads/edit/"+lead.Id+"'><i class='fa fa-pencil'></i></a></li>")
                            });
                        placeholder.removeClass('hidden');
                        new PNotify({'title':Lang.get('messages.similar-leads-exist')});
                        $('html, body').animate({
                            scrollTop:placeholder.offset().top-50
                        }, 50);
                    }else{// else just create it
                        //validate the url and convert it
                        formData['Website'] = addhttp(formData['Website']);
                        $.ajax({
                            type: "POST",
                            url: api_address + 'Leads',
                            data: JSON.stringify(formData),
                            success: function (data) {
                                new PNotify({
                                    title: Lang.get('labels.success'),
                                    text: Lang.get('messages.update-was-successful'),
                                    type: 'success'
                                });
                                window.location = base_url+'/leads/show/'+data.Id;
                            },
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });

                    }
                });
        });
        
         $('#createLeadAnyway').on('click',function (event) {
             var form = $('#createLead');
             var formData = form.find(':input').filter(function () {
                 return $.trim(this.value).length > 0
             }).serializeJSON();
             delete(formData['_token']);
             formData['Website'] = addhttp(formData['Website']);
             $.ajax({
                 type: "POST",
                 url: api_address + 'Leads',
                 data: JSON.stringify(formData),
                 success: function (data) {
                     new PNotify({
                         title: Lang.get('labels.success'),
                         text: Lang.get('messages.update-was-successful'),
                         type: 'success'
                     });
                     window.location = base_url+'/leads/show/'+data.Id;
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
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-lead">
                <div class="panel-heading">
                    <h4><i class="fa fa-plus-square"> </i> @lang('labels.create-lead')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><i class="fa fa-reorder"></i>@lang('labels.create-lead')</h4>
                            <div class="form-horizontal">
                                {!! Form::open(['id'=>'createLead']) !!}
                                <div class="form-group">
                                    {!! Form::label('lead-Company',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                    {!! Form::text('Company',null,['class'=>'form-control','id'=>'lead-Company','placeholder'=>Lang::get('labels.enter-company-name'),'required'=>'required']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-Website',Lang::get('labels.homepage'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('Website',null,['class'=>'form-control','id'=>'lead-Website','placeholder'=>Lang::get('labels.enter-homepage'),'required'=>'required']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="lead-Email" class="col-sm-3 control-label">e-mail:</label>
                                    <div class="col-sm-6">
                                        <input type="email" class="form-control" name="Email" id="lead-Email" placeholder="Angiv e-mail" >
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-Phone',Lang::get('labels.phone'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('PhoneNumber',null,['class'=>'form-control','id'=>'lead-Phone','placeholder'=>Lang::get('labels.enter-phone'),'pattern'=>'[+]?\d*']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="lead-CI-Number" class="col-sm-3 control-label">CI-Number:</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="CINumber" class="form-control" id="lead-CI" placeholder="@lang('labels.ci-number')">
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-City',Lang::get('labels.city'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('City',null,['class'=>'form-control','id'=>'lead-City','placeholder'=>Lang::get('labels.enter-city')]) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-City',Lang::get('labels.country'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('Country_Id',$countries,null,['class'=>'form-control','id'=>'lead-Country']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-ContactPerson',Lang::get('labels.contact-person'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('ContactPerson',null,['class'=>'form-control','id'=>'lead-ContactPerson']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-ContactPhone',Lang::get('labels.contact-phone'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('ContactPhone',null,['class'=>'form-control','id'=>'lead-ContactPhone','pattern'=>'[+]?\d*']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-ContactEmail',Lang::get('labels.contact-email'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::email('ContactEmail',null,['class'=>'form-control','id'=>'lead-ContactEmail','type'=>'email']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="taxonomySearch" class="col-md-3 control-label">@lang('labels.taxonomy')</label>
                                    <div class="col-sm-6">
                                        <input id="taxonomySearch" class="form-control" placeholder="Search category...">
                                        <input type="hidden" name="Taxonomy_Id">
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-Type',Lang::get('labels.type'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('Type',withEmpty($leadTypes),null,['class'=>'form-control','id'=>'lead-Type']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-AdwordsId','AdWords ID',['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('AdwordsId',null,['class'=>'form-control','id'=>'lead-AdwordsId','pattern'=>'\b\d{3}[-]?\d{3}[-]?\d{4}\b']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-AnalyticsId','AnalyticsId ID',['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('AnalyticsId',null,['class'=>'form-control','id'=>'lead-AnalyticsId']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-textSource',Lang::get('labels.source'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::text('TextSource',null,['class'=>'form-control','id'=>'lead-textSource']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-Status',Lang::get('labels.status'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('Status',withEmpty($leadStatuses),null,['class'=>'form-control','id'=>'lead-Status','required'=>'required']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('lead-User_Id',Lang::get('labels.assigned-to'),['class'=>'col-md-3 control-label']) !!}
                                    <div class="col-sm-6">
                                        {!! Form::select('User_Id',withEmpty($users),Auth::user()->externalId,['class'=>'form-control','id'=>'lead-User_Id']) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="lead-Partner" class="col-sm-3 control-label">@lang('labels.partner')</label>
                                    <div class="col-sm-6">
                                     {!! Form::select('Partner_Id',withEmpty($partners,Lang::get('labels.select-partner')),null,['class'=>'form-control','id'=>"lead-Partner"]) !!}
                                    </div>
                                </div>
                                <div class="btn-toolbar">
                                    <hr />
                                    {!! Form::submit(strtoupper(Lang::get('labels.save')),['class'=> 'btn btn-orange btn-label form-control']) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}

                        <div class="col-md-6 existingLeads hidden">
                            <h4>@lang('labels.similar-leads')</h4>
                            <ul class="similarLeadsList">
                            </ul>
                            <button class="btn btn-green" id="createLeadAnyway">@lang('labels.create-anyway')</button>
                        </div>

                    </div>{{-- end of first row --}}
                </div>
            </div>
        </div>
    </div>
@stop