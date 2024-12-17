<a class="@if($status=='disabled') tw-text-gray-400 visited:tw-text-gray-400 @elseif($status=='down') tw-text-red-600 visited:tw-text-red-600 @else tw-text-blue-900 visited:tw-text-blue-900 dark:tw-text-dark-white-100 dark:visited:tw-text-dark-white-100 @endif"
   href="{{ $link }}"
   {{ $attributes }}>
    {{ $slot->isNotEmpty() ? $slot : $label }}
</a>
