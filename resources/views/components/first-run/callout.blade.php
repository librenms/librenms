@props(['type' => 'info'])
@php
    // tinted, so a notice stands out without overpowering the page in either theme
    [$colors, $icon] = match ($type) {
        'success' => ['tw:bg-green-50 tw:border-green-200 tw:text-green-900 tw:dark:bg-green-400/10 tw:dark:border-green-400/20 tw:dark:text-green-100', 'fa-circle-check tw:text-green-600 tw:dark:text-green-400'],
        'warning' => ['tw:bg-amber-50 tw:border-amber-200 tw:text-amber-900 tw:dark:bg-amber-400/10 tw:dark:border-amber-400/20 tw:dark:text-amber-100', 'fa-triangle-exclamation tw:text-amber-500 tw:dark:text-amber-400'],
        'danger' => ['tw:bg-red-50 tw:border-red-200 tw:text-red-900 tw:dark:bg-red-400/10 tw:dark:border-red-400/20 tw:dark:text-red-100', 'fa-circle-exclamation tw:text-red-600 tw:dark:text-red-400'],
        default => ['tw:bg-sky-50 tw:border-sky-200 tw:text-sky-900 tw:dark:bg-sky-400/10 tw:dark:border-sky-400/20 tw:dark:text-sky-100', 'fa-circle-info tw:text-sky-600 tw:dark:text-sky-400'],
    };
@endphp
<div {{ $attributes->class(['tw:flex tw:gap-3 tw:rounded-lg tw:border tw:px-4 tw:py-3 tw:text-sm tw:text-left', $colors]) }}>
    <i class="fa-solid {{ $icon }} tw:mt-0.5" aria-hidden="true"></i>
    <div class="tw:min-w-0 tw:flex-1">{{ $slot }}</div>
</div>
