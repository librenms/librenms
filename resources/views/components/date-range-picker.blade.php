<div {{ $attributes }}
    class="tw:relative"
     x-data="dateRangePicker"
     x-on:click.outside="open = false"
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

    <input type="hidden" name="from" :value="startValue">
    <input type="hidden" name="to" :value="endValue">

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
                @foreach($availablePresets as $key => $preset)
                <button type="button"
                        class="preset-btn tw:px-3 tw:py-2 tw:text-sm tw:bg-gray-100 tw:dark:bg-gray-700 tw:hover:bg-gray-200 tw:dark:hover:bg-gray-600 tw:rounded-md tw:transition-colors tw:min-w-[40px] tw:dark:text-gray-400"
                        :class="{'tw:bg-blue-500 tw:text-white': activePreset === '{{ $key }}'}"
                        x-on:click="setPreset('{{ $key }}')">{{ $preset['label'] }}</button>
                @endforeach
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

            // Computed properties
            get startValue() {
                if (!this.startDate) return '';
                return this.startTime ? `${this.startDate} ${this.startTime}` : this.startDate;
            },

            get endValue() {
                if (!this.endDate) return '';
                return this.endTime ? `${this.endDate} ${this.endTime}` : this.endDate;
            },

            get hasValue() {
                return !!(this.startValue || this.endValue);
            },

            get displayText() {
                if (!this.hasValue) return this.placeholder;

                if (this.startValue && this.endValue) {
                    return `${this.startValue} to ${this.endValue}`;
                } else if (this.startValue) {
                    return `From ${this.startValue}`;
                } else if (this.endValue) {
                    return `Until ${this.endValue}`;
                }

                return this.placeholder;
            },

            init() {
                // Attach API for external JS control
                this.$el.dateRangePicker = {
                    get: () => this.getValue(),
                    set: (values) => this.setValue(values),
                    clear: () => this.clearRange(),
                    open: () => this.open = true,
                    close: () => this.open = false,
                };

                if (this.$el.dataset.start) this.parseDateTime(this.$el.dataset.start, 'start');
                if (this.$el.dataset.end) this.parseDateTime(this.$el.dataset.end, 'end');
                if (this.$el.dataset.placeholder) this.placeholder = this.$el.dataset.placeholder;
            },

            parseDateTime(value, type) {
                if (!value) return;
                if (value.includes(' ')) {
                    const [date, time] = value.split(' ');
                    if (type === 'start') { this.startDate = date; this.startTime = time; }
                    else { this.endDate = date; this.endTime = time; }
                } else {
                    if (type === 'start') { this.startDate = value; this.startTime = ''; }
                    else { this.endDate = value; this.endTime = ''; }
                }
            },

            toggleDropdown() {
                this.open = !this.open;
            },

            // API alias per issue description
            toggle() {
                this.toggleDropdown();
            },

            updateValues() {
                // Clear preset when manually changing values
                this.activePreset = null;
            },

            applyRange() {
                this.open = false;
                this.emitChange();
            },

            clearRange() {
                this.startDate = '';
                this.endDate = '';
                this.startTime = '';
                this.endTime = '';
                this.activePreset = null;
                this.open = false;
                this.emitChange();
            },

            setPresetFromDataset(ds) {
                const key = ds.presetKey || null;
                const preset = {
                    hours: ds.hours ? parseInt(ds.hours, 10) : null,
                    days: ds.days ? parseInt(ds.days, 10) : null,
                };
                this.setPreset(key, preset);
            },

            // New API: set a preset by key and object {hours|days}
            setPreset(key, preset) {
                this.activePreset = key || null;
                const now = new Date();
                let startDate = null;
                if (preset && Number.isInteger(preset.hours)) {
                    startDate = new Date(now.getTime() - preset.hours * 60 * 60 * 1000);
                } else if (preset && Number.isInteger(preset.days)) {
                    startDate = new Date(now.getTime() - preset.days * 24 * 60 * 60 * 1000);
                    // For day presets, set to start of day
                    startDate.setHours(0, 0, 0, 0);
                }
                if (startDate) {
                    this.startDate = startDate.toISOString().split('T')[0];
                    if (preset && Number.isInteger(preset.hours)) {
                        const hh = startDate.getHours().toString().padStart(2, '0');
                        const mm = startDate.getMinutes().toString().padStart(2, '0');
                        this.startTime = `${hh}:${mm}`;
                    } else {
                        this.startTime = '';
                    }
                } else {
                    this.startDate = '';
                    this.startTime = '';
                }
                // Clear end for presets
                this.endDate = '';
                this.endTime = '';
                this.applyRange();
            },

            emitChange() {
                const detail = this.getValue();

                this.$el.dispatchEvent(new CustomEvent('date-range-changed', {
                    detail,
                    bubbles: true
                }));
            },

            getValue() {
                return {
                    startValue: this.startValue,
                    endValue: this.endValue,
                    startDate: this.startDate,
                    startTime: this.startTime,
                    endDate: this.endDate,
                    endTime: this.endTime,
                    hasValue: this.hasValue,
                    activePreset: this.activePreset,
                };
            },

            setValue(start = '', end = '') {
                this.parseDateTime(start, 'start');
                this.parseDateTime(end, 'end');
                this.activePreset = null;
                this.emitChange();
            },
        }));
    });
</script>
@endPushOnce
