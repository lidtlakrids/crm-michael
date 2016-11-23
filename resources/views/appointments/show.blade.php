@extends('layout.main')
@section('page-title',Lang::get('labels.appointment').'')

@section('styles')
@stop

@section('scripts')
    @include('scripts.x-editable')
    <script>
        $(document).ready(function () {
            $('.bookAppointment').click(function (event) {
                $(event.target).prop('disabled', true);
                //get the event id
                var appointmentId = getModelId();
                //get the leadId
                var leadId = $(event.target).val();

                $.ajax({
                    url: api_address + "Leads(" + leadId + ")/action.Book",
                    type: "POST",
                    data: JSON.stringify({Calendar_Id: appointmentId}),
                    success: function (data) {
                        setTimeout(function () {
                            window.location.reload(true);
                        }, 3000)
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });

            $('.createOnGoogle').click(function (event) {
                $(event.target).prop('disabled', true);
                //get the event id
                var appointmentId = getModelId();
                $.ajax({
                    url: api_address + "CalendarEvents(" + appointmentId + ")/CreateOnGoogle",
                    type: "GET",
                    success: function (data) {
                        setTimeout(function () {
                            window.location.reload(true);
                        }, 3000)
                    }
                });
            });

            if (appointment.Model && appointment.ModelId) {
                $.when(getCompanyName(appointment.Model, appointment.ModelId))
                        .then(function (name) {
                            if (name.value !== 'Undefined') {
                                $('.appointmentCompanyName').text(name.value);
                            }
                        });
            }
//            // set a handling date
//            $("#app-StartTime").editable({
//                url: api_address + "CalendarEvents(" + getModelId() + ")",
//                params: function (params) {
//                    data = {};
//                    data['Start'] = params.value;
//                    return JSON.stringify(data);
//                },
//                ajaxOptions: {
//                    type: "patch",
//                    dataType: 'application/json',
//                    beforeSend: function (request) {
//                        request.setRequestHeader("Content-Type", "application/json");
//                    },
//                    success: function () {
//                        $('#app-EndTime').attr('data-value', moment(data['Start']).add(30, 'm').format()).html(toDateTime(moment(data['Start']).add(30, 'm')));
//                    }
//                },
//                template: 'D-MMM-YYYY HH:mm',
//                format: 'YYYY-MM-DDTHH:mm:ss.sssZ', // ISO date
//                viewformat: "DD-MM-YYYY HH:mm",
//                combodate: {
//                    minYear: moment().year(),
//                    maxYear: moment().year() + 3,
//                    firstItem: 'name'
//                }
//            });
//
//            // set a handling date
//            $("#app-EndTime").editable({
//                url: api_address + "CalendarEvents(" + getModelId() + ")",
//                params: function (params) {
//                    var data = {};
//                    data['End'] = params.value;
//                    return JSON.stringify(data);
//                },
//                ajaxOptions: {
//                    type: "patch",
//                    dataType: 'application/json',
//                    beforeSend: function (request) {
//                        request.setRequestHeader("Content-Type", "application/json");
//                    }
//                },
//                template: 'DD-MMM-YYYY HH:mm',
//                format: 'YYYY-MM-DDTHH:mm:ss.sssZ',
//                viewformat: "DD-MM-YYYY HH:mm",
//                combodate: {
//                    minYear: moment().year(),
//                    maxYear: moment().year() + 3,
//                    firstItem: 'name'
//                }
//            });

            //define responsive timeline border height
            setTimeLineBorders();


            var ccAppointment = $('#ccAppointment').val();
            if(ccAppointment){
                $('.updateAppointment').prop('disabled',true).text('Appointment is completed or cancelled');
            }

        });

    </script>

@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','CalendarEvent',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $appointment->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4><i class="fa fa-calendar"></i> {{$appointment->Summary or "Appointment"}}</h4>
                </div>

                <div class="panel-body">
                    <div class="col-md-4 col-sm-6">
                            <p>
                                <strong>{{$appointment->Booker->FullName or $appointment->User->FullName}}</strong> created
                                    @if($appointment->EventType != null)
                                        @if($appointment->EventType == 'Appointment')
                                            an
                                        @else
                                            a
                                        @endif
                                        <strong>@lang('labels.'.$appointment->EventType)</strong>
                                    @else
                                        an <strong>event</strong>
                                    @endif

                            </p>
                                <p>
                                    From&nbsp;
                                    <a id="app-StartTime" title="Click to set or change Start date"
                                       data-type="combodate" data-value="{{$appointment->Start}}"
                                       data-pk="{{$appointment->Id}}" data-placement="right">
                                        @if(isset($appointment->Start))
                                            {{date('d-m-Y H:i',strtotime($appointment->Start))}}
                                        @else
                                            Set a Start date
                                        @endif
                                    </a>
                                    </p>
                                    <p>
                                    Until &nbsp;
                                    <a id="app-EndTime" title="Click to set or change End date"
                                       data-type="combodate" data-value="{{$appointment->End}}"
                                       data-pk="{{$appointment->Id}}" data-placement="right">
                                        @if(isset($appointment->End))
                                            {{date('d-m-Y H:i',strtotime($appointment->End))}}
                                        @else
                                            Set an End date
                                        @endif
                                    </a>
                                </p>
                        <hr>
                        <p class="multiline col-md-offset-1">{{$appointment->Description or ""}}</p>

                        @if($appointment->Model != null && $appointment->ModelId != null)
                        <p><strong>For item:</strong>
                            <a href="{{linkToItem($appointment->Model,$appointment->ModelId,true)}}"
                                                         target="_blank" class="appointmentCompanyName">View</a></p>
                        @endif

                        @if($appointment->Booker != null)
                            @if($appointment->Booker->FullName !== $appointment->User->FullName)
                            <div>Booked for <strong>{{$appointment->User->FullName or "--"}}</strong></div>
                            @endif
                        @endif
                        <div class="text-right">
                            @if($appointment->HtmlLink !== null)
                                <a href="{{$appointment->HtmlLink}}"
                                   target="_blank">@lang('labels.view-in-calendar')</a>
                            @endif
                        </div>

                        <hr>
                        <div class="col-sm-6" style="margin-bottom: 30px;">
                            <button class="updateAppointment btn btn-primary form-control" data-calendarevent-id="{{$appointment->Id}}">
                                Update
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 " style="padding-left: 50px;">
                        @if(!empty($appointment->Attendees))
                            <h4>@lang('labels.attendees')</h4>
                            @foreach($appointment->Attendees as $attendee)
                                <div class="checkbox block">
                                    <label>
                                        <input checked="" disabled="disabled" value="{{$attendee->EMail or ""}}"
                                               name="appointment-Attendees" type="checkbox">
                                        {{$attendee->EMail or "--"}}
                                    </label>
                                </div>
                            @endforeach
                        @endif

                        @if(!$appointment->IsCreated && $appointment->Model == "Lead") <!-- not created, give option to book it -->
                        @lang('messages.appointment-not-created-in-calendar')<br>
                        <button class="btn btn-orange bookAppointment"
                                value="{{$appointment->ModelId}}">@lang('labels.create-appointment')</button>
                        @elseif(!$appointment->IsCreated)
                            @lang('messages.appointment-not-created-in-calendar')<br>
                            <button class="btn btn-orange createOnGoogle"
                                    value="{{$appointment->ModelId}}">@lang('labels.create-appointment')</button>
                        @endif
                    </div>
                    <div class="col-md-4  col-sm-6">
                        <?php $cancelled = false; ?>
                            @foreach($appointment->Activity as $activity )
                                <?php
                                $message = '';
                                $icon = '';
                                $color = 'lightgrey';
                                if ($activity->ActivityType == "Cancel") {
                                    $message = 'cancelled the appointment';
                                    $icon = 'times';
                                    $color = '#d94136';
                                    $cancelled = true;
                                } elseif ($activity->ActivityType == "Move") {
                                    $message = 'moved the appointment';
                                    $icon = 'calendar-o';
                                    $color = '#0e4194';
                                } elseif ($activity->ActivityType == "NoAnswer") {
                                    $message = 'did not get an answer';
                                    $icon = 'frown-o';
                                    $color = 'grey';
                                } elseif ($activity->ActivityType == 'Completed') {
                                    $message = 'completed the appointment';
                                    $icon = 'check';
                                    $color = '#0f7e3e';
                                    $cancelled = true;

                                }
                                ?>
                                <div class="row timelineItem">
                                    <div class="col-xs-6 col-sm-2">
                                        <span class=" fa-stack fa-2x">
                                        <i class="fa fa-circle fa-stack-2x" style="color:lightgrey;"></i>
                                        <i class="fa fa-{{$icon}} fa-stack-1x" style="color:{{$color}};"></i>
                                        </span>
                                        <div class="borderDiv hidden-xs" style="border-left:3px solid lightgrey; margin-top: -5px; margin-bottom: -5px; margin-left: 27px;"></div>
                                    </div>
                                    <div class="col-xs-6 col-sm-3  col-sm-push-7 text-right" style="margin-top: 15px;">{{date('d-m-Y H:i',strtotime($activity->Created))}}</div>
                                    <div class="col-xs-12 col-sm-7 col-sm-pull-3"
                                         style="padding-top: 15px;">
                                        <p>{{$activity->User->FullName or "--"}}<strong> {{$message}}</strong>@if($activity->Comment->Message): </p>
                                        <p ><span class="multiline">"{{$activity->Comment->Message}}"@endif</span>
                                        </p>
                                    </div>
                                    <div class="horizontalBorderDiv col-xs-12 col-sm-10" style="border-bottom:1px solid lightgrey; margin-bottom: 15px;"></div>
                                </div>
                            @endforeach
                        <input type="hidden" id="ccAppointment" value="{{$cancelled}}">
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            @include('layout.tabs-section',
                            ['appointments'=>true])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
    </div>

@stop