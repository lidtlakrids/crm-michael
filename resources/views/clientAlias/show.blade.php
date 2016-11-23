@extends('layout.main')
@section('page-title',Lang::get('labels.client')." : ".$clientAlias->Name)

@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
@include('scripts.dataTablesScripts')
<script>
    $(document).ready(function () {

        var aliasId   = $('#ModelId').val();
        var aliasName = $('#AliasName').val();

    // creating a draft function

    $('.createDraftBtn').click(function (event) {
        $(event.target).prop('disabled',true);
    //get the alias ID =
        var aliasId = $('#ModelId').val();
        $.ajax({
            type: "POST",
            url: api_address + 'Drafts/action.ByClient',
            data: JSON.stringify({ClientAlias_Id: aliasId}),
            success: function (data) {
                window.location = base_url + '/drafts/show/' + data.Id;
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    });

    // set the contract value
    $('.totalContractsValue').append(parseInt($('#contractsValue').val()).format());

    $('.overdueInvoices').append($('.overdueInvoice').toArray().length);

        $('.createReminder').on('click',function(event){

            var aliasId = getModelId();
            if(typeof aliasId == "undefined" && aliasId == ""){
                new PNotify({
                    title:"Error",
                    text:Lang.get('labels.client-not-set'),
                    type:'error'
                });
                return;
            }
            //disable double clicking
            $(event.target).prop('disabled',true);

            $.post(api_address+"ClientAlias("+aliasId+")/action.CreateReminder")
                    .success(function(data){
                        window.location = base_url+'/drafts/show/'+data.Id;
                    }).error(function(){
            })
        });

        //unlick the select. We need this link for the acl
        $('#ClientManager_Id_Select').change( function (event) {
            var userId = $(event.target).val();
            var clientId = $('#Client_Id').val();
            var userName = $(event.target).find('option:selected').text();
            $.ajax({
                url: api_address + "Clients("+clientId+')',
                type: "PATCH",
                data:JSON.stringify({ClientManager_Id : userId}),
                success: function (data) {
                    $('.contractClientManagerPlaceholder').html(userName);
                    $('#ClientManager_Id_Select').addClass('hidden');
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        $('#editContractClientManager').click(function(){
            $('#ClientManager_Id_Select').toggleClass('hidden');
        });

//        $.when(getEligableDiscount(aliasId))
//                .then(function (data) {
//                   $('.eligibleDiscountPlaceholder').text(data.value+' %')
//                })

        initializeTimeVault('ClientAlias',aliasId);

        var clientNumber = $('.clientPhoneNumber').text();
        console.log(clientNumber);
        if(clientNumber.length > 0) {
            $.ajax({
                url: api_address + "CallLogs/LastCall",
                type: "POST",
                data:JSON.stringify({Number : String(clientNumber).replace('+','').replace('+45','')}),
                success: function (data) {
                    var lastCallPlaceholder = $('.lastCallPlaceholder');
                    if(data == ''){
                        lastCallPlaceholder.text('No information');
                    }else{
                        $.when(fingUserByLocalNumber(data.EmployeeLocalNumber)).then(function (user) {
                            var user = user.value.length > 0 ? user.value[0].FullName : '';
                            lastCallPlaceholder.text(toDateTime(data.TimeStamp)+' '+user)
                        });
                    }
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        }
        nextAppointment(getModel(),getModelId());

});
</script>
@stop
@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','ClientAlias',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $clientAlias->Id,['id'=>'ModelId']) !!}
    {!! Form::hidden('AliasName',$clientAlias->Name,['id'=>'AliasName']) !!}
    {!! Form::hidden('Client_Id',$clientAlias->Client_Id,['id'=>'Client_Id']) !!}
    {{--hidden fields for tasks--}}
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-sky">
            <div class="panel-heading">
                <h4><i class="fa fa-user"> </i> @lang('labels.client')</h4>
                <div class="options">
                    @if(isAllowed('clientAlias','patch'))
                        <a href="{{url('clientAlias/edit',$clientAlias->Id)}}" title="@lang('labels.edit-client')">
                            <i class="fa fa-edit"></i>
                        </a>
                    @endif
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel">
                            <h4><strong>{{$clientAlias->Name  or '---'}}</strong> <a href="http://www.proff.dk/branches%C3%B8g?q={{$clientAlias->Name  or '---'}}" target="_blank"><i class="fa fa-search small"></i></a></h4>
                            <dl class="dl-horizontal-row-2">
                                <dt>@lang('labels.homepage')</dt>
                                <dd><a class="adwordsPendingLinkName" href="{{$clientAlias->Homepage}}" target="_blank">{{$clientAlias->Homepage  or '---'}}</a></dd>

                                <dt>Client mail</dt>
                                <dd> <a href="mailto:{{$clientAlias->CompanyEmail or "---"}}">{{$clientAlias->CompanyEmail or "---"}}</a></dd>

                                <dt>Invoice mail</dt>
                                <dd> <a href="mailto:{{$clientAlias->EMail or "---"}}">{{$clientAlias->EMail or "---"}}</a></dd>

                                <dt>@lang('labels.phone')</dt>
                                <dd>
                                    @if(Auth::user()->localNumber != null)
                                        <span class="pseudolink flexfoneCallOut clientPhoneNumber">{{$clientAlias->PhoneNumber  or ''}}</span>
                                    @else
                                        <a href="tel:{{$clientAlias->PhoneNumber or ""}}" class="clientPhoneNumber">{{$clientAlias->PhoneNumber or ""}}</a>
                                    @endif
                                </dd>

                                <dt>@lang('labels.address')</dt>
                                <dd>{{$clientAlias->Address  or ''}}, {{$clientAlias->Country->CountryCode  or ''}}{{$clientAlias->zip  or ''}} {{$clientAlias->City  or ''}}</dd>

                                <dt>@lang('labels.country')</dt>
                                <dd>{{$clientAlias->Country->CountryCode or ""}}</dd>

                                <dt>Seller</dt>
                                <dd>{{$clientAlias->User->FullName  or ''}}</dd>

                                <dt>@lang('labels.main-contact')</dt>
                                {{--suppose it is the first added--}}
                                <dd>{{$clientAlias->Contact[0]->Name or ""}}</dd>

                                <dt>@lang('labels.ci-number')</dt>
                                <dd><a href="{{url('clients/show',$clientAlias->Client->Id)}}"><strong>{{$clientAlias->Client->CINumber  or '---'}}</strong> </a></dd>

                                {{--<dt>@lang('labels.client-manager')</dt>--}}
                                {{--<dd>--}}
                                    {{--@if(isAllowed('clients','patch'))--}}
                                        {{--<span class="contractClientManagerPlaceholder">--}}
                                    {{--{!! $clientAlias->Client->ClientManager->FullName or Form::select('ClientManager_Id',withEmpty($clientManagers),null,['class'=>'form-control input-sm','id'=>'ClientManager_Id_Select'])!!}--}}
                                    {{--</span>--}}
                                        {{--{!! Form::select('ClientManager_Id',withEmpty($clientManagers),null,['class'=>'form-control input-sm hidden','id'=>'ClientManager_Id_Select']) !!}--}}
                                    {{--@else--}}
                                        {{--{{$clientAlias->ClientAlias->Client->ClientManager->FullName or "--"}}--}}
                                    {{--@endif--}}
                                {{--</dd>--}}
                                <dt>@lang('labels.adwords-id')</dt>
                                <dd class="adwordsIdCheck">
                                    @if(isset($clientAlias->AdwordsId))
                                        {{$clientAlias->AdwordsId}}<span class="adwordsIdOptions"></span>
                                    @elseif(isAllowed('clientAlias','patch'))
                                        <form id="saveAdwordsId">
                                            <input id='clientAdwordsId' name="AdwordsId" min="0" pattern='\b\d{3}[-]?\d{3}[-]?\d{4}\b' required="required">
                                            <input type="hidden" value="{{$clientAlias->Id}}" name="clientAliasId">
                                            <button type="submit">Save</button>
                                        </form>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel">
                            <h4><i class="fa fa-info"></i> @lang('labels.customer-overview')</h4>
                            <div class="customer-overview" style="">
                                <dl class="dl-horizontal-row">
                                    <dt>@lang('labels.class') / @lang('labels.potential')</dt>
                                    <dd>{{$clientAlias->Class or "--"}} / {{$clientAlias->Potential or "--"}}</dd>

                                    <dt>@lang('labels.last-call')</dt>
                                    <dd class="lastCallPlaceholder"></dd>

                                    <dt>Total Value / Month</dt>
                                    <dd class="totalContractsValue">DKK </dd>

                                    <dt>@lang('labels.total-contracts')</dt>
                                    <dd>{{count($clientAlias->Contract)}}</dd>

                                    <dt>@lang('labels.overdue-invoices')</dt>
                                    <dd class="overdueInvoices"></dd>

                                    {{--<dt>@lang('labels.eligible-discount')</dt>--}}
                                    {{--<dd class="eligibleDiscountPlaceholder"></dd>--}}
                                    <dt>Counseling Time</dt>
                                    <dd class="counselingTime spinner" style="position: relative;"></dd>
                                    <dt>Next appointment</dt>
                                    <dd class="next-appointment">...</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div><!-- end row 1 -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group-horizontal text-left">
                            @if(isAllowed('orders','post'))
                                <a id="createOrderBtn" href="{{url('orders/create'."?company=".urlencode($clientAlias->Name).'&url='.$clientAlias->Homepage,[0,$clientAlias->Id])}}" class="btn btn-orange btn-label" style="width: 170px; text-align: left;">
                                    <i class="fa fa-plus"></i> @lang('labels.create-order')
                                </a>
                            @endif
                            @if(isAllowed('drafts','post'))
                                     <button class="btn btn-midnightblue btn-label createDraftBtn" style="width: 170px; text-align: left;"><i class="fa fa-money"></i>@lang('labels.create-draft')</button>
                            @endif
                            @if(isAllowed('invoices','pay'))
                                <button class="btn btn-default btn-label createReminder" title="@lang('labels.create-reminder')">
                                    <i class="fa fa-bomb"></i>@lang('labels.reminder')
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        @include('layout.tabs-section',
                      ['contracts'  => isAllowed('contracts','get')?$clientAlias->Contract:false,
                       'orders'     => isAllowed('orders','get')?true:false,
                       'files'      => isAllowed('fileStorages','get')?true:false,
                       'appointments' => true,
                       'contacts'   =>$clientAlias->Id,
                       'aliasId'    =>$clientAlias->Id,
                       'information'=>$clientAlias,
                       'invoices'   => isAllowed('invoices','get')?$clientAlias->Invoice:[],
                       'drafts'     => isAllowed('drafts','get')?true:null
                      ])
                </div>
            </div>
        </div>
    </div>
</div>
</div> {{--end row--}}

@stop