@props([
    'component',
    'config' => [],
    'filterName' => null,
    'filterFields' => [],
    'rowLabel' => null,
    'pageSelected' => __('components.selectable-table.page_selected', ['count' => '__count__']),
    'selectAllMatching' => __('components.selectable-table.select_all_matching', ['count' => '__count__']),
    'allMatchingSelected' => __('components.selectable-table.all_matching_selected', ['count' => '__count__']),
])

{{--
    Paginated, filterable table in a panel, rows are loaded from config.listUrl. See the script below for the server contract.

    component: Alpine.data() name of a LibreNMS.selectableTable() component, config is passed to it
    filterName/filterFields: x-filter for this table, config.filter is the initial filter
    rowLabel: Alpine expression naming a row for screen readers, default row.{rowKey}

    Slots, the current row is "row":
    columns  th cells, required
    row      td cells, required. Attributes go on the tr. Below md rows are cards, cells show their data-label attribute
    actions  bulk action buttons, rows are only selectable when this is set
    heading  extra controls next to the filter
    empty    text when no rows match
    footer   replaces the pagination
    before   content above the table, it can use the table state
    default  content after the table (modals), it can use the table state

    notify(message) shows the result of an action where the selection bar was, use it instead of a success toast
--}}
@php
    $rowKey = $config['rowKey'] ?? 'id';
    $rowLabel ??= 'row.' . $rowKey;
    $selectable = isset($actions);
