<a class="@if($status=='disabled') tw:text-gray-500! tw:visited:text-gray-500! @elseif($status=='down') tw:text-red-600 tw:visited:text-red-600 tw:dark:text-red-500! tw:dark:visited:text-red-500! @else tw:text-blue-900 tw:visited:text-blue-900 tw:dark:text-dark-white-100! tw:dark:visited:text-dark-white-100! @endif"
   href="{{ $link }}"
   {{ $attributes }}>
    {{ $slot->isNotEmpty() ? $slot : $label }}
</a>
