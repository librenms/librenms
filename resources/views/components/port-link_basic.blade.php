<a class="@if($status=='disabled') tw:text-gray-400 tw:visited:text-gray-400 @elseif($status=='down') tw:text-red-600 tw:visited:text-red-600 @else tw:text-blue-900 tw:visited:text-blue-900 tw:dark:text-dark-white-100 tw:dark:visited:text-dark-white-100 @endif"
   href="{{ $link }}"
   {{ $attributes }}>
    {{ $slot->isNotEmpty() ? $slot : $label }}
</a>
