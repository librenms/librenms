@props(['refresh' => 0, 'callback' => 'location.reload'])
<script>
    var no_refresh = false;
    var Countdown = {
        refreshNum: 0,
        sec: {{ max($refresh, 30) }},

        Start: function () {
            $("#countdown_timer_pause").html("<i class=\"fa fa-pause fa-fw fa-lg\"></i> {{ __('Pause') }}");
            this.interval = setInterval(() => {
                this.sec -= 1;
                if (this.sec <= 0) {
                    {{ $callback }}();
                    this.Reset();
                }
                $("#countdown_timer").text("{{ __('Refresh in :sec') }}".replace(":sec", this.sec));
            }, 1000);
        },
        Pause: function () {
            clearInterval(this.interval);
            delete this.interval;
            $("#countdown_timer_pause").html("<i class=\"fa fa-play fa-fw fa-lg\"></i> {{ __('Resume') }}");
            $("#countdown_timer").text(" {{ __('Refresh paused') }}");
        },
        Resume: function () {
        @if($refresh)
            if (!this.interval) this.Start();
        @endif
        },
        Reset: function () {
            this.sec = {{ max($refresh, 30) }};
            this.refreshNum++;
        },
    };

    $(document).ready(function () {
@if($refresh)
        $("#countdown_timer_menu").show();
        $("#countdown_timer_divider").show();

        Countdown.Start();
@elseif($refresh === 0)
        no_refresh = true;
        $("#countdown_timer_menu").show();
        $("#countdown_timer_divider").show();
        $("#countdown_timer").text("{{ __('Refresh disabled') }}");
        $("#countdown_timer_pause").closest("li").hide();
@else
        no_refresh = true;
        // hide the menu items
        $("#countdown_timer_menu").hide();
        $("#countdown_timer_divider").hide();
@endif

        $("#countdown_timer_pause").on("click", function (event) {
            event.preventDefault();
            if (Countdown.interval) {
                Countdown.Pause();
            } else {
                Countdown.Resume();
            }
        });

        $("#countdown_timer_refresh").on("click", function (event) {
            event.preventDefault();
            {{ $callback }}();
            Countdown.Reset();
        });

        $("#countdown_timer").closest("a").on("click", function (event) {
            event.preventDefault();
        });
    });
</script>
