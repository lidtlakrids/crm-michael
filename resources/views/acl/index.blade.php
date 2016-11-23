@extends('layout.main')
@section('page-title','ACL')

@section('scripts')
    <script>

        $(document).ready(function () {

            $('.updateAros').on('click', function (event) {
                var button = $(event.target);

                button.prop('disabled',true);

                $.post(api_address+'Acls/action.UpdateRequesters').success(function (data) {
                    button.prop('disabled',false);
                })

            })
        })



    </script>

@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <span>{!! Html::linkAction('AclController@userRoles',Lang::get('labels.user-roles'),array(),array('class' => 'btn btn-default')) !!}</span>
            <span>{!! Html::linkAction('AclController@userPermissions',Lang::get('labels.user-permissions'),array(),array('class' => 'btn btn-default')) !!}</span>
            <span>{!! Html::linkAction('AclController@rolePermissions',Lang::get('labels.role-permissions'),array(),array('class' => 'btn btn-default')) !!}</span>
            <span>{!! Html::linkAction('AclController@roles',Lang::get('labels.roles'),array(),array('class' => 'btn btn-default')) !!}</span>
            <span><button class="updateAros">@lang('labels.update-requesters')</button></span>
        </div>
    </div>
@endsection
