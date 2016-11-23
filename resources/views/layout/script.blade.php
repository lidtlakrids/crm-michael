{!! Html::script('/js/lib/jquery.min.js') !!}
{!! Html::script( asset('/js/lib/pnotify.custom.min.js')) !!}
{!! Html::script( asset('/js/lib/bootstrap.min.js')) !!}
{!! Html::script( asset('/js/lib/jquery-ui.min.js')) !!} {{-- jqueryUI needs to be included after bootstrap to prevent conflicts --}}
{!! Html::script( asset('/js/lib/jquery.cookie.js')) !!}
{!! Html::script( asset('/js/lib/jquery.nicescroll.min.js')) !!}
{!! Html::script( asset('/js/lib/bootbox.min.js')) !!}
{!! Html::script( asset('/js/lib/jquery.template.min.js')) !!}
{{-- Jquery more, for overflowing content --}}
{!! Html::script( asset('/js/lib/jquery.more-plugin.min.js')) !!}
{!! Html::script( asset('/js/lib/jquery.more.min.js')) !!}
{!! Html::script( asset('/js/lib/moment.min.js')) !!}
{!! Html::script( asset('/js/lib/daterangepicker.min.js')) !!}
{!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
{!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
{!! Html::script(asset('js/lib/bootstrap-tokenfield.min.js')) !!}

{{-- END Jquery more, for overflowing content --}}
{{-- SIGNAL R--}}
{{--{!! Html::script( asset('js/lib/signalr.min.js')) !!}--}}
{{--{!! Html::script( 'http://svn.crmtest.dk:8484/signalr/hubs') !!}--}}
{{-- END SIGNAL R--}}
{{-- custom scripts--}}
{!! Html::script( asset('/js/lib/application.js')) !!}
{!! Html::script( asset('/js/lib/comments.js?v=5')) !!}
{!! Html::script( asset('/js/lib/entityOwnership.js')) !!}
{{-- http://www.highcharts.com/download--}}
{!! Html::script( asset('/js/jquery.highchartTable.js')) !!}
{{-- http://highcharttable.org/ --}}
{!! Html::script( asset('/js/plugins/Highcharts-4.2.3/js/highcharts.js')) !!}
{!! Html::script( asset('/js/plugins/Highcharts-4.2.3/js/highcharts-more.js')) !!}
{!! Html::script( asset('/js/plugins/Highcharts-4.2.3/js/modules/no-data-to-display.js')) !!}
{{--  http://morrisjs.github.io/morris.js/ --}}
{{--{!! Html::script( asset('/js/plugins/charts-morrisjs/morris.min.js')) !!}--}}
{{-- App insights, tracking page performance --}}
{{--{!! Html::script( asset('js/lib/appInsights.js')) !!}--}}
{{-- Serialize to json script. Turns form data to suitable json that the backend can understand--}}
{!! Html::script(asset('/js/lib/serializetoJson.min.js')) !!}

@include('scripts.x-editable')
@yield('scripts')
