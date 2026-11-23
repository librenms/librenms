@props(['src', 'range', 'options' => new App\Data\Graphing\GraphViewOptions, 'view' => ['compare' => null, 'trend' => false, 'percentile_lines' => true]])

@pushOnce('before-app', 'graph-components')
    @vite('resources/js/graphs.js')
@endPushOnce

{{--
    range: ['last' => seconds] up to now, or ['from' => time, 'to' => time].
    options: App\Data\Graphing\GraphViewOptions.
    view: compare with an earlier time (period, day or week, null for none), trend lines and percentile lines.
    Use it with $el.timeSeriesGraph: setRange(range), refresh(), zoom(steps), pan(steps), reset(), setOptions(options),
    image() for a PNG Blob and csv() for the data shown.
    Dispatches graph-range-changed with what is shown, graph-state-changed with {loading, refreshing, zoomed, features},
    graph-resized with {width, height} once dragged, nulls for the automatic size, and graph-columns-changed with {columns, hidden_percentiles}.
    features is what the graph draws by itself, null until loaded: {mirror: has inverted series,
    stacking: all, none or some stacked, null for one series, scale: fit, zero or limits, percentileLines: draws percentile lines}.
--}}
@php
    $columnLabels = collect(App\Data\Graphing\GraphViewOptions::COLUMNS)->mapWithKeys(fn (string $column) => [$column => __("graph.legend.$column")]);
    $chart = [
        'src' => $src,
        'range' => $range,
        'options' => [...get_object_vars($options), ...$view],
        'columns' => App\Data\Graphing\GraphViewOptions::COLUMNS,
        'columnLabels' => $columnLabels,
        // the header of a percentile column of the graph, such as 25th
        'percentileLabel' => __('graph.legend.percentile', ['percentile' => '%p']),
        'minWidth' => App\Data\Graphing\GraphViewOptions::MIN_WIDTH,
        'minHeight' => App\Data\Graphing\GraphViewOptions::MIN_HEIGHT,
        'maxHeight' => App\Data\Graphing\GraphViewOptions::MAX_HEIGHT,
    ];
    $sortButton = 'tw:inline-flex tw:items-center tw:gap-1 tw:p-0 tw:border-0 tw:bg-transparent tw:font-semibold tw:text-inherit tw:cursor-pointer tw:whitespace-nowrap';
    $handle = 'tw:absolute tw:bottom-0 tw:z-10 tw:w-4 tw:h-4 tw:touch-none tw:opacity-30 tw:group-hover:opacity-100 tw:transition-opacity
               tw:after:absolute tw:after:bottom-0.5 tw:after:w-2.5 tw:after:h-2.5 tw:after:border-b-2 tw:after:border-gray-500 tw:dark:after:border-gray-400';
