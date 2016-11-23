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
                        @if(isAllowed('contracts','patch'))
                            <a href="{{url('seo/edit',$contract->Id)}}" title="Edit contract"><i class="fa fa-pencil"></i></a>
                        @endif
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
                            <dl class="dl-horizontal-row">
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
                                    @if($contract->NeedInformation)
                                        <a target="_blank" href="{{url('orders/information',$contract->Id)}}">Get info.</a>
                                    @else
                                        @if(empty($contract->InformationSchemes))
                                            @lang('messages.contract-does-not-need-info')
                                        @else
                                            <a target="_blank" href="{{url('information/show',end($contract->InformationSchemes)->Id)}}">Latest Info. Scheme</a>
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
                                                <th>@lang('labels.production-manager')</th>
                                                <th>@lang('labels.contracts')</th>
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
                                 'contractId' =>$contract->Id,
                                 'orders'=>$contract->OriginalOrder,
                                 'information'=>$contract->ClientAlias,
                                 'appointments' => true,
                                 'admin' => true,
                                 'contracts' => $contract->ClientAlias->Contract,
                                 'packageId'  =>(isset($contract->ProductPackage->Id)?$contract->ProductPackage->Id:1), // todo fix this for old contract products
                                 'seo'=>true,
                                 'checklist'=>true
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
