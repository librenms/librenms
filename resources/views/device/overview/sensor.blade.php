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
                <x-device.overview.sensor-row :sensor="$sensor"></x-device.overview.sensor-row>
            @endforeach
        @endforeach
    </div>
</div>
