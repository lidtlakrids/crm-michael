@extends('layout.main')

@section('page-title',Lang::get('labels.user-roles'))

@section('styles')
    {!! Html::style(asset('css/defaultTheme.css'))  !!} {{-- THIS CSS IS FOR FIXED HEADER --}}
@stop

@section('scripts')
    {!! Html::script(asset('js/lib/jquery.fixedheadertable.min.js')) !!}

    <script>
        $(function () {
            $('#scroll').fixedHeaderTable({
                footer: false,
                autoShow: true
            });

                $('td').click(function (){
                if(this.id != ""){
                    var Role = $('table th').eq($(this).index()).attr('id');
                    var UserId = this.parentNode.id;
                    var postdata = {"role": Role};
                    var td = this;

                    if (this.id == 0) {
                        var url = api_address + "Users('" + UserId + "')/action.AddToRole";
                        var new_content = "<i style='color:#008000;' class='fa fa-check'></i>";
                        var new_id = 1;
                        var message = Lang.get('messages.role-was-added');
                    } else {
                        var url = api_address + "Users('" + UserId + "')/action.RemoveFromRole";
                        var new_content = "<i style='color: #FF0000;' class='fa fa-times'></i>";
                        var new_id = 0;
                        var message = Lang.get('messages.role-was-removed');
                    }
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: JSON.stringify(postdata),
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        },
                        success: function () {
                            td.innerHTML = new_content;
                            td.id = new_id;
                            //                            new PNotify({
                            //                                title: message,
                            //                                type: 'success'
                            //                            });
                        },
                        error: function (error) {
                            error = JSON.parse(error.responseText);
                            new PNotify({
                                title: error.error.message,
                                type: 'error'
                            });
                        }
                    });
                 } // end if
            });
        });
    </script>
@stop

@section('content')

    <div class="row">
        <div class="col-xs-12 table-responsive">
            <div class="panel panel-acl">
                <div class="panel-heading">
                    <h4><i class="fa fa-unlock"></i> User Roles</h4>
                    <div class="options">
                        <i class="fa fa-question-circle"></i>
                    </div>
                </div>
                <div class="panel-body">
                    <div style="height: 500px">
                        @if(isset($roles->value))
                        <table id="scroll" class="table table-hover table-responsive table-list">
                            <thead>
                                <tr>
                                    <th>@lang('labels.user')</th>
                                    <th>@lang('labels.name')</th>
                                    @foreach($roles->value as $role)
                                        <th id="{{$role->Name}}">{{$role->Name}} <i style="color:blue;" class="fa fa-question" title="{{$role->Description}}"></i></th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($users->value as $user)
                                    <tr id="{{$user->Id}}">
                                        <td><a href="{{url('acl/userPermissions')}}">{{$user->UserName}}</a></td>
                                        <td>{{$user->FullName}}</td>
                                        {{--print all roles for user--}}
                                        @for($i=0;$i < count($roles->value);$i++)
                                        <?php
                                            $hasRole = false;
                                            foreach($user->Roles as $role){
                                                if($role->RoleId == $roles->value[$i]->Id){
                                                    $hasRole = true;
                                                }
                                            }?>
                                                @if($hasRole)
                                               <td id="1"> <i  style="color: #008000;" class="fa fa-check" title="@lang('labels.remove-from-role')"></i>  </td>
                                                @else
                                                <td id="0">  <i  style="color:#FF0000;" class="fa fa-times" title="@lang('labels.add-to-role')"></i> </td>
                                                @endif
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop