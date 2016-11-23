@extends('layout.main')
@section('page-title',Lang::get('Notification').$notification->Content)

@section('styles')
@stop

@section('scripts')
    <script>
        if(notification.Model && notification.ModelId){
            $.when(getCompanyName(notification.Model, notification.ModelId))
                    .then(function (name) {
                        if (name.value !== 'Undefined') {
                            $('.itemName').append(name.value);
                        }
                    });
        }
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-midnightblue">
                <div class="panel-heading">
                    <h4><i class="fa fa-exclamation"></i> Notification</h4>
                </div>

                <div class="panel-body">
                    <div class="col-md-5 col-sm-6">
                        <div class="row">
                            <div class="col-md-8">
                                <p>
                                    Created by <strong>{{$notification->Creator->FullName or "--"}}</strong>
                                    on
                                    <strong>{{toDateTime($notification->Created)}}</strong>
                                </p>
                                <p>
                                    Recipient: <strong>{{$notification->Recipient->FullName or "--"}}</strong>
                                </p>
                            </div>
                            <div class="col-md-4">



                                    @if(isset($notification->Read))
                                    <p style="color: green; text-align: right;">
                                        <strong><i class="fa fa-eye"></i> Seen</strong> on {{toDateTime($notification->Read)}}
                                    </p>
                                        @if(isAdmin() || Auth::user()->externalId == $notification->Recipient_Id)
                                                <span class="btn pull-right mark-as-not-seen"
                                                 data-notification-id="{{$notification->Id}}" style="color: orange;">
                                                <i class='fa fa-eye-slash'></i> Mark as unseen </span>
                                        @endif
                                    @else
                                    <p class="pull-right" style="color: orange;">
                                        <strong><i class="fa fa-eye-slash"></i> Not seen yet</strong>
                                    </p>
                                    @endif
                            </div>
                        </div>
                        <hr>
                        <div class="multiline">{!!  $notification->Content or ""!!}</div>
                        </br>
                        @if(isset($notification->Model) && isset($notification->ModelId))
                            <p class="itemName"><strong>For item:</strong> <a href="{{linkToItem($notification->Model,$notification->ModelId,true)}}"
                                                             target="_blank">{{$notification->Model . " " . $notification->ModelId . " "}}</a></p>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop