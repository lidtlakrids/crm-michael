@extends('layout.main')
@section('page-title',Lang::get('labels.role-permissions'))
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
        $(function(){
            $('#scroll').fixedHeaderTable({
                footer: false,
                autoShow: true
            });

            $('.permissions').on("click",function() {

                if (!this.id) {
                    var td = this;
                    var acoId = td.parentNode.id
                    var aroId = $('#aroId').val();
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
                            td.className = "removePermission";
                            td.id = data.Id;
                            td.innerHTML = '<i class="fa fa-check" style="color: #008000;"></i>';
                        },
                        error: function (error) {
                            error = JSON.parse(error.responseText);
                            new PNotify({
                                title: error.error.message,
                                type: 'error'
                            });
                        }
                    });
                } else {

                    var td = this;
                    var AroacoId = td.id;
                    var url = api_address + 'Acls(' + AroacoId + ')';
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        },
                        success: function (data) {
                            new PNotify({
                                title: 'Permission removed',
                                type: 'success'
                            });
                            td.innerHTML = '<i class="fa fa-times" style="color: #FF0000;"></i>';
                            td.removeAttribute('id');
                        },
                        error: function (error) {
                            error = JSON.parse(error.responseText);
                            new PNotify({
                                title: error.error.message,
                                type: 'error'
                            });
                        }
                    });
                } // END ELSE
            });
        });
    </script>
@stop

@section('content')


<div class="row">
    <div class="col-md-6">
        <div class="panel panel-acl">
            <div class="panel-heading">
                <h4><i class="fa fa-check-circle-o"></i> RolePermissions</h4>
                <div class="options">
                    <i class="fa fa-question-circle"></i>
                </div>
            </div>
            <div class="panel-body">
                @if(isset($roles) && $roles != null)
                    <dl class="dl-horizontal">
                        @foreach($roles as $role)
                            <dt>{{$role->Alias}}</dt>
                            <dd><a href="{{url('acl/rolePermissions',$role->Id)}}">@lang('labels.edit')<i class="fa fa-pencil"></i></a></dd>
                        @endforeach
                    </dl>
                @else
                 <div style="height: 500px;">    {{-- For the fixed header to work--}}
                        <input id="aroId" type="hidden" value="{{$role->Id}}">
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
                                    @if(array_key_exists($aco->Id,$permissions))
                                        <td id="{{$permissions[$aco->Id]}}" class="permissions"> <i style="color: #008000;" class="fa fa-check" title="@lang('labels.forbid-permission')"></i> </td>
                                    @else
                                        <td class="permissions"> <i style="color:#FF0000;" class="fa fa-times" title="@lang('labels.allow-permission')"></i> </td>
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