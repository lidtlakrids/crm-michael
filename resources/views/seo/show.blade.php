@extends('layout.main')
@section('page-title',"SEO ".Lang::get('labels.contract')." : ".$contract->Id)
@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
    {!! Html::script(asset('js/lib/seo-section.js')) !!}
    @include('scripts.dataTablesScripts')
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
                if(data.Minutes){
                    var minutes = data.Minutes;
                    delete(data.Minutes);
                }
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

                        if(minutes){

                            $.ajax({
                                url: api_address+"TimeVaults/Withdraw",
                                type: "POST",
                                data:JSON.stringify({Minutes:minutes,Model:"Contract",Item:modelId,Comment:data.Comment}),
                                success : function()
                                {
                                    initializeTimeVault('Contract',modelId);
                                },
                                beforeSend: function (request)
                                {
                                    request.setRequestHeader("Content-Type", "application/json");
                                }
                            });
                        }

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
                    success: function (data) {
                      location.reload(true);
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

                $(event.target).css('pointer-events','');
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

                var val = $(event.target).val();

                if(!$(event.target).prop('checked')){
                    console.log(1);

                    textarea.html(textarea.html().replace("Worked on "+val+'\n',''));
                    return false;
                }
                textarea.html(textarea.html()+Lang.get('labels.worked-on')+' '+val+ '\n');
            });

            initializeTimeVault('Contract',modelId)
        });
    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Contract',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $contract->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="panel panel-seo">
            <div class="panel-heading">
                <h4><i class="fa fa-search"></i> {{$contract->Product->Name}} - @lang('labels.'.strtolower($contract->TeamStatus))</h4> <!-- product name - status -->
                <div class="options">
                    @if(isAllowed('contracts','patch'))
                        <a href="{{url('adwords/edit',$contract->Id)}}" title="@lang('labels.edit-contract')"><i class="fa fa-edit"></i></a>
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
                        <div class="row">
                            <div class="col-md-6">
                                <h4><strong><a href="{{url('clientAlias/show',$contract->ClientAlias->Id)}}"><i class="fa fa fa-user"></i> {{$contract->ClientAlias->Name or "--"}}</a></strong></h4>
                            </div>
                            <div class="col-md-6 pull-right"> <h4>Time Logged : <span class="counselingTime spinner" style="position: relative;"></span></h4></div>
                        </div>
                        <dl class="dl-horizontal-row">
                            <dt>@lang('labels.product')</dt>
                            <dd><strong>{{$contract->Product->Name or "--"}}</strong></dd>
                            <dt>@lang('labels.homepage')</dt>
                            <dd><a target="_blank" href="@if($contract->Domain != null && $contract->Domain != ''){{addHttp($contract->Domain)}} @else{{addHttp($contract->ClientAlias->Homepage)}}@endif">{{$contract->Domain or $contract->ClientAlias->Homepage}}</a></dd>
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
                            <dt>@lang('labels.next-optimization')</dt>
                            <dd>
                                @if($contract->NextOptimize != null)
                                    {{toDate($contract->NextOptimize)}}
                                @else
                                    Not set
                                @endif
                            </dd>
                            <dt>@lang('labels.city')</dt>
                            <dd>{{$contract->ClientAlias->City or "--"}}</dd>
                            <dt>@lang('labels.main-contact')</dt>
                            <dd>{{$contract->ClientAlias->Contact[0]->Name or "--"}}</dd>
                            <dt>@lang('labels.contact-phone')</dt>
                            <dd><a href="" title="@lang('labels.call-main-contact')">{{$contract->ClientAlias->Contact[0]->Phone or "--"}}</a></dd>
                            <dt>@lang('labels.country')</dt>
                            <dd>{{$contract->Country->CountryCode or "--"}}</dd>
                            <dt>@lang('labels.seller')</dt>
                            <dd>{{$contract->User->FullName or "--"}}</dd>
                            <dt> @lang('labels.client-manager')</dt>
                            <dd>{{$contract->ClientAlias->Client->ClientManager->FullName or "--"}}</dd>

                            <dt> @lang('labels.production-manager')</dt>
                            <dd>{{$contract->Manager->FullName or "--"}}</dd>

                            <dt>@lang('labels.contract-number')</dt>
                            <dd><a href="">{{$contract->Id}}</a></dd>
                            <dt>@lang('labels.payment-status')</dt>
                            <dd><strong style="color: @if($contract->Invoice['PaymentStatus'] == "Paid" && isset($contract->Invoice['PaymentStatus'])) green @else red @endif">
                                    {{$contract->Invoice['PaymentStatus'] or Lang::get('labels.unknown')}}
                                    @if($contract->Invoice['PaymentStatus']=="Paid")
                                        {{toDate(end($contract->Invoice['Invoices'])->Payed)}}
                                    @endif
                                </strong>
                            </dd>
                            <dt>@lang('labels.information-scheme')</dt>
                            <dd>
                                @if(empty($contract->InformationSchemes))
                                    @if($contract->NeedInformation)
                                        <a target="_blank" href="{{url('orders/information',$contract->Id)}}">@lang('labels.get-information')</a>
                                    @else
                                        @lang('messages.contract-does-not-need-info')
                                    @endif
                                @else
                                    <a target="blank" href="{{url('information/show',end($contract->InformationSchemes)->Id)}}">@lang('labels.newest-information-scheme')</a>
                                @endif
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            @if(in_array($contract->Status,['Active','Standby']))
                               {{--Here we render controls, depending on the contract status--}}
                                @if($contract->TeamStatus == "Optimize")
                                <div class="col-md-6">
                                    <div class="row">
                                        <h4>Optimization</h4>
                                        <div class="latest-note">
                                            <div class="col-md-12">
                                                {{--<a href="#" class="btn btn-midnightblue" title="@lang('labels.go-to-adwords-account')">@lang('labels.go-to-adwords-account')</a>--}}
                                                <button class="btn btn-adwords startOptimize @if(!strcmp(end($contract->Activity)->ActivityType,"StartOptimize")) hidden @endif" title="@lang('labels.start-optimize')">@lang('labels.start-optimize')</button> <!-- changes to read stop optimize button -->
                                                <a href="#" class="btn btn-comment addCommentButton" title="@lang('labels.add-comment')">@lang('labels.add-comment')</a>
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

                                            <div class="form-group">
                                                <label class="control-label col-md-3" for="loggedMinutes">Time spent (minutes)</label>
                                                <div class="col-md-3">
                                                    <input type="number" min="0" name="Minutes" id="loggedMinutes" class="form-control" required="required">
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
                                <div class="col-md-6">

                                    <div class="panel">
                                        <div class="panel-body">
                                            <a href="#" class="btn btn-adwords produceContract">@lang('labels.produce')</a>
                                        </div>
                                    </div>
                                </div>
                            @elseif($contract->TeamStatus == "Starting")
                                <div class="col-md-3">
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
                                                                    <input type="checkbox" name="StartupMail"  value="true" checked="checked">
                                                                    If this is checked, the client will receive an email, about the contract startup
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div>
                                                    <button type="submit" class="btn btn-adwords">@lang('labels.start')</button>
                                                </div>
                                            </form>
                                        </div>
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
                </div>  <!-- Closing the first row  -->
                <div class="row">
                    <div class="col-md-12">
                        @include('layout.tabs-section',
                        ['contracts'  =>$contract->ClientAlias->Contract,
                         'files'      =>true,
                         'information'=>$contract->ClientAlias,
                         'contractId' =>$contract->Id,
                         'packageId'  =>(isset($contract->ProductPackage->Id)?$contract->ProductPackage->Id:1), // todo fix this for old contract products
                         'contacts'   =>$contract->ClientAlias_Id,
                         'aliasId'    =>$contract->ClientAlias->Id,
                         'invoices'   =>$contract->Invoice['Invoices'],
                         'checklist'  =>true,
                         'clientLogins'=>true,
                         'timeline'   =>$contract->Activity,
                         'seo'        => true,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop