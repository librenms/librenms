@props(['device', 'ports'])
@if($device->ports_total_count)
    <x-device.overview.panel :title="__('Overall Traffic')" icon="fa fa-road">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            <div class="tw:p-2">
                <x-graph type="device_bits" :device="$device" aspect="wide" :columns="['md' => 2]" class="tw:w-full" img-class="tw:w-full tw:h-auto" popup
                         :popup-title="$device->display . ' - ' . __('Device Traffic')" />
            </div>
            <div class="tw:flex tw:flex-wrap tw:gap-3 tw:p-3">
                @foreach([
                    [__('Total'), $device->ports_total_count, 'lnms-btn-default', []],
                    [__('Up'), $device->ports_up_count, 'lnms-btn-success', ['state' => ['eq' => 'up'], 'ignore' => ['eq' => 0], 'disabled' => ['eq' => 0], 'deleted' => ['eq' => 0]]],
                    [__('Down'), $device->ports_down_count, 'lnms-btn-danger', ['state' => ['eq' => 'down'], 'ignore' => ['eq' => 0], 'disabled' => ['eq' => 0], 'deleted' => ['eq' => 0]]],
                    [__('Disabled'), $device->ports_disabled_count, 'lnms-btn-primary', ['disabled' => ['eq' => 1]]],
                ] as [$label, $count, $class, $filter])
                    <a class="lnms-btn {{ $class }}" role="button" href="{{ route('device', ['device' => $device->device_id, 'tab' => 'ports', 'filter' => $filter]) }}">
                        {{ $label }}: <span class="lnms-btn-badge">{{ $count }}</span>
                    </a>
                @endforeach
            </div>
            <div class="tw:flex tw:flex-wrap tw:gap-x-1 tw:px-2 tw:py-1 tw:bg-neutral-100 tw:dark:bg-dark-gray-200">
                @foreach($ports as $port)
                    <x-port-link :port="$port">{{ strtolower($port->getShortLabel()) }}</x-port-link>@if(! $loop->last),@endif
                @endforeach
            </div>
        </div>
    </x-device.overview.panel>
@endif
