@extends('layout.main')
@section('page-title',Lang::get('labels.edit-contract')." : ".$contract->Id)

@section('styles')


@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}

<script>
    $(document).ready(function() {
        $( "#contract-StartDate" ).datepicker({
            format: "d-m-Y"
        });

        $( "#contract-EndDate" ).datepicker({
            format: "d-m-Y"
        });

        $( "#contract-NextOptimize" ).datepicker({
            format: "d-m-Y"
        });

      var form = $('#editContract');

        // turn the form into hash map
        var startItems = convertSerializedArrayToHash(form.serializeArray());


        $(form).on('submit', function (event) {
            event.preventDefault();

            var contractId = $('#ModelId').val();

            // find eventual changes
            var currentItems = convertSerializedArrayToHash(form.serializeArray());

            var itemsToSubmit = hashDiff( startItems, currentItems);

            // sets null for all empty input
            for (var prop in itemsToSubmit) {
                if (itemsToSubmit[prop] === "") {
                    itemsToSubmit[prop] = null;
                }
            }
            // custom formatings
            if(itemsToSubmit['Domain']){
                itemsToSubmit['Domain'] = addhttp(itemsToSubmit['Domain']);
            }

            if(itemsToSubmit['StartDate'] != null){
                itemsToSubmit['StartDate'] = new Date(itemsToSubmit['StartDate']);
            }

            if(itemsToSubmit['EndDate'] != null){
                itemsToSubmit['EndDate'] = new Date(itemsToSubmit['EndDate']);
            }
            if(itemsToSubmit['NextOptimize'] != null){
                itemsToSubmit['NextOptimize'] = new Date(itemsToSubmit['NextOptimize']);
            }

            itemsToSubmit.NeedInformation = $('#contract-NeedInformation').prop('checked');


            //send request only if something changed
            if(!$.isEmptyObject(itemsToSubmit)) {
                $.ajax({
                    type: "PATCH",
                    url: api_address + 'Contracts('+contractId+')',
                    data: JSON.stringify(itemsToSubmit),
                    success: function (data) {
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get('messages.update-was-successful'),
                            type: 'success'
                        });
                        saveError('Contract_Update_Log '+JSON.stringify(itemsToSubmit));
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
    {!! Form::hidden('Model','Contract',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $contract->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4>@lang('labels.edit')</h4>
                <div class="options">
                    {{--       If we are editing child task, go back to the parent  --}}
                    <a href="{{URL::previous()}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>                  </div>
            </div>
            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'editContract']) !!}

                    <div class="form-group">
                        {!! Form::label('contract-Domain',Lang::get('labels.homepage'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::text('Domain',$contract->Domain,['class'=>'form-control','id'=>'contract-Domain']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-Status',Lang::get('labels.status'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::select('Status',withEmpty($statuses['ContractStatus']),findEnumNumber($statuses['ContractStatus'],$contract->Status),['class'=>'form-control','id'=>'contract-Status','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-Priority',Lang::get('labels.priority'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::select('Priority',withEmpty($statuses['ContractPriority']),findEnumNumber($statuses['ContractPriority'],$contract->Priority),['class'=>'form-control','id'=>'contract-Priority','required'=>'required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-PaymentTerm',Lang::get('labels.payment-terms'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::select('PaymentTerm',withEmpty($statuses['ContractTerms']),findEnumNumber($statuses['ContractTerms'],$contract->PaymentTerm),['class'=>'form-control','id'=>'contract-PaymentTerm']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-StartDate',Lang::get('labels.start-date'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            <?php $startDate = ($contract->StartDate != null)? Carbon::parse($contract->StartDate)->format('d-m-Y') : null; ?>
                            {!! Form::text('StartDate',$startDate ,['class'=>'form-control','id'=>'contract-StartDate']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-EndDate',Lang::get('labels.end-date'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            <?php $endDate = ($contract->EndDate != null)? Carbon::parse($contract->EndDate)->format('d-m-Y') : null; ?>
                            {!! Form::text('EndDate', $endDate ,['class'=>'form-control','id'=>'contract-EndDate']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-EndDate','Next Optimize',['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            <?php $nextOptimize = ($contract->NextOptimize != null)? Carbon::parse($contract->NextOptimize)->format('d-m-Y') : null; ?>
                            {!! Form::text('NextOptimize', $nextOptimize ,['class'=>'form-control','id'=>'contract-NextOptimize']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-User_Id',Lang::get('labels.seller'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            <?php $seller = ( $contract->User != null)?  $contract->User->Id : null; ?>

                            {!! Form::select('User_Id', withEmpty($users),$seller, ['class' => 'form-control','id'=>'contract-User_Id']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-Manager_Id',Lang::get('labels.assigned-to'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            <?php $manager = ( $contract->Manager != null)?  $contract->Manager->Id : null; ?>

                            {!! Form::select('Manager_Id', withEmpty($users),$manager, ['class' => 'form-control','id'=>'contract-Manager_Id']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-Country_Id',Lang::get('labels.country'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            <?php $country = ( $contract->Country != null)?  $contract->Country->Id : null; ?>

                            {!! Form::select('Country_Id',withEmpty($countries) ,$country, ['class' => 'form-control','id'=>'contract-Country_Id']) !!}
                        </div>
                    </div>


                    <div class="form-group">
                        {!! Form::label('contract-AdwordsId',"AdwordsId",['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::text('AdwordsId',$contract->AdwordsId,['class' => 'form-control','id'=>'contract-AdwordsId','pattern'=>'\b\d{3}[-]?\d{3}[-]?\d{4}\b']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contract-NeedInformation',Lang::get('labels.need-information'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::checkbox('NeedInformation',$contract->NeedInformation,$contract->NeedInformation, ['class' => 'form-control','id'=>'contract-NeedInformation']) !!}
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