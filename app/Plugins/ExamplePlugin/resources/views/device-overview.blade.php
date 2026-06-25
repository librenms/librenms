<div class="overview-panel tw:mb-5">
    <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">
        <strong>{{ $title }}</strong> <a href="{{ $url }}">[EDIT]</a>
    </div>
    <div class="tw:flex tw:min-w-0 tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-zinc-800">
        <div class="tw:px-4 tw:py-2">
            {!! Str::markdown($device->notes ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
        </div>
    </div>
</div>
