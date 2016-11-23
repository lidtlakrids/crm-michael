@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        <?php if($status == "Break"): ?>
               startBreakCounter();
        <?php endif;?>
    });
</script>
@stop
    @if (in_array($status,['CheckedOut','Absent','Sick','Vacation','error']) )
        <div style="float:left; margin-left: 5px;">
            <input type="submit" class="slide-button transition btn-begin-work" onclick="beginWork()" value="@lang('labels.check-in')">
        </div>
    @elseif($status == "CheckedIn")

        <div style="float:left; margin-left: 5px;">
        <input type="submit" class="slide-button transition btn-end-work" onclick="endWork()" value="@lang('labels.check-out')">
        </div>

        <div style="float:left; margin-left: 5px;">
        <input type="submit" class="slide-button transition btn-begin-break" onclick="beginBreak()" value="@lang('labels.begin-break')">
        </div>

    @elseif($status =="Break")

        <div style="float:left; margin-left: 5px;">
            <input type="submit" class="slide-button transition btn-end-work" onclick="endWork()" value="@lang('labels.check-out')">
        </div>

        <div style="float:left; margin-left: 5px;">
            <input type="submit" class="slide-button transition btn-begin-work" onclick="beginWork()" value="@lang('labels.end-break')">
        </div>

        <div id="break-timer">
            <span id="counter-description" class="center-text"> @lang('labels.break-duration')</span>
            <span id="counter"></span>
            <div id="pause-controls-wrapper">
                <div style="width: 100%; margin-left: 50%;">
                    <input type="submit" class="slide-button transition btn-begin-work" onclick="beginWork()" value="@lang('labels.end-break')">
                 </div>
            </div>
        </div>
@endif

