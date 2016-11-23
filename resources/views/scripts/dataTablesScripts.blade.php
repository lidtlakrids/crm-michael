{!! Html::style('/css/jquery.dataTables.min.css') !!}
{!! Html::style('/css/responsive.dataTables.css') !!}

{!! Html::script( asset('/js/lib/jquery.dataTables.min.js')) !!}
{!! Html::script( asset('/js/lib/jquery.dataTables.odata.js')) !!}
{!! Html::script( asset('/js/lib/dataTables.responsive.min.js')) !!}
{{-- Set a global Timeout setting on search --}}
<script type="text/javascript">
    $.extend( true, $.fn.dataTable.defaults, {
        searchDelay: 1000,
    } );
</script>