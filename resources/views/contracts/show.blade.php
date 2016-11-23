@extends('layout.main')
@section('page-title',Lang::get('labels.contract')." : ".$contract->Id)

@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
    {!! Html::script(asset('js/lib/seo-section.js/')) !!}
    @include('scripts.dataTablesScripts')
    <script>
    $(document).ready(function () {
        var model   = $('#Model').val();
        var modelId = $('#ModelId').val();

    $('.createNewDraft').on('click',function(event){
        $(event.target).attr('disabled',true);
        var clientId = $(event.target).data('client-id');
        if(clientId != 0){
            $.ajax({
                type: "POST",
                url: api_address + 'Drafts/action.ByClient',
                data: JSON.stringify({ClientAlias_Id:clientId}),
                success: function (data) {
                    window.location.replace(base_url+'/drafts/show/'+data.Id);
                },
                error: handleError,
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        }
        $(event.target).attr('disabled',false);
        return false;
    });

        //starting a contract
    $('.startContract').on('click',function (event) {

        //get the comment for the star
        var comment = $('#startComment').val();
        console.log(comment);
        if(comment == ""){
            
        }
    });
        //de-click the select. We need this link for the acl
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
                error: handleError,
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        $('#editContractClientManager').click(function(){
            $('#ClientManager_Id_Select').toggleClass('hidden');
        });

        //set active tab depending on the team status
        $('.stepy-header').find('.'+team_status.toLowerCase()).addClass('stepy-active');

        $('#assignOrderToContract').on('submit',function (event) {
            event.preventDefault();
            var form = $(this);
            var btn = form.find(':submit');
            btn.prop('disabled',true);

            var data = form.serializeJSON();
            $.ajax({
                url: api_address + "Contracts("+getModelId()+')',
                type: "PATCH",
                data:JSON.stringify(data),
                success: function (data) {
                    new PNotify({
                        title: 'Order associated. Refreshing...'
                    });
                    location.reload();
                },
                error: function (error) {
                    btn.prop('disabled',false);
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        initializeTimeVault('Contract',modelId)
    });
</script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Contract',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $contract->Id,['id'=>'ModelId']) !!}
    {!! Form::hidden('Client_Id',$contract->ClientAlias->Client->Id,['id'=>"Client_Id"]) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="panel panel-contract">
            <div class="panel-heading">
                <h4><i class="fa fa-file"></i> {{$contract->Product->Name}} @lang('labels.contract')</h4>
                <div class="options">
                    <a href="#" title="@lang('labels.view-client')"><i class="fa fa-info-circle"></i></a>
                    @if(isAllowed('contracts','patch'))<a href="{{url('contracts/edit',$contract->Id)}}" title="@lang('labels.edit-contract')"><i class="fa fa-edit"></i></a>@endif
                    @if($contract->Parent_Id == null && $contract->ProductPackage_Id != null) <!-- only upgrade and renew parent contracts -->
                        <a href="{{url('contracts/upgrade',$contract->Id)}}" title="@lang('labels.upgrade')"><i class="fa fa-level-up"></i></a>
                        @if($contract->StartDate != null)@endif<a href="{{url('contracts/renew',$contract->Id)}}" title="@lang('labels.renew')"><i class="fa fa-refresh"></i></a>
                    @endif
                </div>
            </div>
        <div class="panel-body">
            <div class="row">
                <ul id="basicwizard-header" class="stepy-header">
                    <li id="basicwizard-head-0"  class="assign" style="cursor: default;">
                        <div>1. @lang('labels.assign')</div>
                    </li>
                    <li id="basicwizard-head-1"  class="production" style="cursor: default;">
                        <div>2. @lang('labels.production')</div>
                    </li>
                    <li id="basicwizard-head-3" class="starting"  style="cursor: default;">
                        <div>3. @lang('labels.starting')</div>
                    </li>
                    <li id="basicwizard-head-3"  class="optimize" style="cursor: default;">
                        <div>4. @lang('labels.optimize')</div>
                    </li>
                    <li id="basicwizard-head-3" class="suspended standby"  style="cursor: default;">
                        <div>5. @lang('labels.stopped')</div>
                    </li>
                </ul>
            </div>
            <div class="row">
                <!-- contract status -->
                <div class="col-md-12">
                        <div class="
                            @if($contract->Status == 'Active')
                                alert-success
                            @elseif($contract->Status == 'Standby')
                                alert-warning
                            @elseif($contract->Status == 'Suspended' || $contract->Status == 'Cancelled')
                                alert-danger
                            @else
                                alert-info
                            @endif
                                text-center">
                            This contract is {{$contract->Status}}
                        </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="panel-row">
                        <div class="row">
                            <div class="col-md-6">
                                <h3>
                                    <strong>
                                        {!! Html::linkAction('ClientAliasController@show',$contract->ClientAlias->Name , array($contract->ClientAlias->Id))!!}
                                    </strong>
                                    <a href="http://www.proff.dk/branches%C3%B8g?q={{$contract->ClientAlias->Name  or '---'}}" target="_blank">
                                        <i class="fa fa-search small"></i>
                                    </a>
                                </h3>
                            </div>
                            <div class="col-md-6 pull-right"> <h4>Time Logged : <span class="counselingTime spinner" style="position: relative;"></span></h4></div>
                        </div>
                        <hr style="pading:5px; margin:5px;" />
                        <dl class="dl-horizontal-row-2">
                            <dt>@lang('labels.product')</dt>
                            <dd><strong>{!! Html::linkAction('ProductsController@show', $contract->Product->Name , array($contract->Product->Id)) !!}</strong></dd>

                            <dt>@lang('labels.homepage')</dt>
                            <dd><a href="{{addHttp($contract->Domain)}}" class="adwordsPendingLinkName" target="_blank">{{$contract->Domain or $contract->ClientAlias->Homepage}}</a></dd>

                            <dt>@lang('labels.created-date')</dt>
                            <dd>{{toDateTime($contract->Created)}}</dd>

                            <dt>@lang('labels.start-date')</dt>
                            <dd>@if($contract->StartDate != null){{toDate($contract->StartDate)}} @else &nbsp; @endif</dd>

                            <dt>@lang('labels.end-date')</dt>
                            <dd>@if($contract->EndDate != null){{toDate($contract->EndDate)}}@else &nbsp; @endif </dd>

                            <dt>Next Optimization</dt>
                            <dd>
                                <strong style="color: @if(Carbon::parse($contract->NextOptimize)->isPast()) red @else green @endif">
                                    @if($contract->NextOptimize != null){{toDate($contract->NextOptimize)}} @else -- @endif
                                </strong>
                            </dd>

                            <dt>Runlength (binding period) </dt>
                            <dd>{{$contract->RunLength or ""}}</dd>

                            <dt>Invoice terms</dt>
                            <dd>{{$contract->PaymentTerm or ""}}</dd>

                            <dt>@lang('labels.email')</dt>
                            <dd><a href="mailto:{{$contract->ClientAlias->CompanyEmail or $contract->ClientAlias->EMail}}">{{$contract->ClientAlias->CompanyEmail or $contract->ClientAlias->EMail}}</a></dd>

                            <dt>@lang('labels.phone')</dt>
                            <dd>
                                @if(Auth::user()->localNumber != null)
                                    <span class="pseudolink flexfoneCallOut">{{$contract->ClientAlias->PhoneNumber  or '---'}}</span>
                                @else
                                    <a href="tel:{{$contract->ClientAlias->PhoneNumber or "---"}}">{{$contract->ClientAlias->PhoneNumber or "---"}}</a>
                                @endif
                            <dt>@lang('labels.address')<!--, @lang('labels.city') & @lang('labels.country')--></dt>
                            <dd>{{$contract->ClientAlias->Address or "--"}}, {{$contract->ClientAlias->zip or "--"}} {{$contract->ClientAlias->City or "--"}}, {{$contract->Country->CountryCode or "---"}}</dd>

                            <dt>@lang('labels.main-contact')</dt>
                            <dd>{{$contract->ClientAlias->Contact[0]->Name or "--"}}</dd>

                            <dt>@lang('labels.contact-phone')</dt>
                            <dd><a href="" title="@lang('labels.call-main-contact')">{{$contract->ClientAlias->Contact[0]->Phone or "--"}}</a></dd>

                            <dt>@lang('labels.seller')</dt>
                            <dd> {{$contract->User->FullName or "--"}}</dd>

                            {{--<dt>@lang('labels.client-manager')</dt>--}}
                            {{--<dd>--}}
                                {{--@if(isAllowed('clients','patch'))--}}
                                    {{--<span class="contractClientManagerPlaceholder">--}}
                                    {{--{!! $contract->ClientAlias->Client->ClientManager->FullName or Form::select('ClientManager_Id',withEmpty($clientManagers),null,['class'=>'form-control input-sm','id'=>'ClientManager_Id_Select'])!!}--}}
                                    {{--</span>--}}
                                {{--@else--}}
                                    {{--{{$contract->ClientAlias->Client->ClientManager->FullName or "--"}}--}}
                                {{--@endif--}}
                            {{--</dd>--}}
                            <dt>@lang('labels.production-manager')</dt>
                            <dd>{{$contract->Manager->FullName or "--"}}</dd>

                            <dt>@lang('labels.adwords-id')</dt>
                            <dd class="adwordsIdCheck">
                                @if(isset($contract->AdwordsId))
                                    {{$contract->AdwordsId}}<span class="adwordsIdOptions"></span>
                                @elseif(isset($contract->ClientAlias->AdwordsId))
                                    {{$contract->ClientAlias->AdwordsId}}
                                    <span class="adwordsIdOptions"></span>

                                    <a href="#" class="quickSaveContractId alert-info" data-type="text" data-pk="{{$contract->Id}}" title="This is the Adwords ID of the Client. Click here, if the contract should have different Adwords ID">
                                        Client Adwords Id
                                    </a>
                                @elseif(isAllowed('contracts','patch'))
                                    <a href="#" class="quickSaveContractId alert-info" data-type="text" data-pk="{{$contract->Id}}" title="Click to save Adwords Id on the Contract">
                                        Set Adwords Id
                                    </a>
                                @endif
                            </dd>

                            <dt>@lang('labels.payment-status')</dt>
                            <dd><strong style="color: @if($contract->Invoice['PaymentStatus']=="Paid") green @else red @endif ;">
                                    {{$contract->Invoice['PaymentStatus']}}
                                    @if($contract->Invoice['PaymentStatus']=="Paid")
                                        {{toDate(end($contract->Invoice['Invoices'])->Payed)}}
                                    @endif
                                </strong>
                            </dd>
                        </dl>
                        <div id="accordion" class="panel-group panel-info">
                            <div class="panel panel-default">
                                <a class="collapsed" href="#collapseOne" data-parent="#accordion" data-toggle="collapse">
                                    <div class="panel-heading">
                                        <h4>Pause options</h4>
                                    </div>
                                </a>
                                <div id="collapseOne" class="panel-collapse collapse" style="height: 0px;">
                                    @if(!empty($contract->Activity) && strcmp(end($contract->Activity)->ActivityType,"Pause"))
                                        <div class="form-inline">
                                            <form id="pauseContractForm">
                                                <div class="form-group-sm">
                                                    <textarea name="Comment" rows="1" cols="40" class="form-control" placeholder="@lang('labels.pause-comment')" required="required"></textarea>
                                                </div>
                                                <div class="btn-toolbar" style="margin-top:10px;">
                                                    <button type="submit" class="btn btn-warning" id="startContractPause">@lang('labels.start-pause')</button>
                                                </div>
                                            </form>
                                        </div>
                                    @else
                                        <div class="form-inline">
                                            <form id="resumeContractForm">
                                                <div class="form-group-sm">
                                                    <textarea name="Comment" rows="1" cols="40" class="form-control" placeholder="@lang('labels.comment')" required="required"></textarea>
                                                </div>
                                                <div class="btn-toolbar" style="margin-top:10px;">
                                                    <button type="submit" class="btn btn-warning" id="startContractPause">@lang('labels.resume')</button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="make-draft pull-right" style="padding-top:20px;">
                            @if($contract->Product->ProductType != null)
                                <?php
                                    if(in_array(strtolower($contract->Product->ProductType->Name),['seo','social','local','google+'])){
                                        $url = 'seo';
                                    }else{
                                        $url = 'adwords';
                                    }
                                ?>
                                <a href="{{url($url.'/show',$contract->Id)}}" class="btn btn-inverse">{{$contract->TeamStatus}}</a>
                            @endif

                            @if(isAllowed('drafts','post'))
                                <a href="#" class="btn btn-inverse createNewDraft" data-client-id="{{$contract->ClientAlias->Id or 0}}">@lang('labels.create-draft')</a>
                            @endif
                        </div><!-- end row btn draft -->
                   </div>
               </div>

                <div class="col-md-6">
                   <div class="panel-contract-info">
                       <div class="panel-body" style="height: 650px;">
                           <div class="row">
                               @if($contract->Parent != null && $contract->ProductPackage_Id != null)
                                   @if($contract->Parent->NeedInformation && empty($contract->InformationSchemes))
                                       <a class="btn btn-green btn-md" href="{{url('orders/information',$contract->Parent_Id)}}">@lang('labels.get-information')</a>
                                   @else
                                       {{-- check if the contract doesn't need info by default and give option to change that--}}
                                       @if(!$contract->Parent->NeedInformation)
                                           @lang('messages.contract-does-not-need-info')
                                           @if(!empty($contract->Parent->InformationSchemes))
                                               <div class="action-btn"><br />
                                                   <a class="btn btn-adwords" href="{{url('information/show',end($contract->Parent->InformationSchemes)->Id)}}">@lang('labels.newest-information-scheme')</a>
                                               </div>
                                           @endif
                                       @elseif(!empty($contract->Parent->InformationSchemes))
                                           <dl class="dl-horizontal">
                                               <dt>@lang('labels.newest-information-scheme')</dt>
                                               <dd>
                                                   <a class="btn btn-green btn-xs" href="{{url('information/show',end($contract->Parent->InformationSchemes)->Id)}}">{{end($contract->Parent->InformationSchemes)->Id}}</a>
                                               </dd>
                                               <dt>@lang('labels.get-information')</dt>
                                               <dd>
                                                   <a class="btn btn-green btn-xs" href="{{url('orders/information',$contract->Parent->Id)}}">@lang('link')</a>
                                               </dd>
                                           </dl>
                                       @endif
                                   @endif
                               @else
                                   @if($contract->NeedInformation && empty($contract->InformationSchemes))
                                        <a class="btn btn-green btn-md" href="{{url('orders/information',$contract->Id)}}">@lang('labels.get-information')</a>
                                   @else
                                       {{-- check if the contract doesn't need info by default and give option to change that--}}
                                       @if(!$contract->NeedInformation)
                                           @lang('messages.contract-does-not-need-info')
                                           @if(!empty($contract->InformationSchemes))
                                            <div class="action-btn"><br />
                                                <a class="btn btn-adwords" href="{{url('information/show',end($contract->InformationSchemes)->Id)}}">@lang('labels.newest-information-scheme')</a>
                                            </div>
                                           @endif
                                        @elseif(!empty($contract->InformationSchemes))
                                            <dl class="dl-horizontal">
                                                <dt>@lang('labels.newest-information-scheme')</dt>
                                                <dd>
                                                    <a class="btn btn-green btn-xs" href="{{url('information/show',end($contract->InformationSchemes)->Id)}}">{{end($contract->InformationSchemes)->Id}}</a>
                                                </dd>
                                                <dt>@lang('labels.get-information')</dt>
                                                <dd>
                                                    <a class="btn btn-green btn-xs" href="{{url('orders/information',$contract->Id)}}">@lang('link')</a>
                                                </dd>
                                            </dl>
                                        @endif
                                    @endif
                               @endif
                           </div>
                           <hr>
                           <div class="panel panel-order">
                               <div class="panel-heading">
                                   <h4><i class="fa fa-info-circle"></i> @lang('labels.order')</h4>
                                   <div class="options">
                                       <a class="panel-collapse" href="#"><i class="fa fa-chevron-down"></i></a>
                                   </div>
                               </div>
                               <div class="panel-body">
                                   @if(isset($contract->OriginalOrder->Id))
                                       <a href="{{url('orders/show',$contract->OriginalOrder_Id)}}" target="_blank" class="btn btn-sky">See Order</a>

                                       <dl class="dl-horizontal">
                                           @foreach($contract->OriginalOrder->OrderFieldValue as $fieldVal)
                                               <dt>{{$fieldVal->OrderField->DisplayName}}</dt>
                                               <dd>@if($fieldVal->OrderField->OrderFieldType =='Textarea')<pre>{{$fieldVal->value}}</pre> @else {{$fieldVal->value}} @endif</dd>
                                           @endforeach
                                       </dl>
                                   @else
                                       @if(isAdmin())
                                           <form id="assignOrderToContract">
                                               <label for="contractOrderId">Order ID</label>
                                               <input id='contractOrderId' type="number" name="OriginalOrder_Id" min="0" required="required">
                                               <button type="submit">Save</button>
                                           </form>
                                       @else
                                           @lang('messages.order-not-set')
                                       @endif
                                   @endif
                               </div>
                           </div>
                           <hr>
                           <div class="panel panel-contract">
                               <div class="panel-heading">
                                   <h4>
                                       <i class="fa  fa-folder-open"></i>
                                       @if($contract->ProductPackage_Id != null)
                                           Add-ons
                                       @else
                                           @lang('labels.sub-contracts')
                                       @endif
                                       <small>@if(count($contract->Children)>0) ({{count($contract->Children)}})@endif</small>
                                   </h4>
                                   <div class="options">
                                       <a class="panel-collapse" href="#"><i class="fa fa-chevron-down"></i></a>
                                       @if($contract->ProductPackage_Id != null && $contract->Parent_Id == null)
                                           <a title="Select Add-ons" href="{{url('contracts/addons',$contract->Id)}}"><i class="fa fa-plus"></i></a>
                                       @endif
                                   </div>
                               </div>
                               <div class="panel-body">
                                   @if($contract->Parent_Id == null)
                                           @if(!empty($contract->Children))
                                               <div class="table-responsive">
                                                   <table id="table-list" class="table table-condensed table-hover">
                                                       <thead>
                                                       <tr>
                                                           <th>@lang('labels.product')</th>
                                                           <th>@lang('labels.start-date')</th>
                                                           <th>@lang('labels.end-date')</th>
                                                           <th>@lang('labels.actions')</th>
                                                       </tr>
                                                       </thead>
                                                       <tbody>
                                                       @foreach($contract->Children as $child)
                                                           <tr data-child-contract="{{$child->Id}}">
                                                               <td><a href="{{url('contracts/show',$child->Id)}}">{{$child->Product->Name or "---"}}</a></td>
                                                               <td>@if($child->StartDate != null){{toDate($child->StartDate)}} @endif</td>
                                                               <td>@if($child->EndDate != null){{toDate($child->EndDate)}} @endif</td>
                                                               <td>
                                                                   <a href="{{url('contracts/edit',$child->Id)}}" title="Edit Contract"><i class="fa fa-edit"></i></a>
                                                               </td>
                                                           </tr>
                                                       @endforeach
                                                       </tbody>
                                                   </table>
                                               </div>
                                               @endif
                                       @else
                                           <div class="alert alert-dismissable alert-info">
                                               <h3>@lang('labels.addon-contract')</h3>
                                               <p>@lang('messages.addon-contract') : {{$contract->Parent->Product->Name}}</p>
                                               <br>
                                               <p>
                                                   @if($contract->Product->ProductType != null)
                                                       <?php
                                                       if(in_array(strtolower($contract->Product->ProductType->Name),['seo','social','local','google+'])){
                                                           $url = 'seo';
                                                       }else{
                                                           $url = 'adwords';
                                                       }
                                                       ?>
                                                       <a href="{{url($url.'/show',$contract->Parent_Id)}}" class="btn btn-info">@lang('labels.see-parent-contract')</a>
                                                   @endif
                                               </p>
                                           </div>
                                       @endif
                               </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- TABS -->
                <div class="col-md-12">
                    @include('layout.tabs-section',
                    ['files'      =>true,
                     'admin' => true,
                     'invoices'=>$contract->Invoice['Invoices'],
                     'drafts'=>isAllowed('drafts','get')?true:null,
                     'contacts'=>$contract->ClientAlias_Id,
                     'contractId'=>$contract->Id,
                     'information' => $contract->ClientAlias,
                     'orders'   => $contract->OriginalOrder,
                     'timeline'   =>$contract->Activity,
                     'appointments' => true,
                     'clientLogins'=>true,
                     'seo'=>in_array($contract->ContractType_Id,[3,8,18,20])?true:null
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
    {{-- End  Row --}}

@stop