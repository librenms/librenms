@props([
    'method',
    'namePrefix' => 'secret_data',
    'modelPrefix' => 'formData',
    'descriptionName' => 'description',
    'descriptionModel' => 'description',
    'descriptionPlaceholder' => null,
    'errorKey' => null,
    'typeName' => 'secret_type',
    'typeModel' => 'secretType',
    'typeErrorKey' => null,
])

@php($secretTypes = $method['secret_types'] ?? [])

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

        @if(count($secretTypes) > 1)
            <div class="form-group"
                 @if($typeErrorKey) :class="(errors && errors['{{ $typeErrorKey }}']) ? 'has-error' : ''" @endif>
                <label class="control-label">{{ __('Secret Type') }}</label>
                <select name="{{ $typeName }}" class="form-control" x-model="{{ $typeModel }}">
                    @foreach($secretTypes as $secretType)
                        <option value="{{ $secretType['type'] }}">{{ $secretType['label'] }}</option>
                    @endforeach
                </select>
                @if($typeErrorKey)
                    <template x-if="errors && errors['{{ $typeErrorKey }}']">
                        <span class="help-block" x-text="errors['{{ $typeErrorKey }}']?.[0]"></span>
                    </template>
                @endif
            </div>
        @elseif(count($secretTypes) === 1)
            <input type="hidden" name="{{ $typeName }}" value="{{ $secretTypes[0]['type'] }}">
        @endif
    </div>

    @foreach($secretTypes as $secretType)
        {{-- only the fields of the selected type are submitted --}}
        <template x-if="{{ count($secretTypes) === 1 ? 'true' : $typeModel . " === '" . $secretType['type'] . "'" }}">
            <div>
                <x-field-schema-fields
                    :fields="$secretType['schema_fields']"
                    :method-type="$method['type']"
                    id-prefix="new_secret"
                    :name-prefix="$namePrefix"
                    :model-prefix="$modelPrefix"
                    :grid="true" />
            </div>
        </template>
    @endforeach
</div>
