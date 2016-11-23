@extends('layout.main')
@section('page-title',Lang::get('labels.my-account'))
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <i class="fa fa-info"></i> User profile
                    <div class="options">
                        <a href="{{url('my-profile/edit')}}"><i class="fa fa-edit" title="@lang('labels.edit-profile')"></i></a>
                        <a href="{{url('my-profile/change-password')}}"><i class="fa fa-key"
                                                                           title="@lang('labels.change-password')"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-condensed">
                                    <h3><strong>{{ $user->FullName }}</strong></h3>
                                    <tbody>
                                    <tr>
                                        <td>@lang('labels.username')</td>
                                        <td>{{$user->UserName}}</td>
                                    </tr>
                                    <tr>
                                        <td>@lang('labels.birthdate')</td>
                                        <td>
                                            @if($user->Birthdate != null)
                                                {{date('d-m-Y',strtotime($user->Birthdate))}}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('labels.email')</td>
                                        <td>{{$user->Email or "--"}}</td>
                                    </tr>
                                    <tr>
                                        <td>@lang('labels.phone')</td>
                                        <td>{{$user->PrivatePhone or ""}}</td>
                                    </tr>
                                    <tr>
                                        <td>@lang('labels.address')</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>@lang('labels.nearest-relatives')</td>
                                        <td class="multiline">{{$user->NearestRelatives}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{--<div class="col-md-6">--}}
                        {{--<h3>About</h3>--}}
                        {{--<p>--}}
                        {{--Lorem ipsum dolor sit amet consectetur adipisicing elit. Asperiores in eveniet sapiente--}}
                        {{--error fuga tenetur ex ea dignissimos voluptas ab molestiae eos totam quo dolorem maxime illo--}}
                        {{--neque quia itaque. Asperiores in eveniet sapiente error fuga tenetur ex ea dignissimos--}}
                        {{--voluptas ab molestiae eos totam quo dolorem maxime illo neque quia itaque.--}}
                        {{--</p>--}}
                        {{--<p>--}}
                        {{--Dsperiores in eveniet sapiente error fuga tenetur ex ea dignissimos voluptas ab molestiae--}}
                        {{--eos totam quo dolorem maxime illo neque quia itaque.--}}
                        {{--</p>--}}
                        {{--</div>--}}
                    </div>
                    {{--<hr>--}}
                    {{--<div class="row">--}}
                        {{--<div class="col-md-12">--}}
                            {{--<div class="tab-container tab-success">--}}
                                {{--<ul class="nav nav-tabs">--}}
                                    {{--<li class="active"><a href="#home1" data-toggle="tab">Timeline</a></li>--}}
                                    {{--<li class=""><a href="#profile1" data-toggle="tab">Assigned Projects</a></li>--}}
                                {{--</ul>--}}
                                {{--<div class="tab-content">--}}
                                    {{--<div class="tab-pane active clearfix" id="home1">--}}
                                        {{--<div class="col-md-12">--}}
                                            {{--<h4 class="timeline-month"><span>November</span> <span>2013</span></h4>--}}
                                            {{--<ul class="timeline">--}}
                                                {{--<li class="timeline-orange">--}}
                                                    {{--<div class="timeline-icon"><i class="fa fa-camera"></i></div>--}}
                                                    {{--<div class="timeline-body">--}}
                                                        {{--<div class="timeline-header">--}}
                                                            {{--<span class="author">Posted by <a href="#">David Tennant</a></span>--}}
                                                            {{--<span class="date">Monday, November 21, 2013</span>--}}
                                                        {{--</div>--}}
                                                        {{--<div class="timeline-content">--}}
                                                            {{--<h3>Consectetur Adipisicing Elit</h3>--}}
                                                            {{--<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Officia, officiis, molestiae, deserunt asperiores architecto ut vel repudiandae dolore inventore nesciunt necessitatibus doloribus ratione facere consectetur suscipit!</p>--}}
                                                        {{--</div>--}}
                                                    {{--</div>--}}
                                                {{--</li>--}}
                                                {{--<li class="timeline-warning">--}}
                                                    {{--<div class="timeline-icon"><i class="fa fa-pencil"></i></div>--}}
                                                    {{--<div class="timeline-body">--}}
                                                        {{--<div class="timeline-header">--}}
                                                            {{--<span class="author">Posted by <a href="#">David Tennant</a></span>--}}
                                                            {{--<span class="date">Monday, November 21, 2013</span>--}}
                                                        {{--</div>--}}
                                                        {{--<div class="timeline-content">--}}
                                                            {{--<h3>Consectetur Adipisicing Elit</h3>--}}
                                                            {{--<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Officia, officiis, molestiae, deserunt asperiores architecto ut vel repudiandae dolore inventore nesciunt necessitatibus doloribus ratione facere consectetur suscipit!</p>--}}
                                                        {{--</div>--}}
                                                    {{--</div>--}}
                                                {{--</li>--}}
                                            {{--</ul>--}}
                                        {{--</div>--}}

                                        {{--<div class="col-md-12">--}}
                                            {{--<h4 class="timeline-month"><span>December</span> <span>2013</span></h4>--}}
                                            {{--<ul class="timeline">--}}
                                                {{--<li class="timeline-success">--}}
                                                    {{--<div class="timeline-icon"><i class="fa fa-video-camera"></i></div>--}}
                                                    {{--<div class="timeline-body">--}}
                                                        {{--<div class="timeline-header">--}}
                                                            {{--<span class="author">Posted by <a href="#">David Tennant</a></span>--}}
                                                            {{--<span class="date">Monday, December 21, 2013</span>--}}
                                                        {{--</div>--}}
                                                        {{--<div class="timeline-content">--}}
                                                            {{--<h3>Consectetur Adipisicing Elit</h3>--}}
                                                            {{--<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Officia, officiis, molestiae, deserunt asperiores architecto ut vel repudiandae dolore inventore nesciunt necessitatibus doloribus ratione facere consectetur suscipit!</p>--}}
                                                        {{--</div>--}}
                                                    {{--</div>--}}
                                                {{--</li>--}}
                                                {{--<li class="timeline-midnightblue">--}}
                                                    {{--<div class="timeline-icon"><i class="fa fa-group"></i></div>--}}
                                                    {{--<div class="timeline-body">--}}
                                                        {{--<div class="timeline-header">--}}
                                                            {{--<span class="author">Posted by <a href="#">David Tennant</a></span>--}}
                                                            {{--<span class="date">Thursday, December 12, 2013</span>--}}
                                                        {{--</div>--}}
                                                        {{--<div class="timeline-content">--}}
                                                            {{--<h3>Consectetur Adipisicing Elit</h3>--}}
                                                            {{--<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Officia, officiis, molestiae, deserunt asperiores architecto ut vel repudiandae dolore inventore nesciunt necessitatibus doloribus ratione facere consectetur suscipit!</p>--}}
                                                        {{--</div>--}}
                                                    {{--</div>--}}
                                                {{--</li>--}}
                                            {{--</ul>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}

                                    {{--<div class="tab-pane" id="profile1">--}}
                                        {{--<div class="table-responsive">--}}
                                            {{--<table class="table table-striped">--}}
                                                {{--<thead>--}}
                                                {{--<tr>--}}
                                                    {{--<th width="5%">#</th>--}}
                                                    {{--<th width="35%">Project Title</th>--}}
                                                    {{--<th width="35%">Due Date</th>--}}
                                                    {{--<th width="25%">Progress</th>--}}
                                                {{--</tr>--}}
                                                {{--</thead>--}}
                                                {{--<tbody>--}}
                                                {{--<tr>--}}
                                                    {{--<td>1</td>--}}
                                                    {{--<td>Lorem ipsum</td>--}}
                                                    {{--<td>Nov 5, 2013</td>--}}
                                                    {{--<td>--}}
                                                        {{--<div class="progress progress-striped" style="margin:5px 0 0">--}}
                                                            {{--<div class="progress-bar progress-bar-info" style="width: 30%;"></div>--}}
                                                        {{--</div>--}}
                                                    {{--</td>--}}
                                                {{--</tr>--}}
                                                {{--<tr>--}}
                                                    {{--<td>2</td>--}}
                                                    {{--<td>Dignissimos voluptas</td>--}}
                                                    {{--<td>Nov 10, 2013</td>--}}
                                                    {{--<td>--}}
                                                        {{--<div class="progress progress-striped" style="margin:5px 0 0">--}}
                                                            {{--<div class="progress-bar progress-bar-danger" style="width: 55%;"></div>--}}
                                                        {{--</div>--}}
                                                    {{--</td>--}}
                                                {{--</tr>--}}
                                                {{--<tr>--}}
                                                    {{--<td>3</td>--}}
                                                    {{--<td>Tenetur ex ea dignissimos</td>--}}
                                                    {{--<td>Nov 11, 2013</td>--}}
                                                    {{--<td>--}}
                                                        {{--<div class="progress progress-striped" style="margin:5px 0 0">--}}
                                                            {{--<div class="progress-bar progress-bar-success" style="width: 35%;"></div>--}}
                                                        {{--</div>--}}
                                                    {{--</td>--}}
                                                {{--</tr>--}}
                                                {{--<tr>--}}
                                                    {{--<td>4</td>--}}
                                                    {{--<td>Quo dolorem maxime</td>--}}
                                                    {{--<td>Nov 21, 2013</td>--}}
                                                    {{--<td>--}}
                                                        {{--<div class="progress progress-striped" style="margin:5px 0 0">--}}
                                                            {{--<div class="progress-bar progress-bar-primary" style="width: 20%;"></div>--}}
                                                        {{--</div>--}}
                                                    {{--</td>--}}
                                                {{--</tr>--}}
                                                {{--<tr>--}}
                                                    {{--<td>5</td>--}}
                                                    {{--<td>Dsperiores</td>--}}
                                                    {{--<td>Nov 17, 2013</td>--}}
                                                    {{--<td>--}}
                                                        {{--<div class="progress progress-striped" style="margin:5px 0 0">--}}
                                                            {{--<div class="progress-bar progress-bar-inverse" style="width: 70%;"></div>--}}
                                                        {{--</div>--}}
                                                    {{--</td>--}}
                                                {{--</tr>--}}
                                                {{--</tbody>--}}
                                            {{--</table>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                </div>
            </div>
        </div>
    </div>


@endsection
