@props([
    /**
     * Array of field definitions — either from buildSchemaFields() (Alpine mode)
     * or schema() (plain POST mode, keyed by field name).
     *
     * Alpine mode shape (from buildSchemaFields()):
     *   [['key', 'field_type', 'label', 'options', 'default', 'visible_if_expression', 'min', 'max'], ...]
     *
     * Plain POST mode shape (from schema()):
     *   ['field_name' => ['type', 'label', 'options', 'visible_if'], ...]
     */
    'fields' => [],

    /**
     * HTML name prefix for inputs.
     *
     * Alpine mode:   "secret_data" → name="secret_data[version]"
     * Plain POST:    "" → name="version"
     */
    'namePrefix' => '',

    /**
     * Alpine x-model prefix. When non-empty the component emits x-model bindings.
     * Leave empty for plain POST forms.
     *
     * Example: "formData" → x-model="formData['version']"
     */
    'modelPrefix' => '',

    /**
     * Used to build unique IDs for password toggle buttons when in Alpine mode.
     * E.g. "snmp" → id="secret_snmp_community"
     */
    'methodType' => '',

    /**
     * When true, password fields check the 'unmask' gate for Secret before
     * showing the toggle button. When false the button is always shown.
     * Only relevant for secret-edit forms where existing masked values exist.
     */
    'checkCanUnmask' => false,

    /**
     * Pre-populated values for plain POST mode (from the model / old input).
     * Ignored in Alpine mode (use the x-data initialisation outside this component).
     *
     * ['version' => 'v2c', 'community' => 'public', ...]
     */
    'data' => [],

    /**
     * When true the component wraps fields in a two-column responsive grid.
     * Set to false to render fields without a grid wrapper.
     */
    'grid' => false,
])

@php
    /**
     * Normalise both calling conventions to a unified internal shape:
     *
     *   [
     *     'key'                  => string,
     *     'type'                 => string,   // select|password|number|text
     *     'label'                => string,
     *     'options'              => array,    // [value => label, ...]
     *     'visible_if_expr'      => string|null,   // Alpine x-show expression
     *     'visible_if_data'      => array|null,    // JSON data for data-visible-if attr
     *     'min'                  => int|null,
     *     'max'                  => int|null,
     *   ]
     */
    $normalised = [];

    // Detect Alpine mode: array items are numeric-indexed with a 'key' key
    $isAlpineMode = !empty($fields) && isset(array_values($fields)[0]['key']);

    if ($isAlpineMode) {
        // From buildSchemaFields()
        foreach ($fields as $field) {
            // Prefer the structured translation key used by the old method-settings partial when
            // a methodType is provided, falling back to the FieldDefinition label or the key itself.
            $translationKey = $methodType ? "poller.method_settings.{$methodType}.{$field['key']}" : null;
            $label = ($translationKey && __($translationKey) !== $translationKey)
                ? $translationKey
                : ($field['label'] ?? ucfirst($field['key']));

            $normalised[] = [
                'key'             => $field['key'],
                'type'            => $field['field_type'] ?? 'text',
                'label'           => $label,
                'options'         => $field['options'] ?? [],
                'visible_if_expr' => $field['visible_if_expression'] ?? null,
                'visible_if_data' => null,
                'min'             => $field['min'] ?? null,
                'max'             => $field['max'] ?? null,
                'placeholder'     => $field['placeholder'] ?? null,
                'required'        => ! empty($field['required']),
            ];
        }
    } else {
        // From schema() — keyed by field name
        foreach ($fields as $key => $config) {
            $normalised[] = [
                'key'             => $key,
                'type'            => $config['type'] ?? 'text',
                'label'           => $config['label'] ?? ucfirst($key),
                'options'         => $config['options'] ?? [],
                'visible_if_expr' => null,
                'visible_if_data' => $config['visible_if'] ?? null,
                'min'             => $config['min'] ?? null,
                'max'             => $config['max'] ?? null,
                'placeholder'     => $config['placeholder'] ?? null,
                'required'        => ! empty($config['required']),
            ];
        }
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
        return old($oldKey, $data[$key] ?? '');
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
         @if($field['visible_if_expr']) x-show="{{ $field['visible_if_expr'] }}" @endif
         @if($field['visible_if_data']) data-visible-if="{{ json_encode($field['visible_if_data']) }}" @endif
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
                            @if(!$isAlpineMode && (string) $value === (string) $optVal) selected @endif>
                        {{ __($optLabel) }}
                    </option>
                @endforeach
            </select>

        @elseif($type === 'password')
            @php $showToggle = !$checkCanUnmask || \Illuminate\Support\Facades\Gate::check('unmask', \App\Models\Secret::class); @endphp

            @if($checkCanUnmask && !\Illuminate\Support\Facades\Gate::check('unmask', \App\Models\Secret::class))
                {{-- User cannot unmask; show a masked readonly placeholder --}}
                <input type="password"
                       id="{{ $id }}"
                       name="{{ $name }}"
                       value="********"
                       class="form-control"
                       readonly>
            @else
                <div class="input-group tw:w-full">
                    <input type="password"
                           id="{{ $id }}"
                           name="{{ $name }}"
                           value="{{ $isAlpineMode ? '' : $value }}"
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
                                data-target="{{ $id }}"
                                title="{{ __('Show/hide') }}">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </span>
                </div>
            @endif

        @elseif($type === 'number')
            <input type="number"
                   id="{{ $id }}"
                   name="{{ $name }}"
                   value="{{ $isAlpineMode ? '' : $value }}"
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
                   value="{{ $isAlpineMode ? '' : $value }}"
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

@if(!$isAlpineMode && !empty($normalised))
    {{-- Plain POST mode: vanilla JS for password toggles and conditional visibility --}}
    <script>
        (function () {
            // Password toggle
            document.querySelectorAll('.btn-toggle-password').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const input = document.getElementById(this.dataset.target);
                    const icon  = this.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    }
                });
            });

            // Conditional visibility based on data-visible-if JSON
            const allFields = document.querySelectorAll('[data-visible-if]');

            function evaluateCondition(condition, value) {
                if (condition && typeof condition === 'object' && condition.$in) {
                    return condition.$in.includes(value);
                }
                return value === condition;
            }

            function checkVisibility() {
                allFields.forEach(function (el) {
                    const raw = el.getAttribute('data-visible-if');
                    if (!raw || raw === 'null') { return; }
                    try {
                        const conditions = JSON.parse(raw);
                        let visible = true;
                        for (const [dep, condition] of Object.entries(conditions)) {
                            const depEl = document.getElementById(dep);
                            if (depEl && !evaluateCondition(condition, depEl.value)) {
                                visible = false;
                                break;
                            }
                        }
                        el.style.display = visible ? '' : 'none';
                        el.querySelectorAll('input, select').forEach(function (inp) {
                            inp.disabled = !visible;
                        });
                    } catch (e) {
                        console.error('field-schema-fields: error parsing visibility condition', e);
                    }
                });
            }

            document.querySelectorAll('input, select').forEach(function (inp) {
                inp.addEventListener('change', checkVisibility);
            });

            checkVisibility();
        })();
    </script>
@endif
