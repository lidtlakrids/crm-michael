@extends('layout.main')
@section('page-title',"AdWords ".Lang::get('labels.contract')." : ".$contract->Id)
@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')

    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
    <script>
    $(document).ready(function(){
            var model   = $('#Model').val();
            var modelId = $('#ModelId').val();

            $('.startOptimize').on('click',function(event){
                var btn = $(event.target);
                btn.prop('disabled',true);
                $.ajax({
                    type: "POST",
                    url: api_address + 'Contracts('+modelId+')/action.StartOptimize',
                    success: function (data) {
                        $('.optimizationMenu').removeClass('hidden');
                        btn.prop('disabled',false);
                        $(event.target).addClass('hidden');
                    },
                    error: function (err) {
                        btn.prop('disabled',false);
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

                $(event.target).css('pointer-events','');
                return false;
            });

            $('#endOptimizationForm').on('submit',function(event){
                event.preventDefault();
                var data = $(this).serializeJSON();
                var form = $(event.target);
                var btn = form.find(':submit');
                //disable the link untill the function is over
                btn.prop('disabled',true);
                $.ajax({
                    type: "POST",
                    url: api_address + 'Contracts('+modelId+')/action.EndOptimize',
                    data:JSON.stringify(data),
                    success: function () {
                        btn.prop('disabled',false);
                        form[0].reset();
                        $('.optimizationMenu').addClass('hidden');
                        $('.startOptimize').removeClass('hidden');
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get("messages.optimize-completed"),
                            type: 'success'
                        });
                    },
                    error: function (err) {
                        btn.prop('disabled',false);
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });

            $('.produceContract').on('click',function(event){
                //disable the link untill the function is over
                $(event.target).css('pointer-events','none');
                $.ajax({
                    type: "POST",
                    url: api_address + 'Contracts('+modelId+')/action.Produced',
                    success: function () {
                      location.reload(true);
                    },
                    error: function (error) {
                        $(event.target).css('pointer-events','');
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
                return false;
            });

            $('#startContractForm').on('submit',function(event){
                event.preventDefault();
                var data = $(this).serializeJSON();
                var form = $(event.target);
                var btn = form.find(':submit');
                var startupMail = false;
                if(data.StartupMail){
                    startupMail = true;
                    delete(data.StartupMail);
                }
                btn.prop('disabled',true);

                $.ajax({
                    type: "POST",
                    url: api_address + 'Contracts('+modelId+')/action.Start',
                    data:JSON.stringify(data),
                    success: function () {
                        if(startupMail){
                            $.get(api_address+"Contracts("+modelId+')/SendStartupMail')
                                .success(function () {
                                    location.reload(true);
                                })
                        }else{
                            location.reload(true);
                        }
                    },
                    error: function (error) {
                        btn.prop('disabled',false)
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });

            //set active tab depending on the team status
            $('.stepy-header').find('.'+team_status.toLowerCase()).addClass('stepy-active');

            $('.optimizeComment').on('change', function (event) {
                var textarea = $('#optimizeNote');
                var html = textarea.val();

                var val = $(event.target).val();

                if(!$(event.target).prop('checked')){
                    textarea.val(textarea.val().replace("Worked on "+val+'\n',''));
                }else{
                    textarea.val(textarea.val()+Lang.get('labels.worked-on')+' '+val+ '\n');
                }
            });

            // check if we have startup meeting for this contract
            if(team_status == "Starting") {
                findStartupMeeting();
            }
        });
    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Contract',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $contract->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="panel panel-adwords">
            <div class="panel-heading">
                <h4><i class="fa fa-file"></i> {{$contract->Product->Name}} - @lang('labels.'.strtolower($contract->TeamStatus))</h4> <!-- product name - status -->
                <div class="options">
                    @if(empty($contract->InformationSchemes) && $contract->NeedInformation)
                        <a href="{{url('orders/information',$contract->Id)}}" title="@lang('labels.information')">
                            <i class="fa fa-info-circle"></i>
                        </a>
                    @endif
                    @if(isAllowed('contracts','patch'))
                        <a href="{{url('adwords/edit',$contract->Id)}}" title="@lang('labels.edit-contract')">
                            <i class="fa fa-edit"></i>
                        </a>
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
                    <div class="col-md-4">
                        <h4><strong><a href="{{url('clientAlias/show',$contract->ClientAlias->Id)}}"><i class="fa fa fa-user"></i> {{$contract->ClientAlias->Name or "--"}}</a></strong></h4>
                            <dl class="dl-horizontal-row-2">
                                <dt>@lang('labels.product')</dt>
                                <dd><strong>{{$contract->Product->Name or "--"}}</strong></dd>
                                <dt>@lang('labels.homepage')</dt>
                                <dd><a href="{{isset($contract->Domain) ? addHttp($contract->Domain): addHttp($contract->ClientAlias->Homepage)}}" class="adwordsPendingLinkName">{{$contract->Domain or $contract->ClientAlias->Homepage}}</a></dd>
                                <dt>@lang('labels.period')</dt>
                                <dd>
                                    @if($contract->StartDate != null && $contract->EndDate != null)
                                       {{toDate($contract->StartDate)}}  => {{toDate($contract->EndDate)}}
                                    @else
                                        @lang('labels.starting')
                                    @endif
                                </dd>

                                <dt>Runlength (binding period) </dt>
                                <dd>{{$contract->RunLength or ""}} months</dd>

                                <dt>
                                    @if($contract->ProductPackage_Id != null && $contract->Parent_Id == null)
                                        Last Optimization
                                    @else
                                        Next Optimization
                                    @endif
                                </dt>

                                <dd>
                                    @if($contract->NextOptimize != null)
                                        {{date('d-m-Y',strtotime($contract->NextOptimize))}}
                                    @else
                                        @lang('labels.unknown')
                                    @endif
                                </dd>
                                <dt>@lang('labels.city')</dt>
                                <dd>{{$contract->ClientAlias->City or "--"}}</dd>

                                <dt>@lang('labels.main-contact')</dt>
                                <dd>{{$contract->ClientAlias->Contact[0]->Name or "--"}}</dd>
                                <dt>@lang('labels.contact-phone')</dt>
                                <dd>
                                    @if(Auth::user()->localNumber != null)
                                        <span title="@lang('labels.call-main-contact')" class="pseudolink flexfoneCallOut">{{$contract->ClientAlias->Contact[0]->Phone  or ''}}</span>
                                    @else
                                        <a title="@lang('labels.call-main-contact')" href="tel:{{$contract->ClientAlias->Contact[0]->Phone or ""}}">{{$contract->ClientAlias->Contact[0]->Phone or ""}}</a>
                                    @endif
                                </dd>

                                <dt>@lang('labels.country')</dt>
                                <dd>{{$contract->Country->CountryCode or "-"}}</dd>
                                <dt>@lang('labels.seller') </dt>
                                <dd>{{$contract->User->FullName or "-"}}</dd>
                                <dt> @lang('labels.client-manager')</dt>
                                <dd>{{$contract->ClientAlias->Client->ClientManager->FullName or "--"}}</dd>
                                <dt>@lang('labels.production-manager')</dt>
                                <dd>
                                    {{$contract->Manager->FullName or "--"}}
                                    {!! Form::hidden('Manager_Id',$contract->Manager_Id,['id'=>'contract-Manager_Id']) !!}
                                </dd>
                                <dt>@lang('labels.contract-number')</dt>
                                <dd><a href="">{{$contract->Id}}</a></dd>
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
                                <dd>
                                    <strong style="color: @if($contract->Invoice['PaymentStatus'] == "Paid" && isset($contract->Invoice['PaymentStatus'])) green @else red @endif">
                                        {{$contract->Invoice['PaymentStatus'] or Lang::get('labels.unknown')}}
                                        @if($contract->Invoice['PaymentStatus']=="Paid")
                                            {{toDate(end($contract->Invoice['Invoices'])->Payed)}}
                                        @endif
                                    </strong>
                                </dd>

                                <dt>@lang('labels.information-scheme')</dt>
                                <dd>
                                    @if($contract->Parent != null && $contract->ProductPackage_Id != null)
                                        @if(empty($contract->Parent->InformationSchemes))
                                            @if($contract->Parent->NeedInformation)
                                                <a target="_blank" href="{{url('orders/information',$contract->Parent->Id)}}">@lang('labels.get-information')</a>
                                            @else
                                                @lang('messages.contract-does-not-need-info')
                                            @endif
                                        @else
                                            <a target="blank" href="{{url('information/show',end($contract->Parent->InformationSchemes)->Id)}}">@lang('labels.newest-information-scheme')</a>
                                        @endif
                                    @else
                                        @if(empty($contract->InformationSchemes))
                                            @if($contract->NeedInformation)
                                                <a target="_blank" href="{{url('orders/information',$contract->Id)}}">@lang('labels.get-information')</a>
                                            @else
                                                @lang('messages.contract-does-not-need-info')
                                            @endif
                                        @else
                                            <a target="blank" href="{{url('information/show',end($contract->InformationSchemes)->Id)}}">@lang('labels.newest-information-scheme')</a>
                                        @endif
                                    @endif
                                </dd>
                            </dl>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                        @if(in_array($contract->Status,['Active','Standby']))
                            {{--Here we render controls, depending on the contract status--}}
                            @if($contract->TeamStatus == "Optimize")
                            <div class="col-md-12">
                                <div class="row">
                                    <h4>Optimization</h4>
                                    <div class="latest-note">
                                        <div class="col-md-12">
                                            <div class="row">
                                                {{--<a href="#" class="btn btn-midnightblue" title="@lang('labels.go-to-adwords-account')">@lang('labels.go-to-adwords-account')</a>--}}
                                                <button class="btn btn-adwords startOptimize @if(!strcmp(end($contract->Activity)->ActivityType,"StartOptimize")) hidden @endif" title="@lang('labels.start-optimize')">@lang('labels.start-optimize')</button> <!-- changes to read stop optimize button -->
                                                <a href="#" class="btn btn-comment addCommentButton" title="@lang('labels.add-comment')">@lang('labels.add-comment')</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="@if(strcmp(end($contract->Activity)->ActivityType,"StartOptimize")) hidden @endif optimizationMenu"> <!-- This section is hidden if the contract is not in optimization -->
                                    <form id="endOptimizationForm">
                                        <div class="row">
                                            <div class="panel-default">
                                                <div class="form-group">
                                                    <div class="col-sm-12">
                                                        <textarea name="Comment" id="optimizeNote" cols="180" rows="8" class="form-control" placeholder="@lang('labels.what-was-optimized')?" required="required"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 control-label">@lang('labels.worked-on'):</label>
                                                <div class="col-sm-6">
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="optimizeComment" id="inlinecheckbox1" value="@lang('labels.keywords')"> @lang('labels.keywords')
                                                    </label>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="optimizeComment" id="inlinecheckbox2" value="CPC"> CPC
                                                    </label>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="optimizeComment" id="inlinecheckbox3" value="CTR"> CTR
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                          <div class="col-md-12">
                                            <div class="btn-toolbar">
                                                <button class="btn-magenta btn endOptimize">@lang('labels.end-optimize')</button> <!-- you cannot stop if you have not type in text in optimizeNote -->
                                            </div>
                                          </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @elseif($contract->TeamStatus == "Production")
                                <div class="col-md-12">

                                    <div class="panel">
                                        <div class="panel-body">
                                            <div class="row">
                                                <a href="#" class="btn btn-adwords produceContract">@lang('labels.produce')</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($contract->TeamStatus == "Starting")
                                <div class="col-md-12">
                                    <div class="panel">
                                        <div class="panel-heading"><h4>@lang('labels.start-contract')</h4></div>
                                        <div class="panel-body">
                                            <form id="startContractForm" class="form-horizontal">
                                                <div class="form-group">
                                                    <textarea name="Comment" id="startNote" cols="180" rows="8" class="form-control" placeholder="@lang('labels.comment')?" required="required"></textarea>
                                                </div>
                                                @if($contract->Parent_Id == null)
                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label">Start-up mail</label>
                                                        <div class="col-sm-9">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input type="checkbox" name="StartupMail" value="true" checked="checked">
                                                                    If this is checked, the client will receive an email, about the contract startup
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="btn-toolbar" style="margin-top:10px;">
                                                    <button type="submit" class="btn btn-adwords">@lang('labels.start')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="startupMeeting">
                                        <div class="startupMeetingCalendar responsive-iframe-container"></div>

                                    </div>
                                </div>
                            @endif
                        @endif

                        </div>
                        <div class="row" style="margin-top: 10px;">
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
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-contract">
                            <div class="panel-heading">
                                <h4>
                                    <i class="fa fa-folder-open"></i>
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
                                                        <td>@if($child->StartDate != null){{toDate($contract->StartDate)}} @endif</td>
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
                                            @if(isset($contract->Parent->ContractType))
                                                @if($contract->Parent->ContractType != null)
                                                    <?php
                                                    if(in_array(strtolower($contract->Parent->ContractType->Name),['seo','social','local','google+'])){
                                                        $url = 'seo';
                                                    }else{
                                                        $url = 'adwords';
                                                    }
                                                    ?>
                                                    <a href="{{url($url.'/show',$contract->Parent_Id)}}" class="btn btn-info">@lang('labels.see-parent-contract')</a>
                                                @endif
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>  <!-- Closing the first row  -->

                <div class="row">
                    <div class="col-md-12">
                        @include('layout.tabs-section',
                        ['contracts'  =>$contract->ClientAlias->Contract,
                         'orders'     =>$contract->OriginalOrder,
                         'files'      =>true,
                         'contractId' =>$contract->Id,
                         'appointments' => true,
                          'drafts'=>isAllowed('drafts','get')?true:null,
                         'packageId'  =>(isset($contract->ProductPackage->Id)?$contract->ProductPackage->Id:1), // todo fix this for old contract products
                         'contacts'   =>$contract->ClientAlias_Id,
                         'aliasId'    =>$contract->ClientAlias->Id,
                         'information'=>$contract->ClientAlias,
                         'invoices'   =>$contract->Invoice['Invoices'],
                         'timeline'   =>$contract->Activity,
                         'checklist'=>'true',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop