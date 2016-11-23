@extends('layout.main')
@section('page-title',Lang::get('labels.seller-goals'))

@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function(){

            $('#applySellerGoalsFilter').on('click',function (event) {
               event.preventDefault();

                var query = '';
                $.map($('.sellerGoalsFilters'),function(a,b){
                    var el = $(a);
                    if(el.val() != ""){
                        query += el.prop('name')+"="+el.val()+'&';
                    }
                });
                window.location = [location.protocol, '//', location.host, location.pathname].join('')+"?" +query

            });
//
//            var stats = $('.goalStatistics');
//
//            $.each(stats,function(a,stats){
//                var goalId = $(stats).data('goal-id');
//                    calcGoalStats(goalId,stats);
//            });

            $('body').on('submit','#createGoalForm',function(event){
                event.preventDefault();
                var data = $(this).serializeJSON();
                $.ajax({
                    type: "POST",
                    url: api_address + 'SellerGoals',
                    data: JSON.stringify(data),
                    success: function (data) {
                        window.location.reload()
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });
//
//            function calcGoalStats(goalId,element) {
//                var stats = $(element);
//                $.when($.get(api_address+'SellerGoals('+goalId+')/Stats'))
//                    .then(function(data){
//                        var grouped = groupStats(data.value);
//                        for( var prop in grouped){
//                            if(prop !== "Payments" && prop !== 'MeetingsGoal') {
//                                var goal = parseInt(stats.find('.' + prop + 'Goal').text().replace('.',""));
//                                stats.find('.' + prop + 'Value').removeClass('spinner').append(grouped[prop].format(true));
//                                var diff = grouped[prop] - goal;
//                                var diffPlaceholder = stats.find('.' + prop + 'ValueDifference');
//                                diffPlaceholder.removeClass('spinner');
//                                diffPlaceholder.addClass(diff >= 0 ? "success" : "danger");
//                                diffPlaceholder.append(diff.format(true));
//                            }else if(prop=='Payments'){
//                                stats.find('.Payments').append(grouped[prop].format(true)+" kr.");
//                            }else if(prop=='CompletedMeetings'){
//                                var goal = parseInt(stats.find('.' + prop).text().replace('.',""));
//                                stats.find('.' + prop + 'Value').removeClass('spinner').append(grouped[prop].format(true));
//                                var diff = grouped[prop] - goal;
//                                var diffPlaceholder = stats.find('.' + prop + 'ValueDifference');
//                                diffPlaceholder.removeClass('spinner');
//                                diffPlaceholder.addClass(diff >= 0 ? "success" : "danger");
//                                diffPlaceholder.append(diff.format(true));
//                            }
//                        }
//                    })
//            }
//
//            //groups the array of stats for a certain goal
//            function groupStats(stats) {
//
//                var grouped = {
//                    NewSalesCount:0,
//                    NewSalesPayments:0,
//                    NewSales:0,
//                    Payments:0,
//                    ReSalesCount:0,
//                    ReSales:0,
//                    UpSaleCount:0,
//                    UpSale:0,
//                    Calls:0,
//                    CompletedMeetings:0,
////                    MeetingsGoal:0
//                };
//
//                stats.forEach(function(index,val){
//                    grouped.NewSalesCount    += parseInt(index.NewSalesCount);
//                    grouped.NewSalesPayments += parseInt(index.NewSalesPayments);
//                    grouped.NewSales         += parseInt(index.NewSalesValue);
//                    grouped.Payments         += parseInt(index.Payments);
//                    grouped.ReSalesCount     += parseInt(index.ReSalesCount);
//                    grouped.ReSales          += parseInt(index.ReSalesValue);
//                    grouped.UpSaleCount      += parseInt(index.UpSaleCount);
//                    grouped.UpSale           += parseInt(index.UpSaleValue);
//                    grouped.Calls            += parseInt(index.Calls);
//                    grouped.CompletedMeetings     += parseInt(index.CompletedMeetings);
////                    grouped.MeetingsGoal += parseInt(index.CompletedMeetings);
//                });
//                return grouped;
//            }
            
            $('.EditSellerGoal').on('click',function (event) {
                var id = $(event.target).data('goal-id');
                $.get(api_address+"SellerGoals("+id+')?$expand=User($select=Id,FullName)')
                    .success(function(data){
                        var modal = getDefaultModal();
                        modal.find('.modal-title').append(Lang.get('labels.edit-goal'));
                        modal.find('.modal-body').loadTemplate(base_url+'/templates/sellerGoals/editGoalForm.html',
                            {
                                GoalId : id,
                                NewSalesCountGoalLabel : Lang.get('labels.new-sales-count'),
                                NewSalesCountGoal      : data.NewSalesCountGoal,
                                NewSalesGoalLabel      : Lang.get('labels.new-sales-goal'),
                                NewSalesGoal           : data.NewSalesGoal,
                                UpSalesCountGoalLabel  : Lang.get('labels.upsale-count'),
                                UpSalesCountGoal       : data.UpSalesCountGoal,
                                UpSalesGoalLabel       : Lang.get('labels.upsale-goal'),
                                UpSalesGoal            : data.UpSalesGoal,
                                ReSalesCountGoalLabel  : Lang.get('labels.resale-count'),
                                ReSalesCountGoal       : data.ReSalesCountGoal,
                                ReSalesGoalLabel       : Lang.get('labels.resale-goal'),
                                ReSalesGoal            : data.ReSalesGoal,
                                CallsGoalLabel         : Lang.get('labels.calls-goal'),
                                CallsGoal              : data.CallsGoal,
                                HealthChecksGoalLabel  : Lang.get('labels.healthchecks-goal'),
                                HealthChecksGoal       : data.HealthChecksGoal,
//                                MeetingsGoal:data.MeetingsGoal,
                                CreateLabel            : Lang.get('labels.create')
                            },
                            {
                                overwriteCache:true
                            })
                    })
            });


            $('.DeleteSellerGoal').on('click',function (event) {
                var placeholder = $(event.target).closest('.col-md-6');
                var id = $(event.target).data('goal-id');
                bootbox.confirm("Are you sure?", function(result)
                {
                    if(result) {
                        $.ajax({
                            url: api_address + "SellerGoals(" + id + ")",
                            type: "Delete",
                            success: function () {
                                placeholder.remove();
                            },
                            error: handleError,
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });
                    }
                });
            });

            $('body').on('submit','#editGoalForm',function (event) {
                event.preventDefault();
                var formData = $(this).serializeJSON();
                var ID = formData.GoalId;
                delete(formData.GoalId);
                $.ajax({
                    url: api_address+"SellerGoals("+ID+")",
                    type: "PATCH",
                    data:JSON.stringify(formData),
                    success : function()
                    {
                        window.location.reload();
                        closeDefaultModal()
                    },
                    error: handleError,
                    beforeSend: function (request)
                    {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });

            $('body').on('submit','#copyGoalForm',function(event){
                event.preventDefault();
                var data = $(this).serializeJSON();
                $.ajax({
                    type: "POST",
                    url: api_address + 'SellerGoals',
                    data: JSON.stringify(data),
                    success: function (data) {
                        window.location.reload()
                    },
                    beforeSend: function (request){
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });
        })
    </script>
@stop

@section('content')
    <div class="row">
        <div class="panel panel-sales">
            <div class="panel-heading">
                <h4><i class="fa fa-bar-chart-o">&nbsp;</i>@lang('labels.seller-goals')</h4>
                <div class="options">
                    @if(isAllowed('sellerGoals','post'))
                        <i class="fa fa-plus" id="createGoal" title="@lang('labels.create-goal')"></i>
                    @endif
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6 col-md-offset-6">
                        <div class="">
                            <div class="form-group-sm col-md-3">
                                {!! Form::select('user',withEmpty($sellers,Lang::get('labels.select-seller')),$userId,['class'=>'form-control sellerGoalsFilters']) !!}
                            </div>
                            <div class="form-group-sm col-md-3">
                                {!! Form::select('year',withEmpty($years,Lang::get('labels.select-year')),$year,['class'=>'form-control sellerGoalsFilters']) !!}
                            </div>
                            <div class="form-group-sm col-md-3">
                                {!! Form::select('month',withEmpty($months,Lang::get('labels.select-month')),$month,['class'=>'form-control sellerGoalsFilters']) !!}
                            </div>
                            <div class="form-group-sm col-md-3">
                                <button class="btn btn-green" id="applySellerGoalsFilter">Go</button>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                @foreach($goals as $goal)
                    <div class="col-md-3 col-sm-3">
                        <a href="{{url('seller-goals/show',$goal->Id)}}" title="@lang('labels.see-daily-stats')">
                            <strong>
                               {{date("F", mktime(0, 0, 0, $goal->Month))}}&nbsp;{{$goal->Year or "-"}} -
                            {{$goal->User->FullName or "-"}}
                            </strong>
                        </a>
                        &nbsp;
                        @if(isAllowed('sellerGoals','patch'))
                            <i class="fa fa-pencil EditSellerGoal" title="@lang('labels.edit-goal')" data-goal-id="{{$goal->Id}}"></i>
                        @endif
                        &nbsp;
                        @if(isAllowed('sellerGoals','post'))
                            <i class="fa fa-copy CopySellerGoal" title="@lang('labels.copy-goal')" data-goal-id="{{$goal->Id}}"></i>
                        @endif
                        &nbsp;
                        @if(isAllowed('sellerGoals','delete'))
                            <i class="fa fa-times DeleteSellerGoal" title="Delete This Goal" data-goal-id="{{$goal->Id}}"></i>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-list cell-border datatables dtr-inline">
                                <thead>
                                    <tr>
                                        <th>@lang('labels.goal-name')</th>
                                        <th>@lang('labels.goal')</th>
                                        <th>@lang('labels.value')</th>
                                        <th>@lang('labels.difference')</th>
                                    </tr>
                                </thead>
                                <tbody class="goalStatistics" data-goal-id="{{$goal->Id}}">
                                    <tr class="NewSalesCountStats">
                                        <td>@lang('labels.NewSalesCountGoal')</td>
                                        <td class="NewSalesCountGoal">{{$goal->NewSalesCountGoal}}</td>
                                        <td class="NewSalesCountValue">{{$goal->NewSalesCount}}</td>
                                        <td class="NewSalesCountValueDifference">{{$goal->NewSalesCountDiff}}</td>
                                    </tr>
                                    <tr class="NewSalesValueStats">
                                        <td>@lang('labels.NewSalesGoal')</td>
                                        <td class="NewSalesGoal">{{formatMoney($goal->NewSalesGoal,0)}} {{config('gcm.money-code-short')}}</td>
                                        <td class="NewSalesValue">{{formatMoney($goal->NewSalesValue,0)}} {{config('gcm.money-code-short')}}</td>
                                        <td class="NewSalesValueDifference">{{formatMoney($goal->NewSalesValueDiff,0)}} {{config('gcm.money-code-short')}}</td>
                                    </tr>
                                    <tr class="ReSalesCountStats">
                                        <td>@lang('labels.ReSalesCountGoal')</td>
                                        <td class="ReSalesCountGoal">{{$goal->ReSalesCountGoal}}</td>
                                        <td class="ReSalesCountValue">{{$goal->ReSalesCount}}</td>
                                        <td class="ReSalesCountValueDifference">{{$goal->ReSalesCountDiff}}</td>
                                    </tr>
                                    <tr class="ReSalesValueStats">
                                        <td>@lang('labels.ReSalesGoal')</td>
                                        <td class="ReSalesGoal">{{formatMoney($goal->ReSalesGoal,0)}} kr.</td>
                                        <td class="ReSalesValue">{{formatMoney($goal->ReSalesValue,0)}} kr.</td>
                                        <td class="ReSalesValueDifference">{{formatMoney($goal->ReSalesValueDiff,0)}} kr.</td>
                                    </tr>
                                    <tr class="UpSalesCountStats">
                                        <td>@lang('labels.UpSalesCountGoal')</td>
                                        <td class="UpSaleCountGoal">{{$goal->UpSalesCountGoal}}</td>
                                        <td class="UpSaleCountValue">{{$goal->UpSalesCount}}</td>
                                        <td class="UpSaleCountValueDifference">{{$goal->UpSalesCountDiff}}</td>
                                    </tr>
                                    <tr class="UpSalesValueStats">
                                        <td>@lang('labels.UpSalesGoal')</td>
                                        <td class="UpSaleGoal">{{formatMoney($goal->UpSalesGoal,0)}} kr.</td>
                                        <td class="UpSaleValue">{{formatMoney($goal->UpSalesValue,0)}} kr.</td>
                                        <td class="UpSaleValueDifference">{{formatMoney($goal->UpSalesValueDiff,0)}} kr.</td>
                                    </tr>
                                    <tr class="CallsStats">
                                        <td>@lang('labels.CallsGoal')</td>
                                        <td class="CallsGoal">{{$goal->CallsGoal}}</td>
                                        <td class="CallsValue">{{$goal->Calls}}</td>
                                        <td class="CallsValueDifference">{{$goal->CallsDiff}}</td>
                                    </tr>
                                    <tr class="HealthCheckStats">
                                        <td>HC goal</td>
                                        <td class="CompletedMeetings">{{$goal->HealthChecksGoal}}</td>
                                        <td class="CompletedMeetingsValue">{{$goal->CompletedMeetings}}</td>
                                        <td class="CompletedMeetingsValueDifference">{{$goal->CompletedMeetingsDiff}}</td>
                                    </tr>
                                    <tr>
                                        <td>@lang('labels.total-paid')</td>
                                        <td class="Payments" colspan="3">{{formatMoney($goal->Payments,0)}} kr.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach


            </div>
        </div>
    </div>
@stop