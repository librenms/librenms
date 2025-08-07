{{-- The component properties are now handled by the DateRangePicker class --}}

<div id="{{ $componentId }}" class="tw:relative">
    <input
        type="text"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        class="{{ $class }}"
        readonly
        @if($required) required @endif
        @if($disabled) disabled @endif
        onclick="toggleDateInputs('{{ $componentId }}')"
    />

    <div class="tw:hidden tw:absolute tw:top-full tw:left-0 tw:right-0 tw:bg-white tw:border tw:border-gray-300 tw:rounded-md tw:shadow-lg tw:z-10 tw:p-4 tw:mt-1"
         id="{{ $componentId }}-inputs">
        @if($presets)
            <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mb-3">
                @foreach($availablePresets as $key => $preset)
                <button type="button" class="preset-btn tw:px-3 tw:py-2 tw:text-sm tw:bg-gray-100 hover:tw:bg-gray-200 tw:rounded-md tw:transition-colors tw:min-w-[40px]"
                        onclick="setSimplePreset(this, '{{ $componentId }}', '{{ $key }}')">{{ $preset['label'] }}</button>
                @endforeach
            </div>
        @endif
        <div class="tw:flex tw:gap-3 tw:mb-3">
            <div class="tw:flex-1">
                <label class="tw:block tw:text-xs tw:text-gray-600 tw:mb-1">From</label>
                <input type="date" id="{{ $componentId }}-start" class="tw:w-full tw:px-2 tw:py-1 tw:border tw:rounded">
            </div>
            <div class="tw:flex-1">
                <label class="tw:block tw:text-xs tw:text-gray-600 tw:mb-1">To</label>
                <input type="date" id="{{ $componentId }}-end" class="tw:w-full tw:px-2 tw:py-1 tw:border tw:rounded">
            </div>
        </div>
        <div class="tw:flex tw:justify-between">
            <button type="button" onclick="clearSimpleRange('{{ $componentId }}')"
                    class="tw:px-3 tw:py-1 tw:text-sm tw:text-gray-500 hover:tw:text-gray-700">Clear</button>
            <button type="button" onclick="applySimpleRange('{{ $componentId }}')"
                    class="tw:px-3 tw:py-1 tw:text-sm tw:bg-blue-500 tw:text-white tw:rounded hover:tw:bg-blue-600">Apply</button>
        </div>
    </div>
</div>

<script>
    // Pass the preset data to JavaScript
    window.dateRangePresets = @json($availablePresets);

    /**
     * Toggle the visibility of the date inputs dropdown
     *
     * @param {string} componentId
     */
    function toggleDateInputs(componentId) {
        const inputs = document.getElementById(componentId + '-inputs');
        inputs.classList.toggle('tw:hidden');
    }

    /**
     * Apply the selected date range
     *
     * @param {string} componentId
     */
    function applySimpleRange(componentId) {
        const startInput = document.getElementById(componentId + '-start');
        const endInput = document.getElementById(componentId + '-end');
        const mainInput = document.querySelector(`#${componentId} input[readonly]`);

        let rangeText = '';
        if (startInput.value && endInput.value) {
            rangeText = `${startInput.value} to ${endInput.value}`;
        } else if (startInput.value) {
            rangeText = `From ${startInput.value}`;
        } else if (endInput.value) {
            rangeText = `Until ${endInput.value}`;
        }

        mainInput.value = rangeText;
        document.getElementById(componentId + '-inputs').classList.add('tw:hidden');
    }

    /**
     * Clear the date range
     *
     * @param {string} componentId
     */
    function clearSimpleRange(componentId) {
        document.getElementById(componentId + '-start').value = '';
        document.getElementById(componentId + '-end').value = '';
        document.querySelector(`#${componentId} input[readonly]`).value = '';
        document.getElementById(componentId + '-inputs').classList.add('tw:hidden');
    }

    /**
     * Set a preset date range
     *
     * @param {HTMLElement} button
     * @param {string} componentId
     * @param {string} preset
     */
    function setSimplePreset(button, componentId, preset) {
        const startInput = document.getElementById(componentId + '-start');
        const endInput = document.getElementById(componentId + '-end');
        const mainInput = document.querySelector(`#${componentId} input[readonly]`);
        const presetData = window.dateRangePresets[preset];

        if (!presetData) {
            console.error(`Preset ${preset} not found`);
            return;
        }

        const today = new Date();
        const formatDate = (date) => date.toISOString().split('T')[0];

        let startDate = null;

        // Calculate the start date based on the preset
        if (presetData.hours) {
            startDate = new Date(today.getTime() - presetData.hours * 60 * 60 * 1000);
        } else if (presetData.days) {
            startDate = new Date(today.getTime() - presetData.days * 24 * 60 * 60 * 1000);
        }

        if (startDate) {
            startInput.value = formatDate(startDate);
        } else {
            startInput.value = '';
        }

        // End date is always empty for these presets
        endInput.value = '';

        // Remove active class from other buttons
        const container = document.getElementById(componentId + '-inputs');
        container.querySelectorAll('.preset-btn').forEach(btn => btn.classList.remove('tw:bg-blue-500', 'tw:text-white'));
        button.classList.add('tw:bg-blue-500', 'tw:text-white');

        // Set the main input value
        mainInput.value = presetData.text;

        // Apply the range automatically
        applySimpleRange(componentId);
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdowns = document.querySelectorAll('[id$="-inputs"]');
        dropdowns.forEach(dropdown => {
            // Using getAttribute to check for the class instead of closest selector
            const parent = dropdown.parentElement;
            if (parent && parent.getAttribute('class').includes('tw:relative') && !parent.contains(e.target)) {
                dropdown.classList.add('tw:hidden');
            }
        });
    });
</script>
