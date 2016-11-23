@extends('layout.main')
@section('page-title',Lang::get('labels.lead')." : ".$lead->Id)
@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
<style>
    #ad {
        background-color: #FFF8E7;
    }
    #ad a:link {
        color: #0E1CB3;
        font-size: medium;
        line-height: 1.0;
    }
     cite {
        color: #00802A;
        display: inline-block;
        margin-bottom: 1px;
    }

    /*.stack-custom2 {*/
        /*left: auto;*/
        /*right: 10%;*/
        /*bottom: auto;*/
        /*top:15%;*/
    /*}*/
</style>
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}

    <script>
    $(document).ready(function(){
   //console.log(lead);
        var model = $('#Model').val();
        var modelId = $('#ModelId').val();
        var commentinput = $('input[name=Comment]');

        $('#quickUpdateLead').on('submit',function(event){
            event.preventDefault();
            var formData = $(this).find(':input').filter(function(){
                return $.trim(this.value).length > 0
            }).serializeJSON();

            if(commentinput.val() != ""){
                var commentData = {Model : model , ModelId:modelId,Message: commentinput.val()};
                saveComment(commentData);
                commentinput.val('');
            }
            delete(formData.oldStatus);
            delete(formData.Comment);
            if(formData.Website){
                if(!validateUrl(addhttp(formData.Website))){
                    new PNotify({
                        title: "Invalid homepage",
                        type: 'error'
                    });
                    return false;
                }
            }
            $.ajax({
                url: api_address + "Leads("+getModelId()+')',
                type: "PATCH",
                data:JSON.stringify(formData),
                success: function (data) {
                    if(formData.Website){
                        $('.leadWebsite').html('<i class="fa fa-link"></i><a class="adwordsPendingLinkName" href="'+addhttp(formData.Website)+'" target="_blank">'+formData.Website+'</a>')
                        new PNotify({
                            title: "Refreshing the page...",
                            type: 'success'
                        });
                        location.reload(true);

                    }
                    if(formData.Email) $('.leadEmail').html(formData.Email);
                    if(formData.Company) $('.leadCompany').html(formData.Company);
                    if(formData.PhoneNumber) $('.leadPhone').html(formData.PhoneNumber);
                    if(formData.ContactPerson) $('.leadContactPerson').html(formData.ContactPerson);
                    if(formData.ContactEmail) $('.leadContactEmail').html("<a href='mailto:"+formData.ContactEmail+"'>"+formData.ContactEmail+"</a>");
                    if(formData.AdwordsId) {
                        $('.adwordsIdCheck').html(formData.AdwordsId+'<span class="adwordsIdOptions"></span>');

                        $.when(checkAdWordsLink(formData.AdwordsId))
                            .then(function (result) {
                                changeAdwordsLinkStatus(result);
                            })
                    }
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        $('#moveToDeadBtn').on('click',function(event){
            event.preventDefault();
            if(commentinput.val() != ""){
                var commentData = {Model : model , ModelId:modelId,Message: commentinput.val()};
                saveComment(commentData);
                commentinput.val('');
            }
            /// patch the lead status to be dead
            var deadStatus = '3';
            var oldStatus = $('#oldStatus').val();
            $.ajax({
                type: "PATCH",
                url: api_address + 'Leads('+modelId+')',
                data: JSON.stringify({Status:deadStatus}),
                success: function (data) {
                    // find a lead with same status and same assigned and redirect to it
                    $.get(api_address+'Leads?$select=Id&$filter=Status eq webapi.Models.LeadStatus\''+oldStatus+'\' and User_Id eq \''+getUserId()+'\'&$top=1')
                        .success(function (data) {
                            if(data.value.length > 0){
                                new PNotify({
                                    title:"Success. moving to next",
                                    type: "success"
                                });
                                window.location = base_url+'/leads/show/'+data.value[0].Id;
                            }else{
                                new PNotify({
                                    title:"Success",
                                    text:"No more "+oldStatus+" leads",
                                    type:"success"
                                })
                            }
                            $('#moveToDeadBtn').hide();
                            commentinput.val('');
                        });
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

        $('#moveToRotate').on('click',function(event){
            event.preventDefault();
            if(commentinput.val() != ""){
                var commentData = {Model : model , ModelId:modelId,Message: commentinput.val()};
                saveComment(commentData);
                commentinput.val('');
            }
            /// patch the lead status to be dead
            var rotateStatus = '6';
            var oldStatus = $('#oldStatus').val();
            $.ajax({
                type: "PATCH",
                url: api_address + 'Leads('+modelId+')',
                data: JSON.stringify({Status:rotateStatus}),
                success: function (data) {
                    // find a lead with same status and same assigned and redirect to it
                    $.get(api_address+'Leads?$select=Id&$filter=Status eq webapi.Models.LeadStatus\''+oldStatus+'\' and User_Id eq \''+getUserId()+'\'&$top=1')
                            .success(function (data) {
                                if(data.value.length > 0){
                                    new PNotify({
                                        title:"Success. moving to next",
                                        type: "success"
                                    });
                                    window.location = base_url+'/leads/show/'+data.value[0].Id;
                                }else{
                                    new PNotify({
                                        title:"Success",
                                        text:"No more "+oldStatus+" leads",
                                        type:"success"
                                    })
                                }
                                $('#moveToRotate').hide();
                                commentinput.val('');
                            });
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


        nextAppointment("Lead",modelId);
//        function nextLead() {
//
//            var userquery = '';
//            var currentUser = getUserId();
//            if(lead.User_Id == null){
//                userquery = "User_Id eq null"
//            }else{
//                if(lead.User_Id != currentUser){
//                    if(inRoleNeutral('Administrator') || inRoleNeutral('Developer')){
//                        userquery = "User_Id eq '"+lead.User_Id+"'";
//                    }
//
//                }else{
//                    userquery = "User_Id eq '"+currentUser+"'"
//                }
//            }
//            // find a lead with same status and same assigned and redirect to it
//            $.get(api_address+'Leads?$select=Id,Website,Company&$filter=Status eq webapi.Models.LeadStatus\''+lead.Status+'\' and '+userquery+' and Id lt '+lead.Id+'&$orderby=Id desc&$top=1')
//                .success(function (data) {
//                    if(data.value.length > 0){
//                        var stack_custom2 = {"dir1": "right", "dir2": "up", "push": "top"};
//                        var opts = {
//                            title: "Next lead",
//                            text: "<a href='"+base_url+"/leads/show/"+data.value[0].Id+"'>"+data.value[0].Website+"</a>",
//                            addclass: "stack-custom2",
//                            stack: stack_custom2,
//                            hide: false
//                        };
//                        new PNotify(opts)
//                    }else{
//                        $.get(api_address+'Leads?$select=Id,Website,Company&$filter=Status eq webapi.Models.LeadStatus\''+lead.Status+'\' and '+userquery+' and Id gt '+lead.Id+'&$orderby=Id desc&$top=1')
//                            .success(function (data) {
//                                var stack_custom2 = {"dir1": "right", "dir2": "up", "push": "top"};
//                                if(data.value.length > 0){
//                                    var opts = {
//                                        title: "Next lead",
//                                        text: "<a href='"+base_url+"/leads/show/"+data.value[0].Id+"'>"+data.value[0].Website+"</a>",
//                                        addclass: "stack-custom2",
//                                        stack: stack_custom2,
//                                        hide: false
//                                    };
//                                    new PNotify(opts)
//                                }else {
//                                    var opts = {
//                                        title: "This is the last for this status",
//                                        addclass: "stack-custom2",
//                                        stack: stack_custom2,
//                                        hide: false
//                                    };
//                                    new PNotify(opts)
//                                }
//                            });
//                    }
//                });
//        }
//
//        nextLead();
    });
    </script>
@stop
@section('content')

    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Lead',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $lead->Id,['id'=>'ModelId']) !!}
    {!! Form::hidden('LeadName', $lead->Company,['id'=>'LeadName']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-lead">
                <div class="panel-heading">
                    <h4><i class="fa fa-bullhorn"> </i> @lang('labels.lead')</h4>
                    <div class="options">
                        <a href="{{url('leads/edit',$lead->Id)}}" title="@lang('labels.edit')"><i class="fa fa-edit"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="col-md-12">
                        @if($existing)
                        <div class="alert alert-warning text-center">
                            <strong>
                                @if(isset($existing['Homepage']))
                                    <span>Client with similar Homepage exists :</span> <a target="_blank" href="{{url('clientAlias/show',$existing['Homepage']->Id)}}">{{$existing['Homepage']->Name}} - {{$existing['Homepage']->Homepage}}</a><br>
                                @endif

                                @if(isset($existing['Phone']))
                                    <span>Client with similar Phone number exists :</span> <a target="_blank" href="{{url('clientAlias/show',$existing['Phone']->Id)}}">{{$existing['Phone']->Name}}</a>
                                @endif
                            </strong>
                        </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-5" style="width:465px;">
                            <form id="quickUpdateLead">
                                <div class="panel-row">
                                    {{--Lead company--}}
                                    <h4 class="leadWebsite">
                                        @if(!isset($lead->Website))
                                            <span class="alert-danger">Enter a homepage</span>
                                            {!! quickInput("Website",['type'=>'text','required'=>'required']) !!}
                                        @else
                                            <i class="fa fa-link"></i>
                                            <a class="adwordsPendingLinkName" href="{{addHttp($lead->Website)}}" target="_blank">
                                                {{ $lead->Website }}
                                            </a>
                                        @endif
                                    </h4>

                                    <div class="col-md-555" style="">
                                        
                                        <table class="table datatables">
                                            <tbody>
                                                <tr>
                                                    <td>@lang('labels.client')</td>
                                                    <td class="leadCompany">
                                                        @if($lead->Company == null || $lead->Company == "")
                                                            {!! quickInput("Company",['type'=>'text']) !!}
                                                        @else
                                                            {{$lead->Company}}
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('labels.email')</td>
                                                    <td class="leadEmail">
                                                        @if(validateEmail($lead->Email) || empty($lead->Email))
                                                            {!! $lead->Email or quickInput('Email',['type'=>'email'])!!}
                                                        @else
                                                            {!! quickInput('Email',['type'=>'email','value'=> $lead->Email]) !!} <span class="label label-danger">Bad email</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('labels.phone')</td>
                                                    <td class="leadPhone">
                                                        @if($lead->PhoneNumber != null && $lead->PhoneNumber != '')
                                                            @if(Auth::user()->localNumber != null)
                                                                <span class="pseudolink flexfoneCallOut">{{$lead->PhoneNumber  or ''}}</span>
                                                            @else
                                                                <a href="tel:{{$lead->PhoneNumber or ""}}">{{$lead->PhoneNumber or ""}}</a>
                                                            @endif
                                                        @else
                                                            {!! quickInput('PhoneNumber',['type'=>'text','pattern'=>'[+]?\d*']) !!}
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Contact Person</td>
                                                    <td class="leadContactPerson">
                                                        @if($lead->ContactPerson == null || $lead->ContactPerson == '')
                                                            {!! quickInput('ContactPerson',['type'=>'text']) !!}

                                                        @else
                                                            {{$lead->ContactPerson}}
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Contact Email</td>
                                                    <td class="leadContactEmail">
                                                        @if(validateEmail($lead->ContactEmail) || empty($lead->ContactEmail))
                                                            {!! $lead->ContactEmail or quickInput('ContactEmail',['type'=>'email'])!!}
                                                        @else
                                                            {!! quickInput('ContactEmail',['type'=>'email','value'=> $lead->ContactEmail]) !!} <span class="label label-danger">Bad email</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('labels.city')</td>
                                                    <td>{{$lead->City or "-"}}</td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('labels.category')</td>
                                                    <td><strong>{{$lead->Type or "--"}}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('labels.status')</td>
                                                    <td><strong>{{$lead->Status or "--"}}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('labels.assigned-to')</td>
                                                    <td>{{$lead->User->FullName or "---"}}</td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('labels.adwords-id')</td>
                                                    <td class="adwordsIdCheck">
                                                        @if($lead->AdwordsId != null && $lead->AdwordsId != "")
                                                            {{$lead->AdwordsId}}<span class="adwordsIdOptions"></span>
                                                        @else
                                                            {!! quickInput('AdwordsId',['type'=>'text','pattern'=>'\b\d{3}[-]?\d{3}[-]?\d{4}\b']) !!}
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('labels.next-appointment')</td>
                                                    <td class="next-appointment"></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <!--<dl class="dl-horizontal-row">
                                            <dt>@lang('labels.client')</dt>
                                            <dd class="leadCompany">
                                                @if($lead->Company == null || $lead->Company == "")
                                                    {!! quickInput("Company",['type'=>'text']) !!}
                                                @else
                                                    {{$lead->Company}}
                                                @endif
                                            </dd>

                                            <dt>@lang('labels.email')</dt>
                                            <dd class="leadEmail">
                                                @if(validateEmail($lead->Email) || empty($lead->Email))
                                                    {!! $lead->Email or quickInput('Email',['type'=>'email'])!!}
                                                @else
                                                    {!! quickInput('Email',['type'=>'email','value'=> $lead->Email]) !!} <span class="label label-danger">Bad email</span>
                                                @endif
                                            </dd>

                                            <dt>@lang('labels.phone')</dt>
                                            <dd class="leadPhone">
                                                @if($lead->PhoneNumber != null && $lead->PhoneNumber != '')
                                                    @if(Auth::user()->localNumber != null)
                                                        <span class="pseudolink flexfoneCallOut">{{$lead->PhoneNumber  or ''}}</span>
                                                    @else
                                                        <a href="tel:{{$lead->PhoneNumber or ""}}">{{$lead->PhoneNumber or ""}}</a>
                                                    @endif
                                                @else
                                                    {!! quickInput('PhoneNumber',['type'=>'text','pattern'=>'[+]?\d*']) !!}
                                                @endif
                                            </dd>
                                            <dt>Contact Person</dt>
                                            <dd class="leadContactPerson">
                                                @if($lead->ContactPerson == null || $lead->ContactPerson == '')
                                                    {!! quickInput('ContactPerson',['type'=>'text']) !!}

                                                @else
                                                    {{$lead->ContactPerson}}
                                                @endif
                                            </dd>

                                            <dt>Contact Email</dt>
                                            <dd class="leadContactEmail">
                                                @if(validateEmail($lead->ContactEmail) || empty($lead->ContactEmail))
                                                    {!! $lead->ContactEmail or quickInput('ContactEmail',['type'=>'email'])!!}
                                                @else
                                                    {!! quickInput('ContactEmail',['type'=>'email','value'=> $lead->ContactEmail]) !!} <span class="label label-danger">Bad email</span>
                                                @endif
                                            </dd>

                                            <dt>@lang('labels.city')</dt>
                                            <dd>{{$lead->City or "-"}}</dd>

                                            <dt>@lang('labels.category')</dt>
                                            <dd><strong>{{$lead->Type or "--"}}</strong></dd>

                                            <dt>@lang('labels.status')</dt>
                                            <dd><strong>{{$lead->Status or "--"}}</strong></dd>

                                            <dt>@lang('labels.assigned-to')</dt>
                                            <dd>{{$lead->User->FullName or "---"}}</dd>
                                            <dt>@lang('labels.adwords-id')</dt>
                                            <dd class="adwordsIdCheck">
                                                @if($lead->AdwordsId != null && $lead->AdwordsId != "")
                                                    {{$lead->AdwordsId}}<span class="adwordsIdOptions"></span>
                                                @else
                                                    {!! quickInput('AdwordsId',['type'=>'text','pattern'=>'\b\d{3}[-]?\d{3}[-]?\d{4}\b']) !!}
                                                @endif
                                               </dd>
                                            <dt>@lang('labels.next-appointment')</dt>
                                            <dd class="next-appointment"></dd>
                                        </dl>-->

                                    </div>
                                </div>
                            <div class="clearfix"></div>
                                <div class="panel">
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group">
                                                    <input class="form-control" type="text" name="Comment" placeholder="Quick Comment"><br/>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-md btn-green" type="submit">@lang('labels.save')</button>
                                            </div>
                                        </div>
                                        @if($lead->Status != "Dead")
                                            <input type="hidden" id="oldStatus" value="{{$lead->Status}}">
                                                <button class="btn btn-md btn-inverse" type="button" id="moveToDeadBtn" title="Move to Dead">
                                                    Dead
                                                </button>
                                        @endif
                                        @if($lead->Status != "ReUse")
                                            <input type="hidden" id="oldStatus" value="{{$lead->Status}}">
                                            <button class="btn btn-green btn-inverse" type="button" id="moveToRotate" title="Move to Rotate. For future use">
                                                Rotate
                                            </button>
                                        @endif
                                        @if(isAllowed('orders','get'))
                                            <a class="btn btn-md btn-orange" href="{{url('orders/create?lead='.$lead->Id)}}">@lang('labels.create-order')</a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                            </div>
                        <div class="col-md-4">
                            <h4>Appointments</h4>
                                <table class="table datatables table-striped">
                                    <thead>
                                        <tr>
                                            <th>Start time</th>
                                            <th>Title</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($appointments as $appointment)
                                            <tr>
                                                <td>
                                                    {{toDateTime($appointment->Start)}}
                                                    &nbsp;
                                                    <button class="updateAppointment btn btn-xs" data-calendarevent-id="{{$appointment->Id}}">
                                                        <i class="fa fa-pencil" data-calendarevent-id="{{$appointment->Id}}"> </i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <a href="{{url('appointments/show',$appointment->Id)}}">{{$appointment->Summary}}</a>
                                                </td>

                                                <td>
                                                    {{$appointment->User->FullName}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                        </div>
                        @if(isset($lead->Ads))
                        <div class="col-md-3">
                            <div class="panel">
                                <h4><i class="fa fa-file-o"></i> Ads</h4>
                                <ol class="leadAds" style="height:300px; overflow: scroll; overflow-x: hidden; overflow-y: auto;">
                                    @foreach($lead->Ads as $ad)
                                        <?php if($ad->ShowUri == null && $ad->url == null) continue; ?>
                                        <li>
                                            <h3><a target="_blank" href="{{addHttp($lead->Website.$ad->DestUri)}}">{{ $ad->ShowUri }}</a></h3>
                                            <span style="padding-bottom: 4px;"><i class="fa fa-search"></i>&nbsp;{{$ad->SearchWord2}}</span>
                                            <div class="visible-url">
                                                <div>
                                                    <strong>{!! $ad->headLine !!} </strong><br>
                                                    {!! adParser($ad->AdText) !!}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-8" style="margin-bottom: 20px;">

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            @include('layout.tabs-section',
                            ['appointments'=>($lead->Website != null ? true:null),
                             'appointmentEmail'=>$lead->Email,
                             'files' =>true,
                             'information'=>$lead,
                             'appointmentInfo' => ['Summary'=>"",'Description'=>"".$lead->ContactPerson]
                             ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop