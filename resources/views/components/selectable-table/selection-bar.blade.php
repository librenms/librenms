@props([
    'pageSelected' => __('components.selectable-table.page_selected', ['count' => '__count__']),
    'selectAllMatching' => __('components.selectable-table.select_all_matching', ['count' => '__count__']),
    'allMatchingSelected' => __('components.selectable-table.all_matching_selected', ['count' => '__count__']),
])

{{-- bulk action bar shown while rows are selected, the slot holds the action buttons. Text props use __count__ as the count placeholder --}}
<div x-show="selectionCount > 0" x-cloak
     {{ $attributes->class(['tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:px-4 tw:py-2 tw:border-b tw:border-blue-200 tw:dark:border-blue-900 tw:bg-blue-50 tw:dark:bg-blue-950/40']) }}>
    <span class="tw:font-semibold tw:mr-2" x-text="@js(__('components.selectable-table.selected', ['count' => '__count__'])).replace('__count__', selectionCount)"></span>
    {{ $slot }}
    <button type="button" class="tw:ml-auto tw:p-0 tw:border-0 tw:bg-transparent tw:text-blue-600 tw:hover:underline tw:dark:text-blue-400" x-on:click="clearSelection()">{{ __('Clear selection') }}</button>
</div>

{{-- offer to extend a full page selection to every row matching the filter --}}
<div x-show="pageSelected && total > rows.length" x-cloak
     class="tw:px-4 tw:py-2 tw:text-center tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200 tw:bg-gray-50 tw:dark:bg-dark-gray-400">
    <span x-text="(allMatching ? @js($allMatchingSelected) : @js($pageSelected)).replace('__count__', allMatching ? total : rows.length)"></span>
    <button type="button"
            class="tw:p-0 tw:border-0 tw:bg-transparent tw:text-blue-600 tw:hover:underline tw:dark:text-blue-400"
            x-on:click="allMatching ? clearSelection() : allMatching = true"
            x-text="allMatching ? @js(__('Clear selection')) : @js($selectAllMatching).replace('__count__', total)"></button>
</div>
