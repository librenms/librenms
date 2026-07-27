@props(['device'])

@foreach($device->printerSupplies->groupBy('supply_type') as $type => $supplies)
    <x-device.overview.panel :title="\LibreNMS\Util\StringHelpers::camelToTitle($type === 'opc' ? 'organicPhotoConductor' : $type)"
        icon="fa fa-print" :href="route('device', ['device' => $device->device_id, 'tab' => 'printer'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($supplies as $supply)
                @php
                    $percent = round($supply->supply_current);
                    $colors = \LibreNMS\Util\Color::percentage(100 - $percent, null, '#');
                    $description = (string) $supply->supply_descr;

                    if (\Illuminate\Support\Str::endsWith($description, 'C') || \Illuminate\Support\Str::contains($description, 'cyan', true)) {
                        $colors = [...$colors, 'left' => '#55D6D3', 'right' => '#33B4B1'];
                    } elseif (\Illuminate\Support\Str::endsWith($description, 'M') || \Illuminate\Support\Str::contains($description, 'magenta', true)) {
                        $colors = [...$colors, 'left' => '#F24AC8', 'right' => '#D028A6'];
                    } elseif (\Illuminate\Support\Str::endsWith($description, 'Y') || \Illuminate\Support\Str::contains($description, ['yellow', 'giallo', 'gul'], true)) {
                        $colors = [...$colors, 'left' => '#FFF200', 'right' => '#DDD000'];
                    } elseif (\Illuminate\Support\Str::endsWith($description, 'K') || \Illuminate\Support\Str::contains($description, ['black', 'nero'], true)) {
                        $colors = [...$colors, 'left' => '#000000', 'right' => '#222222'];
                    }
                @endphp
                <div class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2">
                    <span class="tw:w-36 tw:truncate">{{ $supply->supply_descr }}</span>
                    <x-graph type="toner_usage" :vars="['id' => $supply->supply_id]" width="100" height="24" popup
                             :popup-title="$device->display . ' - ' . $supply->supply_descr" />
                    <x-device.overview.percentage class="tw:ml-auto tw:max-w-[200px] tw:flex-1" :percent="$percent" :left_text="''"
                        :right_text="$percent . '%'" :colors="$colors" />
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endforeach
