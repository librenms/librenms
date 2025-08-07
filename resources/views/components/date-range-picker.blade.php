<div id="{{ $componentId }}" class="tw:relative"
     x-data="dateRangePicker('{{ old($name, $value) }}')"
     @click.outside="open = false">
    <input
        type="text"
        name="{{ $name }}"
        x-model="displayValue"
        placeholder="{{ $placeholder }}"
        class="{{ $class }}"
        readonly
        @if($required) required @endif
        @if($disabled) disabled @endif
        @click="toggleDropdown()"
    />

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
        <div class="tw:flex tw:gap-3 tw:mb-3">
            <div class="tw:flex-1">
                <label class="tw:block tw:text-xs tw:text-gray-600 tw:mb-1">From</label>
                <input type="date" x-model="startDate" class="tw:w-full tw:px-2 tw:py-1 tw:border tw:rounded">
            </div>
            <div class="tw:flex-1">
                <label class="tw:block tw:text-xs tw:text-gray-600 tw:mb-1">To</label>
                <input type="date" x-model="endDate" class="tw:w-full tw:px-2 tw:py-1 tw:border tw:rounded">
            </div>
        </div>
        <div class="tw:flex tw:justify-between">
            <button type="button" @click="clearRange()"
                    class="tw:px-3 tw:py-1 tw:text-sm tw:text-gray-500 hover:tw:text-gray-700">Clear</button>
            <button type="button" @click="applyRange()"
                    class="tw:px-3 tw:py-1 tw:text-sm tw:bg-blue-500 tw:text-white tw:rounded hover:tw:bg-blue-600">Apply</button>
        </div>
    </div>
</div>

@pushOnce('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dateRangePicker', (initialValue) => ({
            open: false,
            startDate: '',
            endDate: '',
            displayValue: initialValue,
            presets: null,
            activePreset: null,

            init() {
                this.presets = @js($availablePresets);
                // Initialize from existing value if available
                if (this.displayValue) {
                    // Try to parse the display value to set start/end dates
                    if (this.displayValue.includes(' to ')) {
                        const [start, end] = this.displayValue.split(' to ');
                        this.startDate = start.trim();
                        this.endDate = end.trim();
                    } else if (this.displayValue.startsWith('From ')) {
                        this.startDate = this.displayValue.replace('From ', '').trim();
                    } else if (this.displayValue.startsWith('Until ')) {
                        this.endDate = this.displayValue.replace('Until ', '').trim();
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

                if (fromInput) fromInput.value = this.startDate;
                if (toInput) toInput.value = this.endDate;
            },

            applyRange() {
                let rangeText = '';
                if (this.startDate && this.endDate) {
                    rangeText = `${this.startDate} to ${this.endDate}`;
                } else if (this.startDate) {
                    rangeText = `From ${this.startDate}`;
                } else if (this.endDate) {
                    rangeText = `Until ${this.endDate}`;
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
                } else {
                    this.startDate = '';
                }

                // End date is always empty for these presets
                this.endDate = '';

                // Set the display value
                this.displayValue = preset.text;

                // Apply the range automatically
                this.applyRange();
            }
        }));
    });
</script>
@endPushOnce
