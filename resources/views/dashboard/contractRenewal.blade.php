@extends('layout.main')
@section('page-title',Lang::get('labels.up-for-renewal'))
@section('scripts')
    @include('scripts.dataTablesScripts')


    <script>
        $(document).ready(function(){
            $('#table-list').DataTable({
                bPaginate: false,
                "order": [[ 4, "asc" ]],
                "oLanguage": {
                    "sSearch":       "",
                    "sSearchPlaceholder": Lang.get('labels.search')
                }
            })
        })

    </script>

@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-contract">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i> @lang('labels.contracts')</h4>
                    <div class="info-bar"></div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="alert alert-success">30+ @lang('labels.days')</div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning">7 to 30 @lang('labels.days')</div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-danger">7- @lang('labels.days')</div>
                        </div>
                    </div>
                    <hr>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table id="table-list" class="table table table-hover datatables" width="100%">
                            <thead>
                            <tr>
                                <th>@lang('labels.client')</th>
                                <th>@lang('labels.product')</th>
                                <th>@lang('labels.country')</th>
                                <th>@lang('labels.start-date')</th>
                                <th>@lang('labels.end-date')</th>
                                <th>@lang('labels.assigned-to')</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($contracts as $c)
                                    {{--Calculate how many days it has left--}}
                                    <?php
                                        $today = new Carbon();
                                        $due     = Carbon::parse($c->EndDate);
                                        $daysLeft = $due->diffInDays($today,false);
                                        switch ($daysLeft){
                                            case "$daysLeft" === '0':
                                                $class = "danger";
                                            break;
                                            case $daysLeft > -7 && $daysLeft < 0:
                                                $class = "danger";
                                            break;
                                            case $daysLeft <= -7 && $daysLeft >= -30;
                                                $class = "warning";
                                                break;
                                            case $daysLeft < -30 :
                                                $class = "success";
                                                break;
                                            default:
                                                $class= 'danger';
                                                break;
                                        }
                                    ?>
                                    <tr class="{{$class}}">
                                        <td><a href="{{url('contracts/show',$c->Id)}}">{{$c->ClientAlias->Name or "--"}}</a></td>
                                        <td>{{$c->Product->Name or "-"}}</td>
                                        <td>{{$c->Country->CountryCode or "-"}}</td>
                                        <td data-order="{{strtotime($c->StartDate)}}">{{date('d-m-Y',strtotime($c->StartDate))}}</td>
                                        <td data-order="{{strtotime($c->EndDate)}}">{{date('d-m-Y',strtotime($c->EndDate))}}</td>
                                        <td>{{$c->Manager->FullName or "-"}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