@endphp
<div x-data="timeSeriesGraph(@js($chart))"
     x-effect="$dispatch('graph-state-changed', { loading, refreshing, zoomed, features })" {{ $attributes }}>
    <div x-show="caption" x-cloak class="tw:sm:hidden tw:mb-1 tw:text-sm tw:font-semibold tw:truncate" x-text="caption"></div>

    {{-- a dragged size replaces the automatic one --}}
    <div x-ref="box" :style="boxStyle()"
         @if ($options->height !== null) style="width: {{ round(($options->width ?? 1) * 100, 1) }}%; height: {{ $options->height }}px; min-height: 0; max-height: none; aspect-ratio: auto" @endif
         class="tw:group tw:relative tw:mx-auto tw:w-full tw:aspect-[3/1] tw:min-h-[240px] tw:max-h-[60vh] tw:text-gray-700 tw:dark:text-gray-300">
        <div x-ref="plot" class="tw:absolute tw:inset-0" :class="error && 'tw:invisible'"></div>

        <div x-show="loading" class="tw:absolute tw:inset-0 tw:flex tw:items-center tw:justify-center">
            <i class="fa-solid fa-spinner fa-spin fa-2x" aria-hidden="true"></i>
            <span class="tw:sr-only">{{ __('graph.loading') }}</span>
        </div>

        <div x-show="error" x-cloak x-text="error" role="alert"
             class="tw:absolute tw:inset-0 tw:flex tw:items-center tw:justify-center tw:text-lg tw:text-red-600 tw:dark:text-red-400"></div>

        <div x-show="tooltip" x-cloak
             class="tw:absolute tw:z-10 tw:pointer-events-none tw:px-3 tw:py-2 tw:rounded tw:shadow-lg tw:text-sm tw:whitespace-nowrap tw:bg-white tw:dark:bg-dark-gray-400 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100"
             :style="tooltip?.style">
            <div class="tw:font-semibold tw:mb-1" x-text="tooltip?.time"></div>
            <template x-for="row in tooltip?.rows ?? []" :key="row.key">
                <div class="tw:flex tw:items-center tw:gap-2">
                    <span class="tw:inline-block tw:w-3 tw:h-3 tw:rounded-sm tw:shrink-0" :style="{ backgroundColor: row.color }"></span>
                    <span class="tw:grow tw:min-w-0 tw:max-w-64 tw:truncate" x-text="row.label"></span>
                    <span class="tw:font-mono tw:tabular-nums tw:pl-2 tw:shrink-0" x-text="row.value"></span>
                    <span x-show="row.previous !== null" class="tw:font-mono tw:tabular-nums tw:shrink-0 tw:text-gray-500 tw:dark:text-gray-400"
                          :title="@js(__('graph.previous'))" x-text="`(${row.previous})`"></span>
                    <span x-show="row.trend !== null" class="tw:font-mono tw:tabular-nums tw:shrink-0 tw:text-gray-500 tw:dark:text-gray-400"
                          :title="@js(__('graph.trend'))" x-text="`~${row.trend}`"></span>
                </div>
            </template>
            <div x-show="tooltip?.more" class="tw:text-gray-500 tw:dark:text-gray-400">+<span x-text="tooltip?.more"></span> {{ __('graph.more') }}</div>
        </div>

        <div class="{{ $handle }} tw:right-0 tw:cursor-nwse-resize tw:after:right-0.5 tw:after:border-r-2"
             title="{{ __('graph.resize') }}" x-on:pointerdown="startResize($event, 1)" x-on:dblclick="autoSize()"></div>
        <div class="{{ $handle }} tw:left-0 tw:cursor-nesw-resize tw:after:left-0.5 tw:after:border-l-2"
             title="{{ __('graph.resize') }}" x-on:pointerdown="startResize($event, -1)" x-on:dblclick="autoSize()"></div>
    </div>

    <div class="tw:overflow-x-auto tw:mt-2" x-show="legend.length" x-cloak>
        <table class="tw:mx-auto tw:text-xs tw:sm:text-sm">
            <thead>
                <tr class="tw:text-gray-500 tw:dark:text-gray-400">
                    <th class="tw:text-left tw:font-semibold" :aria-sort="sort?.column === 'label' ? (sort.descending ? 'descending' : 'ascending') : 'none'">
                        <button type="button" class="{{ $sortButton }}" x-on:click="sortBy('label')">
                            {{ __('graph.legend.series') }} <i class="fa-solid tw:text-xs" :class="sortIcon('label')" aria-hidden="true"></i>
                        </button>
                    </th>
                    <template x-for="column in shownColumns()" :key="column">
                        <th class="tw:text-right tw:font-semibold tw:px-1.5 tw:sm:px-3"
                            :aria-sort="sort?.column === column ? (sort.descending ? 'descending' : 'ascending') : 'none'">
                            <button type="button" class="{{ $sortButton }} tw:ml-auto" x-on:click="sortBy(column)">
                                <span x-text="columnLabel(column)"></span> <i class="fa-solid tw:text-xs" :class="sortIcon(column)" aria-hidden="true"></i>
                            </button>
                        </th>
                    </template>
                    <th class="tw:w-6 tw:pl-1">
                        {{-- a popover is not clipped by the scrolling legend --}}
                        <button type="button" x-ref="columnsButton" popovertarget="graph-legend-columns-{{ $attributes->get('id', 'graph') }}"
                                title="{{ __('graph.options.columns') }}" aria-label="{{ __('graph.options.columns') }}"
                                class="tw:inline-flex tw:items-center tw:justify-center tw:w-6 tw:h-6 tw:p-0 tw:rounded tw:border-0 tw:bg-transparent tw:text-gray-500 tw:dark:text-gray-400 tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300">
                            <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                        </button>
                        <div popover id="graph-legend-columns-{{ $attributes->get('id', 'graph') }}" x-ref="columnsMenu" role="menu"
                             x-on:beforetoggle="$event.newState === 'open' && placeColumnsMenu()" x-on:scroll.window="closeColumnsMenu()"
                             :style="columnsMenuStyle"
                             class="tw:m-0 tw:inset-auto tw:px-0 tw:py-1 tw:rounded-md tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:bg-white tw:dark:bg-dark-gray-400 tw:shadow-lg tw:text-left tw:font-normal tw:text-sm tw:text-gray-700 tw:dark:text-gray-200">
                            <div class="tw:px-3 tw:pt-1 tw:pb-1.5 tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('graph.options.columns') }}</div>
                            <template x-for="column in menuColumns()" :key="column">
                                <button type="button" role="menuitemcheckbox" :aria-checked="isColumnShown(column)" x-on:click="toggleColumn(column)"
                                        class="tw:flex tw:w-full tw:items-center tw:gap-2 tw:px-3 tw:py-1.5 tw:border-0 tw:bg-transparent tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300 tw:whitespace-nowrap">
                                    <i class="fa-solid fa-check tw:w-4" :class="! isColumnShown(column) && 'tw:invisible'" aria-hidden="true"></i>
                                    <span x-text="columnLabel(column)"></span>
                                </button>
                            </template>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template x-for="item in sortedLegend()" :key="item.key">
                    <tr :class="{
                            'tw:cursor-pointer tw:select-none tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300': item.toggleable,
                            'tw:opacity-40': hidden.includes(item.key),
                        }"
                        x-on:click="toggle(item.key, $event)">
                        <td class="tw:pr-2 tw:sm:pr-6 tw:py-1.5 tw:sm:py-0.5">
                            <template x-if="item.toggleable">
                                <button type="button" :aria-pressed="! hidden.includes(item.key)"
                                        class="tw:flex tw:items-center tw:gap-2 tw:p-0 tw:border-0 tw:bg-transparent tw:text-inherit tw:text-left tw:cursor-pointer">
                                    <span class="tw:inline-block tw:w-3 tw:h-3 tw:rounded-sm tw:shrink-0" :style="{ backgroundColor: item.color }"></span>
                                    <span class="tw:max-w-[30vw] tw:sm:max-w-none tw:truncate" x-text="item.label"></span>
                                </button>
                            </template>
                            <template x-if="! item.toggleable">
                                <span class="tw:flex tw:items-center tw:gap-2">
                                    <span class="tw:inline-block tw:w-3 tw:h-3 tw:rounded-sm tw:shrink-0" :style="{ backgroundColor: item.color }"></span>
                                    <span class="tw:max-w-[30vw] tw:sm:max-w-none tw:truncate" x-text="item.label"></span>
                                </span>
                            </template>
                        </td>
                        <template x-for="column in shownColumns()" :key="column">
                            <td class="tw:text-right tw:font-mono tw:tabular-nums tw:whitespace-nowrap tw:px-1.5 tw:sm:px-3" x-text="item[column]"></td>
                        </template>
                    </tr>
                </template>
            </tbody>
        </table>
        <div class="tw:flex tw:flex-wrap tw:justify-center tw:gap-x-6 tw:gap-y-1 tw:mt-1 tw:text-xs tw:sm:text-sm" x-show="thresholds.length">
            <template x-for="(threshold, index) in thresholds" :key="index">
                <button type="button" :aria-pressed="thresholdShown(index)" x-on:click="toggleThreshold(index)"
                        class="tw:flex tw:items-center tw:gap-2 tw:p-0 tw:border-0 tw:bg-transparent tw:text-inherit tw:cursor-pointer tw:select-none"
                        :class="! thresholdShown(index) && 'tw:opacity-40'">
                    <span class="tw:inline-block tw:w-4 tw:border-t-2 tw:border-dashed" :style="{ borderColor: threshold.color }"></span>
                    <span x-text="threshold.label"></span>
                </button>
            </template>
        </div>
    </div>

</div>

@pushOnce('scripts')
    @include('components.time-series-graph-script')
@endPushOnce
