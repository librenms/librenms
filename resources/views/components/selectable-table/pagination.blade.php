<div class="tw:text-gray-500 tw:dark:text-dark-white-400" x-show="total > 0"
     x-text="@js(__('Showing :first–:last of :total', ['first' => '__first__', 'last' => '__last__', 'total' => '__total__'])).replace('__first__', firstItem).replace('__last__', lastItem).replace('__total__', total)"></div>
<div class="tw:flex tw:items-center tw:gap-2 tw:ml-auto">
    <select x-model.number="perPage" class="lnms-input tw:w-auto" aria-label="{{ __('components.selectable-table.per_page') }}">
        @foreach ([25, 50, 100, 250] as $count)
            <option value="{{ $count }}">{{ $count }}</option>
        @endforeach
    </select>
    <button type="button" class="lnms-btn lnms-btn-default tw:disabled:opacity-50" x-on:click="goTo(page - 1)" :disabled="page <= 1" aria-label="{{ __('Previous page') }}"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
    <span class="tw:whitespace-nowrap" x-text="page + ' / ' + lastPage"></span>
    <button type="button" class="lnms-btn lnms-btn-default tw:disabled:opacity-50" x-on:click="goTo(page + 1)" :disabled="page >= lastPage" aria-label="{{ __('Next page') }}"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
</div>
