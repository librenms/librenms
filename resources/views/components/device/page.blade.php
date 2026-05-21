@section('title', $pagetitle)

<div class="container-fluid">
    <x-panel class="tw:rounded-2xl! tw:border tw:border-gray-300 tw:bg-white tw:shadow-sm tw:dark:border-dark-gray-200 tw:dark:bg-dark-gray-40 tw:mb-0!">
        <x-slot:slot class="tw:pl-5 tw:pr-5">
        <img src="{{ url($device->logo()) }}" title="{{ $device->logo() }}"
             alt="logo"
             class="device-icon-header pull-left tw:dark:bg-gray-50 tw:dark:rounded-lg tw:dark:p-2 tw:ml-2 tw:mt-2 tw:mb-2"
             style="max-height: 100px">
        <div class="pull-left" style="margin-top: 5px;">
            @if($parentDeviceId)
                <a href="{{ route('device', $parentDeviceId) }}" title="{{ __('device.vm_host') }}"><i
                        class="fa fa-server fa-fw fa-lg"></i></a>
            @endif
            @if($device->isUnderMaintenance())
                <span title="{{ __('device.scheduled_maintenance') }}" class="fa fa-wrench fa-fw fa-lg"></span>
            @endif
            <div style="font-size: 20px;">
                <x-device-link :device="$device"/>
                @if($typeIcon)
                    <i class="fa-solid fa-{{ $typeIcon }}" title="{{ $typeText }}"></i>
                @endif
            </div>
            <div class="tw:mt-4"><a href="{{ url('/devices/location=' . urlencode((string) $device->location)) }}"><i class="fa fa-location-dot fa-fw fa-lg"></i> {{ $device->location }}</a> {{ $device->ports_count }} ports, {{ $device->sensors_count }} sensors, {{ $device->wireless_sensors_count }} wireless sensors</div>
        </div>
        <div class="pull-right tw:mt-2 tw:mr-2">
            <x-device.page-links :device="$device" :dropdown-links="$dropdownLinks"/>
            <br style="clear: both;"/>
        </div>
        </x-slot:slot>
    </x-panel>

    @if($overviewGraphs())
        <div class="tw:flex tw:flex-row tw:flex-wrap tw:items-end tw:justify-start tw:gap-1 tw:mt-4 tw:mb-2 tw:mx-6 tw:px-6 tw:pt-3 tw:pb-5 tw:rounded-2xl tw:border tw:border-gray-300 tw:bg-white tw:shadow-sm tw:dark:border-dark-gray-200 tw:dark:bg-dark-gray-400">
            @foreach($overviewGraphs() as $graph)
                <div class="tw:flex tw:flex-col tw:items-center tw:text-center tw:shrink-0">
                    <x-graph-popup :vars="$graph"
                                   :type="$graph['type']"
                                   :width="$graph['width']"
                                   :height="$graph['height']"
                                   :popup-title="$graph['popup_title']"
                                   :device="$device"/>
                    <div class="tw:mt-1 tw:font-semibold tw:text-gray-700 tw:dark:text-dark-white-300">
                        {{ $graph['popup_title'] }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <x-device.page-tabs :device="$device" :dropdown-links="$dropdownLinks"/>

    <div class="tab-content tw:mt-4">
        <div class="tab-pane active">

            {{ $slot }}

        </div>
    </div>
</div>
