@if (!empty($error))
    <div class="alert alert-danger">{{ $error }}</div>
@elseif ($sensors->isEmpty())
    <div class="alert alert-info">{{ __('No health sensors matched the current filters.') }}</div>
@else
    @php($colWidth = max(1, min(12, intdiv(12, max(1, (int) ($cols ?? 3))))))

    <div class="health-sensors-widget row">
        @foreach ($sensors as $row)
            @php($sensor = $row['sensor'])
            @php($status = $row['status'] ?? 'unknown')
            @php($deviceUrl = route('device', ['device' => $sensor->device_id]))
            @php($graphUrl = url('graphs/id=' . $sensor->sensor_id . '/type=sensor_' . $sensor->sensor_class . '/') )
            @php($graphType = 'sensor_' . $sensor->sensor_class)
            @php($graphVars = ['id' => $sensor->sensor_id])

            @if ($display_mode === 'progress-bar')
                @php($value = is_numeric($sensor->sensor_current) ? (float) $sensor->sensor_current : null)
                @php($min = is_numeric($row['gauge_min'] ?? null) ? (float) $row['gauge_min'] : 0.0)
                @php($max = is_numeric($row['gauge_max'] ?? null) ? (float) $row['gauge_max'] : 1.0)
                @php($range = max(0.000001, $max - $min))
                @php($pct = $value === null ? 0.0 : max(0.0, min(100.0, (($value - $min) / $range) * 100.0)))
                @php($barColor = match ($status) {
                'critical' => 'tw:bg-red-600',
                'warning' => 'tw:bg-amber-500',
                default => 'tw:bg-green-600',
            })

                                                    <div class="col-sm-{{ $colWidth }}">
                                                        <div class="tw:mb-3 tw:pt-1.5">
                                                            <div class="tw:text-center tw:text-2xl tw:font-bold dark:tw:text-gray-500">
                                                                <a href="{{ $graphUrl }}" class="tw:block tw:w-full tw:no-underline hover:tw:no-underline">{{ \Str::limit($sensor->sensor_descr, 42) }}</a>
                                                            </div>
                                                            <div class="tw:rounded tw:border tw:border-black/10 dark:tw:border-black/60 tw:bg-black/5 dark:tw:bg-[#3e444c] tw:p-3">
                                                                <div class="tw:flex tw:items-baseline tw:justify-between tw:gap-2">
                                                                    <div class="tw:text-2xl tw:font-semibold tw:leading-tight">
                                                                        <a href="{{ $graphUrl }}" class="tw:block tw:w-full tw:no-underline hover:tw:no-underline">{{ $sensor->formatValue() }}</a>
                                                                    </div>
                                                                    <div class="tw:text-xl tw-font-semibold tw-leading-tight tw:text-right"><a href="{{ $deviceUrl }}" class="tw:text-inherit tw:no-underline hover:tw:underline dark:tw:text-gray-500">{{ $sensor->device?->displayName() ?? __('Unknown device') }}</a>
                                                                    </div>
                                                                </div>
                                                                <div class="tw:mt-2 tw:h-3 tw:w-full tw:rounded tw:bg-black/10 dark:tw:bg-black/40 tw:overflow-hidden">
                                                                    <div class="tw:h-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                @elseif ($display_mode === 'gauge')
                    <div class="col-sm-{{ $colWidth }}">
                        <div class="tw:mb-3 tw:pt-1.5">
                            <div class="tw:text-center tw:text-base tw:font-bold dark:tw:text-gray-500">
                                <a href="{{ $graphUrl }}" class="tw:no-underline hover:tw:no-underline">{{ \Str::limit($sensor->sensor_descr, 42) }}</a> - <a href="{{ $deviceUrl }}" class="tw:text-inherit tw:no-underline hover:tw:underline dark:tw:text-gray-500">{{ $sensor->device?->displayName() ?? __('Unknown device') }}</a>
                            </div>
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
                @elseif ($display_mode === 'graph')
                <div class="col-sm-{{ $colWidth }}">
                    <div class="tw:mb-3 tw:rounded tw:border tw:border-black/10 dark:tw:border-black/60 tw:bg-black/5 dark:tw:bg-[#3e444c] tw:p-2">
                        <div class="tw:text-center tw:text-base tw:font-medium dark:tw:text-gray-700 dark:tw:text-gray-300">
                            <a href="{{ $graphUrl }}" class="tw:text-inherit tw:no-underline hover:tw:underline dark:tw:text-gray-500">{{ \Str::limit($sensor->sensor_descr, 52) }}</a> - <a href="{{ $deviceUrl }}" class="tw:text-inherit tw:no-underline hover:tw:underline dark:tw:text-gray-500">{{ $sensor->device?->displayName() ?? __('Unknown device') }}</a>
                        </div>
                        <x-graph
                            :type="$graphType"
                            :vars="$graphVars"
                            :from="\App\Facades\LibrenmsConfig::get('time.day')"
                            :to="\App\Facades\LibrenmsConfig::get('time.now')"
                            width="450"
                            height="150"
                            class="tw:block tw:no-underline hover:tw:no-underline"
                            img-class="tw:w-full tw:h-auto"
                        />
                    </div>
                </div>
            @else
                <div class="col-sm-{{ $colWidth }}">
                    <div class="tw:mb-3 tw:rounded tw:border tw:border-black/10 dark:tw:border-black/60 tw:shadow-sm dark:tw:shadow-black/30 tw:bg-black/5 dark:tw:bg-[#3e444c] tw:border-l-4 tw:min-h-24 tw:flex tw:flex-col tw:justify-center tw:items-center tw:px-3 tw:py-2
                        @if($status === 'critical') tw:border-l-red-600
                        @elseif($status === 'warning') tw:border-l-amber-500
                        @else tw:border-l-green-600
                        @endif">
                        <a href="{{ $graphUrl }}" class="tw:block tw:w-full tw:no-underline hover:tw:no-underline">
                            <div class="tw:text-7xl tw:font-semibold tw:leading-tight tw:whitespace-nowrap">
                                {{ $sensor->formatValue() }}
                            </div>
                            <div class="tw:mt-1 tw:font-medium hover:tw:underline">
                                {{ \Str::limit($sensor->sensor_descr, 64) }}
                            </div>
                        </a>
                        <div class="tw:mt-1.5 tw:w-full tw:text-center tw:text-sm tw:text-gray-500">
                            <a href="{{ $deviceUrl }}" class="tw:text-inherit tw:no-underline hover:tw:underline">
                                {{ $sensor->device?->displayName() ?? __('Unknown device') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    @if ($display_mode === 'gauge')
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
