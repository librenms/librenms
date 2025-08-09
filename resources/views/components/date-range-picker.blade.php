<div {{ $attributes }}
    class="tw:relative"
     x-data="dateRangePicker"
     x-on:click.outside="closeDropdown"
     data-start="{{ $start }}"
     data-end="{{ $end }}"
     data-placeholder="{{ $placeholder }}">
    <div
        x-text="displayText"
        class="{{ $class }} tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:px-3 tw:py-2 tw:cursor-pointer tw:bg-white tw:dark:text-gray-800"
        :class="{'tw:text-gray-500 tw:dark:text-gray-400': !hasValue}"
        x-on:click="toggleDropdown"
        tabindex="0"
    ></div>

    <input type="hidden" name="from" :value="startString">
    <input type="hidden" name="to" :value="endString">

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
                <template x-for="(data, preset) in presets">
                    <button type="button"
                            class="preset-btn tw:px-3 tw:py-2 tw:text-sm tw:bg-gray-100 tw:dark:bg-gray-700 tw:hover:bg-gray-200 tw:dark:hover:bg-gray-600 tw:rounded-md tw:transition-colors tw:min-w-[40px] tw:dark:text-gray-400"
                            :class="{'tw:bg-blue-500 tw:text-white': preset === activePreset}"
                            x-on:click="setPreset(preset)"
                            x-text="data.label"
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
            activePreset: null,
            presets: {
                "6h": {
                    "label": "6h",
                    "text": "Last 6 hours",
                    "seconds": 21600
                },
                "24h": {
                    "label": "24h",
                    "text": "Last 24 hours",
                    "seconds": 86400
                },
                "48h": {
                    "label": "48h",
                    "text": "Last 48 hours",
                    "seconds": 172800
                },
                "1w": {
                    "label": "1w",
                    "text": "Last week",
                    "seconds": 604800
                },
                "2w": {
                    "label": "2w",
                    "text": "Last 2 weeks",
                    "seconds": 1209600
                },
                "1m": {
                    "label": "1m",
                    "text": "Last month",
                    "seconds": 2592000
                },
                "2m": {
                    "label": "2m",
                    "text": "Last 2 months",
                    "seconds": 5184000
                },
                "1y": {
                    "label": "1y",
                    "text": "Last year",
                    "seconds": 31536000
                },
                "2y": {
                    "label": "2y",
                    "text": "Last 2 years",
                    "seconds": 63072000
                }
            },

            // Computed properties
            get start() {
                if (this.activePreset) {
                    return this.dateAddSeconds(new Date(), this.presets[this.activePreset].seconds);
                }

                if (this.startDate) {
                    return new Date(`${this.startDate} ${this.startTime}`);
                }

                return null;
            },

            get end() {
                if (this.activePreset || !this.endDate) {
                    return null
                }

                // if no end time, we want to include the entire day
                if (!this.endTime) {
                    return this.dateAddSeconds(new Date(this.endDate), 86400);
                }

                return new Date(`${this.endDate} ${this.endTime}`);
            },

            get startString() {
                return this.formatDate(this.start, this.startTime);
            },

            get endString() {
                return this.formatDate(this.end, this.endTime);
            },

            get hasValue() {
                return !!(this.start || this.end);
            },

            get displayText() {
                if (this.activePreset) {
                    return this.presets[this.activePreset].text;
                }

                if (this.startString && this.endString) {
                    return `${this.startString} to ${this.endString}`;
                } else if (this.startString) {
                    return `From ${this.startString}`;
                } else if (this.endString) {
                    return `Until ${this.endString}`;
                }

                return this.placeholder;
            },

            init() {
                // Attach API for external JS control
                this.$el.dateRangePicker = {
                    get: () => this.getRange(),
                    set: (start, end) => this.setRange(start, end),
                    setPreset: (preset) => this.setPreset(preset),
                    clear: () => this.clearRange(),
                    open: () => this.openDropdown(),
                    close: () => this.closeDropdown(),
                };

                if (this.$el.dataset.start) this.parseDateTime(this.$el.dataset.start, 'start');
                if (this.$el.dataset.end) this.parseDateTime(this.$el.dataset.end, 'end');
                if (this.$el.dataset.placeholder) this.placeholder = this.$el.dataset.placeholder;
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
                this.closeDropdown();
                this.emitChange();
            },

            setRange(start = null, end = null) {
                if (start === null && end === null) {
                    return;
                }

                if (start !== null) {
                    [this.startDate, this.startTime] = this.parseDateTime(start);
                }

                if (end !== null) {
                    [this.endDate, this.endTime] = this.parseDateTime(end);
                }

                this.activePreset = null;
                this.applyRange();
            },

            setPreset(preset) {
                this.activePreset = preset;
                this.applyRange();
            },

            clearRange() {
                this.startDate = '';
                this.endDate = '';
                this.startTime = '';
                this.endTime = '';
                this.activePreset = null;
                this.applyRange();
            },

            getRange() {
                return {
                    start: this.start,
                    end: this.end,
                    preset: this.activePreset
                };
            },

            parseDateTime(dateInput) {
                if (dateInput.includes(' ')) {
                    return value.split(' ');
                }

                return [dateInput, ''];
            },

            formatDate(fullDate, time) {
                if (!fullDate) {
                    return '';
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

            dateAddSeconds(date, seconds) {
                console.log('dateAddSeconds', date, seconds);

                const newTimeInMilliseconds = date.getTime() - (seconds * 1000);
                return new Date(newTimeInMilliseconds);
            },

            emitChange() {
                const detail = this.getRange();

                this.$el.dispatchEvent(new CustomEvent('date-range-changed', {
                    detail,
                    bubbles: true
                }));
            }
        }));
    });
</script>
@endPushOnce
