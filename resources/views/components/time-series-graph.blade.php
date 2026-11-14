@props(['src', 'range', 'height' => 300])

{{--
    A graph drawn in the browser from a graph data url.
    range is ['last' => seconds] for a period up to now or ['from' => time, 'to' => time].
--}}
@php
    $button = 'tw:h-8 tw:min-w-8 tw:px-2 tw:inline-flex tw:items-center tw:justify-center tw:gap-1 tw:rounded tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:text-gray-700 tw:dark:text-gray-200 tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300 tw:disabled:opacity-40 tw:disabled:pointer-events-none';
    $kbd = 'tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs';

    // the help by section: what can be done and the ways to do it, each way the keys to combine
    $help = [
        __('graphs.help.time_range') => [
            [__('graphs.help.zoom_range'), [[__('graphs.help.drag')]]],
            [__('graphs.help.zoom'), [['+'], ['-'], ['Ctrl', __('graphs.help.scroll')]]],
            [__('graphs.help.pan'), [['←'], ['→'], ['Shift', __('graphs.help.drag')], ['Shift', __('graphs.help.scroll')]]],
            [__('graphs.help.reset'), [['R'], [__('graphs.help.double_click')]]],
            [__('graphs.help.values'), [[__('graphs.help.hover')]]],
        ],
        __('graphs.help.series') => [
            [__('graphs.help.only'), [[__('graphs.help.click')]]],
            [__('graphs.help.toggle'), [['Ctrl', __('graphs.help.click')]]],
        ],
        __('graphs.help.touch') => [
            [__('graphs.help.pan'), [[__('graphs.help.drag')]]],
            [__('graphs.help.zoom'), [[__('graphs.help.pinch')]]],
            [__('graphs.help.values'), [[__('graphs.help.tap')]]],
            [__('graphs.help.reset'), [[__('graphs.help.double_tap')]]],
        ],
        __('graphs.help.general') => [
            [__('graphs.help.open'), [['?']]],
            [__('graphs.help.close'), [['Esc']]],
        ],
    ];
