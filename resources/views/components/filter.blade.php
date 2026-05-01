@props([
    'fields' => [],
    'reload' => false,
    'hide' => false,
    'initial' => [],
])

<div
    {{ $attributes->merge(['class' => 'tw:relative tw:flex tw:items-center tw:text-[13px] tw:max-w-full' . ($hide ? ' tw:hidden' : '')]) }}
    x-data="filterBarComponent({
        fields: @js($fields),
        reload: @js($reload),
        initial: @js($initial)
    })"
    @click.outside="close()"
    @keydown.escape.window="close()">

    {{-- Main Bar --}}
    <div
        class="tw:flex tw:items-stretch tw:h-[2.4em] tw:rounded-[0.5em] tw:border tw:border-neutral-300 tw:dark:border-dark-gray-300 tw:bg-white tw:dark:bg-dark-gray-500 tw:font-mono tw:shadow-xs tw:max-w-full">

        {{-- LEFT SECTION: Options Dropdown --}}
        <div class="tw:relative tw:flex tw:items-stretch">
            <button type="button"
                    :title="filters.length ? '{{ __('Filter options') }}' : '{{ __('Open filter menu') }}'"
                    @click.stop="showAdd = false; showOptions = !showOptions"
                    class="tw:shrink-0 tw:flex tw:items-center tw:gap-[0.5em] tw:px-[1em] tw:h-full tw:transition-colors tw:border-r tw:border-neutral-200 tw:dark:border-dark-gray-300 tw:tracking-widest tw:rounded-l-[0.5em] tw:hover:bg-neutral-50 tw:dark:hover:bg-dark-gray-400">
                <div class="tw:relative tw:flex tw:items-center">
                    {{-- Restored Original Filter Icon --}}
                    <svg class="tw:w-[1.1em] tw:h-[1.1em] tw:text-neutral-500! tw:dark:text-dark-white-400!" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 2.5h10M3 6h6M5 9.5h2" stroke-linecap="round"/>
                    </svg>
                    <span x-show="filters.length"
                          class="tw:absolute tw:top-[-0.2em] tw:right-[-0.3em] tw:w-[0.5em] tw:h-[0.5em] tw:bg-neutral-600 tw:dark:bg-dark-white-300 tw:rounded-full tw:ring-1 tw:ring-white tw:dark:ring-dark-gray-500"></span>
                </div>
                <span class="tw:uppercase tw:font-bold" x-show="!filters.length">{{ __('Filter') }}</span>
                <span class="tw:text-[0.85em]" x-show="filters.length" x-text="filters.length"></span>
            </button>

            {{-- Left Options Menu --}}
            <div x-show="showOptions" x-cloak x-transition @click.stop
                 class="tw:absolute tw:top-full tw:left-0 tw:mt-[0.5em] tw:w-[15em] tw:bg-white tw:dark:bg-dark-gray-400 tw:border tw:border-neutral-200 tw:dark:border-dark-gray-200 tw:rounded-[0.6em] tw:shadow-xl tw:z-50 tw:py-[0.5em]">

                <div class="tw:px-[1.2em] tw:py-[0.6em] tw:text-[0.7em] tw:font-black tw:text-neutral-400 tw:dark:text-dark-white-400 tw:uppercase tw:tracking-widest">{{ __('Settings') }}</div>

                {{-- Clear (Top) --}}
                <button type="button" @click="clearAll(); showOptions = false"
                        class="tw:flex tw:items-center tw:gap-[0.7em] tw:w-full tw:px-[1.2em] tw:py-[0.7em] tw:text-left tw:hover:bg-neutral-50 tw:dark:hover:bg-dark-gray-300 tw:transition-colors tw:text-red-600! tw:dark:text-red-400! tw:font-bold">
                    <i class="fas fa-trash-alt"></i>
                    <span>{{ __('Clear All') }}</span>
                </button>

                {{-- Save (Bottom) --}}
                <button type="button" @click="savePreferences(); showOptions = false"
                        class="tw:flex tw:items-center tw:gap-[0.7em] tw:w-full tw:px-[1.2em] tw:py-[0.7em] tw:text-left tw:hover:bg-neutral-50 tw:dark:hover:bg-dark-gray-300 tw:transition-colors tw:text-neutral-600! tw:dark:text-dark-white-200! tw:font-bold">
                    <i class="fas fa-save"></i>
                    <span>{{ __('Save Preferences') }}</span>
                </button>
            </div>
        </div>

        {{-- MIDDLE SECTION: Chips --}}
        <div class="tw:flex tw:items-stretch tw:overflow-x-auto tw:scrollbar-none tw:flex-nowrap">
            <template x-for="f in filters" :key="f.key">
                <div
                    class="tw:shrink-0 tw:relative tw:group tw:flex tw:items-stretch tw:h-full tw:border-r tw:border-neutral-200 tw:dark:border-dark-gray-300"
                    role="listitem">
                    <button type="button" title="{{ __('Edit filter') }}"
                            @click="open(fields.find(field => field.key === f.key))"
                            class="tw:flex tw:items-center tw:h-full tw:px-[1em] tw:gap-[0.5em] tw:transition-colors tw:hover:bg-neutral-50 tw:dark:hover:bg-dark-gray-400 tw:whitespace-nowrap">
                        <span class="tw:font-bold tw:text-neutral-900! tw:dark:text-dark-white-100!"
                              x-text="f.label"></span>
                        <span class="tw:text-neutral-400! tw:dark:text-dark-white-400!" x-text="f.sym"></span>
                        <span x-show="f.display" class="tw:font-bold tw:text-neutral-700! tw:dark:text-dark-white-200!"
                              x-text="Array.isArray(f.display) ? f.display.join(', ') : f.display">
                        </span>
                    </button>
                    <button type="button" title="{{ __('Remove filter') }}" @click.stop="remove(f.key)"
                            class="tw:h-full tw:w-0 tw:group-hover:w-[2.4em] tw:flex tw:items-center tw:justify-center tw:bg-neutral-100 tw:dark:bg-dark-gray-400 tw:text-neutral-500! tw:dark:text-dark-white-400! tw:transition-all tw:duration-200 tw:ease-in-out tw:overflow-hidden tw:text-[1.2em] tw:hover:text-red-600! tw:dark:hover:text-red-400!">
                        &times;
                    </button>
                </div>
            </template>
        </div>

        {{-- RIGHT SECTION: Plus Button & Dropdown --}}
        <div class="tw:shrink-0 tw:relative tw:flex tw:items-stretch">
            <button type="button" title="{{ __('Add new filter') }}" @click.stop="showOptions = false; showAdd = !showAdd"
                    @keydown.arrow-down.prevent="navDropdown('next')"
                    @keydown.arrow-up.prevent="navDropdown('prev')"
                    class="tw:w-[2.4em] tw:h-full tw:flex tw:items-center tw:justify-center tw:text-[1.4em] tw:text-neutral-400! tw:dark:text-dark-white-400! tw:hover:text-neutral-900! tw:dark:hover:text-dark-white-100! tw:hover:bg-neutral-50 tw:dark:hover:bg-dark-gray-400 tw:transition-colors tw:rounded-r-[0.5em]">
                +
            </button>

            {{-- Fields Selector (Pinned to Right) --}}
            <div x-show="showAdd" x-cloak x-transition @click.stop
                 :class="filters.length > 0 ? 'tw:right-0' : 'tw:left-0'"
                 class="tw:absolute tw:top-full tw:mt-[0.5em] tw:w-[15em] tw:max-w-[90vw] tw:bg-white tw:dark:bg-dark-gray-400 tw:border tw:border-neutral-200 tw:dark:border-dark-gray-200 tw:rounded-[0.6em] tw:shadow-xl tw:z-50 tw:py-[0.5em]">
                <div
                    class="tw:px-[1.2em] tw:py-[0.6em] tw:text-[0.7em] tw:font-black tw:text-neutral-400 tw:dark:text-dark-white-400 tw:uppercase tw:tracking-widest">{{ __('Select Field') }}</div>
                <template x-for="(field, index) in fields" :key="field.key">
                    <button type="button" @click="open(field)"
                            :class="[isActive(field.key) ? 'tw:text-blue-600! tw:dark:text-blue-400! tw:bg-blue-50/50 tw:dark:bg-blue-900/20' : 'tw:text-neutral-600! tw:dark:text-dark-white-200!', highlightedIndex === index ? 'tw:bg-neutral-100 tw:dark:bg-dark-gray-300' : '']"
                            class="tw:flex tw:items-center tw:justify-between tw:w-full tw:px-[1.2em] tw:py-[0.7em] tw:text-left tw:hover:bg-neutral-50 tw:dark:hover:bg-dark-gray-300 tw:transition-colors">
                        <span x-text="field.label"></span>
                        <svg x-show="isActive(field.key)" class="tw:w-[1em] tw:h-[1em]" viewBox="0 0 10 10" fill="none"
                             stroke="currentColor" stroke-width="2.5">
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
                 class="tw:w-full tw:max-w-[25em] tw:bg-white tw:dark:bg-dark-gray-500 tw:border tw:border-neutral-200 tw:dark:border-dark-gray-300 tw:rounded-[1em] tw:shadow-2xl tw:font-mono"
                 role="dialog" aria-modal="true">

                {{-- Header --}}
                <div
                    class="tw:px-[1.5em] tw:py-[1.2em] tw:border-b tw:border-neutral-100 tw:dark:border-dark-gray-300 tw:bg-blue-50/20 tw:dark:bg-dark-gray-400 tw:flex tw:items-center tw:justify-between tw:rounded-t-[1em]">
                    <div class="tw:text-[1.15em] tw:font-black tw:text-neutral-900! tw:dark:text-dark-white-100!">
                        <span x-text="current?.label"></span>
                        <span
                            class="tw:ml-[0.3em] tw:text-[0.65em] tw:font-normal tw:text-blue-600! tw:dark:text-blue-500! tw:uppercase">{{ __('Filter') }}</span>
                    </div>
                    <button type="button" title="{{ __('Close dialog') }}" @click="close()"
                            class="tw:text-neutral-400! tw:dark:text-dark-white-400! tw:transition-colors tw:hover:text-red-500! tw:dark:hover:text-red-500! tw:text-[1.5em]">
                        &times;
                    </button>
                </div>

                {{-- Body --}}
                <div class="tw:p-[1.5em] tw:space-y-[1.8em]">
                    <div>
                        <span
                            class="tw:block tw:text-[0.75em] tw:font-black tw:text-neutral-400 tw:dark:text-dark-white-400 tw:uppercase tw:tracking-widest tw:mb-[1em]">{{ __('Condition') }}</span>
                        <div class="tw:grid tw:grid-cols-2 tw:gap-[0.5em]">
                            <template x-for="o in ops()" :key="o.v">
                                <button type="button" @click="op = o.v"
                                        class="tw:flex tw:items-center tw:gap-[1em] tw:px-[1em] tw:py-[0.7em] tw:rounded-[0.6em] tw:text-[0.85em] tw:font-bold tw:transition-all tw:border"
                                        :class="op === o.v
                                            ? 'tw:bg-blue-600 tw:dark:bg-blue-600 tw:text-white! tw:dark:text-white! tw:border-blue-600 tw:shadow-md'
                                            : 'tw:bg-neutral-50 tw:dark:bg-dark-gray-400 tw:text-neutral-600! tw:dark:text-dark-white-200! tw:border-neutral-100 tw:dark:border-dark-gray-300 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300'">
                                    <span class="tw:w-[1.2em] tw:text-center tw:opacity-70" x-text="o.s"></span>
                                    <span x-text="o.l"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="!nullary()">
                        <span
                            class="tw:block tw:text-[0.75em] tw:font-black tw:text-neutral-400 tw:dark:text-dark-white-400 tw:uppercase tw:tracking-widest tw:mb-[1em]">{{ __('Value') }}</span>

                        {{-- Text/Number/Date --}}
                        <template x-if="['text','email','number','date'].includes(current?.type) && !current?.endpoint">
                            <input x-ref="valInput" :type="current?.type" x-model="value" @input="display = value"
                                   @keydown.enter="apply()"
                                   class="tw:w-full tw:px-[1em] tw:py-[0.8em] tw:text-[0.95em] tw:bg-neutral-50 tw:dark:bg-dark-gray-400 tw:border tw:border-neutral-200 tw:dark:border-dark-gray-300 tw:rounded-[0.6em] tw:focus:ring-2 tw:focus:ring-blue-500/50 tw:focus:border-blue-500 tw:outline-none tw:text-neutral-900! tw:dark:text-dark-white-100! tw:transition-all"/>
                        </template>

                        {{-- Remote Search --}}
                        <template x-if="current?.endpoint">
                            <div @remote-selected.stop="current.type === 'multi-select' ? toggleMulti($event.detail.id, $event.detail.text) : (value = $event.detail.id, display = $event.detail.text, apply())">
                                <x-remote-dropdown ::endpoint="current.endpoint" ::params="current.params || {}" ::multi="current.type === 'multi-select'"/>
                            </div>
                        </template>

                        {{-- Static Selects & Multi-Selects --}}
                        <template x-if="['select', 'multi-select'].includes(current?.type) && !current?.endpoint">
                            <div class="tw:flex tw:flex-col tw:gap-[0.5em]">
                                <template x-for="opt in getNormalizedOptions()" :key="opt.value">
                                    <button type="button"
                                            @click="current.type === 'multi-select' ? toggleMulti(opt.value, opt.label) : (value = opt.value, display = opt.label, apply())"
                                            class="tw:flex tw:items-center tw:justify-between tw:w-full tw:px-[1.2em] tw:py-[0.8em] tw:rounded-[0.6em] tw:text-[0.85em] tw:font-bold tw:transition-all tw:border tw:text-left"
                                            :class="(current.type === 'multi-select' ? value.includes(opt.value) : value === opt.value)
                                              ? 'tw:bg-blue-600 tw:text-white! tw:border-blue-600 tw:shadow-md'
                                              : 'tw:bg-neutral-50 tw:dark:bg-dark-gray-400 tw:text-neutral-600! tw:dark:text-dark-white-200! tw:border-neutral-100 tw:dark:border-dark-gray-300 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300'">

                                        <span x-text="opt.label"></span>

                                        <svg
                                            x-show="current.type === 'multi-select' ? value.includes(opt.value) : value === opt.value"
                                            class="tw:w-[1.1em] tw:h-[1.1em] tw:transition-transform"
                                            viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M1.5 5l3 3 4-5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- Boolean Toggle --}}
                        <template x-if="current?.type === 'boolean'">
                            <div class="tw:grid tw:grid-cols-2 tw:gap-[0.8em]">
                                <button type="button" @click="value = 1; display = 'Yes'; apply()"
                                        class="tw:py-[1em] tw:rounded-[0.6em] tw:font-bold tw:border tw:transition-all"
                                        :class="value == 1
                                            ? 'tw:bg-blue-50 tw:dark:bg-blue-900/30 tw:text-blue-600! tw:dark:text-blue-400! tw:border-blue-200 tw:dark:border-blue-800'
                                            : 'tw:bg-neutral-50 tw:dark:bg-dark-gray-400 tw:text-neutral-600! tw:dark:text-dark-white-200! tw:border-transparent tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300'">
                                    {{ __('Yes') }}
                                </button>
                                <button type="button" @click="value = 0; display = 'No'; apply()"
                                        class="tw:py-[1em] tw:rounded-[0.6em] tw:font-bold tw:border tw:transition-all"
                                        :class="value == 0 && value !== ''
                                            ? 'tw:bg-blue-50 tw:dark:bg-blue-900/30 tw:text-blue-600! tw:dark:text-blue-400! tw:border-blue-200 tw:dark:border-blue-800'
                                            : 'tw:bg-neutral-50 tw:dark:bg-dark-gray-400 tw:text-neutral-600! tw:dark:text-dark-white-200! tw:border-transparent tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300'">
                                    {{ __('No') }}
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Footer --}}
                <div
                    class="tw:flex tw:items-center tw:justify-between tw:px-[1.5em] tw:py-[1.2em] tw:bg-neutral-50/50 tw:dark:bg-dark-gray-400 tw:rounded-b-[1em] tw:border-t tw:border-neutral-100 tw:dark:border-dark-gray-300">
                    <button type="button" x-show="isActive(current?.key)" @click="remove(current.key); close()"
                            class="tw:h-[2.6em] tw:px-[1.2em] tw:bg-white tw:dark:bg-dark-gray-500 tw:text-neutral-600! tw:dark:text-red-400! tw:border tw:border-neutral-200 tw:dark:border-dark-gray-200 tw:rounded-[0.6em] tw:text-[0.8em] tw:font-bold tw:transition-colors tw:hover:bg-red-50 tw:dark:hover:bg-red-900/20">
                        {{ __('Remove') }}
                    </button>
                    <div x-show="!isActive(current?.key)"></div>
                    <div class="tw:flex tw:gap-[1em]">
                        <button type="button" @click="close()"
                                class="tw:h-[2.6em] tw:px-[1.2em] tw:bg-white tw:dark:bg-dark-gray-500 tw:border tw:border-neutral-200 tw:dark:border-dark-gray-300 tw:text-[0.8em] tw:font-bold tw:text-neutral-600! tw:dark:text-dark-white-300! tw:rounded-[0.6em] tw:transition-colors tw:hover:bg-neutral-50 tw:dark:hover:bg-dark-gray-400">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" @click="apply()"
                                :disabled="!nullary() && (current?.type === 'multi-select' ? !value.length : value === '' || value === null)"
                                class="tw:h-[2.6em] tw:px-[1.8em] tw:text-[0.8em] tw:font-bold tw:bg-blue-600 tw:text-white! tw:rounded-[0.6em] tw:transition-all tw:hover:bg-blue-700 tw:dark:hover:bg-blue-500 tw:disabled:opacity-20">
                            {{ __('Apply') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
