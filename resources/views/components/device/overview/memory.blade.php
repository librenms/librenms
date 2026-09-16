@if($mempools !== [])
    <x-device.overview.panel :title="__('Memory')" icon="fas fa-memory"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'health', 'vars' => 'metric=mempool'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            <div class="tw:p-2">
                <x-graph type="device_mempool" :device="$device" aspect="wide" :columns="['md' => 2]" class="tw:w-full" img-class="tw:w-full tw:h-auto"
                         popup :popup-title="$device->display . ' - ' . __('Memory Usage')" />
            </div>
            @foreach($mempools as $data)
                <div class="tw:flex tw:min-w-0 tw:items-center tw:gap-3 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                    <span class="tw:min-w-0 tw:flex-1 tw:truncate">{{ $data['mempool']->mempool_descr }}</span>
                    <div class="tw:hidden tw:w-20 tw:shrink-0 tw:justify-end tw:lg:flex">
                        <x-graph type="mempool_usage" :vars="['id' => $data['mempool']->mempool_id]" width="80" height="20" popup
                                 :popup-title="$device->display . ' - ' . $data['mempool']->mempool_descr" />
                    </div>
                    <x-device.overview.percentage class="tw:w-full tw:min-w-0 tw:max-w-100 tw:flex-1" :percent="$data['percent']" :warning="$data['mempool']->mempool_perc_warn ?: null"
                        :left_text="$data['leftText']" :right_text="$data['rightText']" :shadow="$data['shadow']" graph_type="mempool_usage"
                        :graph_vars="['id' => $data['mempool']->mempool_id]" :graph_title="$device->display . ' - ' . $data['mempool']->mempool_descr" />
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