@endphp
<div {{ $attributes }}
     x-data="{{ $component }}(@js($config))"
     @if ($filterName) x-on:filter:apply.window="if ($event.detail.name === @js($filterName)) applyFilter($event.detail.filters)" @endif>
    {{ $before ?? '' }}

    <x-panel class="tw:mb-0">
        @if ($filterName || isset($heading))
            <x-slot name="heading" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3">
                @if ($filterName)
                    <x-filter :name="$filterName" :fields="$filterFields" :initial="$config['filter'] ?? []" />
                @endif
                @isset($heading)
                    <div {{ $heading->attributes->class(['tw:flex tw:flex-wrap tw:items-center tw:gap-2']) }}>{{ $heading }}</div>
                @endisset
            </x-slot>
        @endif

        <x-slot name="table">
            @if ($selectable)
                <x-selectable-table.selection-bar :page-selected="$pageSelected" :select-all-matching="$selectAllMatching" :all-matching-selected="$allMatchingSelected">
                    {{ $actions }}
                </x-selectable-table.selection-bar>
            @endif

            <div role="status" aria-live="polite">
                <div x-show="notice && selectionCount === 0" x-cloak x-transition.opacity.duration.300ms
                     class="tw:flex tw:items-center tw:gap-2 tw:px-4 tw:py-2 tw:border-b tw:border-green-200 tw:dark:border-green-900 tw:bg-green-50 tw:dark:bg-green-950/40 tw:text-green-800 tw:dark:text-green-300">
                    <i class="fa fa-circle-check" aria-hidden="true"></i>
                    <span x-text="notice"></span>
                    <button type="button" class="tw:ml-auto tw:p-0 tw:border-0 tw:bg-transparent tw:text-green-800 tw:dark:text-green-300 tw:opacity-60 tw:hover:opacity-100" x-on:click="notice = null" aria-label="{{ __('Close') }}">
                        <i class="fa fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="tw:overflow-x-auto" :class="loading && 'tw:opacity-60'">
                <table class="tw:w-full tw:max-md:block">
                    <thead class="tw:max-md:block">
                    <tr {{ $columns->attributes->class(['tw:max-md:flex tw:*:px-3 tw:*:py-2 tw:*:font-semibold tw:*:whitespace-nowrap tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200 tw:text-left tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400']) }}>
                        @if ($selectable)
                            <th class="tw:w-8">
                                <input type="checkbox"
                                       class="tw:m-0"
                                       :checked="pageSelected"
                                       x-effect="$el.indeterminate = ! pageSelected && pageHasSelection"
                                       x-on:change="selectPage($event.target.checked)"
                                       aria-label="{{ __('components.selectable-table.select_page') }}">
                            </th>
                        @endif
                        {{ $columns }}
                    </tr>
                    </thead>
                    <tbody class="tw:max-md:block tw:divide-y tw:divide-gray-100 tw:dark:divide-dark-gray-300">
                    <template x-for="row in rows" :key="row.{{ $rowKey }}">
                        {{-- below md each row is a two column card, the checkbox is pinned to the top left corner --}}
                        <tr {{ $row->attributes->class([
                                'tw:*:px-3 tw:*:py-2 tw:max-md:relative tw:max-md:grid tw:max-md:grid-cols-2 tw:max-md:gap-3 tw:max-md:px-4 tw:max-md:py-3 tw:max-md:*:p-0',
                                'tw:max-md:pl-11' => $selectable,
                                'tw:max-md:[&>[data-label]]:before:block tw:max-md:[&>[data-label]]:before:mb-1 tw:max-md:[&>[data-label]]:before:content-[attr(data-label)] tw:max-md:[&>[data-label]]:before:uppercase tw:max-md:[&>[data-label]]:before:tracking-wider tw:max-md:[&>[data-label]]:before:text-gray-500',
                            ]) }}
                            :class="isSelected(row) ? 'tw:bg-blue-50/60 tw:dark:bg-blue-950/30' : 'tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-400'">
                            @if ($selectable)
                                <td class="tw:max-md:absolute tw:max-md:left-4 tw:max-md:top-3.5">
                                    <input type="checkbox"
                                           class="tw:m-0"
                                           :checked="isSelected(row)"
                                           x-on:change="toggleSelected(row, $event.target.checked)"
                                           :aria-label="@js(__('Select') . ' ') + {{ $rowLabel }}">
                                </td>
                            @endif
                            {{ $row }}
                        </tr>
                    </template>
                    <tr x-show="! loading && rows.length === 0" x-cloak class="tw:max-md:block">
                        <td colspan="100" class="tw:max-md:block tw:px-4 tw:py-8 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">{{ $empty ?? __('components.selectable-table.no_rows') }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </x-slot>

        <x-slot name="footer" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3">
            @isset($footer)
                {{ $footer }}
            @else
                <x-selectable-table.pagination />
            @endisset
        </x-slot>
    </x-panel>

    {{ $slot }}
</div>

@once
    @push('scripts')
        <script>
        /**
         * Shared state for paginated, filterable tables with multi-select, rendered with the x-selectable-table.* blade components.
         * Output once by the x-selectable-table component, move it to the js bundle if it is needed elsewhere.
         *
         * Usage: Alpine.data('name', (config) => LibreNMS.selectableTable(config, { ...page specific properties }))
         * Page properties override the base ones, getters are kept intact.
         *
         * Server contract:
         *  - listUrl returns Laravel paginator json (data, current_page, last_page, total), extra keys are passed to loaded()
         *    query params: filter, sort, order, page, per_page
         *  - selectionData() returns {ids: [...]} for selected rows or {all: true, filter: {...}} for every row matching the filter
         *
         * Hooks: loadParams(url) adds query params to the list request, loaded(data) handles extra response data.
         *
         * @param {object} config listUrl, rowKey (default id), filter, sort (default rowKey) and lang
         * @param {object} component
         */
        window.LibreNMS = window.LibreNMS || {};
        window.LibreNMS.selectableTable = function (config, component = {}) {
            const rowKey = config.rowKey ?? 'id';

            const base = {
                config: config,
                lang: config.lang ?? {},
                filter: config.filter ?? {},
                rows: [],
                sort: config.sort ?? rowKey,
                order: 'asc',
                page: 1,
                lastPage: 1,
                perPage: 50,
                total: 0,
                loading: false,
                loadId: 0,
                selected: {},
                allMatching: false,
                pending: {},
                notice: null,
                noticeTimer: null,

                init() {
                    this.$watch('perPage', () => this.goTo(1));
                    this.load();
                },

                get firstItem() {
                    return (this.page - 1) * this.perPage + 1;
                },

                get lastItem() {
                    return Math.min(this.page * this.perPage, this.total);
                },

                get selectionCount() {
                    return this.allMatching ? this.total : Object.keys(this.selected).length;
                },

                get pageSelected() {
                    return this.rows.length > 0 && this.rows.every((row) => this.isSelected(row));
                },

                get pageHasSelection() {
                    return this.rows.some((row) => this.isSelected(row));
                },

                isPending(key) {
                    return this.pending[key] === true;
                },

                request(key, method, url, data = null) {
                    this.pending[key] = true;

                    return fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: data === null ? undefined : JSON.stringify(data),
                    }).then(async (response) => {
                        const body = await response.json().catch(() => ({}));
                        if (! response.ok) {
                            throw new Error(body.message ?? this.lang.request_failed ?? @js(__('Request failed')));
                        }

                        return body;
                    }).catch((error) => {
                        toastr.error(error.message);
                        throw error;
                    }).finally(() => delete this.pending[key]);
                },

                loadParams(url) {},

                loaded(data) {},

                load() {
                    const url = LibreNMS.Url.applyNestedParamsToUrl(new URL(this.config.listUrl), 'filter', this.filter);
                    url.searchParams.set('sort', this.sort);
                    url.searchParams.set('order', this.order);
                    url.searchParams.set('page', this.page);
                    url.searchParams.set('per_page', this.perPage);
                    this.loadParams(url);

                    const loadId = ++this.loadId;
                    this.loading = true;
                    this.request('load', 'GET', url)
                        .then((data) => {
                            // a newer request was started, ignore this response
                            if (loadId !== this.loadId) return;
                            this.rows = data.data;
                            this.page = data.current_page;
                            this.lastPage = data.last_page;
                            this.total = data.total;
                            this.loaded(data);
                        })
                        .catch(() => {})
                        .finally(() => this.loading = loadId !== this.loadId);
                },

                applyFilter(filter) {
                    if (JSON.stringify(filter) === JSON.stringify(this.filter)) return;
                    this.filter = filter;
                    this.clearSelection();
                    this.goTo(1);
                },

                goTo(page) {
                    this.page = Math.max(1, Math.min(page, this.lastPage));
                    this.load();
                },

                sortBy(column) {
                    this.order = this.sort === column && this.order === 'asc' ? 'desc' : 'asc';
                    this.sort = column;
                    this.goTo(1);
                },

                sortIcon(column) {
                    if (this.sort !== column) return 'fa-sort tw:opacity-30';
                    return this.order === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                },

                // --- Selection ---
                isSelected(row) {
                    return this.allMatching || this.selected[row[rowKey]] === true;
                },

                toggleSelected(row, checked) {
                    if (this.allMatching) {
                        // drop back to selecting the rows that are visible
                        this.allMatching = false;
                        this.rows.forEach((r) => this.selected[r[rowKey]] = true);
                    }

                    if (checked) {
                        this.selected[row[rowKey]] = true;
                    } else {
                        delete this.selected[row[rowKey]];
                    }
                },

                selectPage(checked) {
                    if (this.allMatching) {
                        this.clearSelection();
                        return;
                    }

                    this.rows.forEach((row) => checked ? this.selected[row[rowKey]] = true : delete this.selected[row[rowKey]]);
                },

                clearSelection() {
                    this.selected = {};
                    this.allMatching = false;
                },

                // show the result of an action where the selection bar was, selecting rows again hides it
                notify(message, timeout = 4000) {
                    clearTimeout(this.noticeTimer);
                    this.notice = message;
                    this.noticeTimer = setTimeout(() => this.notice = null, timeout);
                },

                // request data for the selection, every row matching the filter or the selected row ids
                selectionData() {
                    return this.allMatching
                        ? {all: true, filter: this.filter}
                        : {ids: Object.keys(this.selected).map(Number)};
                },
            };

            return Object.defineProperties(base, Object.getOwnPropertyDescriptors(component));
        };
        </script>
    @endpush
@endonce
