<div x-data="{ group: @js($group) }" x-init="window.addEventListener('filter:apply', (e) => $data.group = e.detail.filters.group?.eq);">
    <h3 x-show="group" x-cloak>
        <span class="devices-font-bold">{{ __('Device Group') }}: </span>
        <span x-text="group"></span>
    </h3>
    @if(!$hideFilter)
        <x-filter
            name="devices"
            :fields="$filterFields"
            :initial="$filter"
            class="tw:border-t tw:border-gray-200 tw:dark:border-gray-700 tw:p-4"
        ></x-filter>
    @endif
    <div class="tw:grid tw:grid-cols-1 md:tw:grid-cols-2 lg:tw:grid-cols-3 xl:tw:grid-cols-4 tw:gap-4 tw:p-4">
        @foreach($devices as $device)
            <div class="tw:flex tw:justify-center">
                <div class="panel panel-default tw:inline-block">
                    <x-graph
                        :device="$device"
                        :type="$graphTemplate['type']"
                        :from="$graphTemplate['from']"
                        :to="$graphTemplate['to']"
                        :vars="$graphTemplate"
                        :url="route('device', ['device' => $device->device_id, 'tab' => 'graphs', 'type' => $graphTemplate['type'], 'from' => $graphTemplate['from'], 'to' => $graphTemplate['to']])"
                        class="tw:-mb-1"
                    />
                </div>
            </div>
        @endforeach
    </div>
    <div class="tw:p-4">
        {{ $devices->withQueryString()->links() }}
    </div>
</div>
