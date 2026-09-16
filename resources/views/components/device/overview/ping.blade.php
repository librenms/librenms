@props(['device', 'visible'])

@if($visible)
    <x-device.overview.panel :title="__('Ping Response')" icon="fas fa-area-chart"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'graphs', 'vars' => 'group=poller'])">
        <div class="tw:p-2">
            <x-graph type="device_icmp_perf" :device="$device" legend="yes" aspect="wide" :columns="['md' => 2]" class="tw:w-full" img-class="tw:w-full tw:h-auto"
                     popup :popup-title="$device->display . ' - ' . __('Ping Response')" />
        </div>
    </x-device.overview.panel>
@endif
