<ol class="breadcrumb">
    @if(isset($result))
    <li>{!! Html::linkAction('DashboardsController@index', Lang::get('labels.home')) !!}</li>
    <?php  $i=1;?>
        @foreach($result as $k=>$address)
            @if($i==$count || in_array($k,['show','edit','create','information']) || is_numeric($k))

            <li class="active"> {{studly_case($k)}}</li>
            @else
                <li> <a href="{{url($address)}}">{{studly_case($k)}}</a></li>
            @endif
           <?php  $i++;?>
        @endforeach
    @endif
</ol>
