@props([
    'start' => '',
    'end' => '',
    'outputFormat' => '', // iso, timestamp, or format string
    'presets' => true,
    'placeholder' => 'Select date range...',
    'class' => 'tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-md'
])

<div {{ $attributes->merge(['class' => 'tw:relative']) }}
     x-data="dateRangePicker"
     x-on:click.outside="closeDropdown"
     data-start="{{ $start }}"
     data-end="{{ $end }}"
     data-presets=" {{ is_array($presets) ? implode(',', $presets) : (string) $presets }}"
     data-output-format="{{ $outputFormat }}"
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
         style="display: none;">
        @if($presets)
            <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mb-3 tw:dark:text-white">
                <template x-for="(preset, idx) in presets">
                    <button type="button"
                            class="preset-btn tw:px-3 tw:py-2 tw:text-sm tw:hover:bg-gray-200 tw:dark:hover:bg-gray-600 tw:rounded-md tw:transition-colors tw:min-w-[40px] tw:dark:text-gray-400"
                            :class="isPresetSelected(preset) ? 'tw:bg-blue-500 tw:text-white tw:dark:text-white' : 'tw:bg-gray-100 tw:dark:bg-gray-700'"
                            x-on:click="setRange(preset, 'now')"
                            x-text="preset"
                    ></button>
                </template>
            </div>
        @endif
        <div class="tw:mb-3">
            <div class="tw:flex-1">
                <label class="tw:block tw:text-xs tw:text-gray-600 tw:dark:text-gray-400 tw:mb-1">From</label>
                <div class="tw:flex tw:flex-wrap tw:gap-1 tw:dark:text-dark-gray-400">
                    <input type="date" x-model="startDate" class="tw:flex-1 tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white">
                    <input type="time" x-model="startTime" class="tw:min-w-fit tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white">
                </div>
            </div>
            <div class="tw:flex-1">
                <label class="tw:block tw:text-xs tw:text-gray-600 tw:dark:text-gray-400 tw:mb-1">To</label>
                <div class="tw:flex tw:flex-wrap tw:gap-1 tw:dark:text-dark-gray-400">
                    <input type="date" x-model="endDate" class="tw:flex-1 tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white">
                    <input type="time" x-model="endTime" class="tw:min-w-fit tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white">
                </div>
            </div>
        </div>
        <div class="tw:flex tw:justify-between tw:dark:text-white">
            <button type="button" x-on:click="clearRange"
                    class="tw:px-3 tw:py-1 tw:text-sm tw:text-gray-500 tw:dark:text-gray-400 tw:hover:text-gray-700 tw:dark:hover:text-gray-300">Clear</button>
            <button type="button" x-on:click="applyRange"
                    class="tw:px-3 tw:py-1 tw:text-sm tw:bg-blue-500 tw:text-white tw:rounded tw:hover:bg-blue-600 tw:dark:bg-blue-600 tw:dark:hover:bg-blue-700">Apply</button>
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
            relativeStartSeconds: null,
            relativeEndSeconds: null,
            outputFormat: '',
            presets: [
                '6h',
                '24h',
                '48h',
                '1w',
                '2w',
                '1mo',
                '2mo',
                '1y',
                '2y',
            ],

            // Computed properties
            get start() {
                if (this.relativeStartSeconds !== null) {
                    return new Date(Date.now() - (this.relativeStartSeconds * 1000));
                }

                if (this.startDate) {
                    return new Date(`${this.startDate} ${this.startTime}`);
                }

                return null;
            },

            get end() {
                if (this.relativeStartSeconds !== null || !this.endDate) {
                    return null
                }

                // if no end time, we want to include the entire day
                if (!this.endTime) {
                    return new Date(new Date(this.endDate).getTime() + 86400000);
                }

                return new Date(`${this.endDate} ${this.endTime}`);
            },

            get outStartString() {
                if (this.relativeStartSeconds !== null) {
                    return this.toShortOffset(this.relativeStartSeconds)
                }

                return this.formatDate(this.start, this.startTime, this.outputFormat);
            },

            get outEndString() {
                if (this.relativeEndSeconds !== null) {
                    return this.toShortOffset(this.relativeEndSeconds)
                }

                if (this.relativeStartSeconds !== null) {
                    return '';
                }

                return this.formatDate(this.end, this.endTime, this.outputFormat);
            },

            get hasValue() {
                return !!(this.start || this.end);
            },

            get displayText() {
                if (this.relativeStartSeconds !== null) {
                    const rel = this.formatRelative(this.relativeStartSeconds);
                    if (rel) {
                        return `${rel} to now`;
                    }
                }

                const startString = this.formatDate(this.start, this.startTime);
                const endString = this.formatDate(this.end, this.endTime);

                if (startString && endString) {
                    return `${startString} to ${endString}`;
                } else if (startString) {
                    return `From ${startString}`;
                } else if (this.endString) {
                    return `Until ${endString}`;
                }

                return this.placeholder;
            },

            init() {
                // Attach API for external JS control
                this.$el.dateRangePicker = {
                    get: () => this.getRange(),
                    set: (start, end) => this.setRange(start, end),
                    clear: () => this.clearRange(),
                    open: () => this.openDropdown(),
                    close: () => this.closeDropdown(),
                };

                if (this.$el.dataset.placeholder) this.placeholder = this.$el.dataset.placeholder;
                if (this.$el.dataset.outputFormat) this.outputFormat = this.$el.dataset.outputFormat;

                this.setRange(this.$el.dataset.start, this.$el.dataset.end);
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
                this.closeDropdown();
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

                if (input === 'now') {
                    return ['', '', null];
                }

                if (this.isRelative(input)) {
                    return ['', '', this.parseRelativeOffset(input)];
                }

                let d;
                let includeTime = false;

                // Check for Unix timestamp
                if (/^\d{10,}$/.test(input)) {
                    const timestamp = parseInt(input);
                    // If less than 13 digits, it's in seconds, otherwise milliseconds
                    d = new Date(input.length < 13 ? timestamp * 1000 : timestamp);
                    // For Unix timestamps, include time unless it's midnight
                    includeTime = !(d.getHours() === 0 && d.getMinutes() === 0 && d.getSeconds() === 0);
                } else {
                    d = new Date(input);
                    includeTime = /\d{1,2}:\d{2}/.test(input);
                }

                if (isNaN(d.getTime())) {
                    return ['', '', null];
                }

                // output the correct formats for the input fields
                const date = d.toLocaleDateString('en-CA');
                const time = includeTime ? d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : '';
                return [date, time, null];
            },

            // Determine if a string is a relative time like 10m, -2h, 7d, 1w, 1y
            isRelative(input) {
                if (!input || typeof input !== 'string') return false;
                const trimmed = input.trim();
                return /^[-+]?\d+\s*(s|m|h|d|w|mo|y)$/.test(trimmed);
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
                if (sign === '+') return -seconds; // future
                return seconds; // past (or explicit '-')
            },

            // Select and format a human relative string using Intl.RelativeTimeFormat
            formatRelative(seconds) {
                if (seconds == null) return '';
                const units = [
                    { unit: 'year', sec: 31536000 },
                    { unit: 'month', sec: 2592000 },
                    { unit: 'week', sec: 604800 },
                    { unit: 'day', sec: 86400 },
                    { unit: 'hour', sec: 3600 },
                    { unit: 'minute', sec: 60 },
                    { unit: 'second', sec: 1 },
                ];
                const abs = Math.abs(seconds);
                const u = units.find(u => abs % u.sec === 0 && abs >= u.sec) || units.find(u => abs >= u.sec) || units[units.length - 1];
                const value = Math.max(1, Math.round(abs / u.sec));
                const rtf = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });
                // rtf: negative = past, positive = future
                return rtf.format(seconds > 0 ? -value : value, u.unit);
            },

            // Convert signed seconds to a short offset label like -1d or +3h
            toShortOffset(seconds) {
                if (seconds === 0) return '0s';
                const units = [
                    { label: 'y', sec: 31536000 },
                    { label: 'mo', sec: 2592000 },
                    { label: 'w', sec: 604800 },
                    { label: 'd', sec: 86400 },
                    { label: 'h', sec: 3600 },
                    { label: 'm', sec: 60 },
                    { label: 's', sec: 1 },
                ];
                const abs = Math.abs(seconds);
                const u = units.find(u => abs % u.sec === 0) || units[units.length - 1];
                const value = Math.round(abs / u.sec) || 0;
                const sign = seconds > 0 ? '-' : '+'; // positive seconds => past => '-'
                return `${sign}${value}${u.label}`;
            },

            // Check if a preset is selected based on seconds value
            isPresetSelected(preset) {
                const sec = this.parseRelativeOffset(preset);
                return this.relativeStartSeconds !== null && sec !== null && sec === this.relativeStartSeconds && !this.endDate && !this.endTime;
            },

            formatDate(fullDate, time, format = 'local') {
                if (!fullDate) {
                    return '';
                }

                if (format === 'iso') {
                    return fullDate.toISOString();
                }

                if (format === 'timestamp') {
                    return Math.floor(fullDate.getTime() / 1000).toString();
                }

                let options = {
                    month: 'numeric',
                    day: 'numeric',
                    year: 'numeric',
                };

                if (time) {
                    options['hour'] = 'numeric';
                    options['minute'] = 'numeric';
                }

                return fullDate.toLocaleDateString(undefined, options);
            },

            emitChange() {
                this.$el.dispatchEvent(new CustomEvent('date-range-changed', {
                    detail: this.getRange(),
                    bubbles: true
                }));
            }
        }));
    });
</script>
@endPushOnce
