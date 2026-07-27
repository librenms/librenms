@props(['device'])

@foreach($device->printerSupplies->groupBy('supply_type') as $type => $supplies)
    <x-device.overview.panel :title="\LibreNMS\Util\StringHelpers::camelToTitle($type === 'opc' ? 'organicPhotoConductor' : $type)"
        icon="fa fa-print" :href="route('device', ['device' => $device->device_id, 'tab' => 'printer'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($supplies as $supply)
                <div class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2">
                    <span class="tw:w-36 tw:truncate">{{ $supply->supply_descr }}</span>
                    <x-graph type="toner_usage" :vars="['id' => $supply->supply_id]" width="100" height="24" popup
                             :popup-title="$device->display . ' - ' . $supply->supply_descr" />
                    <x-device.overview.percentage class="tw:ml-auto" :percent="$supply->supply_current" />
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endforeach
