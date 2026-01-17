@props([
    'start' => '',
    'end' => '',
    'outputFormat' => '', // iso, timestamp, or format string
    'timezone' => '',
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
     data-timezone="{{ $timezone }}"
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
                    <input type="date" x-model="fieldStartDate" class="tw:flex-1 tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white">
                    <input type="time" x-model="fieldStartTime" class="tw:min-w-fit tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white">
                </div>
            </div>
            <div class="tw:flex-1">
                <label class="tw:block tw:text-xs tw:text-gray-600 tw:dark:text-gray-400 tw:mb-1">To</label>
                <div class="tw:flex tw:flex-wrap tw:gap-1 tw:dark:text-dark-gray-400">
                    <input type="date" x-model="fieldEndDate" class="tw:flex-1 tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white">
                    <input type="time" x-model="fieldEndTime" class="tw:min-w-fit tw:px-2 tw:py-1 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:bg-white">
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
<script src="{{ asset('js/RrdGraphJS/moment-timezone-with-data.js') }}"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dateRangePicker', () => ({
            open: false,
            fieldStartDate: '',
            fieldEndDate: '',
            fieldStartTime: '',
            fieldEndTime: '',
            timeZone: '',
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
            get startDate() {
                if (this.relativeStartSeconds !== null) {
                    return moment.tz(moment.tz(this.timeZone) - (this.relativeStartSeconds * 1000), this.timeZone);
                }

                if (this.fieldStartDate && this.fieldStartTime) {
                    return moment.tz(`${this.fieldStartDate} ${this.fieldStartTime}`, this.timeZone);
                }

                if (this.fieldStartDate) {
                    return moment.tz(`${this.fieldStartDate} 00:00`, this.timeZone);
                }

                return null;
            },

            get endDate() {
                if (this.relativeStartSeconds !== null || !this.fieldEndDate) {
                    return null
                }

                // if no end time, we want to include the entire day
                if (!this.fieldEndTime) {
                    return moment.tz(moment.tz(`${this.fieldEndDate} 00:00`, this.timeZone) + 86400000, this.timeZone);
                }

                return moment.tz(`${this.fieldEndDate} ${this.fieldEndTime}`, this.timeZone);
            },

            get outStartString() {
                if (this.relativeStartSeconds !== null) {
                    return this.toShortOffset(this.relativeStartSeconds)
                }

                return this.formatDate(this.startDate, this.outputFormat);
            },

            get outEndString() {
                if (this.relativeEndSeconds !== null) {
                    return this.toShortOffset(this.relativeEndSeconds)
                }

                if (this.relativeStartSeconds !== null) {
                    return '';
                }

                return this.formatDate(this.endDate, this.outputFormat);
            },

            get hasValue() {
                return !!(this.startDate || this.endDate);
            },

            get displayText() {
                if (this.relativeStartSeconds !== null) {
                    const rel = this.formatRelative(this.relativeStartSeconds);
                    if (rel) {
                        return `${rel} to now`;
                    }
                }

                const startString = this.formatDate(this.startDate, 'local');
                const endString = this.formatDate(this.endDate, 'local');

                if (startString && endString) {
                    return `${startString} to ${endString}`;
                } else if (startString) {
                    return `From ${startString}`;
                } else if (endString) {
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
                this.timeZone = this.$el.dataset.timezone;

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
                    [this.fieldStartDate, this.fieldStartTime, this.relativeStartSeconds] = this.parseDateTime(start);
                }

                if (end !== null) {
                    [this.fieldEndDate, this.fieldEndTime, this.relativeEndSeconds] = this.parseDateTime(end);
                }

                this.closeDropdown();
                this.emitChange();
            },

            clearRange() {
                this.fieldStartDate = '';
                this.fieldEndDate = '';
                this.fieldStartTime = '';
                this.fieldEndTime = '';
                this.relativeStartSeconds = null;
                this.relativeEndSeconds = null;
                this.closeDropdown();
                this.emitChange();
            },

            getRange() {
                return {
                    start: this.outStartString,
                    end: this.outEndString,
                    relativeStartSeconds: this.relativeStartSeconds,
                    relativeEndSeconds: this.relativeEndSeconds,
                };
            },

            parseDateTime(dateInput) {
                // Returns [date, time, relativeSeconds]
                if (typeof dateInput !== 'string' || ! dateInput) {
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

                const timestamp = Number(input);
                if (isNaN(timestamp)) {
                    // Non-numeric input is assumed to be a date in the correct timezone
                    d = moment.tz(input, this.timeZone);
                    includeTime = /\d{1,2}:\d{2}/.test(input);
                } else {
                    // Pure numeric input is assumed to be a timestamp

                    // If less than 13 digits, it's in seconds, otherwise milliseconds
                    d = moment.tz(input.length < 13 ? timestamp * 1000 : timestamp, this.timeZone);
                    // For Unix timestamps, include time unless it's midnight
                    includeTime = !(d.hours() === 0 && d.minutes() === 0 && d.seconds() === 0);
                }

                if (! d.isValid()) {
                    console.log('Invalid date time input detected');
                    return ['', '', null];
                }

                const date = d.format('YYYY-MM-DD');
                const time = includeTime ? d.format('HH:mm') : '';
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
                return this.relativeStartSeconds !== null && sec !== null && sec === this.relativeStartSeconds && !this.fieldEndDate && !this.fieldEndTime;
            },

            formatDate(fullDate, outputFormat) {
                if (!fullDate) {
                    return '';
                }

                if (outputFormat === 'iso') {
                    return b.toISOString();
                }

                if (outputFormat === 'timestamp') {
                    return fullDate.unix().toString();
                }

                if (outputFormat != 'local') {
                    // Output format should be specified as something like 'YYYY-MM-DD'
                    return fullDate.format(outputFormat);
                }

                let thisFormat = 'LL';
                if (fullDate.hours() > 0 || fullDate.minutes() > 0) {
                    thisFormat += ' LT';
                }

                return fullDate.format(thisFormat);
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
