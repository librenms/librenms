@if($drives->isNotEmpty())
    <x-device.overview.panel :title="__('Storage')" icon="fa fa-database"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'health', 'vars' => 'metric=storage'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($drives as $data)
                <div class="tw:flex tw:min-w-0 tw:items-center tw:gap-3 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                    <span class="tw:min-w-0 tw:flex-1 tw:truncate" title="{{ $data['drive']->storage_descr }}">{{ $data['description'] }}</span>
                    <div class="tw:hidden tw:w-20 tw:shrink-0 tw:justify-end tw:lg:flex">
                        <x-graph type="storage_usage" :vars="['id' => $data['drive']->storage_id]" width="80" height="20" popup
                                 :popup-title="$device->display . ' - ' . $data['description']" />
                    </div>
                    <x-device.overview.percentage class="tw:w-full tw:min-w-0 tw:max-w-100 tw:flex-1" :percent="$data['percent']" :warning="$data['drive']->storage_perc_warn"
                        :left_text="$data['used'] . ' / ' . $data['total'] . ' (' . $data['percent'] . '%)'" :right_text="$data['free']" graph_type="storage_usage"
                        :graph_vars="['id' => $data['drive']->storage_id]" :graph_title="$device->display . ' - ' . $data['description']" />
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
