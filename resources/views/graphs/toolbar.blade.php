{{-- The controls of the graph page, the same for an image graph and one drawn in the browser. An image changes by loading the page again. --}}
@php
    $button = 'tw:h-10 tw:min-w-10 tw:px-2 tw:inline-flex tw:items-center tw:justify-center tw:gap-1 tw:rounded tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:bg-transparent tw:text-lg tw:text-gray-700 tw:dark:text-gray-200 tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300 tw:disabled:opacity-40 tw:disabled:pointer-events-none';
    $menu = 'tw:absolute tw:right-0 tw:top-full tw:mt-1 tw:z-20 tw:rounded-md tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:bg-white tw:dark:bg-dark-gray-400 tw:shadow-lg tw:text-left';
    $menuItem = 'tw:flex tw:w-full tw:items-center tw:gap-2 tw:px-3 tw:py-2 tw:border-0 tw:bg-transparent tw:text-gray-700 tw:dark:text-gray-200 tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300 tw:whitespace-nowrap';
    $heading = 'tw:mb-1 tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400';
    $segments = 'tw:inline-flex tw:max-w-full tw:shrink-0 tw:rounded tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:overflow-hidden';
    $segment = 'tw:min-h-8 tw:min-w-8 tw:px-2.5 tw:py-1 tw:text-sm tw:border-0 tw:not-first:border-l tw:border-gray-300 tw:dark:border-dark-gray-100 tw:bg-transparent tw:text-gray-700 tw:dark:text-gray-200 tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300 tw:aria-pressed:bg-blue-600 tw:aria-pressed:text-white tw:aria-pressed:hover:bg-blue-600';
    $row = 'tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-x-3 tw:gap-y-1';
    $rowLabel = 'tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200';
    $selectBox = 'tw:min-h-8 tw:max-w-full tw:px-2 tw:py-1 tw:text-sm tw:bg-gray-50 tw:border tw:border-gray-300 tw:text-gray-900 tw:rounded tw:dark:bg-gray-700 tw:dark:border-gray-600 tw:dark:text-white';
    $section = 'tw:p-3 tw:flex tw:flex-col tw:gap-2.5';
    $sectionTitle = 'tw:text-sm tw:font-semibold tw:text-gray-800 tw:dark:text-dark-white-100';
    $kbd = 'tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs';

    // section => [action, ways], a way is the keys to combine
    $help = array_filter([
        __('graph.help.time_range') => array_values(array_filter([
            $drawInBrowser ? [__('graph.help.pan'), [[__('graph.help.drag')], ['←'], ['→'], ['Shift', __('graph.help.scroll')]]] : [__('graph.help.pan'), [['←'], ['→']]],
            $drawInBrowser ? [__('graph.help.zoom'), [['+'], ['-'], ['Ctrl', __('graph.help.scroll')]]] : [__('graph.help.zoom'), [['+'], ['-']]],
            $drawInBrowser ? [__('graph.help.zoom_range'), [['Shift', __('graph.help.drag')]]] : null,
            $drawInBrowser ? [__('graph.help.reset'), [[__('graph.help.double_click')]]] : null,
            $drawInBrowser ? [__('graph.help.reset_all'), [['R']]] : null,
            $drawInBrowser ? [__('graph.help.values'), [[__('graph.help.hover')]]] : null,
            $trendHint ? [__('graph.help.trend'), [[__('graph.help.future_end')]]] : null,
        ])),
        __('graph.help.series') => $drawInBrowser ? [
            [__('graph.help.only'), [[__('graph.help.click')]]],
            [__('graph.help.toggle'), [['Ctrl', __('graph.help.click')]]],
            [__('graph.help.columns'), [['⋮']]],
        ] : [],
        __('graph.help.size') => $drawInBrowser ? [
            [__('graph.help.resize'), [[__('graph.help.drag_corner')]]],
            [__('graph.help.auto_size'), [[__('graph.help.double_click_corner')]]],
        ] : [],
        __('graph.help.touch') => $drawInBrowser ? [
            [__('graph.help.pan'), [[__('graph.help.drag')]]],
            [__('graph.help.zoom'), [[__('graph.help.pinch')]]],
            [__('graph.help.values'), [[__('graph.help.tap')]]],
            [__('graph.help.reset'), [[__('graph.help.double_tap')]]],
        ] : [],
        __('graph.help.general') => [
            [__('graph.help.open'), [['?']]],
            [__('graph.help.close'), [['Esc']]],
        ],
    ]);
