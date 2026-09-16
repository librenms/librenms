@foreach($supplyGroups as $type => $supplies)
    <x-device.overview.panel :title="\LibreNMS\Util\StringHelpers::camelToTitle($type === 'opc' ? 'organicPhotoConductor' : $type)"
        icon="fa fa-print" :href="route('device', ['device' => $device->device_id, 'tab' => 'printer'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($supplies as $data)
                <div class="tw:flex tw:min-w-0 tw:items-center tw:gap-3 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                    <span class="tw:min-w-0 tw:flex-1 tw:truncate">{{ $data['supply']->supply_descr }}</span>
                    <div class="tw:hidden tw:w-20 tw:shrink-0 tw:justify-end tw:lg:flex">
                        <x-graph type="toner_usage" :vars="['id' => $data['supply']->supply_id]" width="80" height="20" popup
                                 :popup-title="$device->display . ' - ' . $data['supply']->supply_descr" />
                    </div>
                    <x-device.overview.percentage class="tw:w-full tw:min-w-0 tw:max-w-50 tw:flex-1" :percent="$data['percent']" :left_text="''"
                        :right_text="$data['percent'] . '%'" :colors="$data['colors']" graph_type="toner_usage"
                        :graph_vars="['id' => $data['supply']->supply_id]" :graph_title="$device->display . ' - ' . $data['supply']->supply_descr" />
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endforeach
