@extends('layout.main')
@section('page-title',Lang::get('labels.edit-order')." : ".$order->Id)

@section('styles')
<style>
    i{
        cursor: pointer;
     }
</style>
@stop

@section('scripts')

<script>

    $(document).ready(function () {


        $('#editOrder').on('submit', function(event){
            event.preventDefault();
            var OrderId = getModelId();
            var form = $(this);

            var items = form.serializeJSON();
            delete(items['_token']);

//            console.log(items);
//            return;
//

//            var formData = {
//                Domain           : $('input[name=Domain]').val(),
//                ClientAlias_Id   : $('input[name=ClientAlias]').val(),
//                User_Id          : $('select[name=User_Id]').val()
//            };
//            var fields = $(this).serializeArray();
//    //        console.log(fields);
            $.ajax({
                type     : "PUT",
                url      : api_address+'Orders('+OrderId+')',
                data     : JSON.stringify(items),
                success  : function(data){
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('messages.update-was-successful'),
                        type: 'success'
                    });
                },
                error    : function(err)
                {
                    new PNotify({
                        title: Lang.get('labels.error'),
                        text: Lang.get(err.statusText),
                        type: 'error'
                    });
                }
            });
            // console.log(formData);
            event.preventDefault(); //STOP default action
        });



        $('.removeProduct').click(function(){
            var id = this.parentNode.id;

            $.ajax(
                {
                url: api_address + 'OrderProducts(' + id + ')',
                type: "DELETE",
                success: function (data) {
                    new PNotify({
                        title:"Product was removed",
                        type: "success"
                    });
                    $('table tr#'+id).remove();
                },
                error: function (error) {
                    new PNotify({
                        title:"Could not remove the product",
                        type: "error"
                    });
                }
            });
        });
    });

</script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Order',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $order->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}

<div class="col-md-8">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h4>@lang('labels.edit-order') {{$order->Id}}</h4>
            <div class="options">
                <a href="{{url('orders/show',$order->Id)}}" title="@lang('labels.back')"><i class="fa fa-arrow-left"></i>@lang('labels.back')</a>
            </div>
        </div>
        <div class="panel-body">
            {!! Form::open(['class'=>'form-horizontal','id'=>'editOrder']) !!}
            {!! Form::hidden('ClientAlias_Id',(isset($order->ClientAlias->Id))?$order->ClientAlias->Id:null,array('id'=>'ClientAliasId')) !!}
            {!! Form::hidden('Id',$order->Id,array('id'=>'ClientAliasId')) !!}
{{--            {!! Form::hidden('OrderId',$order->Id,array('id'=>'OrderId')) !!}--}}

            <div class="form-group">
                {!! Form::label('Domain',Lang::get('labels.homepage'),['class'=>'col-md-3 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('Domain',$order->Domain,['class'=>'form-control','placeholder'=>'http://','required'=>'required']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('User',Lang::get('labels.seller'),['class'=>'col-md-3 control-label']) !!}
                <div class="col-md-3">
                    {!! Form::select('User_Id',withEmpty($users,Lang::get('labels.select-user') ), $order->User->Id, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="col-md-6">
                <div class="row">
                    <hr />
                    @if(isset($orderFields))
                        @foreach($orderFields as $field)
                            <div class="form-group">
                                {!! $field['label']  or "---"!!}
                                <div class="col-sm-12">
                                    {!! $field['element'] or "---"!!}
                                </div>

                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="btn-toolbar">
                {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
            </div>
        </div>
    </div>
</div>

    <div class="col-md-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h4>@lang('labels.products')</h4>
            </div>

            <div class="panel-body">
                @foreach($order->OrderProductPackage as $package)
                    <div class="form-horizontal">
                        <h4>{{$package->ProductPackage->Product->Name}}</h4>
                        <div class="form-group">
                            {{Form::hidden('OrderProductPackage['.$package->Id.'][Id]',$package->Id)}}
                            <label for="orderPP-ProductPrice" class="col-md-3 control-label">@lang('labels.sale-price')</label>
                            <div class="col-sm-6">
                                {!! Form::number('OrderProductPackage['.$package->Id.'][ProductPrice]',$package->ProductPrice,['min'=>0,'class'=>'form-control','id'=>'orderPP-ProductPrice','required'=>'required']) !!}
                                {{Form::hidden('defaults['.$package->Id.'][OriginalPrice]',$package->ProductPackage->Product->SalePrice)}}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block">$$</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="orderPP-RunLength"  class="col-md-3 control-label">@lang('labels.runlength')</label>
                            <div class="col-sm-6">
                                {!! Form::number('OrderProductPackage['.$package->Id.'][RunLength]',$package->RunLength,['min'=>0,'class'=>'form-control','id'=>'orderPP-RunLength','required'=>'required']) !!}
                                {{Form::hidden('defaults['.$package->Id.'][RunLength]',$package->ProductPackage->DefaultRunlength)}}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block">@lang('labels.months')</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="orderPP-PaymentTerms" class="col-md-3 control-label">@lang('labels.payment-terms')</label>
                            <div class="col-sm-6">
                                {!! Form::select('OrderProductPackage['.$package->Id.'][PaymentTerms]',withEmpty($paymentTerms),findEnumNumber($paymentTerms,$package->PaymentTerms),['class'=>'form-control','id'=>'orderPP-PaymentTerms','required'=>'required']) !!}
                                {{Form::hidden('defaults['.$package->Id.'][PaymentTerms]',$package->ProductPackage->DefaultPaymentTerm)}}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block">(default 3 month)</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="orderPP-Discount" class="col-md-3 control-label">@lang('labels.discount')</label>
                            <div class="col-sm-6">
                                {!! Form::number('OrderProductPackage['.$package->Id.'][Discount]',$package->Discount,['min'=>0,'max'=>100,'class'=>'form-control','id'=>'orderPP-Discount']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block">%</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="orderPP-Domain" class="col-md-3 control-label">@lang('labels.homepage')</label>
                            <div class="col-sm-6">
                                {!! Form::text('OrderProductPackage['.$package->Id.'][Domain]',addHttp($package->Domain),['class'=>'form-control','id'=>'orderPP-Domain','placeholder'=>"http://"] )!!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block">If different</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="orderPP-Country" data-content="CountryLabel" class="col-md-3 control-label"></label>
                            <div class="col-sm-6">
                                {!! Form::select('OrderProductPackage['.$package->Id.'][Country_Id]',withEmpty($countries),$package->Country_Id,['class'=>'form-control','id'=>'orderPP-Country']) !!}
                            </div>
                            <div class="col-sm-3">
                                <p class="help-block">Default DK</p>
                            </div>
                        </div>
                    </div>

                @endforeach
            </div>
        </div>
    </div>
    {!! Form::close() !!}


@stop