@endphp
<div x-data="timeSeriesGraph(@js($src), @js($range))" x-on:keydown.window="keydown($event)" {{ $attributes }}>
    <div class="tw:flex tw:items-center tw:justify-end tw:gap-1 tw:mb-1 tw:text-sm">
        <span class="tw:sm:hidden tw:mr-auto tw:font-semibold tw:truncate" x-text="caption"></span>
        <i x-show="refreshing && ! loading" x-cloak class="fa-solid fa-spinner fa-spin tw:mr-2 tw:text-gray-500 tw:dark:text-gray-400" aria-hidden="true"></i>
        <button type="button" class="{{ $button }}" x-on:click="pan(-1)" :disabled="loading" title="{{ __('graphs.earlier') }}" aria-label="{{ __('graphs.earlier') }}">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }}" x-on:click="pan(1)" :disabled="loading" title="{{ __('graphs.later') }}" aria-label="{{ __('graphs.later') }}">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }}" x-on:click="reset" :disabled="! zoomed" title="{{ __('graphs.reset_title') }}">
            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i> {{ __('graphs.reset') }}
        </button>
        <button type="button" x-on:click="showHelp = true" title="{{ __('graphs.help.title') }}" aria-label="{{ __('graphs.help.title') }}"
                class="tw:h-8 tw:w-8 tw:inline-flex tw:items-center tw:justify-center tw:rounded-full tw:text-gray-500 tw:dark:text-gray-400 tw:hover:text-gray-800 tw:dark:hover:text-gray-100">
            <i class="fa-regular fa-circle-question fa-xl" aria-hidden="true"></i>
        </button>
    </div>

    <div class="tw:relative tw:text-gray-700 tw:dark:text-gray-300" style="height: {{ (int) $height }}px">
        <div x-ref="plot" class="tw:absolute tw:inset-0" :class="error && 'tw:invisible'"></div>

        <div x-show="loading" class="tw:absolute tw:inset-0 tw:flex tw:items-center tw:justify-center">
            <i class="fa-solid fa-spinner fa-spin fa-2x" aria-hidden="true"></i>
            <span class="tw:sr-only">{{ __('graphs.loading') }}</span>
        </div>

        <div x-show="error" x-cloak x-text="error" role="alert"
             class="tw:absolute tw:inset-0 tw:flex tw:items-center tw:justify-center tw:text-lg tw:text-red-600 tw:dark:text-red-400"></div>

        {{-- beside the cursor, or along the top where a finger would cover it --}}
        <div x-show="tooltip" x-cloak
             class="tw:absolute tw:z-10 tw:pointer-events-none tw:px-3 tw:py-2 tw:rounded tw:shadow-lg tw:text-sm tw:whitespace-nowrap tw:bg-white tw:dark:bg-dark-gray-400 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100"
             :style="tooltipStyle">
            <div class="tw:font-semibold tw:mb-1" x-text="tooltip?.time"></div>
            <template x-for="row in tooltip?.rows ?? []" :key="row.key">
                <div class="tw:flex tw:items-center tw:gap-2">
                    <span class="tw:inline-block tw:w-3 tw:h-3 tw:rounded-sm tw:shrink-0" :style="{ backgroundColor: row.color }"></span>
                    <span class="tw:grow tw:min-w-0 tw:max-w-64 tw:truncate" x-text="row.label"></span>
                    <span class="tw:font-mono tw:tabular-nums tw:pl-2 tw:shrink-0" x-text="row.value"></span>
                </div>
            </template>
            <div x-show="tooltip?.more" class="tw:text-gray-500 tw:dark:text-gray-400">+<span x-text="tooltip?.more"></span> {{ __('graphs.more') }}</div>
        </div>
    </div>

    <div class="tw:overflow-x-auto tw:mt-2" x-show="legend.length" x-cloak>
        <table class="tw:mx-auto tw:text-xs tw:sm:text-sm">
            <thead>
                <tr class="tw:text-gray-500 tw:dark:text-gray-400">
                    <th></th>
                    @foreach (['now', 'min', 'max', 'avg'] as $column)
                        <th class="tw:text-right tw:font-semibold tw:px-1.5 tw:sm:px-3">{{ __("graphs.legend.$column") }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <template x-for="item in legend" :key="item.key">
                    <tr class="tw:cursor-pointer tw:select-none tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300"
                        :class="hidden.includes(item.key) && 'tw:opacity-40'"
                        x-on:click="toggle(item.key, $event)">
                        <td class="tw:pr-2 tw:sm:pr-6 tw:py-1.5 tw:sm:py-0.5">
                            <span class="tw:flex tw:items-center tw:gap-2">
                                <span class="tw:inline-block tw:w-3 tw:h-3 tw:rounded-sm tw:shrink-0" :style="{ backgroundColor: item.color }"></span>
                                <span class="tw:max-w-[30vw] tw:sm:max-w-none tw:truncate" x-text="item.label"></span>
                            </span>
                        </td>
                        @foreach (['now', 'min', 'max', 'avg'] as $column)
                            <td class="tw:text-right tw:font-mono tw:tabular-nums tw:whitespace-nowrap tw:px-1.5 tw:sm:px-3" x-text="item.{{ $column }}"></td>
                        @endforeach
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <x-modal show="showHelp">
        <x-slot name="heading">
            <h4 class="tw:m-0 tw:text-base tw:font-semibold tw:flex tw:items-center tw:gap-2">
                <i class="fa fa-keyboard-o tw:text-blue-500" aria-hidden="true"></i>
                {{ __('graphs.help.title') }}
            </h4>
        </x-slot>

        @foreach ($help as $section => $rows)
            <div>
                <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">{{ $section }}</h5>
                <div class="tw:grid tw:grid-cols-1 tw:gap-2">
                    @foreach ($rows as [$description, $ways])
                        <div class="tw:flex tw:items-center tw:justify-between tw:gap-3 tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                            <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ $description }}</span>
                            <div class="tw:flex tw:flex-wrap tw:items-center tw:justify-end tw:gap-x-3 tw:gap-y-1.5">
                                @foreach ($ways as $keys)
                                    <span class="tw:flex tw:items-center tw:gap-1 tw:whitespace-nowrap">
                                        @foreach ($keys as $key)
                                            @if (! $loop->first)<span class="tw:text-gray-400">+</span>@endif
                                            <kbd class="{{ $kbd }}">{{ $key }}</kbd>
                                        @endforeach
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </x-modal>
</div>
