@if (!empty($error))
    <div class="alert alert-danger">{{ $error }}</div>
@elseif ($sensors->isEmpty())
    <div class="alert alert-info">{{ __('No health sensors matched the current filters.') }}</div>
@else
    @if ($display_mode === 'progress-bar')
        <div class="health-sensors-widget row">
            @foreach ($sensors as $row)
                @php($sensor = $row['sensor'])
                @php($colWidth = max(1, min(12, intdiv(12, max(1, (int) ($cols ?? 3))))))
                @php($status = $row['status'] ?? 'unknown')
                @php($value = is_numeric($sensor->sensor_current) ? (float) $sensor->sensor_current : null)
                @php($min = is_numeric($row['gauge_min'] ?? null) ? (float) $row['gauge_min'] : 0.0)
                @php($max = is_numeric($row['gauge_max'] ?? null) ? (float) $row['gauge_max'] : 1.0)
                @php($range = max(0.000001, $max - $min))
                @php($pct = $value === null ? 0.0 : max(0.0, min(100.0, (($value - $min) / $range) * 100.0)))
                @php($barColor = match ($status) {
                'critical' => 'tw:bg-red-600',
                'warning' => 'tw:bg-amber-500',
                'ok' => 'tw:bg-green-600',
                default => 'tw:bg-gray-500',
            })
                <div class="col-sm-{{ $colWidth }}">
                    <div class="tw:mb-3 tw:pt-1.5">
                        <div class="tw:text-center tw:text-2xl tw:font-bold dark:tw:text-gray-500">{{ \Str::limit($sensor->sensor_descr, 42) }}</div>
                        <div class="tw:rounded tw:border tw:border-black/10 dark:tw:border-black/60 tw:bg-black/5 dark:tw:bg-[#3e444c] tw:p-3">
                            <div class="tw:flex tw:items-baseline tw:justify-between tw:gap-2">
                                <div class="tw:text-2xl tw:font-semibold tw:leading-tight">
                                    {{ $sensor->formatValue() }}
                                </div>
                                <div class="tw:text-xl tw:text-gray-500 tw:whitespace-nowrap">
                                    {{ __('Min') }}: {{ round($min, 2) }} · {{ __('Max') }}: {{ round($max, 2) }}
                                </div>
                            </div>

                            <div class="tw:mt-2 tw:h-3 tw:w-full tw:rounded tw:bg-black/10 dark:tw:bg-black/40 tw:overflow-hidden">
                                <div class="tw:h-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif ($display_mode === 'gauge')
        <div class="health-sensors-widget row">
            @foreach ($sensors as $row)
            @php($sensor = $row['sensor'])
            @php($colWidth = max(1, min(12, intdiv(12, max(1, (int) ($cols ?? 3))))))
            @php($status = $row['status'] ?? 'unknown')
            <div class="col-sm-{{ $colWidth }}">
                <div class="tw:mb-3 tw:pt-1.5">
                    <div class="tw:text-center tw:text-base tw:font-bold dark:tw:text-gray-500">
                        {{ \Str::limit($sensor->sensor_descr, 42) }}</div>
                    <div id="health-gauge-{{ $id }}-{{ $sensor->sensor_id }}" class="health-gauge-{{ $id }} tw:h-28"
                        data-value="{{ $sensor->sensor_current ?? 0 }}" data-min="{{ $row['gauge_min'] }}"
                        data-max="{{ $row['gauge_max'] }}" data-symbol="{{ $sensor->unit() }}"></div>
                </div>
            </div>
            @endforeach
        </div>
        <script type="text/javascript">
            $('.health-gauge-{{ $id }}').each(function () {
                var $el = $(this);
                new JustGage({
                    id: this.id,
                    min: parseFloat($el.data('min')),
                    max: parseFloat($el.data('max')),
                    value: parseFloat($el.data('value')),
                    symbol: $el.data('symbol') || '',
                    valueFontSize: '14px',
                    labelFontSize: '10px',
                    gaugeWidthScale: 0.6,
                });
            });
        </script>
    @elseif ($display_mode === 'graph')
        <div class="health-sensors-widget row">
            @php($colWidth = max(1, min(12, intdiv(12, max(1, (int) ($cols ?? 3))))))
            @foreach ($sensors as $row)
                @php($sensor = $row['sensor'])
                @php($graphUrl = url('graphs/id=' . $sensor->sensor_id . '/type=sensor_' . $sensor->sensor_class . '/') )
                @php($imgUrl = url('graph.php') . '?type=sensor_' . $sensor->sensor_class . '&id=' . $sensor->sensor_id . '&from=' . \App\Facades\LibrenmsConfig::get('time.day') . '&to=' . \App\Facades\LibrenmsConfig::get('time.now') . '&width=450&height=150' )

                <div class="col-sm-{{ $colWidth }}">
                    <a href="{{ $graphUrl }}" class="tw:block tw:mb-3 tw:rounded tw:border tw:border-black/10 dark:tw:border-black/60 tw:bg-black/5 dark:tw:bg-[#3e444c] tw:p-2 tw:no-underline hover:tw:no-underline">
                        <div class="tw:text-center tw:text-sm tw:font-medium tw:text-gray-700 dark:tw:text-gray-300">{{ \Str::limit($sensor->sensor_descr, 52) }}</div>
                        <div class="tw:text-center tw:text-xs tw:text-gray-500 tw:mb-1">{{ $sensor->device?->displayName() ?? __('Unknown device') }}</div>
                        <img class="tw:w-full tw:h-auto" src="{{ $imgUrl }}" alt="{{ $sensor->sensor_descr }}">
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="health-sensors-widget row">
            @php($colWidth = max(1, min(12, intdiv(12, max(1, (int) ($cols ?? 3))))))
            @foreach ($sensors as $row)
            @php($sensor = $row['sensor'])
            @php($graphUrl = url('graphs/id=' . $sensor->sensor_id . '/type=sensor_' . $sensor->sensor_class . '/') )
            @php($deviceUrl = route('device', ['device' => $sensor->device_id]))
            @php($status = $row['status'] ?? 'unknown')
        
            <div class="col-sm-{{ $colWidth }}">
                <div class="tw:mb-3 tw:rounded tw:border tw:border-black/10 dark:tw:border-black/60 tw:shadow-sm dark:tw:shadow-black/30 tw:bg-black/5 dark:tw:bg-[#3e444c] tw:border-l-4 tw:min-h-24 tw:flex tw:flex-col tw:justify-center tw:items-center tw:px-3 tw:py-2
                                @if($status === 'critical') tw:border-l-red-600
                                @elseif($status === 'warning') tw:border-l-amber-500
                                @elseif($status === 'ok') tw:border-l-green-600
                                @else tw:border-l-gray-500
                                @endif">
                    <a href="{{ $graphUrl }}" class="tw:block tw:w-full tw:no-underline hover:tw:no-underline">
                        <div class="tw:text-7xl tw:font-semibold tw:leading-tight tw:whitespace-nowrap">
                            {{ $sensor->formatValue() }}</div>
                        <div class="tw:mt-1 tw:font-medium hover:tw:underline">{{ \Str::limit($sensor->sensor_descr, 64) }}
                        </div>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
@endif
