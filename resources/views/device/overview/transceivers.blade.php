<div class="overview-panel tw:mb-5">
    <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">
        <x-icons.transceiver></x-icons.transceiver>
        <strong><a href="{{ $transceivers_link }}">{{ __('port.tabs.transceivers') }}</a></strong>
    </div>
    <div class="tw:flex tw:min-w-0 tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-zinc-800">
        @foreach($transceivers as $transceiver)
            <div>
                <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">
                    @if($transceiver->port)
                        <x-port-link :port="$transceiver->port" :vars="['view' => 'transceiver']"></x-port-link>
                    @endif
                    <x-icons.transceiver></x-icons.transceiver> {{ $transceiver->vendor }} {{ $transceiver->type }}
                </div>
                <div class="tw:flex tw:min-w-0 tw:flex-col tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
                    @foreach($sensors as $sensor)
                        @if($sensor->entPhysicalIndex !== null && $sensor->entPhysicalIndex == $transceiver->entity_physical_index && $filterSensors($sensor))
                            <div class="tw:flex tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                                <div class="tw:w-36 tw:shrink-0 tw:whitespace-nowrap">{{ $sensor->sensor_descr }}</div>
                                <div class="tw:hidden tw:sm:flex tw:min-w-0 tw:flex-1 tw:justify-end tw:overflow-hidden">
                                    <x-graph :vars="['id' => $sensor->sensor_id]" :type="'sensor_' . $sensor->sensor_class" width="100" height="24" :popup-title="DeviceCache::getPrimary()->displayName() . ' - ' . $sensor->sensor_descr" loading="lazy"></x-graph>
                                </div>
                                <div class="tw:w-28 tw:shrink-0 tw:flex tw:justify-end tw:ml-auto">
                                    <x-label :status="$sensor->currentStatus()">{{ $sensor->formatValue() }}</x-label>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