@endphp
<div class="tw:flex tw:flex-wrap tw:items-center tw:justify-center tw:gap-2 tw:mt-3 tw:mb-2">
    <div x-ref="graphPickerGroup" class="tw:w-full tw:sm:w-[40ch]">
        <x-date-range-picker data-graph-picker :start="$graphFrom" :end="$graphTo" :reload="! $drawInBrowser"
                             class="tw:w-full tw:text-center tw:px-3 tw:py-1.5 tw:border tw:border-gray-300 tw:rounded-md tw:bg-gray-100! tw:hover:bg-gray-200! tw:dark:bg-white! tw:dark:hover:bg-gray-200!"></x-date-range-picker>
    </div>

    <div class="tw:flex tw:max-w-full tw:flex-wrap tw:items-center tw:justify-center tw:gap-1">
        <i x-show="state.refreshing && ! state.loading" x-cloak class="fa-solid fa-spinner fa-spin tw:mr-1 tw:text-gray-500 tw:dark:text-gray-400" aria-hidden="true"></i>
        <button type="button" class="{{ $button }}" x-on:click="zoom(-1)" :disabled="state.loading" title="{{ __('graph.zoom_out') }}" aria-label="{{ __('graph.zoom_out') }}">
            <i class="fa-solid fa-magnifying-glass-minus" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }}" x-on:click="zoom(1)" :disabled="state.loading" title="{{ __('graph.zoom_in') }}" aria-label="{{ __('graph.zoom_in') }}">
            <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }} tw:ml-1" x-on:click="pan(-1)" :disabled="state.loading" title="{{ __('graph.earlier') }}" aria-label="{{ __('graph.earlier') }}">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }}" x-on:click="pan(1)" :disabled="state.loading" title="{{ __('graph.later') }}" aria-label="{{ __('graph.later') }}">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
        @if ($drawInBrowser)
            {{-- an image has no zoom of its own to undo, each one is a new range --}}
            <button type="button" class="{{ $button }} tw:ml-1" x-on:click="reset()" :disabled="! resettable()" title="{{ __('graph.reset_title') }}">
                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i> {{ __('graph.reset') }}
            </button>
        @endif
    </div>

    <div x-ref="graphActions" class="tw:flex tw:items-center tw:gap-1">
        <div class="tw:relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape="open = false">
            <button type="button" class="{{ $button }}" x-on:click="open = ! open" :aria-expanded="open" title="{{ __('graph.share') }}" aria-label="{{ __('graph.share') }}">
                <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
            </button>
            <div x-show="open" x-cloak class="{{ $menu }} tw:py-1" x-on:click="open = false">
                <button type="button" class="{{ $menuItem }}" x-on:click="copyLink()">
                    <i class="fa-solid fa-link tw:w-4" aria-hidden="true"></i> {{ __('graph.copy_link') }}
                </button>
                <div class="tw:my-1 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-100" role="separator"></div>
                <button type="button" class="{{ $menuItem }}" x-on:click="exportImage()">
                    <i class="fa-regular fa-image tw:w-4" aria-hidden="true"></i> {{ __('graph.export_image') }}
                </button>
                @if ($graphPage['dataUrl'])
                    <button type="button" class="{{ $menuItem }}" x-on:click="exportCsv()">
                        <i class="fa-solid fa-file-csv tw:w-4" aria-hidden="true"></i> {{ __('graph.export_csv') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="tw:relative" x-data="graphSettingsMenu" x-effect="open && placeMenu()"
             x-on:resize.window="open && placeMenu()" x-on:click.outside="open = false" x-on:keydown.escape="open = false">
            <button type="button" class="{{ $button }} tw:relative" x-on:click="open = ! open" :aria-expanded="open" title="{{ __('graph.settings') }}" aria-label="{{ __('graph.settings') }}">
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
                @if ($drawInBrowser)
                    <span x-show="viewChanged()" x-cloak class="tw:absolute tw:-top-1 tw:-right-1 tw:w-2.5 tw:h-2.5 tw:rounded-full tw:bg-blue-600 tw:ring-2 tw:ring-white tw:dark:ring-dark-gray-500"></span>
                @endif
            </button>
            <div x-show="open" x-cloak :style="menuStyle" class="{{ $menu }} tw:w-80 tw:max-w-[calc(100vw-2rem)] tw:max-h-[calc(100vh-10rem)] tw:overflow-y-auto tw:divide-y tw:divide-gray-200 tw:dark:divide-dark-gray-100">
                <section class="{{ $section }}" aria-labelledby="graph-settings-view">
                    <div id="graph-settings-view" class="{{ $sectionTitle }}">{{ __('graph.settings_view') }}</div>

                    @if ($drawInBrowser)
                        <div class="{{ $row }}">
                            <label for="graph-compare" class="{{ $rowLabel }} tw:mb-0">{{ __('graph.options.compare') }}</label>
                            <select id="graph-compare" class="{{ $selectBox }}" x-on:change="setOption('compare', $event.target.value || null)">
                                @foreach (['none' => null, 'period' => 'period', 'day' => 'day', 'week' => 'week'] as $label => $compare)
                                    <option value="{{ $compare }}" :selected="options.compare === @js($compare)">{{ __("graph.options.compare_$label") }}</option>
                                @endforeach
                            </select>
                        </div>

                        <x-toggle size="compact" label-first :label="__('graph.options.trend')" x-bind:checked="options.trend"
                                  x-on:change="setOption('trend', $event.target.checked)"></x-toggle>

                        <div x-show="state.features?.percentileLines" x-cloak>
                            <x-toggle size="compact" label-first :label="__('graph.options.percentile_lines')" x-bind:checked="options.percentile_lines"
                                      x-on:change="setOption('percentile_lines', $event.target.checked)"></x-toggle>
                        </div>

                        {{-- What the graph does by itself is one of the choices, so every choice changes something --}}
                        <div class="{{ $row }}" x-show="state.features" x-cloak>
                            <span id="graph-scale" class="{{ $rowLabel }}">{{ __('graph.options.scale') }}</span>
                            <div class="{{ $segments }}" role="group" aria-labelledby="graph-scale">
                                <button type="button" class="{{ $segment }}" x-show="state.features?.scale !== 'fit'" :aria-pressed="isChosen('scale', 'graph')"
                                        x-on:click="choose('scale', 'graph')"
                                        x-text="state.features?.scale === 'limits' ? @js(__('graph.options.scale_limits')) : @js(__('graph.options.scale_zero'))"></button>
                                @foreach (['fit', 'log'] as $scale)
                                    <button type="button" class="{{ $segment }}" :aria-pressed="isChosen('scale', '{{ $scale }}')"
                                            x-on:click="choose('scale', '{{ $scale }}')">{{ __("graph.options.scale_$scale") }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="{{ $row }}" x-show="state.features?.stacking" x-cloak>
                            <span id="graph-stacking" class="{{ $rowLabel }}">{{ __('graph.options.stacking') }}</span>
                            <div class="{{ $segments }}" role="group" aria-labelledby="graph-stacking">
                                <button type="button" class="{{ $segment }}" x-show="state.features?.stacking === 'some'" :aria-pressed="isChosen('stacking', 'graph')"
                                        x-on:click="choose('stacking', 'graph')">{{ __('graph.options.stacking_graph') }}</button>
                                @foreach (['stack', 'overlay'] as $stacking)
                                    <button type="button" class="{{ $segment }}" :aria-pressed="isChosen('stacking', '{{ $stacking }}')"
                                            x-on:click="choose('stacking', '{{ $stacking }}')">{{ __("graph.options.stacking_$stacking") }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div x-show="state.features?.mirror" x-cloak>
                            <x-toggle size="compact" label-first :label="__('graph.options.mirror')" x-bind:checked="options.mirror"
                                      x-on:change="setOption('mirror', $event.target.checked)"></x-toggle>
                        </div>
                    @else
                        @foreach ($imageOptions as $option)
                            <x-toggle size="compact" label-first :label="$option['label']" :checked="$option['checked']"
                                      x-on:change="window.location = withShownRange({{ Js::from($option['link']) }})"></x-toggle>
                        @endforeach
                    @endif
                </section>

                @if ($drawInBrowser)
                    <section aria-labelledby="graph-settings-preferences">
                        <button type="button" class="tw:flex tw:w-full tw:items-center tw:justify-between tw:gap-2 tw:p-3 tw:border-0 tw:bg-transparent tw:text-left tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-300"
                                x-on:click="togglePreferences()" :aria-expanded="preferencesOpen" aria-controls="graph-settings-preferences-body">
                            <span id="graph-settings-preferences" class="{{ $sectionTitle }}">{{ __('graph.settings_preferences') }}</span>
                            <i class="fa-solid fa-chevron-down tw:text-gray-500 tw:transition-transform" :class="preferencesOpen && 'tw:rotate-180'" aria-hidden="true"></i>
                        </button>
                        <div id="graph-settings-preferences-body" x-show="preferencesOpen" x-collapse x-cloak>
                            <div class="{{ $section }} tw:pt-0">
                                <x-toggle size="compact" label-first :label="__('graph.interactive')" :checked="true"
                                          x-on:change="saveRenderer($event.target.checked)"></x-toggle>

                                <div class="{{ $row }}">
                                    <span id="graph-line-style" class="{{ $rowLabel }}">{{ __('graph.options.line_style') }}</span>
                                    <div class="{{ $segments }}" role="group" aria-labelledby="graph-line-style">
                                        @foreach (App\Data\Graphing\GraphViewOptions::LINE_STYLES as $style)
                                            <button type="button" class="{{ $segment }}" :aria-pressed="options.line_style === '{{ $style }}'"
                                                    x-on:click="setOption('line_style', '{{ $style }}')">{{ __("graph.options.line_style_$style") }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                <x-toggle size="compact" label-first :label="__('graph.options.range')" x-bind:checked="options.range"
                                          x-on:change="setOption('range', $event.target.checked)"></x-toggle>
                            </div>
                        </div>
                    </section>
                @elseif ($canDrawInBrowser)
                    <section class="{{ $section }}" aria-labelledby="graph-settings-preferences">
                        <div id="graph-settings-preferences" class="{{ $sectionTitle }}">{{ __('graph.settings_preferences') }}</div>
                        <x-toggle size="compact" label-first :label="__('graph.interactive')" :checked="false"
                                  x-on:change="saveRenderer($event.target.checked)"></x-toggle>
                    </section>
                @endif
            </div>
        </div>

        <button type="button" x-on:click="showHelp = true" title="{{ __('graph.help.title') }}" aria-label="{{ __('graph.help.title') }}"
                class="tw:h-10 tw:w-10 tw:inline-flex tw:items-center tw:justify-center tw:rounded-full tw:border-0 tw:bg-transparent tw:text-gray-500 tw:dark:text-gray-400 tw:hover:text-gray-800 tw:dark:hover:text-gray-100">
            <i class="fa-regular fa-circle-question fa-xl" aria-hidden="true"></i>
        </button>
    </div>
</div>

<x-modal show="showHelp">
    <x-slot name="heading">
        <h4 class="tw:m-0 tw:text-base tw:font-semibold tw:flex tw:items-center tw:gap-2">
            <i class="fa fa-keyboard-o tw:text-blue-500" aria-hidden="true"></i>
            {{ __('graph.help.title') }}
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
