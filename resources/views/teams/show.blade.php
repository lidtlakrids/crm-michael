@extends('layout.main')
@section('page-title',Lang::get('labels.team')." : ".$team->Name)
@section('styles')
@stop

@section('scripts')
    <script>
        $(document).ready(function(){

            // clicked remove button
            $('body').on('click','.removeUser',function(event){
                var btn = $(event.target);
                btn.prop('disabled',true);

                var user_id = $(event.target).val();
                var team_id = $('#ModelId').val();
                $.ajax({
                    type: "POST",
                    url: api_address + 'ManagerTeams(' + team_id + ')/action.RemoveUser',
                    data: JSON.stringify({User_Id : user_id}),
                    success: function (data) {
                        //copy the table row and append it to the other table
                        var tr = $(event.target).closest('tr').clone();
                        //change the button and the icon
                        $(tr).find('.removeUser').removeClass('removeUser btn-danger').addClass('addUser btn-green').prop('disabled',false);
                        $(tr).find('.fa-times').removeClass('fa-times').addClass('fa-plus');
                        /// add the row the the end of the other table
                        $('#availableUsers >tbody:last-child').append(tr);

                        //remove it from the current one
                        $(event.target).closest('tr').remove();

                    },
                    error: function () {
                        btn.prop('disabled',false);

                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });


            // clicked remove button
            $('body').on('click','.addUser',function(event){
                var btn = $(event.target);
                btn.prop('disabled',true);

                var user_id = $(event.target).val();
                var team_id = $('#ModelId').val();

                $.ajax({
                    type: "POST",
                    url: api_address + 'ManagerTeams(' + team_id + ')/action.AddUser',
                    data: JSON.stringify({User_Id : user_id}),
                    success: function (data) {
                        //copy the table row and append it to the other table
                        var tr = $(event.target).closest('tr').clone();
                        //change the button and the icon
                        $(tr).find('.addUser').removeClass('addUser btn-green').addClass('removeUser btn-gray').prop('disabled',false);
                        $(tr).find('.fa-plus').removeClass('fa-plus').addClass('fa-times');
                        /// add the row the the end of the other table
                        $('#usersInTeam >tbody:last-child').append(tr);
                        //remove the current row from the table
                        $(event.target).closest('tr').remove();
                    },
                    error: function () {
                        btn.prop('disabled',false);
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });
        });
    </script>
@stop

@section('content')
    <input type="hidden" id="Model" value="ManagerTeams">
    <input type="hidden" id="ModelId" value="{{$team->Id}}">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-brown">
                <div class="panel-heading">
                    <h4>@lang('labels.team') : {{$team->Name}}</h4>
                    <div class="options">
                        <a href="{{url('teams/edit',$team->Id)}}" title="@lang('labels.edit')"><i class="fa fa-pencil"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="col-md-4">
                        <h4>@lang('labels.users-in-team')</h4>
                    <table class="table table-condensed table-hover" id="usersInTeam">
                        <thead>
                            <tr>
                                <th>@lang('labels.name')</th>
                                <th>@lang('labels.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($team->Users as $user)
                            <?php if(array_key_exists($user->Id,$users)){ unset($users[$user->Id]);}; ?>
                            <tr>
                                <td>{{$user->FullName or "----"}}</td>
                                <td><button class="btn btn-xs btn-gray pull-right removeUser" value="{{$user->Id}}"><i class="fa fa-times"></i></button></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>

                    <div class="col-md-4">
                        <h4>@lang('labels.users')</h4>
                        <table class="table table-condensed table-hover" id="availableUsers">
                            <thead>
                                <tr>
                                    <th>@lang('labels.name')</th>
                                    <th>@lang('labels.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $id=>$userName)
                                <tr>
                                    <td>{{$userName or "----"}}</td>
                                    <td><button class="btn btn-xs btn-green pull-right addUser" value="{{$id}}"><i class="fa fa-plus"></i></button></td>

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