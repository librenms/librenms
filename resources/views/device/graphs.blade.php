<div x-data="{ group: @js($group) }" x-init="window.addEventListener('filter:apply', (e) => $data.group = e.detail.filters.group?.eq);">
    <h3 x-show="group" x-cloak>
        <span class="devices-font-bold">{{ __('Device Group') }}: </span>
        <span x-text="group"></span>
    </h3>
    <div class="tw:grid tw:grid-cols-1 md:tw:grid-cols-2 lg:tw:grid-cols-3 xl:tw:grid-cols-4 tw:gap-4 tw:p-4">
        @foreach($devices as $device)
            <div class="tw:flex tw:justify-center">
                <div class="panel panel-default tw:inline-block">
                    <x-graph
                            :options="array_merge($graphTemplate, ['device' => $device->device_id])"
                            :url="route('device.graphs', ['device' => $device->device_id, 'type' => $graphTemplate['type'], 'from' => $graphTemplate['from'], 'to' => $graphTemplate['to']])"
                            class="tw:-mb-[4px]"
                    />
                </div>
            </div>
        @endforeach
    </div>
    <div class="tw:p-4">
        {{ $devices->withQueryString()->links() }}
    </div>
    @if(!$hideFilter)
        <x-filter
                :fields="$filterFields"
                :initial-filters="$filter"
                :url="request()->url()"
                class="tw:border-t tw:border-gray-200 tw:dark:border-gray-700 tw:p-4"
        ></x-filter>
    @endif
</div>
