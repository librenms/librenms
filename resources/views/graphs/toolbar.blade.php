{{-- The controls of the graph page, the same for an image graph and one drawn in the browser. An image changes by loading the page again. --}}
@php
    $button = 'tw:h-8 tw:min-w-8 tw:px-2 tw:inline-flex tw:items-center tw:justify-center tw:gap-1 tw:rounded tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:bg-transparent tw:text-gray-700 tw:dark:text-gray-200 tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300 tw:disabled:opacity-40 tw:disabled:pointer-events-none';
    $menu = 'tw:absolute tw:right-0 tw:top-full tw:mt-1 tw:z-20 tw:rounded-md tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:bg-white tw:dark:bg-dark-gray-400 tw:shadow-lg tw:text-left';
    $menuItem = 'tw:flex tw:w-full tw:items-center tw:gap-2 tw:px-3 tw:py-2 tw:border-0 tw:bg-transparent tw:text-gray-700 tw:dark:text-gray-200 tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300 tw:whitespace-nowrap';
    $heading = 'tw:mb-1 tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400';
    $choice = 'tw:px-2 tw:py-1 tw:text-sm tw:rounded tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:text-gray-700 tw:dark:text-gray-200 tw:aria-pressed:bg-blue-600 tw:aria-pressed:border-blue-600 tw:aria-pressed:text-white';
    $kbd = 'tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs';

    // section => [action, ways], a way is the keys to combine
    $help = array_filter([
        __('graphs.help.time_range') => array_values(array_filter([
            $drawInBrowser ? [__('graphs.help.pan'), [[__('graphs.help.drag')], ['←'], ['→'], ['Shift', __('graphs.help.scroll')]]] : [__('graphs.help.pan'), [['←'], ['→']]],
            $drawInBrowser ? [__('graphs.help.zoom'), [['+'], ['-'], ['Ctrl', __('graphs.help.scroll')]]] : [__('graphs.help.zoom'), [['+'], ['-']]],
            $drawInBrowser ? [__('graphs.help.zoom_range'), [['Shift', __('graphs.help.drag')]]] : null,
            $drawInBrowser ? [__('graphs.help.reset'), [['R'], [__('graphs.help.double_click')]]] : null,
            $drawInBrowser ? [__('graphs.help.values'), [[__('graphs.help.hover')]]] : null,
        ])),
        __('graphs.help.series') => $drawInBrowser ? [
            [__('graphs.help.only'), [[__('graphs.help.click')]]],
            [__('graphs.help.toggle'), [['Ctrl', __('graphs.help.click')]]],
        ] : [],
        __('graphs.help.size') => $drawInBrowser ? [
            [__('graphs.help.resize'), [[__('graphs.help.drag_corner')]]],
            [__('graphs.help.auto_size'), [[__('graphs.help.double_click_corner')]]],
        ] : [],
        __('graphs.help.touch') => $drawInBrowser ? [
            [__('graphs.help.pan'), [[__('graphs.help.drag')]]],
            [__('graphs.help.zoom'), [[__('graphs.help.pinch')]]],
            [__('graphs.help.values'), [[__('graphs.help.tap')]]],
            [__('graphs.help.reset'), [[__('graphs.help.double_tap')]]],
        ] : [],
        __('graphs.help.general') => [
            [__('graphs.help.open'), [['?']]],
            [__('graphs.help.close'), [['Esc']]],
        ],
    ]);
