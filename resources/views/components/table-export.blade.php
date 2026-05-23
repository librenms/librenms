@props([
    'exportRoute',
    'params' => [],
    'filter' => [],
    'filterName' => null,
    'page' => 1,
    'perPage' => 50,
])

<div
    {{ $attributes->merge(['class' => 'btn-group']) }}
    x-data="tableExport({
        exportRoute: '{{ $exportRoute }}',
        params: {{ Js::from($params) }},
        filter: {{ Js::from($filter) }},
        filterName: '{{ $filterName }}',
        page: {{ $page }},
        perPage: {{ $perPage }},
    })"
    x-init="window.addEventListener('filter:apply', (e) => onFilterChanged(e.detail))"
>
    <button
        type="button"
        class="btn btn-default dropdown-toggle"
        data-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false"
    >
        <i class="fa fa-download"></i> <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right">
        <li>
            <a href="#" x-on:click.prevent="exportData('visible')">
                <i class="fa-solid fa-fw fa-file-csv"></i> {{ __('Export page') }}
            </a>
        </li>
        <li>
            <a href="#" x-on:click.prevent="exportData('all')">
                <i class="fa-solid fa-fw fa-file-csv"></i> {{ __('Export all results') }}
            </a>
        </li>
    </ul>
</div>

@once
    @push('scripts')
        <script>
            function tableExport({ exportRoute, params, filter, filterName, page, perPage, perPageParam }) {
                return {
                    exportRoute,
                    params,
                    filter,
                    filterName,
                    page,
                    perPage,
                    perPageParam,

                    onFilterChanged(detail) {
                        if (this.filterName && detail?.name !== this.filterName) return;

                        if (detail?.filters && typeof detail.filters === 'object') {
                            this.filter = detail.filters;
                        }
                    },

                    exportData(type) {
                        const url = new URL(this.exportRoute, window.location.origin);

                        LibreNMS.Url.applyNestedParamsToUrl(url, 'filter', this.filter);

                        Object.entries(this.params).forEach(([key, value]) => {
                            if (value !== null && value !== '') {
                                url.searchParams.set(key, value);
                            }
                        });

                        if (type === 'visible') {
                            url.searchParams.set('export', 'page');
                            url.searchParams.set('page', String(this.page));
                            url.searchParams.set('perPage', String(this.perPage));
                        } else {
                            url.searchParams.set('export', 'all');
                        }

                        window.location.href = url.toString();
                    }
                };
            }
        </script>
    @endpush
@endonce
