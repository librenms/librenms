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
                <td>{{ $item['hr']->hrDeviceIndex }}</td>
                <td>
                    @if($item['hr']->hrDeviceType === 'hrDeviceProcessor' && $item['processor'])
                        <a href="{{ route('device', ['device' => $device, 'tab' => 'health', 'vars' => 'metric=processor']) }}" class="tw:font-semibold">
                            {{ $item['hr']->hrDeviceDescr }}
                        </a>
                    @elseif($item['hr']->hrDeviceType === 'hrDeviceNetwork' && $item['port'])
                        <x-port-link :port="$item['port']" :text="$item['interface_text']" />
                    @else
                        {{ $item['hr']->hrDeviceDescr }}
                    @endif
                </td>
                <td>
                    @if($item['hr']->hrDeviceType === 'hrDeviceProcessor' && $item['processor'])
                        <x-graph
                            type="processor_usage"
                            :vars="['id' => $item['processor']->processor_id]"
                            :height="20"
                            :width="100"
                            popup
                            :popup-title="$device->display . ' - ' . $item['hr']->hrDeviceDescr"
                        />
                    @elseif($item['hr']->hrDeviceType === 'hrDeviceNetwork' && $item['port'])
                        <x-graph
                            :port="$item['port']"
                            type="port_bits"
                            :height="20"
                            :width="100"
                            popup
                            :popup-title="$device->display . ' - ' . ($item['interface_text'] ?? $item['hr']->hrDeviceDescr)"
                        />
                    @endif
                </td>
                <td>{{ $item['hr']->hrDeviceType }}</td>
                <td>{{ $item['hr']->hrDeviceStatus }}</td>
                <td>{{ $item['hr']->hrDeviceErrors }}</td>
                <td>{{ $item['hr']->hrProcessorLoad !== null ? $item['hr']->hrProcessorLoad . '%' : '-' }}</td>
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
