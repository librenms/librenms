<div class="table-responsive">
    <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th>{{ __('Index') }}</th>
                <th>{{ __('Description') }}</th>
                <th></th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Errors') }}</th>
                <th>{{ __('Load') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($data['items'] as $item)
            <tr>
                <td>{{ $item['device']->hrDeviceIndex }}</td>
                <td>
                    @if($item['device']->hrDeviceType === 'hrDeviceProcessor' && $item['processor'])
                        <a href="{{ route('device', ['device' => $device, 'tab' => 'health', 'vars' => 'metric=processor']) }}" class="tw:font-semibold">
                            {{ $item['device']->hrDeviceDescr }}
                        </a>
                    @elseif($item['device']->hrDeviceType === 'hrDeviceNetwork' && $item['port'])
                        <x-port-link :port="$item['port']" :text="$item['interface_text']" />
                    @else
                        {{ $item['device']->hrDeviceDescr }}
                    @endif
                </td>
                <td>
                    @if($item['device']->hrDeviceType === 'hrDeviceProcessor' && $item['processor'])
                        <x-graph
                            :device="$device"
                            type="processor_usage"
                            :id="$item['processor']->processor_id"
                            :height="20"
                            :width="100"
                        />
                    @elseif($item['device']->hrDeviceType === 'hrDeviceNetwork' && $item['port'])
                        <x-graph
                            :device="$device"
                            type="port_bits"
                            :id="$item['port']->port_id"
                            :height="20"
                            :width="100"
                        />
                    @endif
                </td>
                <td>{{ $item['device']->hrDeviceType }}</td>
                <td>{{ $item['device']->hrDeviceStatus }}</td>
                <td>{{ $item['device']->hrDeviceErrors }}</td>
                <td>{{ $item['device']->hrProcessorLoad !== null ? $item['device']->hrProcessorLoad . '%' : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center tw:p-5">
                    <em>{{ __('No Host Resources items found for this device.') }}</em>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
