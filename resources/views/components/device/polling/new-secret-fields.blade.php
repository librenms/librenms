@props([
    'method',
    'namePrefix' => 'secret_data',
    'modelPrefix' => 'formData',
    'descriptionName' => 'description',
    'descriptionModel' => 'description',
    'descriptionPlaceholder' => null,
    'errorKey' => null,
])

<div>
    <div class="tw:max-w-md tw:mb-4">
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
    </div>

    <x-field-schema-fields
        :fields="$method['schema_fields']"
        :method-type="$method['type']"
        :name-prefix="$namePrefix"
        :model-prefix="$modelPrefix"
        :grid="true" />
</div>
