@props([
    'endpoint' => null,
    'params'   => null,
    'multi'    => null
])

<div x-data="remoteDropdown({
    endpoint: {{ $endpoint ? "@js($endpoint)" : "current?.endpoint" }},
    params:   {{ $params ? "@js($params)" : "current?.params || {}" }},
    multi:    {{ !is_null($multi) ? "@js($multi)" : "current?.type === 'multi-select'" }}
})" class="tw:space-y-[1em]">

    {{-- Search Input --}}
    <div class="tw:relative">
        <input type="text" x-model.debounce.300ms="search"
               placeholder="{{ __('Type to search...') }}"
               class="tw:w-full tw:px-[1em] tw:py-[0.8em] tw:text-[0.95em] tw:bg-neutral-50 tw:dark:bg-dark-gray-400 tw:border tw:border-neutral-200 tw:dark:border-dark-gray-300 tw:rounded-[0.6em] tw:focus:ring-2 tw:focus:ring-blue-500/50 tw:focus:border-blue-500 tw:outline-none tw:text-neutral-900! tw:dark:text-dark-white-100! tw:transition-all" />

        {{-- Floating Search Icon --}}
        <div class="tw:absolute tw:right-[1em] tw:top-1/2 tw:-translate-y-1/2 tw:text-neutral-400">
            <svg x-show="!isLoading" class="tw:w-[1.1em] tw:h-[1.1em]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <svg x-show="isLoading" class="tw:animate-spin tw:h-[1.1em] tw:w-[1.1em] tw:text-blue-500" viewBox="0 0 24 24">
                <circle class="tw:opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="tw:opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    {{-- Options List (Vertical) --}}
    <div class="tw:max-h-[15em] tw:overflow-y-auto tw:flex tw:flex-col tw:gap-[0.5em] tw:scrollbar-thin">
        <template x-for="(opt, index) in options" :key="index">
            <button type="button" @click="select(opt)"
                    class="tw:flex tw:items-center tw:justify-between tw:w-full tw:px-[1.2em] tw:py-[0.8em] tw:rounded-[0.6em] tw:text-[0.85em] tw:font-bold tw:transition-all tw:border tw:text-left"
                    :class="((Array.isArray(value) ? value.includes(opt.id) : value === opt.id))
                        ? 'tw:bg-blue-600 tw:text-white! tw:border-blue-600 tw:shadow-md'
                        : 'tw:bg-neutral-50 tw:dark:bg-dark-gray-400 tw:text-neutral-600! tw:dark:text-dark-white-200! tw:border-neutral-100 tw:dark:border-dark-gray-300 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300'">

                <span x-text="opt.text || opt"></span>

                <svg x-show="((Array.isArray(value) ? value.includes(opt.id) : value === opt.id))"
                     class="tw:w-[1.1em] tw:h-[1.1em]" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M1.5 5l3 3 4-5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </template>

        {{-- Infinite Scroll sentinel --}}
        <div x-show="hasMore" x-intersect="fetch()" class="tw:py-4 tw:flex tw:justify-center">
            <svg class="tw:animate-spin tw:h-5 tw:w-5 tw:text-blue-500/50" viewBox="0 0 24 24">
                <circle class="tw:opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="tw:opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
</div>
