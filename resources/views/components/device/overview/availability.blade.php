<x-device.overview.panel :title="__('Availability (90 days)')" icon="fa fa-check-circle">
    <div class="tw:p-3">
        <div class="tw:flex tw:items-center tw:gap-px">
            @foreach($availability['days'] as $day)
                <div x-data="{ open: false, x: 0, y: 0, place() { const rect = this.$el.getBoundingClientRect(); this.x = rect.left + rect.width / 2; this.y = rect.top; this.$nextTick(() => { const width = this.$refs.tip?.offsetWidth || 0; const padding = 8; this.x = Math.max(padding + width / 2, Math.min(window.innerWidth - padding - width / 2, this.x)); }); } }"
                     @mouseenter="open = true; place()" @mouseleave="open = false" @scroll.window="open && place()" @resize.window="open && place()"
                     class="tw:relative tw:h-10 tw:flex-1 tw:cursor-pointer tw:rounded-sm {{ $day['color'] }}">
                    <div x-ref="tip" x-show="open" x-cloak :style="`left:${x}px; top:${y - 8}px; transform:translate(-50%, -100%);`"
                         class="tw:pointer-events-none tw:fixed tw:z-9999 tw:min-w-70 tw:whitespace-nowrap tw:rounded tw:border tw:border-gray-300 tw:bg-white tw:px-4 tw:py-3 tw:shadow-md">
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
