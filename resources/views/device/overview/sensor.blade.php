<div class="overview-panel tw:mb-5">
    <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">
        <a href="{{ $sensor_link }}">
            <i class="fa fa-{{ $sensor_class->icon() }} fa-lg icon-theme" aria-hidden="true"></i>
            <strong>{{ $sensor_class->label() }}</strong>
        </a>
    </div>
    <div class="tw:flex tw:min-w-0 tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-zinc-800">
        @foreach($groupedSensors as $group => $sensors)
            @if($group !== '')
                <div class="tw:px-2 tw:py-2 tw:font-bold tw:bg-neutral-100 tw:dark:bg-dark-gray-300"><strong>{{ $group }}</strong></div>
            @endif
            @foreach($sensors as $sensor)
                <div class="tw:flex tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                    <div class="tw:w-36 tw:shrink-0 tw:whitespace-nowrap">
                        <x-popup>
                            <a href="{{ $sensor->graph_link }}">{{ $sensor->sensor_descr }}</a>
                            <x-slot name="title">{{ $sensor->device?->display . ' - ' . $sensor->sensor_descr }}</x-slot>
                            <x-slot name="body">
                                <x-graph-row loading="lazy" :vars="['id' => $sensor->sensor_id]" :type="'sensor_' . $sensor->sensor_class"></x-graph-row>
                            </x-slot>
                        </x-popup>
                    </div>
                    <div class="tw:hidden tw:sm:flex tw:min-w-0 tw:flex-1 tw:justify-end tw:overflow-hidden">
                        <x-popup>
                            <x-graph :vars="['id' => $sensor->sensor_id]" :type="'sensor_' . $sensor->sensor_class" width="100" height="24" loading="lazy"></x-graph>
                            <x-slot name="title">{{ $sensor->device?->display . ' - ' . $sensor->sensor_descr }}</x-slot>
                            <x-slot name="body">
                                <x-graph-row loading="lazy" :vars="['id' => $sensor->sensor_id]" :type="'sensor_' . $sensor->sensor_class"></x-graph-row>
                            </x-slot>
                        </x-popup>
                    </div>
                    <div class="tw:w-28 tw:shrink-0 tw:flex tw:justify-end tw:ml-auto">
                        <x-popup>
                            <a href="{{ $sensor->graph_link }}">
                                <x-label :status="$sensor->currentStatus()">{{ $sensor->formatValue() }}</x-label>
                            </a>
                            <x-slot name="title">{{ $sensor->device?->display . ' - ' . $sensor->sensor_descr }}</x-slot>
                            <x-slot name="body">
                                <x-graph-row loading="lazy" :vars="['id' => $sensor->sensor_id]" :type="'sensor_' . $sensor->sensor_class"></x-graph-row>
                            </x-slot>
                        </x-popup>
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>
</div>
