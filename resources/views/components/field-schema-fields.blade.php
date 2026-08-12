@props([
    /**
     * Array of field definitions — either from buildSchemaFields()
     * or schema() (keyed by field name).
     */
    'fields' => [],

    /**
     * HTML name prefix for inputs.
     * E.g. "secret_data" → name="secret_data[version]"
     */
    'namePrefix' => '',

    /**
     * Alpine x-model prefix. When non-empty the component emits x-model bindings.
     * E.g. "formData" → x-model="formData['version']"
     */
    'modelPrefix' => '',

    /**
     * Used to build unique IDs for password toggle buttons.
     * E.g. "snmp" → id="secret_snmp_community"
     */
    'methodType' => '',

    /**
     * When true, password fields check the 'unmask' gate for Secret before
     * showing the toggle button. When false the button is always shown.
     */
    'checkCanUnmask' => false,

    /**
     * Pre-populated values for plain POST mode (from the model / old input).
     */
    'data' => [],

    /**
     * When true the component wraps fields in a two-column responsive grid.
     */
    'grid' => false,
])

@php
    /**
     * Normalise both calling conventions to a unified internal shape:
     *
     *   [
     *     'key'         => string,
     *     'type'        => string,   // select|password|number|text
     *     'label'       => string,
     *     'options'     => array,    // [value => label, ...]
     *     'visible_if'  => string|null,   // Alpine x-show expression
     *     'min'         => int|null,
     *     'max'         => int|null,
     *     'placeholder' => string|null,
     *     'required'    => bool,
     *   ]
     */
    $normalised = [];

    foreach ($fields as $key => $config) {
        $fieldKey = is_array($config) && isset($config['key']) ? $config['key'] : (string) $key;

        $translationKey = $methodType ? "poller.method_settings.{$methodType}.{$fieldKey}" : null;
        $label = ($translationKey && __($translationKey) !== $translationKey)
            ? $translationKey
            : ($config['label'] ?? ucfirst($fieldKey));

        $type = $config['field_type'] ?? $config['type'] ?? 'text';
        $visibleIf = $config['visible_if_expression'] ?? null;

        if (! $visibleIf && ! empty($config['visible_if'])) {
            $dataVar = $modelPrefix ?: 'formData';
            $visibleIf = collect($config['visible_if'])
                ->map(function (mixed $condVal, string $condKey) use ($dataVar): string {
                    if (is_array($condVal) && isset($condVal['$in'])) {
                        return json_encode(array_values($condVal['$in'])) . '.includes(' . $dataVar . '[' . json_encode($condKey) . '])';
                    }

                    return $dataVar . '[' . json_encode($condKey) . '] === ' . json_encode($condVal);
                })->implode(' && ');
        }

        $normalised[] = [
            'key'         => $fieldKey,
            'type'        => $type,
            'label'       => $label,
            'options'     => $config['options'] ?? [],
            'visible_if'  => $visibleIf,
            'min'         => $config['min'] ?? null,
            'max'         => $config['max'] ?? null,
            'placeholder' => $config['placeholder'] ?? null,
            'required'    => ! empty($config['required']),
        ];
    }

    $hasErrors = isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag;

    // Build the HTML name for an input
    $inputName = fn(string $key): string => $namePrefix !== ''
        ? "{$namePrefix}[{$key}]"
        : $key;

    // Build a unique DOM id for password toggle
    $inputId = fn(string $key): string => implode('_', array_filter(['secret', $methodType, $key]));

    // Resolve current value for plain POST fields
    $currentValue = function(string $key) use ($data, $namePrefix): string {
        $oldKey = $namePrefix !== '' ? "{$namePrefix}.{$key}" : $key;
        $val = old($oldKey, $data[$key] ?? '');
        return is_null($val) ? '' : (string) $val;
    };
@endphp

@if($grid)
    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl">
@endif

@foreach($normalised as $field)
    @php
        $key   = $field['key'];
        $type  = $field['type'];
        $name  = $inputName($key);
        $id    = $inputId($key);
        $value = $currentValue($key);
    @endphp

    <div class="form-group {{ ($hasErrors && $errors->has($key)) ? 'has-error' : '' }}"
         @if($field['visible_if']) x-show="{{ $field['visible_if'] }}" @endif
         id="group-{{ $key }}">

        <label for="{{ $id }}" class="control-label">
            {{ __($field['label']) }}
            @if($field['required'])<span class="tw:text-red-500 tw:ml-0.5" aria-hidden="true">*</span>@endif
        </label>

        @if($type === 'select')
            <select name="{{ $name }}"
                    id="{{ $id }}"
                    class="form-control"
                    @if($modelPrefix) x-model="{{ $modelPrefix }}['{{ $key }}']" @endif>
                @foreach($field['options'] as $optVal => $optLabel)
                    <option value="{{ $optVal }}"
                            @if((string) $value === (string) $optVal) selected @endif>
                        {{ __($optLabel) }}
                    </option>
                @endforeach
            </select>

        @elseif($type === 'password')
            @if($checkCanUnmask && !\Illuminate\Support\Facades\Gate::check('unmask', \App\Models\Secret::class))
                {{-- User cannot unmask; show a masked readonly placeholder --}}
                <input type="password"
                       id="{{ $id }}"
                       name="{{ $name }}"
                       value="********"
                       class="form-control"
                       readonly>
            @else
                <div class="input-group tw:w-full" x-data="{ showPassword: false }">
                    <input :type="showPassword ? 'text' : 'password'"
                           type="password"
                           id="{{ $id }}"
                           name="{{ $name }}"
                           value="{{ $value }}"
                           class="form-control"
                           @if($modelPrefix) x-model="{{ $modelPrefix }}['{{ $key }}']" @endif
                           @if(filled($field['placeholder'])) placeholder="{{ __($field['placeholder']) }}" @endif
                           @if($field['required']) required @endif
                           data-bwignore="true"
                           data-lpignore="true"
                           data-1p-ignore="true"
                           autocomplete="new-password">
                    <span class="input-group-btn">
                        <button type="button"
                                class="btn btn-default btn-toggle-password"
                                @click="showPassword = !showPassword"
                                data-target="{{ $id }}"
                                title="{{ __('Show/hide') }}">
                            <i class="fa" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'" class="fa fa-eye-slash"></i>
                        </button>
                    </span>
                </div>
            @endif

        @elseif($type === 'number')
            <input type="number"
                   id="{{ $id }}"
                   name="{{ $name }}"
                   value="{{ $value }}"
                   class="form-control"
                   @if($modelPrefix) x-model="{{ $modelPrefix }}['{{ $key }}']" @endif
                   @if(filled($field['placeholder'])) placeholder="{{ __($field['placeholder']) }}" @endif
                   @if($field['required']) required @endif
                   @isset($field['min']) min="{{ $field['min'] }}" @endisset
                   @isset($field['max']) max="{{ $field['max'] }}" @endisset>

        @else
            <input type="text"
                   id="{{ $id }}"
                   name="{{ $name }}"
                   value="{{ $value }}"
                   class="form-control"
                   @if($modelPrefix) x-model="{{ $modelPrefix }}['{{ $key }}']" @endif
                   @if(filled($field['placeholder'])) placeholder="{{ __($field['placeholder']) }}" @endif
                   @if($field['required']) required @endif>
        @endif

        @if($hasErrors && $errors->has($key))
            <span class="help-block">{{ $errors->first($key) }}</span>
        @endif
    </div>
@endforeach

@if($grid)
    </div>
@endif
