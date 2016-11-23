<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('page-title')</title>
        <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
        <style>
            #screen-container{ margin: auto;  width: 70%;}
            .screen-container{ }
        </style>
        @include('layout.style')
    </head>
    <body>
        <div id="screen-container">
            <div class="screen-container">
                @yield('screen')
            </div>
            @include('layout.footer')

        </div>
        @include('layout.script')
    </body>
</html>