@props(['color' => 'gray'])
<span {{ $attributes->class([
    'tw:inline-block tw:px-1.5 tw:py-px tw:rounded tw:text-xs tw:font-semibold tw:whitespace-nowrap',
    'tw:bg-green-100 tw:text-green-800 tw:dark:bg-green-900/40 tw:dark:text-green-300' => $color == 'green',
    'tw:bg-amber-100 tw:text-amber-800 tw:dark:bg-amber-900/40 tw:dark:text-amber-300' => $color == 'amber',
    'tw:bg-blue-100 tw:text-blue-800 tw:dark:bg-blue-900/40 tw:dark:text-blue-300' => $color == 'blue',
    'tw:bg-red-100 tw:text-red-800 tw:dark:bg-red-900/40 tw:dark:text-red-300' => $color == 'red',
    'tw:bg-gray-100 tw:text-gray-700 tw:dark:bg-dark-gray-200 tw:dark:text-dark-white-300' => $color == 'gray',
    'tw:font-normal! tw:px-0! tw:text-gray-500 tw:dark:text-dark-white-400' => $color == 'muted',
]) }}>{{ $slot }}</span>
