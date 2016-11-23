@extends('layout.main')

@section('content')
    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <i class="fa fa-info"></i> Role Info for {{ $role['display_name'] }}
                <a href="{{url('acl/editRole',$role['id'])}}" class="btn btn-default">Edit role</a>
            </div>

            <div class="panel-body">
                <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="example">

                    <tbody>

                    <tr>
                        <td> Name: </td>
                        <td>
                            @if (isset($role['name']))
                                {{ $role['name'] }}
                            @else
                                -----
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Display name: </td>
                        <td>
                            @if (isset($role['display_name']))
                                {{ $role['display_name'] }}
                            @else
                                -----
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Description: </td>
                        <td>
                            @if (isset($role['description']))
                                {{ $role['description'] }}
                            @else
                                -----
                            @endif
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">Add role permissions</div>

            <div class="panel-body">

                {!! Form::open(['method'=>'POST','action'=>['RolesPermissionController@showRole'],'class'=>'form-horizontal']) !!}
                <div class="form-group">
                    {!! Form::label('name','Permission name:',['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('name',null,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('display_name','Display name:',['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('display_name',null,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('description','Description:',['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('description',null,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="btn-toolbar">
                    {!! Form::submit('Add role permission',['class'=> 'btn btn-primary form-control']) !!}
                </div>
                {!! Form::close() !!}

            </div>

        </div>
    </div>

<div class="col-md-6">
    <div class="panel-grape">
        <div class="panel-heading"><span><i class="fa fa-info"></i> Permissions for role : {{$role['display_name']}}</span>
        </div>
        <table class="table table-hover table-bordered">
            <thead>
                <th>Permission</th>
                <th>Display name</th>
                <th>Description</th>
            </thead>
            <tbody>
                @foreach($permissions as $perm)
                <tr>
                    <td>{{$perm['name']}}</td>
                    <td>{{$perm['display_name']}}</td>
                    <td>{{$perm['description']}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
