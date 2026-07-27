<x-device.overview.panel :title="__('Availability (90 days)')" icon="fa fa-check-circle">
    <div class="tw:p-4">
        <div class="tw:flex tw:items-center tw:gap-px">
            @foreach($availability['days'] as $day)
                <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false"
                     class="tw:relative tw:h-8 tw:flex-1 tw:cursor-pointer tw:rounded-sm {{ $day['color'] }}">
                    <div x-show="open" x-cloak
                         class="tw:absolute tw:bottom-full tw:left-1/2 tw:z-50 tw:mb-2 tw:min-w-56 tw:-translate-x-1/2 tw:rounded tw:border tw:border-gray-300 tw:bg-white tw:px-4 tw:py-3 tw:shadow-md">
                        <div class="tw:mb-1 tw:font-bold tw:text-gray-800">{{ $day['date'] }}</div>
                        @if($day['outages'] === null)
                            <div class="tw:text-gray-400">{{ __('No data') }}</div>
                        @elseif($day['outages'] === [])
                            <div class="tw:text-green-600">{{ __('No outage') }}</div>
                        @else
                            @foreach($day['outages'] as $outage)<div class="tw:text-red-500">{{ $outage }}</div>@endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="tw:mt-1 tw:flex tw:justify-between tw:text-sm tw:text-gray-400">
            <span>{{ __('90 days ago') }}</span>
            <strong class="{{ $availability['totalColor'] }}">{{ $availability['total'] }}% {{ __('uptime') }}</strong>
            <span>{{ __('Today') }}</span>
        </div>
    </div>
</x-device.overview.panel>
