@props([
    'start' => '',
    'end' => '',
    'presets' => true,
    'reload' => false,
    'placeholder' => __('Select date range...'),
    'class' => 'tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-md'
])

<div {{ $attributes->merge(['class' => 'tw:relative']) }}
     x-data="dateRangePicker"
     x-on:click.outside="closeDropdown"
     data-start="{{ $start }}"
     data-end="{{ $end }}"
     data-reload="{{ $reload ? 'true' : 'false' }}"
     data-presets=" {{ is_array($presets) ? implode(',', $presets) : (string) $presets }}"
     data-placeholder="{{ $placeholder }}">
    <div
        x-text="displayText"
        class="{{ $class }} tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:px-3 tw:py-2 tw:cursor-pointer tw:bg-white tw:dark:text-gray-800"
        :class="{'tw:text-gray-500 tw:dark:text-gray-400': !hasValue}"
        x-on:click="toggleDropdown"
        tabindex="0"
    ></div>

    <input type="hidden" name="from" :value="outStartString">
    <input type="hidden" name="to" :value="outEndString">

    <div class="tw:absolute tw:top-full tw:left-0 tw:right-0 tw:bg-white tw:dark:bg-dark-gray-400 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded-md tw:shadow-lg tw:z-10 tw:p-4 tw:mt-1 tw:dark:text-gray-400"
         x-show="open"
         x-transition:enter="tw:transition tw:ease-out tw:duration-200"
         x-transition:enter-start="tw:opacity-0 tw:transform tw:-translate-y-2"
         x-transition:enter-end="tw:opacity-100 tw:transform tw:translate-y-0"
         x-transition:leave="tw:transition tw:ease-in tw:duration-150"
         x-transition:leave-start="tw:opacity-100 tw:transform tw:translate-y-0"
         x-transition:leave-end="tw:opacity-0 tw:transform tw:-translate-y-2"
         style="    display: none;">
        @if($presets)
            <div class="tw:grid tw:gap-2 tw:justify-center tw:mb-3 tw:dark:text-white"
                 :style="isCompact ? 'grid-template-columns: repeat(auto-fit, minmax(40px, max-content))' : 'grid-template-columns: repeat(auto-fit, minmax(70px, max-content))'">
                <template x-for="(preset, idx) in presets">
                    <button type="button"
                            class="preset-btn tw:px-2.5 tw:py-1.5 tw:text-sm tw:whitespace-nowrap tw:hover:bg-gray-200 tw:dark:hover:bg-gray-600 tw:rounded-md tw:transition-colors tw:dark:text-gray-400"
                            :class="isPresetSelected(preset.value) ? 'tw:bg-blue-500 tw:text-white tw:dark:text-white' : 'tw:bg-gray-100 tw:dark:bg-gray-700'"
                            x-on:click="setRange(preset.value, 'now')"
                            x-text="isCompact ? preset.value : preset.label"
                    ></button>
                </template>
            </div>
        @endif
        <div class="tw:flex-1 tw:mb-3">
            <label class="tw:block tw:text-gray-600 tw:dark:text-gray-400 tw:mb-1">{{ __('From') }} <span x-show="preset || ! start" x-text="'(' + (preset ? preset.label : '{{ __('All') }}') + ')'"></span></label>
            <div class="tw:flex tw:flex-wrap tw:gap-1 tw:dark:text-dark-gray-400">
                <input type="date" x-model="startDate" class="tw:flex-1 tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white tw:w-full">
                <input type="time" x-model="startTime" class="tw:min-w-fit tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white tw:w-full">
            </div>
        </div>
        <div class="tw:flex-1 tw:mb-4">
            <label class="tw:block tw:text-gray-600 tw:dark:text-gray-400 tw:mb-1">{{ __('To')  }} <span x-show="! end">({{ __('Now') }})</span></label>
            <div class="tw:flex tw:flex-wrap tw:gap-1 tw:dark:text-dark-gray-400">
                <input type="date" x-model="endDate" class="tw:flex-1 tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white tw:w-full">
                <input type="time" x-model="endTime" class="tw:min-w-fit tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white tw:w-full">
            </div>
        </div>
        <div class="tw:flex tw:justify-between tw:items-center tw:gap-2">
            <button type="button" x-on:click="clearRange"
                    class="tw:px-4 tw:py-1.5 tw:font-medium tw:text-gray-500 tw:dark:text-gray-400 tw:border tw:border-gray-200 tw:dark:border-gray-600 tw:rounded tw:hover:bg-gray-50 tw:dark:hover:bg-gray-800 tw:hover:text-gray-700 tw:dark:hover:text-gray-300 tw:transition-colors tw:duration-150">
                {{ __('Clear') }}
            </button>
            <button type="button" x-on:click="applyRange"
                    class="tw:px-4 tw:py-1.5 tw:font-medium tw:text-white! tw:bg-blue-500 tw:dark:bg-blue-600 tw:rounded tw:shadow-sm tw:hover:bg-blue-600 tw:dark:hover:bg-blue-700 tw:active:scale-95 tw:transition-all tw:duration-150">
                {{ __('Apply') }}
            </button>
        </div>
    </div>
</div>
