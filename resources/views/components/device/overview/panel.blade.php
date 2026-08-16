@props(['title', 'icon', 'href' => null])

<x-panel {{ $attributes->class(['tw:mb-5 tw:overflow-hidden tw:rounded-lg tw:border tw:border-gray-300 tw:shadow-sm tw:dark:border-dark-gray-200']) }}>
    <x-slot:heading class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">
        @if($href)<a href="{{ $href }}">@endif
            <i class="{{ $icon }} fa-lg icon-theme" aria-hidden="true"></i>
            <strong>{{ $title }}</strong>
        @if($href)</a>@endif
    </x-slot:heading>
    <x-slot:slot class="tw:p-0! tw:bg-white tw:dark:bg-dark-gray-400">
        {{ $slot }}
    </x-slot:slot>
</x-panel>
