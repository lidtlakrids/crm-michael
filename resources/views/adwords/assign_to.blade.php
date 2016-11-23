@extends('layout.main')
@section('page-title',Lang::get('labels.assign') . (isset($contract)? " : ".$contract->Id : ""))
@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop



@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
<script>
    $(document).ready(function () {

        var model = $('#Model').val();
        var modelId = $('#ModelId').val();


        $('#assignBtn').on('click',function(event){
            var user_id = $('select[name=Manager_Id]').val();
            var contract_id = modelId;
            var template_id = $('select[name=Template_Id]').val();

            // if we didn't select user, notify
            if(user_id == ""){
                new PNotify({
                    title: Lang.get('labels.error'),
                    text: Lang.get('Select team'), //todo translation
                    type: 'error'
                });
                return false;  // stopping the actions
            }else{
                var formData = {
                    Manager_Id : user_id
                };

                if(template_id != ""){
                    formData.TemplateId=template_id;
                }
                $(event.target).prop('disabled',true);
                $.ajax({
                    type     : "POST",
                    url      : api_address+'Contracts('+contract_id+')/action.Assign',
                    data     : JSON.stringify(formData),
                    success  : function(data) {
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get('messages.update-was-successful'),
                            type: 'success'
                        });

                        window.location.reload(true);
                        $(event.target).prop('disabled',false);

                    },
                    error    : function(err)
                    {
                        new PNotify({
                            title: Lang.get('labels.error'),
                            text: Lang.get(err.responseJSON.error.innererror.message),
                            type: 'error',
                            nonblock: {
                                nonblock: true,
                                nonblock_opacity: .2
                            }
                        });
                        $(event.target).prop('disabled',false);

                    },
                    beforeSend: function (request)
                    {
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
        <div class="col-md-12">
            <div class="panel panel-adwords">
                <div class="panel-heading">
                    <h4><i class="fa fa-group"></i> @lang('labels.assign') @lang('labels.contract')</h4>
                    <div class="options">
                        <a href="{{url('adwords/edit',$contract->Id)}}"><i class="fa fa-pencil"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <ul id="basicwizard-header" class="stepy-header">
                            <li id="basicwizard-head-0"  class="stepy-active" style="cursor: default;">
                                <div>1. Assign</div>
                            </li>
                            <li id="basicwizard-head-1"  style="cursor: default;">
                                <div>2. Produce</div>
                            </li>
                            <li id="basicwizard-head-3"  style="cursor: default;">
                                <div>3. Startup</div>
                            </li>
                            <li id="basicwizard-head-3"  style="cursor: default;">
                                <div>4. Optimize</div>
                            </li>
                            <li id="basicwizard-head-3"  style="cursor: default;">
                                <div>5. Stopped</div>
                            </li>
                        </ul>
                    </div>
                    <div class="row">

                        <div class="col-md-4">
                            <h4><strong><a href="{{url('clientAlias/show',$contract->ClientAlias->Id)}}"><i class="fa fa fa-user"></i> {{$contract->ClientAlias->Name or "---"}}</a></strong></h4>
                            <dl class="dl-horizontal-row-2">
                                <dt>@lang('labels.homepage')</dt>
                                <dd><a href="{{$contract->Domain}}" target="_blank">{{$contract->Domain or $contract->ClientAlias->Homepage}}</a></dd>

                                <dt>@lang('labels.seller')</dt>
                                <dd>{{$contract->User->FullName or "-"}}</dd>

                                <dt>@lang('labels.product')</dt>
                                <dd>{{$contract->Product->Name or "Not set"}}</dd>

                                <dt>@lang('labels.payment-status')</dt>
                                <dd><strong style="color:@if($contract->Invoice['PaymentStatus'] == "Paid") green @else red @endif">{{$contract->Invoice['PaymentStatus']}}</strong></dd>

                                <dt>Information scheme</dt>
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
                                <dt>Country</dt>
                                <dd>{{$contract->Country->CountryCode or '-'}}</dd>
                                {{--<dd><a href="{{url('information/show',end($contract->InformationScheme)->Id)}}" target="_blank">Latest Info. Scheme</a></dd>--}}
                            </dl>
                            <div class="panel">
                                <div class="panel-body1">
                                    {!! Form::label('Manager_Id',Lang::get('labels.assigned-to'),['class'=>'small-header']) !!}
                                    {!! Form::select('Manager_Id', withEmpty($users) ,null, ['class'=> 'form-control']) !!}

                                    <br/>
                                    {!! Form::label('Template_Id',Lang::get('labels.task-template'),['class'=>'small-header']) !!}
                                    {!! Form::select('Template_Id',withEmpty($templates) ,null, ['class'=> 'form-control']) !!}
                                    <br/>
                                    <input class="btn btn-adwords pull-right" value="Assign" type="submit" id="assignBtn" />
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
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
                                                        <th>Manager</th>
                                                        <th>@lang('labels.actions')</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($contract->Children as $child)
                                                        <tr data-child-contract="{{$child->Id}}">
                                                            <td><a href="{{url('adwords/show',$child->Id)}}">{{$child->Product->Name or "---"}}</a></td>
                                                            <td>@if($child->StartDate != null){{Carbon::parse($child->StartDate)->format('d-m-Y')}} @endif</td>
                                                            <td>@if($child->EndDate != null){{Carbon::parse($child->EndDate)->format('d-m-Y')}} @endif</td>
                                                            <td>{{$child->Manager->FullName or "-"}}</td>
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
                                            <p>This is a Addon contract of {{$contract->Parent->Product->Name}}</p>
                                            <br>
                                            <p>
                                                <a class="btn btn-info" target="_blank" href="{{url('adwords/show',$contract->Parent_Id)}}">@lang('labels.see-parent-contract')</a>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4 pull-right">
                            <div class="panel panel-green1">
                                <div class="panel-heading1">
                                    <h4><i class="fa fa-group"></i> Assigned Overview</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>Manager</th>
                                                <th>Contracts</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($userContractsCount as $k=>$val)
                                                <tr>
                                                    <td>{{$k}}</td>
                                                    <td>{{$val}}</td>
                                                </tr>

                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="clear-fix" style="height: 20px;"></div>
                        <!-- TABS -->
                        <div class="col-md-12">
                            @include('layout.tabs-section',
                            ['files'=>true,
                             'orders'=>$contract->OriginalOrder,
                             'information'=>$contract->ClientAlias,
                             'appointments' => true,
                             'admin' => true,
                             'invoices'=>$contract->Invoice['Invoices'],
                             'contracts' => $contract->ClientAlias->Contract,
                             'contractId' => $contract->Id,
                             'packageId'  =>(isset($contract->ProductPackage->Id)?$contract->ProductPackage->Id:1), // todo fix this for old contract products
                             'timeline'   =>$contract->Activity,
                            ])
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
