@props(['device'])

@if($device->mempools->isNotEmpty())
    <x-device.overview.panel :title="__('Memory')" icon="fas fa-memory"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'health', 'vars' => 'metric=mempool'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            <div class="tw:p-2">
                <x-graph type="device_mempool" :device="$device" aspect="wide" class="tw:w-full" img-class="tw:w-full tw:h-auto"
                         popup :popup-title="$device->display . ' - ' . __('Memory Usage')" />
            </div>
            @foreach($device->mempools as $mempool)
                <div class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2">
                    <span class="tw:w-36 tw:truncate">{{ $mempool->mempool_descr }}</span>
                    <x-graph type="mempool_usage" :vars="['id' => $mempool->mempool_id]" width="100" height="24" popup
                             :popup-title="$device->display . ' - ' . $mempool->mempool_descr" />
                    <x-device.overview.percentage class="tw:ml-auto" :percent="$mempool->mempool_perc" :warning="$mempool->mempool_perc_warn"
                        :label="\LibreNMS\Util\Number::formatBi($mempool->mempool_used) . ' / ' . \LibreNMS\Util\Number::formatBi($mempool->mempool_total) . ' (' . round($mempool->mempool_perc) . '%)'" />
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
