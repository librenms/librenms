@props(['device'])

@php
    $storage = $device->storage->filter(fn ($drive) => $drive->isValid($device->os));
@endphp

@if($storage->isNotEmpty())
    <x-device.overview.panel :title="__('Storage')" icon="fa fa-database"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'health', 'vars' => 'metric=storage'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($storage as $drive)
                @php
                    $percent = round($drive->storage_perc);
                    $total = \LibreNMS\Util\Number::formatBi($drive->storage_size);
                    $free = \LibreNMS\Util\Number::formatBi($drive->storage_free);
                    $used = \LibreNMS\Util\Number::formatBi($drive->storage_used);
                @endphp
                <div class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2">
                    <span class="tw:w-40 tw:truncate" title="{{ $drive->storage_descr }}">{{ \Illuminate\Support\Str::limit($drive->storage_descr, 50) }}</span>
                    <x-graph type="storage_usage" :vars="['id' => $drive->storage_id]" width="100" height="24" popup
                             :popup-title="$device->display . ' - ' . $drive->storage_descr" />
                    <x-device.overview.percentage class="tw:ml-auto tw:max-w-[400px] tw:flex-1" :percent="$percent" :warning="$drive->storage_perc_warn"
                        :left_text="$used . ' / ' . $total . ' (' . $percent . '%)'" :right_text="$free" />
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
