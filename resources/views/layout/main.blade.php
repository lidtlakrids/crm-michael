<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="icon" href="{!! asset('images/cropped-favicon-32x32.png') !!}"/>
    <title>@yield('page-title')</title>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link href='https://fonts.googleapis.com/css?family=Roboto+Mono:400,500|Roboto:300,400,500' rel='stylesheet' type='text/css'>
    @include('layout.style')
</head>
<body>
@include('layout.header')
<datalist id="modelNames">
    <option value="Contract">
    <option value="Invoice">
    <option value="Order">
    <option value="Draft">
    <option value="TaskList">
    <option value="Lead">
    <option value="ClientAlias">
</datalist>
    <div id="page-container">
        <!-- BEGIN SIDEBAR -->
        @include('layout.left-menu')
        @include('layout.right-sidebar')
        <div id="page-content">
            <div id='wrap'>
                <div id="page-heading">
                 @include('layout.breadcrumbs')
                </div>
                <div class="container">
                {{--@if (Session::has('message'))--}}
                    {{--<div class="alert alert-success">{{ Session::get('message') }}</div>--}}
                {{--@endif--}}
                    <!-- MODAL START -->
                    <div class="modal fade" id="defaultModal" role="dialog" aria-labelledby="ModalLabel"
                         aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close btn-xs" data-dismiss="modal"
                                            aria-hidden="true">&times;</button>
                                    <h4 class="modal-title"></h4>
                                </div>
                                <div class="modal-body">
                                </div>
                                <div class="modal-footer">
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
@include('layout.footer')
@include('scripts.js-translation')
@yield('js-localization.head')
@include('layout.script')
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-82645514-1', 'auto', {
        userId: getUserName()
    });
    ga('send', 'pageview');
</script>
</body>
</html>