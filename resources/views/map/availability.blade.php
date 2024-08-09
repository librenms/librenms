@extends('layouts.librenmsv1')

@section('title', __('Availability Map'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-availability-title-left">
                    <span class="page-availability-title">Availability map for</span>
                    <select id="show_items" class="page-availability-report-select" name="show_items" onchange="refreshMap()">
                        <option value="0" selected>only devices</option>
@if($services)
                        <option value="1" >only services</option>
                        <option value="2" >devices and services</option>
@endif
                    </select>
                </div>
@if($use_groups)
                <div class="page-availability-title-right">
                    <span class="page-availability-title">Device group</span>
                    <select id="show_group" class="page-availability-report-select" name="show_group" onchange="refreshMap()">
                        <option value="0" selected>show all devices</option>
@foreach($devicegroups as $g)
                        <option value="{{$g['id']}}">{{$g['name']}}</option>
@endforeach
                    </select>
                </div>
@endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="page-availability-title-left" id="countdown">
                    <span class="countdown_timer" id="countdown_timer"></span><a href="#"><span class="countdown_timer_status" id="countdown_timer_status"></span></a>
                </div>
                <div class="page-availability-title-right" style="float: right">
                    <div class="page-availability-report-host" id="devices-summary" style="display:none">
                        <span>Total hosts</span>
                        <span class="label label-success label-font-border label-border" id="devices-up"></span>
                        <span class="label label-warning label-font-border label-border" id="devices-warn"></span>
                        <span class="label label-danger label-font-border label-border" id="devices-down"></span>
                    </div>
                    <div class="page-availability-report-host" id="services-summary" style="display:none">
                        <span>Total services</span>
                        <span class="label label-success label-font-border label-border" id="services-up"></span>
                        <span class="label label-warning label-font-border label-border" id="services-warn"></span>
                        <span class="label label-danger label-font-border label-border" id="services-down"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="device-list"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="service-list"></div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
@endsection

@section('scripts')
<script>
    var Countdown;
    function refreshMap() {
        group = null;
        if ($("#show_group").val()) {
            group = $("#show_group").val();
        }

        if ($("#show_items").val() == 0 || $("#show_items").val() == 2) {
            $.post( '{{ route('maps.getdevices') }}', {disabled: 0, disabled_alerts: null, group: group})
                .done(function( data ) {
                    var host_warn_count = 0;
                    var host_up_count = 0;
                    var host_down_count = 0;
                    var host_maintenance_count = 0;

                    function deviceSort(a,b) {
@if($sort == 'hostname')
                        return (data[a]["sname"] > data[b]["sname"]) ? 1 : -1;
@elseif($sort == 'status')
                        return (data[a]["status"] > data[b]["status"]) ? 1 : -1;
@else
                        // Sort not set, or unknown sort {{$sort}}
                        return 0;
@endif
                    }
                    var devhtml = '';
                    var keys = Object.keys(data).sort(deviceSort);
                    $.each( keys, function( key_idx, device_id ) {
                        var device = data[device_id];
                        var state, fullclass, compactclass;
                        if (device['status']) {
                            if (device['uptime'] && (device['uptime'] < {{$uptime_warn}})) {
                                state = 'warn';
                                fullclass = 'label-warning';
                                compactclass = 'availability-map-oldview-box-warn';
                                host_warn_count++;
                            } else {
                                state = 'up';
                                fullclass = 'label-success';
                                compactclass = 'availability-map-oldview-box-up';
                                host_up_count++;
                            }
                        } else if (device['maintenance']) {
                            state = 'alert-disabled';
                            fullclass = 'label-default';
                            compactclass = 'availability-map-oldview-box-ignored';
                            host_maintenance_count++;
                        } else {
                            state = 'down';
                            fullclass = 'label-danger';
                            compactclass = 'availability-map-oldview-box-down';
                            host_down_count++;
                        }

                        devhtml += '                <a href="' + device["url"] + '" title="' + device["sname"] + ' - ' + device["updowntime"] + '">\n';
@if($compact)
                        devhtml += '                    <div class="' + compactclass + '"></div>\n';
@else
                        devhtml += '                    <div class="device-availability ' + state + '" style="width:{{$box_size}}px;">\n';
                        devhtml += '                        <span class="availability-label label ' + fullclass + ' label-font-border">' + state + '</span>\n';
                        devhtml += '                        <span class="device-icon"><img src="' + device["icon"] + '" title="' + device["icontitle"] + '"/></span><br>\n';
                        devhtml += '                        <span class="small">' + device["sname"] + '</span>\n';
                        devhtml += '                    </div>\n';
@endif
                        devhtml += '                </a>\n';
                    });
                    $("#device-list").html(devhtml);
                    $("#devices-up").text('up: ' + host_up_count);
                    $("#devices-warn").text('warn: ' + host_warn_count);
                    $("#devices-down").text('down: ' + host_down_count);
                    $("#devices-summary").show();
                });
        } else {
            $("#device-list").html("");
            $("#devices-summary").hide();
        }
        if ($("#show_items").val() == 1 || $("#show_items").val() == 2) {
            $.post( '{{ route('maps.getservices') }}', {disabled: 0, disabled_alerts: null, device_group: group})
                .done(function( data ) {
                    var service_warn_count = 0;
                    var service_up_count = 0;
                    var service_down_count = 0;

                    function serviceSort(a,b) {
@if($sort == 'hostname')
                        return (a["device_name"] > b["device_name"]) ? 1 : -1;
@elseif($sort == 'status')
                        return (a["status"] > b["status"]) ? 1 : -1;
@else
                        // Sort not set, or unknown sort {{$sort}}
                        return 0;
@endif
                    }

                    var svchtml = '';
                    var services = data.sort(serviceSort);
                    $.each( services, function( svc_idx, service ) {
                        if (service['status'] == 0) {
                            fullclass = 'label-success';
                            compactclass = 'availability-map-oldview-box-up';
                            state = 'up';
                            service_up_count++;
                        } else if (service['status'] == 1) {
                            fullclass = 'label-warning';
                            compactclass = 'availability-map-oldview-box-warn';
                            state = 'warn';
                            service_warn_count++;
                        } else {
                            fullclass = 'label-danger';
                            compactclass = 'availability-map-oldview-box-down';
                            state = 'down';
                            service_down_count++;
                        }
                        svchtml += '                <a href="' + service["url"] + '" title="' + service["device_name"] + ' - ' + service["updowntime"] + '">\n';
@if($compact)
                        svchtml += '                    <div class="' + compactclass + '"></div>\n';
@else
                        svchtml += '                    <div class="service-availability ' + state + '" style="width:{{$box_size}}px;">\n';
                        svchtml += '                        <span class="service-name-label label ' + fullclass + ' label-font-border">' + service["type"] +'</span>\n';
                        svchtml += '                        <span class="availability-label label ' + fullclass + ' label-font-border">' + state + '</span>\n';
                        svchtml += '                        <span class="device-icon"><img src="' + service["icon"] + '" title="' + service["icontitle"] + '"/></span><br>\n';
                        svchtml += '                        <span class="small">' + service["device_name"] + '</span>\n';
                        svchtml += '                    </div>\n';
@endif
                        svchtml += '                </a>\n';
                    });
                    $("#service-list").html(svchtml);
                    $("#services-up").text('up: ' + service_up_count);
                    $("#services-warn").text('warn: ' + service_warn_count);
                    $("#services-down").text('down: ' + service_down_count);
                    $("#services-summary").show();
                });
        } else {
            $("#service-list").html("");
            $("#service-summary").hide();
        }
        Countdown.Reset();
    }

    $(document).ready(function () {
        $("#countdown_timer_status").html("<i class=\"fa fa-pause fa-fw fa-lg\"></i> Pause");
        Countdown = {
            sec: {{$page_refresh}},

            Start: function () {
                var cur = this;
                this.interval = setInterval(function () {
                    $("#countdown_timer_status").html("<i class=\"fa fa-pause fa-fw fa-lg\"></i> Pause");
                    cur.sec -= 1;
                    if (cur.sec <= 0) {
                        refreshMap();
                        cur.sec = {{$page_refresh}};
                    }
                    $("#countdown_timer").html("<i class=\"fa fa-clock-o fa-fw fa-lg\"></i> Refresh in " + cur.sec);
                }, 1000);
            },

            Pause: function () {
                clearInterval(this.interval);
                $("#countdown_timer_status").html("<i class=\"fa fa-play fa-fw fa-lg\"></i> Resume");
                $("#countdown_timer").html("<i class=\"fa fa-clock-o fa-fw fa-lg\"></i> Refresh paused");
                delete this.interval;
            },

            Resume: function () {
                if (!this.interval) this.Start();
            },

            Reset: function () {
                this.sec = {{$page_refresh}};
            },
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

        refreshMap();
    });
</script>
@endsection

