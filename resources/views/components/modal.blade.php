@props([
    'name' => null,
    'show' => null,
    'title' => null,
    'maxWidth' => 'lg',
])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'tw:max-w-sm',
    'md' => 'tw:max-w-md',
    'lg' => 'tw:max-w-lg',
    'xl' => 'tw:max-w-xl',
    '2xl' => 'tw:max-w-2xl',
    default => $maxWidth,
};

$showExpr = $show ?? $name;
@endphp

<div x-show="{{ $showExpr }}" x-cloak
     x-on:keydown.escape.window="{{ $showExpr }} = false"
     {{ $attributes->merge(['class' => 'tw:fixed tw:inset-0 tw:z-50 tw:flex tw:items-center tw:justify-center tw:p-4 tw:bg-black/50 tw:backdrop-blur-xs']) }}>
    <div x-on:click.outside="{{ $showExpr }} = false"
         class="tw:bg-white tw:dark:bg-dark-gray-500 tw:text-gray-900 tw:dark:text-dark-white-100 tw:rounded-xl tw:shadow-2xl tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:w-full {{ $maxWidthClass }} tw:overflow-hidden tw:flex tw:flex-col tw:max-h-[85vh]">

        @if (isset($heading))
            <div {{ $heading->attributes->class(['tw:flex tw:items-center tw:justify-between tw:px-5 tw:py-3.5 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200']) }}>
                {{ $heading }}
                <button type="button"
                        x-on:click="{{ $showExpr }} = false"
                        class="lnms-btn lnms-btn-default tw:h-7 tw:w-7 tw:p-0 tw:flex tw:items-center tw:justify-center tw:text-xs tw:rounded-full"
                        aria-label="{{ __('Close') }}">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
        @elseif ($title)
            <div class="tw:flex tw:items-center tw:justify-between tw:px-5 tw:py-3.5 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200">
                <h4 class="tw:m-0 tw:text-base tw:font-semibold tw:flex tw:items-center tw:gap-2">
                    {{ $title }}
                </h4>
                <button type="button"
                        x-on:click="{{ $showExpr }} = false"
                        class="lnms-btn lnms-btn-default tw:h-7 tw:w-7 tw:p-0 tw:flex tw:items-center tw:justify-center tw:text-xs tw:rounded-full"
                        aria-label="{{ __('Close') }}">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        <div class="tw:p-5 tw:overflow-y-auto tw:space-y-5 tw:text-sm">
            {{ $slot }}
        </div>

        @isset($footer)
            <div {{ $footer->attributes->class(['tw:px-5 tw:py-3 tw:bg-gray-50 tw:dark:bg-dark-gray-400 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-200 tw:flex tw:items-center tw:justify-end tw:gap-2']) }}>
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
