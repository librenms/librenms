@props([
    'method',
    'namePrefix' => 'secret_data',
    'modelPrefix' => 'formData',
    'descriptionName' => 'description',
    'descriptionModel' => 'description',
    'descriptionPlaceholder' => null,
    'defaultName' => 'default',
    'defaultModel' => null,
    'errorKey' => null,
])

<div>
    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl tw:mb-4">
        <div class="form-group"
             @if($errorKey) :class="(errors && errors['{{ $errorKey }}']) ? 'has-error' : ''" @endif>
            <label class="control-label">{{ __('Secret Description') }}</label>
            <input type="text"
                   name="{{ $descriptionName }}"
                   class="form-control"
                   @if($descriptionPlaceholder) placeholder="{{ $descriptionPlaceholder }}" @endif
                   @if($descriptionModel) x-model="{{ $descriptionModel }}" @endif
                   value="{{ old($descriptionName) }}">
            @if($errorKey)
                <template x-if="errors && errors['{{ $errorKey }}']">
                    <span class="help-block" x-text="errors['{{ $errorKey }}']?.[0]"></span>
                </template>
            @endif
        </div>
        <div class="form-group tw:flex tw:items-end">
            <div class="checkbox tw:mb-0">
                <label>
                    <input type="hidden" name="{{ $defaultName }}" value="0">
                    <input type="checkbox"
                           name="{{ $defaultName }}"
                           value="1"
                           @if($defaultModel) x-model="{{ $defaultModel }}" @else {{ old($defaultName) ? 'checked' : '' }} @endif>
                    {{ __('Make Default') }}
                </label>
            </div>
        </div>
    </div>

    <x-field-schema-fields
        :fields="$method['schema_fields']"
        :method-type="$method['type']"
        :name-prefix="$namePrefix"
        :model-prefix="$modelPrefix"
        :grid="true" />
</div>
