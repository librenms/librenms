@if (!empty($error))
    <div class="alert alert-danger">{{ $error }}</div>
@elseif ($sensors->isEmpty())
    <div class="alert alert-info">{{ __('No health sensors matched the current filters.') }}</div>
@else
    @if ($display_mode === 'number')
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
                            <div class="tw:text-7xl tw:font-semibold tw:leading-tight tw:whitespace-nowrap">{{ $sensor->formatValue() }}</div>
                            <div class="tw:mt-1 tw:font-medium hover:tw:underline">{{ \Str::limit($sensor->sensor_descr, 64) }}</div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="health-sensors-widget row">
            @foreach ($sensors as $row)
                @php($sensor = $row['sensor'])
                @php($colWidth = max(1, min(12, intdiv(12, max(1, (int) ($cols ?? 3))))))
                @php($status = $row['status'] ?? 'unknown')
                <div class="col-sm-{{ $colWidth }}">
                    <div class="tw:mb-3 tw:pt-1.5">
                        <div class="tw:text-center tw:text-base tw:font-bold dark:tw:text-gray-500">{{ \Str::limit($sensor->sensor_descr, 42) }}</div>
                        <div
                            id="health-gauge-{{ $id }}-{{ $sensor->sensor_id }}"
                            class="health-gauge-{{ $id }} tw:h-28"
                            data-value="{{ $sensor->sensor_current ?? 0 }}"
                            data-min="{{ $row['gauge_min'] }}"
                            data-max="{{ $row['gauge_max'] }}"
                            data-symbol="{{ $sensor->unit() }}"
                        ></div>
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
    @endif
@endif
