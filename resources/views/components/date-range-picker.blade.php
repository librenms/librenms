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
            <div class="tw:grid tw:grid-cols-[repeat(auto-fit,minmax(40px,max-content))] tw:gap-2 tw:justify-center tw:mb-3 tw:dark:text-white">
                <template x-for="(preset, idx) in presets">
                    <button type="button"
                            class="preset-btn tw:px-3 tw:py-2 tw:text-sm tw:hover:bg-gray-200 tw:dark:hover:bg-gray-600 tw:rounded-md tw:transition-colors tw:min-w-10 tw:dark:text-gray-400"
                            :class="isPresetSelected(preset.value) ? 'tw:bg-blue-500 tw:text-white tw:dark:text-white' : 'tw:bg-gray-100 tw:dark:bg-gray-700'"
                            x-on:click="setRange(preset.value, 'now')"
                            x-text="preset.label"
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

@pushOnce('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dateRangePicker', () => ({
            open: false,
            startDate: '',
            endDate: '',
            startTime: '',
            endTime: '',
            placeholder: 'Select date range...',
            reload: false,
            isInitializing: false,
            relativeStartSeconds: null,
            relativeEndSeconds: null,
            presets: [
                { value: '6h',  label: '6 Hours',  relative: 'Last 6 hours' },
                { value: '24h', label: '24 Hours', relative: 'Last 24 hours' },
                { value: '48h', label: '48 Hours', relative: 'Last 48 hours' },
                { value: '1w',  label: '1 Week',   relative: 'Last week' },
                { value: '2w',  label: '2 Weeks',  relative: 'Last 2 weeks' },
                { value: '1mo', label: '1 Month',  relative: 'Last month' },
                { value: '2mo', label: '2 Months', relative: 'Last 2 months' },
                { value: '1y',  label: '1 Year',   relative: 'Last year' },
                { value: '2y',  label: '2 Years',  relative: 'Last 2 years' },
            ],

            get start() {
                if (this.relativeStartSeconds !== null) {
                    return LibreNMS.Date.now().minus({ seconds: this.relativeStartSeconds });
                }

                if (this.startDate) {
                    return LibreNMS.Date.parse(this.startTime ? `${this.startDate}T${this.startTime}` : `${this.startDate}T00:00`);
                }

                return null;
            },

            get end() {
                if (this.relativeStartSeconds !== null || !this.endDate) {
                    return null;
                }

                if (!this.endTime) {
                    // No time supplied, include the entire day
                    return LibreNMS.Date.parse(`${this.endDate}T23:59:59`);
                }

                return LibreNMS.Date.parse(`${this.endDate}T${this.endTime}`);
            },

            get outStartString() {
                if (this.relativeStartSeconds !== null) {
                    return LibreNMS.Date.toShortOffset(this.relativeStartSeconds);
                }

                return LibreNMS.Date.toUrl(this.start);
            },

            get outEndString() {
                if (this.relativeEndSeconds !== null) {
                    return LibreNMS.Date.toShortOffset(this.relativeEndSeconds);
                }

                if (this.relativeStartSeconds !== null) {
                    return '';
                }

                return LibreNMS.Date.toUrl(this.end);
            },

            get hasValue() {
                return !!(this.start || this.end);
            },

            get preset() {
                return this.presets.find(p => this.isPresetSelected(p.value));
            },

            get displayText() {
                if (this.relativeStartSeconds !== null) {
                    // Use the matched preset's label; otherwise derive one from the raw offset.
                    const matched = this.preset;
                    const rel = matched ? matched.relative : this.relativeLabelFromSeconds(this.relativeStartSeconds);
                    if (rel) {
                        return rel;
                    }
                }

                const startString = this.formatDate(this.start, this.startTime);
                const endString = this.formatDate(this.end, this.endTime);

                if (startString && endString) {
                    return `${startString} to ${endString}`;
                } else if (startString) {
                    return `From ${startString} to now`;
                } else if (endString) {
                    return `Until ${endString}`;
                }

                return this.placeholder;
            },

            init() {
                this.isInitializing = true;
                // Attach API for external JS control
                this.$el.dateRangePicker = {
                    get: () => this.getRange(),
                    set: (start, end) => this.setRange(start, end),
                    clear: () => this.clearRange(),
                    open: () => this.openDropdown(),
                    close: () => this.closeDropdown(),
                };

                this.reload = this.$el.dataset.reload === 'true';

                if (this.$el.dataset.placeholder) this.placeholder = this.$el.dataset.placeholder;

                this.setRange(this.$el.dataset.start, this.$el.dataset.end);
                this.isInitializing = false;
            },

            closeDropdown() {
                this.open = false;
            },

            openDropdown() {
                this.open = true;
            },

            toggleDropdown() {
                this.open = !this.open;
            },

            applyRange() {
                this.relativeStartSeconds = null;
                this.relativeEndSeconds = null;
                this.closeDropdown();
                this.emitChange();
            },

            setRange(start = null, end = null) {
                if (start === null && end === null) {
                    return;
                }

                if (start !== null) {
                    [this.startDate, this.startTime, this.relativeStartSeconds] = this.parseDateTime(start);
                }

                if (end !== null) {
                    [this.endDate, this.endTime, this.relativeEndSeconds] = this.parseDateTime(end);
                }

                this.closeDropdown();
                this.emitChange();
            },

            clearRange() {
                this.startDate = '';
                this.endDate = '';
                this.startTime = '';
                this.endTime = '';
                this.relativeStartSeconds = null;
                this.relativeEndSeconds = null;
                this.emitChange();
            },

            getRange() {
                return {
                    start: this.start,
                    end: this.end,
                    relativeStartSeconds: this.relativeStartSeconds,
                    relativeEndSeconds: this.relativeEndSeconds,
                };
            },

            parseDateTime(dateInput) {
                // Returns [date, time, relativeSeconds]
                if (typeof dateInput !== 'string') {
                    return ['', '', null];
                }

                const input = dateInput.trim();

                if (!input || input === 'now') {
                    return ['', '', null];
                }

                if (this.isRelative(input)) {
                    return ['', '', this.parseRelativeOffset(input)];
                }

                const dt = LibreNMS.Date.parse(input);

                if (!dt || !dt.isValid) {
                    return ['', '', null];
                }

                const hasTime = !(dt.hour === 0 && dt.minute === 0 && dt.second === 0);
                const pickerString = LibreNMS.Date.toPicker(dt);
                const [datePart, timePart] = pickerString.split('T');

                return [datePart, hasTime ? timePart : '', null];
            },

            // Determine if a string is a relative time like 10m, -2h, 7d, 1w, 1y
            isRelative(input) {
                if (!input || typeof input !== 'string') return false;
                return /^[-+]?\d+\s*(s|m|h|d|w|mo|y)$/.test(input.trim());
            },

            // Returns seconds offset from now: positive for past (e.g., 10m => 600), negative for future (+1h => -3600)
            parseRelativeOffset(input) {
                if (!this.isRelative(input)) return null;
                const m = input.trim().match(/^([-+]?)(\d+)\s*(s|m|h|d|w|mo|y)$/);
                if (!m) return null;
                const sign = m[1];
                const num = parseInt(m[2], 10);
                const unit = m[3];
                const map = { s: 1, m: 60, h: 3600, d: 86400, w: 604800, mo: 2592000, y: 31536000 };
                const seconds = num * (map[unit] || 0);
                return sign === '+' ? -seconds : seconds; // '+' => future => negative
            },

            // Fallback "Last ..." label for a past offset not covered by the preset list
            // (e.g. a hand-edited ?from=-5d). Presets supply their own label above.
            relativeLabelFromSeconds(seconds) {
                if (!seconds || seconds <= 0) return '';
                const units = [
                    { unit: 'year', sec: 31536000 },
                    { unit: 'month', sec: 2592000 },
                    { unit: 'week', sec: 604800 },
                    { unit: 'day', sec: 86400 },
                    { unit: 'hour', sec: 3600 },
                    { unit: 'minute', sec: 60 },
                    { unit: 'second', sec: 1 },
                ];
                const u = units.find(u => seconds % u.sec === 0 && seconds >= u.sec) || units.find(u => seconds >= u.sec) || units[units.length - 1];
                const value = Math.max(1, Math.round(seconds / u.sec));
                return value === 1 ? `Last ${u.unit}` : `Last ${value} ${u.unit}s`;
            },

            // Check if a preset is selected based on seconds value
            isPresetSelected(preset) {
                const sec = this.parseRelativeOffset(preset);
                return this.relativeStartSeconds !== null && sec !== null && sec === this.relativeStartSeconds && !this.endDate && !this.endTime;
            },

            formatDate(dt, timeString) {
                if (!dt || !dt.isValid) return '';

                const opts = !!(timeString) || !(dt.hour === 0 && dt.minute === 0 && dt.second === 0)
                    ? { month: 'numeric', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric' }
                    : { month: 'numeric', day: 'numeric', year: 'numeric' };

                return LibreNMS.Date.display(dt, opts);
            },

            triggerReload() {
                if (this.isInitializing || !this.reload) return;
                LibreNMS.Url.updateDateRange(this.relativeStartSeconds, this.relativeEndSeconds, this.start, this.end);
            },

            emitChange() {
                this.$el.dispatchEvent(new CustomEvent('date-range-changed', {
                    detail: this.getRange(),
                    bubbles: true
                }));
                this.triggerReload();
            }
        }));
    });
</script>
@endPushOnce
