@extends('layout.main')
@section('page-title',Lang::get('labels.edit-client').": ".$clientAlias->Id)

@section('styles')
    {!! Html::style(asset('css/jquery-ui.min.css')) !!}
    {!! Html::style(asset('css/jquery-ui.structure.min.css')) !!}
    {!! Html::style(asset('css/jquery-ui.theme.min.css')) !!}
@stop

@section('scripts')
<script>
    $(document).ready(function(){
        var aliasForm = $('#editAlias');

            // turn the form into hash map
        var startItems = convertSerializedArrayToHash(aliasForm.serializeArray());

        aliasForm.on('submit', function(event){
            event.preventDefault();
            var btn = $(event.target).find(':submit');
            // find eventual changes
            var currentItems = convertSerializedArrayToHash(aliasForm.serializeArray());
            var itemsToSubmit = hashDiff( startItems, currentItems);
            //send request only if something changed
            itemsToSubmit.Subscribed = $('#ClientAlias-Subscribed').prop('checked');
            if(!$.isEmptyObject(itemsToSubmit)) {
                //disable the button till the form executes
                btn.prop('disabled',true);
                // sets null for all empty input
                for (var prop in itemsToSubmit) {
                    if (itemsToSubmit[prop] === "") {
                        itemsToSubmit[prop]= null;
                    }
                }
                var AliasId = $('#ModelId').val();
                $.ajax({
                    type: "PATCH",
                    url: api_address + 'ClientAlias(' + AliasId + ')',
                    data: JSON.stringify(itemsToSubmit),
                    success: function (data) {
                    window.location = base_url+'/clientAlias/show/'+getModelId();
                    },
                    error: function (err) {
                        new PNotify({
                            title: Lang.get('labels.error'),
                            text: Lang.get(err.statusText),
                            type: 'error'
                        });
                        btn.prop('disabled',false);
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
    {!! Form::hidden('Model','ClientAlias',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $clientAlias->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-group"></i> @lang('labels.edit-alias')</h4>
                    <div class="options">
                        <a href="{{url('clientAlias/show',$clientAlias->Id)}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['method'=>'POST','action'=>['ClientAliasController@update',$clientAlias->Id],'id'=>'editAlias']) !!}
                        <div class="form-group">
                            {!! Form::label('Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Name',$clientAlias->Name,['class'=>'form-control','required'=>'required','autocomplete'=>'off']) !!}
                            </div>
                        </div>
                        {!!  Form::hidden('AliasId', $clientAlias->Id,['id'=>'AliasId']) !!}
                        <div class="form-group">
                            {!! Form::label('Homepage',Lang::get('labels.homepage'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Homepage',$clientAlias->Homepage,['class'=>'form-control']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('Address',Lang::get('labels.address'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Address',$clientAlias->Address,['class'=>'form-control']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('zip',Lang::get('labels.zip'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('zip',$clientAlias->zip,['class'=>'form-control']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('City',Lang::get('labels.city'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('City',$clientAlias->City,['class'=>'form-control']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('PhoneNumber',Lang::get('labels.phone'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('PhoneNumber',$clientAlias->PhoneNumber,['class'=>'form-control','pattern'=>'[+]?\d*']) !!}
                            </div>
                            <div class="col-md-3">
                                <p></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('CompanyEmail',"Client mail",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::email('CompanyEmail',$clientAlias->CompanyEmail,['class'=>'form-control','required'=>'required','autocomplete'=>'off']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('EMail',"Invoice mail",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::email('EMail',$clientAlias->EMail,['class'=>'form-control','required'=>'required','autocomplete'=>'off']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="taxonomySearch" class="col-md-3 control-label">@lang('labels.taxonomy')</label>
                            <div class="col-sm-6">
                                <input id="taxonomySearch" class="form-control" placeholder="Search category..." value="{{$clientAlias->Taxonomy->Name or ""}}">
                                <input type="hidden" name="Taxonomy_Id">
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('AnalyticsId','Analytics',['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('AnalyticsId',$clientAlias->AnalyticsId,['class'=>'form-control']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('AdwordsId','Adwords '.Lang::get('labels.number'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {{--adwords ID allows dashes or no dashes for numbers--}}
                                {!! Form::text('AdwordsId',$clientAlias->AdwordsId,['class'=>'form-control','pattern'=>'\b\d{3}[-]?\d{3}[-]?\d{4}\b']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block">111-111-1111 OR 1234567890</p>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('User_Id',Lang::get('labels.seller'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::select('User_Id',withEmpty($users),$clientAlias->User_Id,['class'=>'form-control']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('Country_Id',Lang::get('labels.country'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::select('Country_Id',withEmpty($countries),$clientAlias->Country_Id,['class'=>'form-control','required'=>'required']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('Subscribed',"Subscribe to newsletter",['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::checkbox('Subscribed',$clientAlias->Subscribed,$clientAlias->Subscribed,['class'=>'form-control','id'=>'ClientAlias-Subscribed']) !!}
                            </div>
                        </div>


                        <div class="form-group">
                            {!! Form::label('Class',Lang::get('labels.class'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::select('Class',withEmpty(['A'=>'A','B'=>'B','C'=>'C']),$clientAlias->Class,['class'=>'form-control']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('Potential',Lang::get('labels.potential'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::select('Potential',withEmpty([1,2,3]),$clientAlias->Potential,['class'=>'form-control']) !!}
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