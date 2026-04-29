@props([
    'fields' => []
])

<div {{ $attributes->merge(['class' => 'tw:relative tw:flex tw:items-center tw:text-[13px] tw:max-w-full']) }}
     x-data="filterBarComponent({ fields: @js($fields) })"
     @click.outside="close()"
     @keydown.escape.window="close()">

    {{-- Main Bar --}}
    <div class="tw:flex tw:items-stretch tw:h-[2.4em] tw:rounded-[0.5em] tw:border tw:border-neutral-300 tw:dark:border-neutral-700 tw:bg-white tw:dark:bg-neutral-950 tw:font-mono tw:shadow-xs tw:max-w-full">

        {{-- LEFT SECTION: Filter Label / Clear Toggle --}}
        <div class="tw:relative tw:flex tw:items-stretch">
            <button type="button"
                    {{-- Tooltip changes based on state --}}
                    :title="filters.length ? '{{ __('Clear all active filters') }}' : '{{ __('Open filter menu') }}'"
                    @click.stop="filters.length ? clearAll() : (showAdd = !showAdd)"
                    class="tw:shrink-0 tw:flex tw:items-center tw:gap-[0.5em] tw:px-[1em] tw:h-full tw:transition-colors tw:border-r tw:border-neutral-200 tw:dark:border-neutral-800 tw:tracking-widest tw:rounded-l-[0.5em]"
                    :class="filters.length ? 'tw:cursor-pointer tw:hover:bg-red-50 tw:dark:hover:bg-red-950/30 tw:text-neutral-900! tw:dark:text-neutral-100!' : 'tw:text-neutral-400! tw:hover:bg-neutral-50 tw:dark:hover:bg-neutral-900'">
                <div class="tw:relative tw:flex tw:items-center">
                    <svg class="tw:w-[1.1em] tw:h-[1.1em]" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 2.5h10M3 6h6M5 9.5h2" stroke-linecap="round"/>
                    </svg>
                    <span x-show="filters.length" class="tw:absolute tw:-top-[0.2em] tw:-right-[0.3em] tw:w-[0.5em] tw:h-[0.5em] tw:bg-neutral-600 tw:dark:bg-neutral-400 tw:rounded-full tw:ring-1 tw:ring-white tw:dark:ring-neutral-950"></span>
                </div>
                <span class="tw:uppercase tw:font-bold" x-show="!filters.length">{{ __('Filter') }}</span>
                <span class="tw:font-normal" x-show="filters.length" x-text="filters.length"></span>
            </button>

            {{-- Left Dropdown --}}
            <div x-show="showAdd && !filters.length" x-cloak x-transition
                 @click.stop
                 class="tw:absolute tw:top-full tw:left-0 tw:mt-[0.5em] tw:w-[15em] tw:bg-white tw:dark:bg-neutral-900 tw:border tw:border-neutral-200 tw:dark:border-neutral-800 tw:rounded-[0.6em] tw:shadow-xl tw:z-50 tw:py-[0.5em]">
                <div class="tw:px-[1.2em] tw:py-[0.6em] tw:text-[0.7em] tw:font-black tw:text-neutral-400 tw:uppercase tw:tracking-widest">{{ __('Select Field') }}</div>
                <template x-for="field in fields" :key="field.key">
                    <button type="button" @click="open(field)"
                            :class="isActive(field.key) ? 'tw:text-blue-600! tw:dark:text-blue-400! tw:bg-blue-50/50 tw:dark:bg-blue-900/20' : 'tw:text-neutral-600! tw:dark:text-neutral-300!'"
                            class="tw:flex tw:items-center tw:justify-between tw:w-full tw:px-[1.2em] tw:py-[0.7em] tw:text-left tw:hover:bg-neutral-50 tw:dark:hover:bg-neutral-800 tw:transition-colors">
                        <span x-text="field.label"></span>
                        <svg x-show="isActive(field.key)" class="tw:w-[1em] tw:h-[1em]" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M1.5 5l3 3 4-5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </template>
            </div>
        </div>

        {{-- MIDDLE SECTION: Scrollable Chips --}}
        <div class="tw:flex tw:items-stretch tw:overflow-x-auto tw:scrollbar-none tw:flex-nowrap">
            <template x-for="f in filters" :key="f.key">
                <div class="tw:shrink-0 tw:relative tw:group tw:flex tw:items-stretch tw:h-full tw:border-r tw:border-neutral-200 tw:dark:border-neutral-800" role="listitem">
                    {{-- Chip Edit Tooltip --}}
                    <button type="button"
                            title="{{ __('Edit filter') }}"
                            @click="open(fields.find(field => field.key === f.key))"
                            class="tw:flex tw:items-center tw:h-full tw:px-[1em] tw:gap-[0.5em] tw:transition-colors tw:hover:bg-neutral-50 tw:dark:hover:bg-neutral-900 tw:whitespace-nowrap">
                        <span class="tw:font-bold tw:text-neutral-900! tw:dark:text-neutral-100!" x-text="f.label"></span>
                        <span class="tw:text-neutral-400!" x-text="f.sym"></span>
                        <span x-show="!nullary(f.op)" class="tw:font-bold tw:text-neutral-700! tw:dark:text-neutral-400!">
                            <template x-if="currentField(f.key)?.type === 'boolean'">
                                <span x-text="f.value == 1 ? '{{ __('Yes') }}' : '{{ __('No') }}'"></span>
                            </template>
                            <template x-if="currentField(f.key)?.type !== 'boolean'">
                                <span x-text="Array.isArray(f.value) ? f.value.join(', ') : f.value"></span>
                            </template>
                        </span>
                    </button>

                    {{-- Chip Remove Tooltip --}}
                    <button type="button"
                            title="{{ __('Remove filter') }}"
                            @click.stop="remove(f.key)"
                            class="tw:h-full tw:w-0 tw:group-hover:w-[2.4em] tw:flex tw:items-center tw:justify-center tw:bg-neutral-100 tw:dark:bg-neutral-800 tw:text-neutral-500! tw:transition-all tw:duration-200 tw:ease-in-out tw:overflow-hidden tw:text-[1.2em] tw:hover:text-red-600! tw:dark:hover:text-red-500!">
                        &times;
                    </button>
                </div>
            </template>
        </div>

        {{-- RIGHT SECTION: Plus Button & Right Dropdown --}}
        <div class="tw:shrink-0 tw:relative tw:flex tw:items-stretch">
            {{-- Plus Button Tooltip --}}
            <button type="button"
                    title="{{ __('Add new filter') }}"
                    @click.stop="showAdd = !showAdd"
                    class="tw:w-[2.4em] tw:h-full tw:flex tw:items-center tw:justify-center tw:text-[1.4em] tw:text-neutral-400! tw:hover:text-neutral-900! tw:dark:hover:text-white! tw:hover:bg-neutral-50 tw:dark:hover:bg-neutral-900 tw:transition-colors tw:rounded-r-[0.5em]">
                +
            </button>

            {{-- Right Dropdown --}}
            <div x-show="showAdd && filters.length" x-cloak x-transition
                 @click.stop
                 class="tw:absolute tw:top-full tw:right-0 tw:mt-[0.5em] tw:w-[15em] tw:bg-white tw:dark:bg-neutral-900 tw:border tw:border-neutral-200 tw:dark:border-neutral-800 tw:rounded-[0.6em] tw:shadow-xl tw:z-50 tw:py-[0.5em]">
                <div class="tw:px-[1.2em] tw:py-[0.6em] tw:text-[0.7em] tw:font-black tw:text-neutral-400 tw:uppercase tw:tracking-widest">{{ __('Select Field') }}</div>
                <template x-for="field in fields" :key="field.key">
                    <button type="button" @click="open(field)"
                            :class="isActive(field.key) ? 'tw:text-blue-600! tw:dark:text-blue-400! tw:bg-blue-50/50 tw:dark:bg-blue-900/20' : 'tw:text-neutral-600! tw:dark:text-neutral-300!'"
                            class="tw:flex tw:items-center tw:justify-between tw:w-full tw:px-[1.2em] tw:py-[0.7em] tw:text-left tw:hover:bg-neutral-50 tw:dark:hover:bg-neutral-800 tw:transition-colors">
                        <span x-text="field.label"></span>
                        <svg x-show="isActive(field.key)" class="tw:w-[1em] tw:h-[1em]" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M1.5 5l3 3 4-5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- Dialog Modal (Teleported) --}}
    <template x-teleport="body">
        <div x-show="dialog" x-cloak
             class="tw:fixed tw:inset-0 tw:z-100 tw:flex tw:items-center tw:justify-center tw:p-[1.5em] tw:bg-neutral-950/60 tw:backdrop-blur-xs tw:text-[14px]"
             @click="close()">

            <div x-show="dialog" x-transition @click.stop
                 class="tw:w-full tw:max-w-[25em] tw:bg-white tw:dark:bg-neutral-900 tw:border tw:border-neutral-200 tw:dark:border-neutral-800 tw:rounded-[1em] tw:shadow-2xl tw:font-mono"
                 role="dialog" aria-modal="true">

                {{-- Modal Header --}}
                <div class="tw:px-[1.5em] tw:py-[1.2em] tw:border-b tw:border-neutral-100 tw:dark:border-neutral-800 tw:bg-blue-50/20 tw:dark:bg-blue-950/10 tw:flex tw:items-center tw:justify-between tw:rounded-t-[1em]">
                    <div class="tw:text-[1.15em] tw:font-black tw:text-neutral-900! tw:dark:text-neutral-100!">
                        <span x-text="current?.label"></span>
                        <span class="tw:ml-[0.3em] tw:text-[0.65em] tw:font-normal tw:text-blue-600! tw:dark:text-blue-500! tw:uppercase">{{ __('Filter') }}</span>
                    </div>
                    {{-- Modal Close Tooltip --}}
                    <button type="button"
                            title="{{ __('Close dialog') }}"
                            @click="close()"
                            class="tw:text-neutral-400! tw:transition-colors tw:hover:text-red-500! tw:dark:hover:text-red-500! tw:text-[1.5em]">&times;</button>
                </div>

                {{-- Modal Body --}}
                <div class="tw:p-[1.5em] tw:space-y-[1.8em]">
                    <div>
                        <span class="tw:block tw:text-[0.75em] tw:font-black tw:text-neutral-400 tw:uppercase tw:tracking-widest tw:mb-[1em]">{{ __('Condition') }}</span>
                        <div class="tw:grid tw:grid-cols-2 tw:gap-[0.5em]">
                            <template x-for="o in ops()" :key="o.v">
                                <button type="button" @click="op = o.v"
                                        class="tw:flex tw:items-center tw:gap-[1em] tw:px-[1em] tw:py-[0.7em] tw:rounded-[0.6em] tw:text-[0.85em] tw:font-bold tw:transition-all tw:border"
                                        :class="op === o.v
                                            ? 'tw:bg-blue-600 tw:dark:bg-blue-600 tw:text-white! tw:dark:text-white! tw:border-blue-600 tw:shadow-md'
                                            : 'tw:bg-neutral-50 tw:dark:bg-neutral-800 tw:text-neutral-600! tw:dark:text-neutral-300! tw:border-neutral-100 tw:dark:border-neutral-700 tw:hover:bg-neutral-100 tw:dark:hover:bg-neutral-700'">
                                    <span class="tw:w-[1.2em] tw:text-center tw:opacity-70" x-text="o.s"></span>
                                    <span x-text="o.l"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="!nullary()">
                        <span class="tw:block tw:text-[0.75em] tw:font-black tw:text-neutral-400 tw:uppercase tw:tracking-widest tw:mb-[1em]">{{ __('Value') }}</span>

                        {{-- Case 1: Standard Inputs --}}
                        <template x-if="['text','email','number','date'].includes(current?.type) && !current?.endpoint">
                            <input x-ref="valInput" :type="current?.type" x-model="value" @keydown.enter="apply()"
                                   class="tw:w-full tw:px-[1em] tw:py-[0.8em] tw:text-[0.95em] tw:bg-neutral-50 tw:dark:bg-neutral-800 tw:border tw:border-neutral-200 tw:dark:border-neutral-700 tw:rounded-[0.6em] tw:focus:ring-2 tw:focus:ring-blue-500/50 tw:focus:border-blue-500 tw:outline-none tw:text-neutral-900! tw:dark:text-neutral-100! tw:transition-all" />
                        </template>

                        {{-- Case 2: Remote Search --}}
                        <template x-if="current?.endpoint">
                            <div class="tw:space-y-[0.8em]">
                                <div class="tw:relative">
                                    <input x-ref="remoteSearch"
                                           type="text"
                                           x-model.debounce.300ms="searchQuery"
                                           @input="fetchRemote()"
                                           placeholder="{{ __('Type to search...') }}"
                                           class="tw:w-full tw:px-[1em] tw:py-[0.8em] tw:text-[0.95em] tw:bg-neutral-50 tw:dark:bg-neutral-800 tw:border tw:border-neutral-200 tw:dark:border-neutral-700 tw:rounded-[0.6em] tw:focus:ring-2 tw:focus:ring-blue-500/50 tw:focus:border-blue-500 tw:outline-none tw:text-neutral-900! tw:dark:text-neutral-100! tw:transition-all" />

                                    {{-- Spinner --}}
                                    <div x-show="isLoading" class="tw:absolute tw:right-[1em] tw:top-1/2 tw:-translate-y-1/2">
                                        <svg class="tw:animate-spin tw:h-[1.2em] tw:w-[1.2em] tw:text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="tw:opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="tw:opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>

                                {{-- Results List --}}
                                <div class="tw:max-h-[12em] tw:overflow-y-auto tw:flex tw:flex-col tw:gap-[0.4em] tw:scrollbar-thin tw:dark:scrollbar-thumb-neutral-700">
                                    <template x-for="opt in remoteOptions" :key="opt.id || opt">
                                        <button type="button"
                                                @click="current.type === 'multi-select' ? toggleMulti(opt.id || opt) : value = (opt.id || opt)"
                                                class="tw:relative tw:px-[1.2em] tw:py-[0.7em] tw:rounded-[0.6em] tw:text-[0.9em] tw:font-bold tw:transition-all tw:border tw:text-left tw:hover:bg-neutral-100 tw:dark:hover:bg-neutral-700"
                                                :class="(current.type === 'multi-select' ? value.includes(opt.id || opt) : value === (opt.id || opt))
                                ? 'tw:bg-blue-50 tw:dark:bg-blue-900/30 tw:text-blue-600! tw:dark:text-blue-300! tw:border-blue-200 tw:dark:border-blue-800'
                                : 'tw:bg-neutral-50 tw:dark:bg-neutral-800 tw:text-neutral-600! tw:dark:text-neutral-300! tw:border-transparent'">
                                            <span x-text="opt.text || opt"></span>
                                            <span x-show="current.type === 'multi-select' ? value.includes(opt.id || opt) : value === (opt.id || opt)"
                                                  class="tw:absolute tw:right-[1.2em] tw:top-1/2 tw:-translate-y-1/2 tw:text-[0.85em] tw:text-blue-600! tw:dark:text-blue-400!"
                                                  aria-hidden="true">&check;</span>
                                        </button>
                                    </template>
                                    <div x-show="searchQuery.length >= 2 && remoteOptions.length === 0 && !isLoading"
                                         class="tw:text-center tw:py-[1em] tw:text-[0.8em] tw:text-neutral-500! tw:italic">
                                        {{ __('No results found.') }}
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Case 3: Static Selects --}}
                        <template x-if="['select', 'multi-select'].includes(current?.type) && !current?.endpoint">
                            <div class="tw:flex tw:flex-col tw:gap-[0.4em]">
                                <template x-for="opt in (current?.options ?? [])" :key="opt">
                                    <button type="button" @click="current.type === 'multi-select' ? toggleMulti(opt) : value = opt"
                                            @keydown.space.stop
                                            class="tw:relative tw:px-[1.2em] tw:py-[0.7em] tw:rounded-[0.6em] tw:text-[0.9em] tw:font-bold tw:transition-all tw:border tw:text-left tw:hover:bg-neutral-100 tw:dark:hover:bg-neutral-700"
                                            :class="(current.type === 'multi-select' ? value.includes(opt) : value === opt)
                                                ? 'tw:bg-blue-50 tw:dark:bg-blue-900/30 tw:text-blue-600! tw:dark:text-blue-300! tw:border-blue-200 tw:dark:border-blue-800'
                                                : 'tw:bg-neutral-50 tw:dark:bg-neutral-800 tw:text-neutral-600! tw:dark:text-neutral-300! tw:border-transparent'">
                                        <span x-text="opt"></span>
                                        <span x-show="current.type === 'multi-select' ? value.includes(opt) : value === opt"
                                              class="tw:absolute tw:right-[1.2em] tw:top-1/2 tw:-translate-y-1/2 tw:text-[0.85em] tw:text-blue-600! tw:dark:text-blue-400!"
                                              aria-hidden="true">&check;</span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- Case 4: Boolean (Yes/No Toggle) --}}
                        <template x-if="current?.type === 'boolean'">
                            <div class="tw:grid tw:grid-cols-2 tw:gap-[0.8em]">
                                <button type="button"
                                        @click="value = 1"
                                        class="tw:flex tw:items-center tw:justify-center tw:gap-[0.5em] tw:px-[1em] tw:py-[1em] tw:rounded-[0.6em] tw:text-[0.95em] tw:font-bold tw:transition-all tw:border"
                                        :class="value == 1
                    ? 'tw:bg-blue-50 tw:dark:bg-blue-900/30 tw:text-blue-600! tw:dark:text-blue-300! tw:border-blue-200 tw:dark:border-blue-800'
                    : 'tw:bg-neutral-50 tw:dark:bg-neutral-800 tw:text-neutral-600! tw:dark:text-neutral-300! tw:border-transparent'">
                                    <span x-show="value == 1" class="tw:text-blue-600! tw:dark:text-blue-400!">&check;</span>
                                    {{ __('Yes') }}
                                </button>

                                <button type="button"
                                        @click="value = 0"
                                        class="tw:flex tw:items-center tw:justify-center tw:gap-[0.5em] tw:px-[1em] tw:py-[1em] tw:rounded-[0.6em] tw:text-[0.95em] tw:font-bold tw:transition-all tw:border"
                                        :class="value == 0 && value !== ''
                    ? 'tw:bg-blue-50 tw:dark:bg-blue-900/30 tw:text-blue-600! tw:dark:text-blue-300! tw:border-blue-200 tw:dark:border-blue-800'
                    : 'tw:bg-neutral-50 tw:dark:bg-neutral-800 tw:text-neutral-600! tw:dark:text-neutral-300! tw:border-transparent'">
                                    <span x-show="value == 0 && value !== ''" class="tw:text-blue-600! tw:dark:text-blue-400!">&check;</span>
                                    {{ __('No') }}
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="tw:flex tw:items-center tw:justify-between tw:px-[1.5em] tw:py-[1.2em] tw:bg-neutral-50/50 tw:dark:bg-neutral-950/40 tw:rounded-b-[1em] tw:border-t tw:border-neutral-100 tw:dark:border-neutral-800">
                    <button type="button" x-show="isActive(current?.key)" @click="remove(current.key); close()"
                            class="tw:h-[2.6em] tw:px-[1.2em] tw:bg-white tw:dark:bg-neutral-900 tw:text-neutral-600! tw:dark:text-neutral-400! tw:border tw:border-neutral-200 tw:dark:border-neutral-700 tw:rounded-[0.6em] tw:text-[0.8em] tw:font-bold tw:transition-all tw:hover:bg-red-50 tw:dark:hover:bg-red-950/40 tw:hover:text-red-600! tw:dark:hover:text-red-500! tw:hover:border-red-100 tw:dark:hover:border-red-900/50">
                        {{ __('Remove') }}
                    </button>
                    <div x-show="!isActive(current?.key)"></div>
                    <div class="tw:flex tw:gap-[1em]">
                        <button type="button" @click="close()"
                                class="tw:h-[2.6em] tw:px-[1.2em] tw:bg-white tw:dark:bg-neutral-900 tw:border tw:border-neutral-200 tw:dark:border-neutral-700 tw:text-[0.8em] tw:font-bold tw:text-neutral-600! tw:dark:text-neutral-400! tw:rounded-[0.6em] tw:hover:bg-neutral-50 tw:dark:hover:bg-neutral-800 tw:transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" @click="apply()"
                                :disabled="!nullary() && (current?.type === 'multi-select' ? !value.length : !value)"
                                class="tw:h-[2.6em] tw:px-[1.8em] tw:text-[0.8em] tw:font-bold tw:bg-blue-600 tw:dark:bg-blue-600 tw:text-white! tw:dark:text-white! tw:rounded-[0.6em] tw:shadow-md tw:hover:bg-blue-700 tw:dark:hover:bg-blue-500 tw:disabled:opacity-20 tw:transition-all">
                            {{ __('Apply') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
