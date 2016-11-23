<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Green Click Media Aps</title>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {!! Html::style(asset('css/styles.css')) !!}
    {!! Html::script( asset('/js/lib/jquery.js')) !!}
</head>
<body>

@include('header')

    @unless(Auth::guest())
        <div class="se-pre-con"></div>
        <div id="centerPopup"></div>
        <div id="page-container">
    <!-- BEGIN SIDEBAR -->
    @include('left-menu')
    @include('right-sidebar')
    <div id="page-content">
        <div id='wrap'>
            <div class="container">
            @if($errors->has())
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger">
                    {{$error}}
                    </div>
                @endforeach
            @endif

            @if (Session::has('message'))
                <div class="alert alert-success">{{ Session::get('message') }}</div>
            @endif

            @yield('content')
            </div>
        </div>
    </div>
</div>
@include('footer')
{!! Html::script( asset('/js/lib/jquery-ui.min.js')) !!}
{!! Html::script( asset('/js/js-translation.js')) !!}
{!! Html::script( asset('/js/lib/application.js')) !!}
{!! Html::script( asset('/js/lib/bootstrap.min.js')) !!}
{!! Html::script( asset('/js/lib/jquery.cookie.js')) !!}
{!! Html::script( asset('/js/lib/jquery.nicescroll.min.js'))!!}
@endunless
</body>
</html>