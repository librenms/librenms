@extends('layouts.librenmsv1')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                {!! $content !!}
            </div>
        </div>
    </div>

    @if($refresh)
        <script type="text/javascript">
            $(document).ready(function () {

                $("#countdown_timer_status").html("<i class=\"fa fa-pause fa-fw fa-lg\"></i> Pause");
                var Countdown = {
                    sec: {{ (int)$refresh }},

                    Start: function () {
                        var cur = this;
                        this.interval = setInterval(function () {
                            $("#countdown_timer_status").html("<i class=\"fa fa-pause fa-fw fa-lg\"></i> Pause");
                            cur.sec -= 1;
                            display_time = cur.sec;
                            if (display_time == 0) {
                                location.reload();
                            }
                            if (display_time % 1 === 0 && display_time <= 300) {
                                $("#countdown_timer").html("<i class=\"fa fa-clock-o fa-fw fa-lg\"></i> Refresh in " + display_time);
                            }
                        }, 1000);
                    },

                    Pause: function () {
                        clearInterval(this.interval);
                        $("#countdown_timer_status").html("<i class=\"fa fa-play fa-fw fa-lg\"></i> Resume");
                        delete this.interval;
                    },

                    Resume: function () {
                        if (!this.interval) this.Start();
                    }
                };

                Countdown.Start();

                $("#countdown_timer_status").on("click", function (event) {
                    event.preventDefault();
                    if (Countdown.interval) {
                        Countdown.Pause();
                    } else {
                        Countdown.Resume();
                    }
                });

                $("#countdown_timer").on("click", function (event) {
                    event.preventDefault();
                });

            });
        </script>
    @else
        <script type="text/javascript">
            var no_refresh = true;
            $(document).ready(function () {
                $("#countdown_timer").html("Refresh disabled");
                $("#countdown_timer_status").html("<i class=\"fa fa-pause fa-fw fa-lg\"></i>");
                $("#countdown_timer_status").on("click", function (event) {
                    event.preventDefault();
                });
            });
        </script>
    @endif

    @config('enable_footer')
    <nav class="navbar navbar-default {{ $navbar }} navbar-fixed-bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h5>Powered by <a href="{{ \LibreNMS\Config::get('project_home') }}" target="_blank" rel="noopener" class="red">{{ \LibreNMS\Config::get('project_name') }}</a>.</h5>
                </div>
            </div>
        </div>
    </nav>
    @endconfig
@endsection