@endphp
<div class="tw:flex tw:flex-wrap tw:items-center tw:justify-center tw:gap-2 tw:mt-3 tw:mb-2">
    <div class="tw:w-full tw:sm:w-[40ch]">
        <x-date-range-picker :start="$graphFrom" :end="$graphTo" :reload="! $drawInBrowser"
                             class="tw:w-full tw:text-center tw:px-3 tw:py-1.5 tw:border tw:border-gray-300 tw:rounded-md tw:bg-gray-100! tw:hover:bg-gray-200! tw:dark:bg-white! tw:dark:hover:bg-gray-200!"></x-date-range-picker>
    </div>

    <div class="tw:flex tw:items-center tw:gap-1">
        <i x-show="state.refreshing && ! state.loading" x-cloak class="fa-solid fa-spinner fa-spin tw:mr-1 tw:text-gray-500 tw:dark:text-gray-400" aria-hidden="true"></i>
        <button type="button" class="{{ $button }}" x-on:click="zoom(-1)" :disabled="state.loading" title="{{ __('graphs.zoom_out') }}" aria-label="{{ __('graphs.zoom_out') }}">
            <i class="fa-solid fa-magnifying-glass-minus" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }}" x-on:click="zoom(1)" :disabled="state.loading" title="{{ __('graphs.zoom_in') }}" aria-label="{{ __('graphs.zoom_in') }}">
            <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }} tw:ml-1" x-on:click="pan(-1)" :disabled="state.loading" title="{{ __('graphs.earlier') }}" aria-label="{{ __('graphs.earlier') }}">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }}" x-on:click="pan(1)" :disabled="state.loading" title="{{ __('graphs.later') }}" aria-label="{{ __('graphs.later') }}">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
        @if ($drawInBrowser)
            {{-- an image has no zoom of its own to undo, each one is a new range --}}
            <button type="button" class="{{ $button }} tw:ml-1" x-on:click="reset()" :disabled="! state.zoomed" title="{{ __('graphs.reset_title') }}">
                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i> {{ __('graphs.reset') }}
            </button>
        @endif
    </div>

    <div class="tw:flex tw:items-center tw:gap-1">
        <div class="tw:relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape="open = false">
            <button type="button" class="{{ $button }}" x-on:click="open = ! open" :aria-expanded="open" title="{{ __('graphs.export') }}" aria-label="{{ __('graphs.export') }}">
                <i class="fa-solid fa-download" aria-hidden="true"></i>
            </button>
            <div x-show="open" x-cloak class="{{ $menu }} tw:py-1" x-on:click="open = false">
                <button type="button" class="{{ $menuItem }}" x-on:click="exportImage()">
                    <i class="fa-regular fa-image tw:w-4" aria-hidden="true"></i> {{ __('graphs.export_image') }}
                </button>
                @if ($graphDataUrl)
                    <button type="button" class="{{ $menuItem }}" x-on:click="exportCsv()">
                        <i class="fa-solid fa-file-csv tw:w-4" aria-hidden="true"></i> {{ __('graphs.export_csv') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="tw:relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape="open = false">
            <button type="button" class="{{ $button }}" x-on:click="open = ! open" :aria-expanded="open" title="{{ __('graphs.settings') }}" aria-label="{{ __('graphs.settings') }}">
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
            </button>
            <div x-show="open" x-cloak class="{{ $menu }} tw:p-3 tw:w-72 tw:max-w-[calc(100vw-2rem)] tw:flex tw:flex-col tw:gap-3">
                @if ($canDrawInBrowser)
                    <x-toggle size="sm" :label="__('graphs.interactive')" :checked="$drawInBrowser"
                              x-on:change="saveRenderer($event.target.checked)"></x-toggle>
                @endif

                @if ($drawInBrowser)
                    <x-toggle size="sm" :label="__('graphs.options.range')" x-bind:checked="options.range"
                              x-on:change="setOption('range', $event.target.checked)"></x-toggle>

                    <div x-show="state.features.mirror">
                        <x-toggle size="sm" :label="__('graphs.options.mirror')" x-bind:checked="options.mirror"
                                  x-on:change="setOption('mirror', $event.target.checked)"></x-toggle>
                    </div>

                    <div x-show="state.features.stacking">
                        <div class="{{ $heading }}">{{ __('graphs.options.stacking') }}</div>
                        <div class="tw:flex tw:gap-1">
                            @foreach (App\Data\Graphing\GraphViewOptions::STACKING as $stacking)
                                <button type="button" class="{{ $choice }}" :aria-pressed="options.stacking === '{{ $stacking }}'"
                                        x-on:click="setOption('stacking', '{{ $stacking }}')">{{ __("graphs.options.stacking_$stacking") }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="{{ $heading }}">{{ __('graphs.options.compare') }}</div>
                        <div class="tw:flex tw:flex-wrap tw:gap-1">
                            @foreach (['none' => null, 'period' => 'period', 'day' => 'day', 'week' => 'week'] as $label => $compare)
                                <button type="button" class="{{ $choice }}" :aria-pressed="options.compare === @js($compare)"
                                        x-on:click="setOption('compare', @js($compare))">{{ __("graphs.options.compare_$label") }}</button>
                            @endforeach
                        </div>
                    </div>

                    <x-toggle size="sm" :label="__('graphs.options.trend')" x-bind:checked="options.trend"
                              x-on:change="setOption('trend', $event.target.checked)"></x-toggle>

                    <div>
                        <div class="{{ $heading }}">{{ __('graphs.options.scale') }}</div>
                        <div class="tw:flex tw:gap-1">
                            @foreach (App\Data\Graphing\GraphViewOptions::SCALES as $scale)
                                <button type="button" class="{{ $choice }}" :aria-pressed="options.scale === '{{ $scale }}'"
                                        x-on:click="setOption('scale', '{{ $scale }}')">{{ __("graphs.options.scale_$scale") }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="{{ $heading }}">{{ __('graphs.options.columns') }}</div>
                        <div class="tw:flex tw:flex-wrap tw:gap-1">
                            @foreach (App\Data\Graphing\GraphViewOptions::COLUMNS as $column)
                                <button type="button" class="{{ $choice }}" :aria-pressed="options.columns.includes('{{ $column }}')"
                                        x-on:click="toggleColumn('{{ $column }}')">{{ __("graphs.legend.$column") }}</button>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" class="{{ $button }} tw:self-start" x-on:click="autoSize()" :disabled="options.height === null">
                        <i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i> {{ __('graphs.options.auto_size') }}
                    </button>
                @else
                    @foreach ($imageOptions as $option)
                        <x-toggle size="sm" :label="$option['label']" :checked="$option['checked']"
                                  x-on:change="window.location = withShownRange({{ Js::from($option['link']) }})"></x-toggle>
                    @endforeach
                    @if ($trendHint)
                        <p class="tw:m-0 tw:text-sm tw:text-gray-500 tw:dark:text-gray-400">{{ __('To show trend, set to future date') }}</p>
                    @endif
                @endif
            </div>
        </div>

        <button type="button" x-on:click="showHelp = true" title="{{ __('graphs.help.title') }}" aria-label="{{ __('graphs.help.title') }}"
                class="tw:h-8 tw:w-8 tw:inline-flex tw:items-center tw:justify-center tw:rounded-full tw:border-0 tw:bg-transparent tw:text-gray-500 tw:dark:text-gray-400 tw:hover:text-gray-800 tw:dark:hover:text-gray-100">
            <i class="fa-regular fa-circle-question fa-xl" aria-hidden="true"></i>
        </button>
    </div>
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
