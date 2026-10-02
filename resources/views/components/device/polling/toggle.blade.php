@props([
    'name',
    'model',
    'label',
])

<label class="tw:flex tw:items-center tw:cursor-pointer tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:bg-white tw:dark:bg-dark-gray-500 tw:w-full">
    <div class="tw:relative tw:shrink-0">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1" class="tw:sr-only tw:peer" x-model="{{ $model }}">
        <div class="tw:block tw:w-12 tw:h-7 tw:rounded-full tw:transition-colors tw:duration-200 tw:peer-focus-visible:ring-2 tw:peer-focus-visible:ring-blue-400 tw:peer-focus-visible:ring-offset-2 tw:dark:peer-focus-visible:ring-offset-dark-gray-500"
             :class="{{ $model }} ? 'tw:bg-blue-600' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></div>
        <div class="tw:absolute tw:left-0.5 tw:top-0.5 tw:w-6 tw:h-6 tw:rounded-full tw:transition-transform tw:duration-200 tw:bg-white tw:shadow-sm"
             :class="{{ $model }} ? 'tw:translate-x-5' : 'tw:translate-x-0'"></div>
    </div>
    <span class="tw:ml-3 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ $label }}</span>
</label>
