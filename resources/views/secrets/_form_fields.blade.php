@foreach($schema as $field => $config)
    <div class="form-group dynamic-field {{ $errors->has($field) ? 'has-error' : '' }}"
         data-visible-if="{{ isset($config['visible_if']) ? json_encode($config['visible_if']) : 'null' }}"
         id="group-{{ $field }}">
        <label for="{{ $field }}" class="control-label">{{ __($config['label'] ?? ucfirst($field)) }}</label>

        @if(($config['type'] ?? 'text') === 'select')
            <select name="{{ $field }}" id="{{ $field }}" class="form-control">
                @foreach($config['options'] ?? [] as $val => $text)
                    <option
                        value="{{ $val }}" {{ (string)old($field, $data[$field] ?? '') === (string)$val ? 'selected' : '' }}>
                        {{ __($text) }}
                    </option>
                @endforeach
            </select>
        @elseif(($config['type'] ?? 'text') === 'password')
            @can('unmask', \App\Models\Secret::class)
                <div class="input-group">
                    <input type="password"
                           class="form-control"
                           id="{{ $field }}"
                           name="{{ $field }}"
                           value="{{ old($field, $data[$field] ?? '') }}"
                           data-bwignore="true"
                           data-lpignore="true"
                           data-1p-ignore="true"
                           autocomplete="new-password">
                    <span class="input-group-btn">
                        <button type="button"
                                class="btn btn-default btn-toggle-password"
                                data-target="{{ $field }}"
                                title="{{ __('Show/hide') }}">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </span>
                </div>
            @else
                <input type="password"
                       class="form-control"
                       id="{{ $field }}"
                       name="{{ $field }}"
                       value="{{ old($field, $data[$field] ?? '') }}"
                       data-bwignore="true"
                       data-lpignore="true"
                       data-1p-ignore="true"
                       autocomplete="new-password">
            @endcan
        @else
            <input type="{{ $config['type'] ?? 'text' }}"
                   class="form-control"
                   id="{{ $field }}"
                   name="{{ $field }}"
                   value="{{ old($field, $data[$field] ?? '') }}">
        @endif

        @if($errors->has($field))
            <span class="help-block">{{ $errors->first($field) }}</span>
        @endif
    </div>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // --- Password toggle (show/hide) ---
        document.querySelectorAll('.btn-toggle-password').forEach(btn => {
            btn.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.target);
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                }
            });
        });

        // --- Visibility conditions ---
        const fields = document.querySelectorAll('.dynamic-field');

        function evaluateCondition(condition, fieldValue) {
            if (typeof condition === 'object' && condition !== null) {
                if (condition.$in) return condition.$in.includes(fieldValue);
            }
            return fieldValue === condition;
        }

        function checkVisibility() {
            fields.forEach(field => {
                const conditionStr = field.getAttribute('data-visible-if');
                if (conditionStr && conditionStr !== 'null') {
                    try {
                        const conditions = JSON.parse(conditionStr);
                        let isVisible = true;
                        for (const [depField, condition] of Object.entries(conditions)) {
                            const depElement = document.getElementById(depField);
                            if (depElement && !evaluateCondition(condition, depElement.value)) {
                                isVisible = false;
                                break;
                            }
                        }
                        const innerInputs = field.querySelectorAll('input, select');
                        field.style.display = isVisible ? 'block' : 'none';
                        innerInputs.forEach(input => input.disabled = !isVisible);
                    } catch (e) {
                        console.error('Error parsing visibility conditions', e);
                    }
                }
            });
        }

        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('change', checkVisibility);
        });

        checkVisibility();
    });
</script>
