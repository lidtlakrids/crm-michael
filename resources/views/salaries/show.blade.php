@extends('layout.main')
@section('page-title',Lang::get('labels.salary-bonus'))

@section('scripts')
<script>
    $(document).ready(function(){

        $("#seller-period").change(function(){
            var group = $("option:selected", this).parent('optgroup');
            //get the start and end period
            var start = new Date($(group.children()[0]).text());
            var end   = new Date($(group.children()[1]).text());
            var userId = getUserId();

            var formData = {
                StartDate : start,
                EndDate : end,
                User_Id : getModelId()
            };

            $.ajax({
                type: "POST",
                url: api_address + 'Salaries/action.Overview',
                data: JSON.stringify(formData),
                success: function (data) {
                    console.log(data);
                },
                error: handleError,
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });

        });


    })

</script>
@stop


@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Salary',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $userId,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}

<div class="row">
    <div class="col-md-3">
        <div class="panel panel-primary">
            <div class="panel-heading"><h4>@lang('labels.months')</h4></div>
            <div class="panel-body">
                <form id="paymentsOverview">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="seller-period" class="control-label col-md-3">@lang('labels.select-period')</label>
                            <div class="col-md-6">
                                {!! Form::select('Period',$sellerPeriods,null,['class'=>'form-control','id'=>'seller-period']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6">
                            <div class="checkbox block">
                                <label >
                                    <input checked="" name="Bonus" type="checkbox">
                                    @lang('labels.request-bonus')
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="btn-toolbar">
                        {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control','id'=>'']) !!}
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>




@stop