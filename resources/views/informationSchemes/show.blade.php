@extends('layout.main')
@section('page-title',Lang::get('labels.information-scheme').": ".$infoScheme->Id)
@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
    <script>
        $(document).ready(function(){

            var model   = $('#Model').val();
            var modelId = $('#ModelId').val();

            $('.approveInfo').click(function(event)
            {
                $(event.target).prop('disabled',true);
                var infoSchemeId = modelId;
                $.ajax({
                    method: "POST",
                    url: api_address+"InformationSchemes("+infoSchemeId+")/action.Approve",
                    success: function( msg ) {
                        location.reload();
                    },
                    error:function(error){
                        $(event.target).prop('disabled',false);
                        handleError(error);
                    },
                    beforeSend: function (request)
                    {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
                return false;
            });
        });
    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','InformationScheme',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $infoScheme->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-orange">
                <div class="panel-heading">
                    <h4>{{mb_strtoupper(Lang::get('labels.information'))}}</h4>
                    {{--admin options--}}
                    <div class="options">
                     <a href="{{url('information/edit',$infoScheme->Id)}}" title="@lang('labels.edit')"><i class="fa fa-edit"></i></a>
                    </div>
                    {{--end admin options--}}
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" id="OrderId" value="{{$infoScheme->Id}}">
                            @if(!$infoScheme->ApprovedDate)
                                <h3 style="background: #16a085; padding: 5px 10px; color: #fff; border-radius: 1px; margin: 20px 0 20px; text-align:center">@lang('messages.waiting-approval')</h3>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <dl class="dl-horizontal">
                                <dt>@lang('labels.client')</dt>
                                <dd> <a href="{{url('clientAlias/show',$infoScheme->Contract->ClientAlias->Id)}}"> {{$infoScheme->Contract->ClientAlias->Name or "--"}}</a></dd>
                                <dt>@lang('labels.homepage')</dt>
                                <dd> <a target="_blank" href="{{$infoScheme->Contract->Domain or $infoScheme->Contract->ClientAlias->Homepage}}"> {{$infoScheme->Contract->Domain or $infoScheme->Contract->ClientAlias->Homepage}}</a></dd>
                                <dt>@lang('labels.contract')</dt>
                                <dd><a href="{{url('contracts/show',$infoScheme->Contract->Id)}}">{{$infoScheme->Contract->Product->Name or "-"}}</a></dd>
                            </dl>
                        </div>
                    </div>

                    <div class="row">
                        <hr />
                        <div class="col-md-8">
                            <h4>@lang('labels.order-info')</h4>
                            <div class="table-responsive">
                                <table class="table table-condensed table-bordered" style="width: 100%">
                                    <tbody>
                                    @foreach($infoScheme->FieldValue as $value)
                                        <tr>
                                            <td style="width: 33%">{{$value['DisplayName']}}: </td>
                                            <td >

                                            @if(isset($value['Type']) && $value['Type'] == "CampaignGoal")
                                                    <div class="col-md-6">
                                                        <div class="row">
                                                            <div class="col-md-4"></div>
                                                            <div class="col-md-4"><strong>Current</strong></div>
                                                            <div class="col-md-4"><strong>Expected</strong></div>
                                                        </div>
                                                        @if($value['value'] != null)
                                                            @foreach($value['value'] as $item=>$values)
                                                                <div class="row">
                                                                    <div class="col-md-4"><b>{{$item}}</b></div>
                                                                    <div class="col-md-4">{{$values->current}}</div>
                                                                    <div class="col-md-4">{{$values->expected}}</div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                @else
                                                   <span class="multiline">{{ ($value['value']) }}</span>
                                                @endif

                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-12">
                                @if(!empty($infoScheme->Products))
                                    <h4>Add-ons</h4>
                                    <ol>
                                        @foreach($infoScheme->Products as $product)
                                            <li>{{$product->Product->Name}}</li>
                                        @endforeach
                                    </ol>
                                @endif
                            </div>


                            <div class="pull-right">
                                <div class="btn-group-horizontal">
                                    @if(!$infoScheme->ApprovedDate && isAllowed('informationSchemes','approve'))
                                        {!! Form::button('<i class="fa fa-check"></i>'.Lang::get('labels.approve'), array('class' => 'approveInfo btn btn-success btn-label'))!!}
                                    @endif
{{--                                    <a href="{{url('information/edit',$infoScheme->Id)}}" title="@lang('labels.edit-order')" class="btn btn-inverse btn-label" style="width: 135px;"><i class="fa fa-edit"></i>{{strtoupper(Lang::get('labels.edit'))}}</a>--}}
                                    <a href="#" class="btn btn-inverse printPage"><i class="fa fa-print"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @include('layout.tabs-section',
                        ['files'      =>true,
                         'admin' => true,
                        ])
                    </div>
                    <div class="panel-footer hidden-print">
                    </div>
                </div>
            </div>
        </div>
        {{--END CONTRACT CONFIRMATION--}}
    </div>

@stop
