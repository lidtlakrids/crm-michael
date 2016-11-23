@extends('layout.screen_layout')
@section('page-title',Lang::get('labels.users-screen'))
@section('styles')
    <style type="text/css">
        .btn
        {
            cursor:pointer;
            display: inline;
            border-right: solid #000000;
        }
        .btn:last-child{border-right: none;}

        .active1
        {
            text-decoration: underline;
        }
        .table-center
        {
            margin : 0 auto;
        }

    </style>
    <link  href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link  href="{{ asset('css/clock-style.css') }}" rel="stylesheet">
@endsection

@section('scripts')
    <script src="{{ asset('/js/lib/jquery.js') }}"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/moment.js/2.0.0/moment.min.js"></script> <!-- move to local -->
    <script src="/js/clock-script.js"></script><!-- clock script -->
    <script>
        $(document).ready(function () {
            setTimeout(function () {
                location.reload(true)
            },30000)
        })
    </script>
@stop


@section('screen')

<div class="row">
    <div class="col-md-4">
        <a class="info-tiles tiles-toyo">
            <div class="tiles-heading">@lang('labels.time')</div>
            <div class="tiles-body-alt">
                <i class="fa fa-clock-o"></i>
                <div class="text-center" style="height:65px;"><span class="text-top"></span>

                    <div id="clock" class="dark">
                            <div class="digits"></div>
                    </div>
                </div>
                <small>DATE</small>
            </div>

        </a>
    </div>
    <div class="col-md-4">
        <a class="info-tiles tiles-orange">
            <div class="tiles-heading">@lang('labels.at-work-today')</div>
            <div class="tiles-body-alt">
                <i class="fa fa-group"></i>
                <div class="text-center"><span class="text-top"></span>{{(count($users['CheckedIn']) + (count($users['Break'])))}}</div>
                <small>@lang('labels.are-logged-in')</small>
            </div>

        </a>
    </div>
    <div class="col-md-4">
        <a class="info-tiles tiles-indigo">
            <div class="tiles-heading">Orders Today</div>
            <div class="tiles-body-alt">
                <i class="fa fa-shopping-cart"></i>
                <div class="text-center"><span class="text-top">*</span></div>
                <small>*</small>
            </div>


        </a>
    </div>
    {{--<div class="col-md-4">--}}
        {{--<a class="info-tiles tiles-brown">--}}
            {{--<div class="tiles-heading">???</div>--}}
            {{--<div class="tiles-body-alt">--}}
                {{--<i class="fa fa-check-square"></i>--}}
                {{--<div class="text-center"><span class="text-top"></span>27</div>--}}
                {{--<small>Er logget ind</small>--}}
            {{--</div>--}}
            {{--<div class="tiles-footer"></div>--}}
        {{--</a>--}}
    {{--</div>--}}
</div>

<!-- Tables -->
<div class="row">
    <div class="col-md-4">
        <div class="panel panel-brown">
            <div class="panel-heading">
                <h4>@lang('labels.checked-out')</h4>
            </div>
            <div class="panel-body">
                <div class="table-vertical">
                    <table class="table table-striped table-condensed">
                        <thead>
                        <tr>
                            <th>@lang('labels.name')</th>
                            <th>UID</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users['CheckedOut'] as $checkedOut)
                            <tr>
                                <td>{{$checkedOut->User->FullName}}</td>
                                <td>{{strtoupper($checkedOut->User->UserName)}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-sky">
            <div class="panel-heading">
                <h4>@lang('labels.checked-in')</h4>
            </div>
            <div class="panel-body">
                    <table class="table table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>@lang('labels.name')</th>
                                <th>UID</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($users['CheckedIn'] as $checkedIn)
                            <tr>
                                <td>{{$checkedIn->User->FullName}}</td>
                                <td>{{strtoupper($checkedIn->User->UserName)}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
    <div class="col-md-4">
        <div class="panel panel-green">
            <div class="panel-heading">
                <h4>@lang('labels.break')</h4>
            </div>
            <div class="panel-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="domvertical">
                        <div class="table-vertical">
                            <table class="table table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th>@lang('labels.name')</th>
                                    <th>UID</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($users['Break'] as $break)
                                    <tr>
                                        <td>{{$break->User->FullName}}</td>
                                        <td>{{strtoupper($break->User->UserName)}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop