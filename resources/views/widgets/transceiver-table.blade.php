@php
    // Keep the headers and empty-state colspan valid even when no lane list
    // was supplied, for example during an upgrade or an empty response.
    $laneNumbers = is_array($laneNumbers ?? null) && $laneNumbers !== []
        ? $laneNumbers : [0, 1, 2, 3];
@endphp
<div style="height: 100%; overflow: auto;">
    @if($notice)
        <p class="text-warning">{{ $notice }}</p>
    @else
        <table class="table table-condensed table-striped" style="white-space: nowrap;">
            <thead>
                <tr>
                    <th scope="col">{{ __('widgets.transceiver-table.device') }}</th>
                    <th scope="col">{{ __('widgets.transceiver-table.port') }}</th>
                    <th scope="col">{{ __('widgets.transceiver-table.port_description') }}</th>
                    @foreach($laneNumbers as $lane)
                        <th scope="col">{{ __('widgets.transceiver-table.rx_power', ['lane' => $lane]) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['device'] }}</td>
                        <td><a href="{{ $row['port_url'] }}">{{ $row['port'] }}</a></td>
                        <td>{{ $row['description'] !== '' ? $row['description'] : '—' }}</td>
                        @foreach($laneNumbers as $lane)
                            @php($reading = $row['readings'][$lane] ?? null)
                            <td>
                                @if($reading)
                                    <span title="{{ $reading['tooltip'] }}"
                                          aria-label="{{ __('widgets.transceiver-table.reading_label', ['lane' => $lane, 'value' => $reading['value'], 'status' => $reading['status']]) }}"
                                          style="display: inline-block; padding: 3px 7px; border-radius: 3px; font-weight: bold; background-color: {{ $reading['background'] }}; color: {{ $reading['foreground'] }};">
                                        {{ $reading['value'] }}
                                    </span>
                                @else
                                    <span class="text-muted" title="{{ __('widgets.transceiver-table.no_lane') }}">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ 3 + count($laneNumbers) }}">{{ __('widgets.transceiver-table.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <small class="text-muted">{{ __('widgets.transceiver-table.legend') }}</small>
    @endif
</div>
