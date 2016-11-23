@extends('layout.main')
@section('page-title',Lang::get('labels.edit-loyalty-bonus'))
@section('scripts')
    <script>
        $(document).ready(function () {
            $('#editLoyaltyBonusForm').on('submit', function (event) {
                event.preventDefault();
                var data = $(this).serializeJSON();
                    delete(data._token);
                $.ajax({
                    type: "PATCH",
                    url: api_address + 'LoyaltyBonus(' + getModelId() + ')',
                    data: JSON.stringify(data),
                    success: function (data) {
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get('messages.update-was-successful'),
                            type: 'success'
                        });
                        window.location = base_url+'/loyalty-bonuses'
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });
        });
    </script>

@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','LoyaltyBonus',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $bonus->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">@lang('labels.edit-loyalty-bonus')</div>

            <div class="panel-body">
                {!! Form::open(['class'=>'form-horizontal','id'=>'editLoyaltyBonusForm']) !!}

                <div class="form-group">
                    <label for="loyaltyBonus-Discount" class="col-md-3 control-label"><strong>@lang('labels.discount')</strong></label>
                    <div class="col-md-6">
                        <input name="Discount" type="number" min="0" max="100" step="1" id="loyaltyBonus-Discount" class="form-control" value="{{$bonus->Discount}}" required="required">
                    </div>
                    <div class="col-sm-3">
                        <p class="help-block">%, ex : 10</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="loyaltyBonus-Months" class="col-md-3 control-label"><strong>@lang('labels.months')</strong></label>
                    <div class="col-md-6">
                        <input name="Months" type="number" min="0" step="1" id="loyaltyBonus-Months" class="form-control" value="{{$bonus->Months}}" required="required">
                    </div>
                </div>

                <div class="btn-toolbar">
                    {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
                </div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>
@stop
