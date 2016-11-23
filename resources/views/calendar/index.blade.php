@extends('layout.main')
@section('page-title',Lang::get('labels.calendar'))
@section('styles')
    <style>

    </style>
@stop
@section('scripts')
@stop


@section('content')
    <div class="row">
        <div class="col-md-12" >
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-calendar"></i> @lang('labels.calendar')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh refreshCalendar"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    @if(isAllowed('calendarEvents','post'))
                    <div class="row">
                        <div><button class="createEvent btn btn-green">Make event</button></div>
                    </div>
                    @endif
                    <div class="row">
                    <div class="responsive-iframe-container" style="min-height: 300px;">
                        <iframe src="https://www.google.com/calendar/embed?showTitle=1&amp;showDate=0&amp;showPrint=0&amp;showCalendars=0&amp;showTz=0&amp;height=400&amp;wkst=2&amp;bgcolor=%23FFFFFF&amp;src={{Auth::user()->userName}}@greenclickmedia.dk&amp;color=%232F6309&amp;ctz=Europe%2FCopenhagen"
                                style="border-width: 0;" seamless></iframe>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
