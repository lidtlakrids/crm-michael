@extends('layout.main')
@section('page-title',Lang::get('labels.user-permissions'))

@section('styles')
    {!! Html::style(asset('css/defaultTheme.css'))  !!} {{-- THIS CSS IS FOR FIXED HEADER --}}
    <style>
        i {
            cursor: pointer;
        }
    </style>
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.fixedheadertable.min.js')) !!}
    <script>
        $(function() {
            $('#scroll').fixedHeaderTable({
                footer: false,
                autoShow: true
            });

            $('.permissions').on("click", function () {
                var td = $(this);
                var aroId = $('#aroId').val();

                //NoT CREATED
                if ($(this).hasClass('notCreated')) {
                    var acoId = td.parent('tr').attr('id');
                    var postdata = {
                        "ACO_Id": acoId,
                        "ARO_Id": aroId,
                        "Allowed": true
                    };
                    var url = api_address + 'Acls';
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: JSON.stringify(postdata),
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        },
                        success: function (data) {
                            td.removeClass('notCreated');
                            td.addClass('createdAllowed');
                            td.attr('id', data.Id);
//                            new PNotify({
//                                title: 'Permission added',
//                                type: 'success'
//                            });
                            td.html("").append('<i class="fa fa-check" style="color: #008000;"></i>');
                        },
                        error: function (error) {
                            error = JSON.parse(error.responseText);
                            new PNotify({
                                title: error.error.message,
                                type: 'error'
                            });
                        }
                    });
                    // PATCH TO DENY IT
                } else if ($(this).hasClass('createdAllowed')) {
                    var AroacoId = td.attr('id');
                    var requestData = {Allowed: false};
                    $.ajax({
                        type: "Patch",
                        url: api_address + 'Acls(' + AroacoId + ')',
                        data: JSON.stringify(requestData),
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        },
                        success: function (data) {
                            new PNotify({
                                title: 'Permission removed',
                                type: 'success'
                            });

                            td.removeClass('createdAllowed');
                            td.addClass('createdDenied');

                            td.html("").append('<i class="fa fa-times" style="color: #FF0000;"></i>');
                        },
                        error: function (error) {
                            error = JSON.parse(error.responseText);
                            new PNotify({
                                title: error.error.message,
                                type: 'error'
                            });
                        }
                    });
                } else if ($(this).hasClass('createdDenied')) {
                    var AroacoId = td.attr('id');
                    var requestData = {Allowed: true};
                    $.ajax({
                        type: "Patch",
                        url: api_address + 'Acls(' + AroacoId + ')',
                        data: JSON.stringify(requestData),
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        },
                        success: function (data) {
                            new PNotify({
                                title: 'Permission removed',
                                type: 'success'
                            });

                            td.removeClass('createdDenied');
                            td.addClass('createdAllowed');

                            td.html("").append('<i class="fa fa-check" style="color: #008000;"></i>');
                        },
                        error: function (error) {
                            error = JSON.parse(error.responseText);
                            new PNotify({
                                title: error.error.message,
                                type: 'error'
                            });
                        }
                    });
                }
            });
            $('.refreshPermissionsCache').click(function(){
                    // user id comes from php
                $.post(api_address+'Users(\''+userId+'\')/action.UpdateCache').success(function(){

                    new PNotify({
                        title:"Updating user cache"
                    })
                })
            })



        });
    </script>
@stop

@section('content')

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-acl">
            <div class="panel-heading">
                <h4><i class="fa fa-check-square-o"></i>@lang('labels.user-permissions')</h4>
                <div class="options">
                    <i class="fa fa-question-circle"></i>
                    @if(isset($user))<i class="fa fa-refresh refreshPermissionsCache" title="Refresh User Permissions cache"></i>@endif
                </div>
            </div>
            <div class="panel-body">
                @if(isset($users))
                    <div class="col-md-12">
                        <dl class="dl-horizontal-row">
                            @foreach($users as $user)
                                @if(isset($userNames[$user->ForeignKey]))
                                <dt>{{$userNames[$user->ForeignKey] or $user->Alias}}</dt>
                                <dd>
                                    <a href="{{url('acl/userPermissions',$user->Id)}}">@lang('labels.edit')<i class="fa fa-pencil"></i></a>
                                </dd>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @else
                    <div style="height: 500px;">
                        <input id="aroId" type="hidden" value="{{$user->Id}}">
                        <table id="scroll"  class="table table-hover table-condensed">
                            <thead>
                            <tr>
                                <th>@lang('labels.controller')</th>
                                <th>@lang('labels.method')</th>
                                <th>@lang('labels.allowed')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($acos as $aco)
                                <tr id="{{$aco->Id}}">
                                    <td>{{$aco->Controller}}</td>
                                    <td>{{$aco->Method}}</td>
                                    @if(array_key_exists($aco->Id,$allowedPermissions))
                                        <td id="{{$allowedPermissions[$aco->Id]}}" class="permissions createdAllowed"> <i style="color: #008000;" class="fa fa-check" title="@lang('labels.forbid-permission')"></i> </td>
                                    @elseif(array_key_exists($aco->Id,$deniedPermissions))
                                        <td id="{{$deniedPermissions[$aco->Id]}}"  class="permissions createdDenied"> <i style="color:#FF0000;" class="fa fa-times" title="@lang('labels.allow-permission')"></i> </td>
                                    @else
                                        <td class="permissions notCreated"> <i style="color:#FF0000;" class="fa fa-times" title="@lang('labels.allow-permission')"></i> </td>

                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop