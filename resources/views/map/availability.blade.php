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
                    var keys = Object.keys(data).sort(deviceSort);
                    var devicelist = document.createElement("div");

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

                        // create badge
                        var devhtml = document.createElement("a");
                        devhtml.href = device["url"];
                        devhtml.title = device["sname"] + ' - ' + device["updowntime"];
                    @if($compact)
                        var devcompact = document.createElement("div");
                        devcompact.classList.add(compactclass);
                        devhtml.appendChild(devcompact);
                    @else
                        var devfull = document.createElement("div");
                        devfull.style.overflow = "hidden";
                        devfull.classList.add("device-availability", state);
                        devfull.style.width = "{{ $box_size }}px";

                        var devstatelabel = document.createElement("span");
                        devstatelabel.classList.add("availability-label", "label", fullclass, "label-font-border");
                        devstatelabel.textContent = state;
                        devfull.appendChild(devstatelabel);

                        var devicon = document.createElement("span");
                        devicon.classList.add("device-icon");
                        devicon.title = device["icontitle"];
                        devfull.appendChild(devicon);

                        var deviconimage = document.createElement("img");
                        deviconimage.src = device["icon"];
                        devicon.appendChild(deviconimage);
                        devfull.appendChild(document.createElement("br"));

                        var devname = document.createElement("span");
                        devname.classList.add("small");
                        devname.textContent = device["sname"];
                        devfull.appendChild(devname);
                        devhtml.appendChild(devfull);
                    @endif
                        devicelist.appendChild(devhtml);
                    });

                    document.getElementById("device-list").innerHTML = devicelist.innerHTML;
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

                    var services = data.sort(serviceSort);
                    var servicelist = document.createElement("div");
                    $.each( services, function( svc_idx, service ) {
                        var fullclass,compactclass,state;

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

                        // create badge
                        var svchtml = document.createElement("a");
                        svchtml.href = service["url"];
                        svchtml.title = service["device_name"] + ' - ' + service["updowntime"];
                    @if($compact)
                        var svccompact = document.createElement("div");
                        svccompact.classList.add(compactclass);
                        svchtml.appendChild(svccompact);
                    @else
                        var svcfull = document.createElement("div");
                        svcfull.style.overflow = "hidden";
                        svcfull.classList.add("service-availability", state);
                        svcfull.style.width = "{{ $box_size }}px";

                        var svctypelabel = document.createElement("span");
                        svctypelabel.classList.add("service-name-label", "label", fullclass, "label-font-border");
                        svctypelabel.textContent = service["type"];
                        svcfull.appendChild(svctypelabel);

                        var svcstatelabel = document.createElement("span");
                        svcstatelabel.classList.add("availability-label", "label", fullclass, "label-font-border");
                        svcstatelabel.textContent = state;
                        svcfull.appendChild(svcstatelabel);

                        var svcicon = document.createElement("span");
                        svcicon.classList.add("device-icon");
                        svcfull.appendChild(svcicon);

                        var svciconimage = document.createElement("img");
                        svciconimage.src = service["icon"];
                        svciconimage.title = service["icontitle"];
                        svcicon.appendChild(svciconimage);
                        svcfull.appendChild(document.createElement("br"));

                        var svcname = document.createElement("span");
                        svcname.classList.add("small");
                        svcname.textContent = service["device_name"];
                        svcfull.appendChild(svcname);
                        svchtml.appendChild(svcfull);
                    @endif
                        servicelist.appendChild(svchtml);
                    });
                    document.getElementById("service-list").innerHTML = servicelist.innerHTML;

                    $("#services-up").text('up: ' + service_up_count);
                    $("#services-warn").text('warn: ' + service_warn_count);
                    $("#services-down").text('down: ' + service_down_count);
                    $("#services-summary").show();
                });
        } else {
            $("#service-list").html("");
            $("#service-summary").hide();
        }
    }

    // initial data load
    $(document).ready(function () {
        refreshMap();
    });
</script>
<x-refresh-timer :refresh="$page_refresh" callback="refreshMap"></x-refresh-timer>
@endsection

