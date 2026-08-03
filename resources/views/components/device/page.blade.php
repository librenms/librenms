@section('title', $pagetitle)

<div class="container-fluid lnms-device-page">
    <x-panel class="lnms-device-page__header {{ $statusBorderClass }}">
        <x-slot:slot class="lnms-device-page__header-body">
        <img src="{{ url($device->logo()) }}" title="{{ $device->logo() }}"
             alt="logo"
             class="device-icon-header lnms-device-page__icon pull-left"
             style="max-height: 100px">
        <div class="pull-left lnms-device-page__identity">
            @if($parentDeviceId)
                <a href="{{ route('device', $parentDeviceId) }}" title="{{ __('device.vm_host') }}" class="lnms-device-page__vm-host"><i
                        class="fa fa-server fa-fw fa-lg"></i></a>
            @endif
            <div class="lnms-device-page__title">
                @if($device->isUnderMaintenance())
                    <span title="{{ __('device.scheduled_maintenance') }}" class="fa fa-wrench fa-fw lnms-device-page__maintenance"></span>
                @endif
                <x-device-link :device="$device"/>
                @if($typeIcon)
                    <i class="fa-solid fa-{{ $typeIcon }}" title="{{ $typeText }}"></i>
                @endif
            </div>
            @if($device->location)
                <div class="lnms-device-page__location"><a href="{{ route('devices', ['filter' => ['location_id' => ['eq' => $device->location_id]]]) }}">{{ $device->location }}</a></div>
            @endif
        </div>
        <div class="pull-right lnms-device-page__graphs">
                @if($overviewGraphs())
                    <div class="lnms-device-page__graph-row">
                        @foreach($overviewGraphs() as $graph)
                            <div class="lnms-device-page__graph-item">
                                <x-graph-popup :vars="$graph" :type="$graph['type']" :width="$graph['width']" :height="$graph['height']"
                                    :popup-title="$graph['popup_title']" :device="$device" />
                                <div class="lnms-device-page__graph-label">
                                    {{ $graph['popup_title'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
        </div>
        </x-slot:slot>
    </x-panel>

    <x-device.page-tabs :device="$device" :dropdown-links="$dropdownLinks"/>

    <div class="tab-content lnms-device-page__content">
        <div class="tab-pane active">

            {{ $slot }}

        </div>
    </div>
</div>
