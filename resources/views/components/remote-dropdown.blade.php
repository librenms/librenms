@props([
    'endpoint' => null,
    'params'   => null,
    'multi'    => null
])

<div x-data="remoteDropdown({
    endpoint: {{ $endpoint ? "@js($endpoint)" : "current?.endpoint" }},
    params:   {{ $params ? "@js($params)" : "current?.params || {}" }},
    multi:    {{ !is_null($multi) ? "@js($multi)" : "current?.type === 'multi-select'" }}
})" class="tw:space-y-[0.8em]">

    {{-- Search Input --}}
    <div class="tw:relative">
        <input type="text" x-model.debounce.300ms="search"
               placeholder="{{ __('Type to search...') }}"
               class="tw:w-full tw:px-[1em] tw:py-[0.8em] tw:bg-neutral-50 tw:dark:bg-dark-gray-400 tw:border tw:border-neutral-200 tw:dark:border-dark-gray-300 tw:rounded-[0.6em] tw:text-neutral-900! tw:dark:text-dark-white-100!" />
    </div>

    {{-- Options List --}}
    <div class="tw:max-h-[15em] tw:overflow-y-auto tw:flex tw:flex-col tw:gap-[0.4em] tw:scrollbar-thin">
        <template x-for="(opt, index) in options" :key="index">
            <button type="button" @click="select(opt)"
                    class="tw:px-[1.2em] tw:py-[0.7em] tw:rounded-[0.6em] tw:text-[0.85em] tw:font-bold tw:transition-all tw:border tw:text-left"
                    :class="((Array.isArray(value) ? value.includes(opt.id) : value === opt.id))
                        ? 'tw:bg-blue-50 tw:dark:bg-blue-900/30 tw:text-blue-600! tw:dark:text-blue-400! tw:border-blue-200'
                        : 'tw:bg-neutral-50 tw:dark:bg-dark-gray-400 tw:text-neutral-600! tw:dark:text-dark-white-200! tw:border-transparent tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300'">
                <span x-text="opt.text || opt"></span>
            </button>
        </template>

        {{-- Infinite Scroll Sentinel --}}
        <div x-show="hasMore" x-intersect="fetch()" class="tw:py-4 tw:flex tw:justify-center">
            <svg class="tw:animate-spin tw:h-5 tw:w-5 tw:text-blue-500" viewBox="0 0 24 24">
                <circle class="tw:opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="tw:opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
</div>
