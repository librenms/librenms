@props([
    'id',
    'label' => null,
    'type' => 'text',
])

@if($label)<label for="{{ $id }}" class="tw-block tw-mb-1 tw-mt-2 tw-font-medium tw-text-gray-900 dark:tw-text-white">{{ $label }}</label>@endif
<input type="{{ $type }}"
       {{ $attributes->merge(['id' => $id, 'class' => 'tw-mb-2 tw-bg-gray-50 tw-border tw-border-gray-300 tw-text-gray-900 tw-rounded-lg tw-block tw-w-full tw-p-2.5 dark:tw-bg-gray-700 dark:tw-border-gray-600 dark:tw-placeholder-gray-400 dark:tw-text-white dark:tw-focus:border-blue-500']) }}
>
