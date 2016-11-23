@extends('layout.main')
@section('page-title',Lang::get('labels.product-package')." : ".$package->Name)

@section('content')
    <div class="row">
            <div class="panel panel-product">
                <div class="panel-heading">
                    <h4>@lang('labels.product-package')</h4>
                    <div class="options">
                        @if(isAllowed('Products','patch'))
                            <a href="{{url('product-packages/edit',$package->Id)}}" title="@lang('labels.edit')"><i
                                        class="fa fa-pencil"></i></a>
                        @endif
                    </div>
                </div>
                <div class="panel-body">
                    <div class="col-sm-12 col-md-6">
                    <dl class="dl-horizontal-row-2">
                        <dt>@lang('labels.name')</dt>
                        <dd>{{$package->Name or "---"}}</dd>

                        <dt>@lang('labels.description')</dt>
                        <dd><p class="multiline">{{$package->Description or "---"}}</p></dd>

                        <dt>@lang('labels.active')</dt>
                        <dd>{{($package->Active)?Lang::get('labels.yes'):Lang::get('labels.no')}}</dd>

                        <dt>@lang('labels.size')</dt>
                        <dd>{{$package->Size or "-"}}</dd>

                        <dt>@lang('labels.max-budget')</dt>
                        <dd>{{$package->MaxBudget or "---"}}</dd>

                        <dt>@lang('labels.cost-price')</dt>
                        <dd>{{$package->CostPrice or "---"}}</dd>

                        <dt>Administration Fee</dt>
                        <dd>{{$package->AdministrationFee or "---"}}</dd>

                        <dt>Creation Fee</dt>
                        <dd>{{$package->CreationFee or "---"}}</dd>

                        <dt>Creation Fee Text</dt>
                        <dd>{{$package->CreationFeeText or "---"}}</dd>

                        <dt>Created</dt>
                        <dd>{{date('d-m-Y H:i',strtotime($package->Created))}}

                        <dt>Modified</dt>
                        <dd>{{date('d-m-Y H:i',strtotime($package->Modified))}}

                        <dt>@lang('labels.add-ons-count')</dt>
                        <dd>{{$package->AddonCount or "---"}}</dd>

                        <dt>Product</dt>
                        <dd><strong>{!! Html::linkAction('ProductsController@show', $package->Product->Name , array($package->Product->Id)) !!}</strong></dd>

                        <dt>Default run length</dt>
                        <dd>{{$package->DefaultRunlength or "---"}}</dd>

                        <dt>Default Payment Term</dt>
                        <dd>{{$package->DefaultPaymentTerm or "---"}}</dd>

                    </dl>
                </div>
                    <div class="col-sm-6">
                        <h4>Allowed products</h4>
                        <ul> @if($package->Products != null)
                            @foreach($package->Products as $addon)
                                <li>{{$addon->Product->Name}}</li>
                            @endforeach
                            @else
                                <p>-</p>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>



@stop