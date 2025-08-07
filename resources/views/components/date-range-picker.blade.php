<div class="tw:relative"
     x-data="dateRangePicker"
     @click.outside="open = false">
    <div
        x-text="displayValue || '{{ $placeholder }}'"
        class="{{ $class }} tw:border tw:border-gray-300 tw:rounded tw:px-3 tw:py-2 tw:cursor-pointer tw:bg-white"
        :class="{'tw:text-gray-500': !displayValue}"
        @click="toggleDropdown"
        tabindex="0"
    ></div>
    <input type="hidden" name="{{ $name }}" x-model="displayValue" @if($required) required @endif @if($disabled) disabled @endif />

    <div class="tw:absolute tw:top-full tw:left-0 tw:right-0 tw:bg-white tw:border tw:border-gray-300 tw:rounded-md tw:shadow-lg tw:z-10 tw:p-4 tw:mt-1"
         x-show="open"
         x-transition:enter="tw:transition tw:ease-out tw:duration-200"
         x-transition:enter-start="tw:opacity-0 tw:transform tw:-translate-y-2"
         x-transition:enter-end="tw:opacity-100 tw:transform tw:translate-y-0"
         x-transition:leave="tw:transition tw:ease-in tw:duration-150"
         x-transition:leave-start="tw:opacity-100 tw:transform tw:translate-y-0"
         x-transition:leave-end="tw:opacity-0 tw:transform tw:-translate-y-2"
         style="display: none;">
        @if($presets)
            <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mb-3">
                @foreach($availablePresets as $key => $preset)
                <button type="button"
                        class="preset-btn tw:px-3 tw:py-2 tw:text-sm tw:bg-gray-100 hover:tw:bg-gray-200 tw:rounded-md tw:transition-colors tw:min-w-[40px]"
                        :class="{'tw:bg-blue-500 tw:text-white': activePreset === '{{ $key }}'}"
                        @click="setPreset('{{ $key }}')">{{ $preset['label'] }}</button>
                @endforeach
            </div>
        @endif
        <div class="tw:mb-3">
            <div class="tw:flex-1">
                <label class="tw:block tw:text-xs tw:text-gray-600 tw:mb-1">From</label>
                <div class="tw:flex tw:flex-wrap tw:gap-1">
                    <input type="date" x-model="startDate" class="tw:flex-1 tw:px-2 tw:py-1 tw:border tw:rounded">
                    <input type="time" x-model="startTime" class="tw:min-w-fit tw:px-2 tw:py-1 tw:border tw:rounded">
                </div>
            </div>
            <div class="tw:flex-1">
                <label class="tw:block tw:text-xs tw:text-gray-600 tw:mb-1">To</label>
                <div class="tw:flex tw:flex-wrap tw:gap-1">
                    <input type="date" x-model="endDate" class="tw:flex-1 tw:px-2 tw:py-1 tw:border tw:rounded">
                    <input type="time" x-model="endTime" class="tw:min-w-fit tw:px-2 tw:py-1 tw:border tw:rounded">
                </div>
            </div>
        </div>
        <div class="tw:flex tw:justify-between">
            <button type="button" @click="clearRange"
                    class="tw:px-3 tw:py-1 tw:text-sm tw:text-gray-500 hover:tw:text-gray-700">Clear</button>
            <button type="button" @click="applyRange"
                    class="tw:px-3 tw:py-1 tw:text-sm tw:bg-blue-500 tw:text-white tw:rounded hover:tw:bg-blue-600">Apply</button>
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
            displayValue: '',
            presets: null,
            activePreset: null,

            init() {
                this.presets = @js($availablePresets);
                // Initialize from existing value if available
                if (this.displayValue) {
                    // Try to parse the display value to set start/end dates and times
                    if (this.displayValue.includes(' to ')) {
                        const [start, end] = this.displayValue.split(' to ');
                        this.parseDateTime(start.trim(), 'start');
                        this.parseDateTime(end.trim(), 'end');
                    } else if (this.displayValue.startsWith('From ')) {
                        this.parseDateTime(this.displayValue.replace('From ', '').trim(), 'start');
                    } else if (this.displayValue.startsWith('Until ')) {
                        this.parseDateTime(this.displayValue.replace('Until ', '').trim(), 'end');
                    }
                }
            },

            parseDateTime(value, type) {
                if (!value) return;

                // Check if the value contains time (contains a space followed by time)
                if (value.includes(' ')) {
                    const [date, time] = value.split(' ');
                    if (type === 'start') {
                        this.startDate = date;
                        this.startTime = time;
                    } else {
                        this.endDate = date;
                        this.endTime = time;
                    }
                } else {
                    // Only date without time
                    if (type === 'start') {
                        this.startDate = value;
                    } else {
                        this.endDate = value;
                    }
                }

                // Add hidden inputs for form submission
                this.$nextTick(() => {
                    // Create hidden inputs if they don't exist
                    if (!document.getElementById(`${this.$el.id}-from-hidden`)) {
                        const fromInput = document.createElement('input');
                        fromInput.type = 'hidden';
                        fromInput.name = 'from';
                        fromInput.id = `${this.$el.id}-from-hidden`;
                        this.$el.appendChild(fromInput);
                    }

                    if (!document.getElementById(`${this.$el.id}-to-hidden`)) {
                        const toInput = document.createElement('input');
                        toInput.type = 'hidden';
                        toInput.name = 'to';
                        toInput.id = `${this.$el.id}-to-hidden`;
                        this.$el.appendChild(toInput);
                    }

                    // Update hidden inputs with initial values
                    this.updateHiddenInputs();
                });
            },

            toggleDropdown() {
                this.open = !this.open;
            },

            updateHiddenInputs() {
                const fromInput = document.getElementById(`${this.$el.id}-from-hidden`);
                const toInput = document.getElementById(`${this.$el.id}-to-hidden`);

                if (fromInput) {
                    fromInput.value = this.startDate;
                    if (this.startTime) {
                        fromInput.value += ` ${this.startTime}`;
                    }
                }

                if (toInput) {
                    toInput.value = this.endDate;
                    if (this.endTime) {
                        toInput.value += ` ${this.endTime}`;
                    }
                }
            },

            applyRange() {
                let rangeText = '';
                let startDisplay = this.startDate;
                let endDisplay = this.endDate;

                // Add time to display if available
                if (this.startDate && this.startTime) {
                    startDisplay = `${this.startDate} ${this.startTime}`;
                }

                if (this.endDate && this.endTime) {
                    endDisplay = `${this.endDate} ${this.endTime}`;
                }

                if (startDisplay && endDisplay) {
                    rangeText = `${startDisplay} to ${endDisplay}`;
                } else if (startDisplay) {
                    rangeText = `From ${startDisplay}`;
                } else if (endDisplay) {
                    rangeText = `Until ${endDisplay}`;
                }

                this.displayValue = rangeText;
                this.updateHiddenInputs();
                this.open = false;

                // Dispatch a change event for parent forms
                this.$el.dispatchEvent(new Event('change', {bubbles: true}));
            },

            clearRange() {
                this.startDate = '';
                this.endDate = '';
                this.startTime = '';
                this.endTime = '';
                this.displayValue = '';
                this.activePreset = null;
                this.updateHiddenInputs();
                this.open = false;

                // Dispatch a change event for parent forms
                this.$el.dispatchEvent(new Event('change', {bubbles: true}));
            },

            setPreset(key) {
                const preset = this.presets[key];
                if (!preset) {
                    console.error(`Preset ${key} not found`);
                    return;
                }

                this.activePreset = key;
                const today = new Date();

                let startDate = null;
                if (preset.hours) {
                    startDate = new Date(today.getTime() - preset.hours * 60 * 60 * 1000);
                } else if (preset.days) {
                    startDate = new Date(today.getTime() - preset.days * 24 * 60 * 60 * 1000);
                }

                if (startDate) {
                    this.startDate = startDate.toISOString().split('T')[0];

                    // Set time if hours are specified
                    if (preset.hours) {
                        // Format time as HH:MM
                        const hours = startDate.getHours().toString().padStart(2, '0');
                        const minutes = startDate.getMinutes().toString().padStart(2, '0');
                        this.startTime = `${hours}:${minutes}`;
                    } else {
                        this.startTime = '';
                    }
                } else {
                    this.startDate = '';
                    this.startTime = '';
                }

                // End date and time are always empty for these presets
                this.endDate = '';
                this.endTime = '';

                // Set the display value
                this.displayValue = preset.text;

                // Apply the range automatically
                this.applyRange();
            }
        }));
    });
</script>
@endPushOnce
