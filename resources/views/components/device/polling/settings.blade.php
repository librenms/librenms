@props([
    'method',
    'namePrefix' => '',
    'modelPrefix' => 'settingsData',
])

@if(!empty($method['settings_fields']))
    <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
        <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Settings') }}</h4>
        <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:bg-white tw:dark:bg-dark-gray-500">
            <x-field-schema-fields
                :fields="$method['settings_fields']"
                :method-type="$method['type']"
                :name-prefix="$namePrefix"
                :model-prefix="$modelPrefix"
                :grid="true" />
        </div>
    </div>
@endif
