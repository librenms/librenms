@props([
    'exportRoute',
    'params' => [],
    'filters' => [],
    'page' => 1,
    'perPage' => 50,
    'perPageParam' => 'perPage',
])

<div {{ $attributes->merge(['class' => 'btn-group pull-right']) }}
     data-page="{{ $page }}"
     data-per-page="{{ $perPage }}"
     data-per-page-param="{{ $perPageParam }}"
     data-filters="{{ json_encode($filters) }}">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-download"></i> <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right">
        <li>
            <a href="#" class="table-export-link" data-export-type="visible"
               data-export-route="{{ $exportRoute }}" data-export-params="{{ json_encode($params) }}">
                <i class="fa-solid fa-fw fa-file-csv"></i> {{ __('Export page') }}
            </a>
        </li>
        <li>
            <a href="#" class="table-export-link" data-export-type="all"
               data-export-route="{{ $exportRoute }}" data-export-params="{{ json_encode($params) }}">
                <i class="fa-solid fa-fw fa-file-csv"></i> {{ __('Export all results') }}
            </a>
        </li>
    </ul>
</div>

@once
    @push('scripts')
    <script>
        function appendFiltersToParams(params, filters) {
            if (! filters || typeof filters !== 'object') {
                return;
            }

            Object.entries(filters).forEach(function ([key, operators]) {
                if (! operators || typeof operators !== 'object') {
                    return;
                }

                Object.entries(operators).forEach(function ([operator, value]) {
                    const paramKey = 'filter[' + key + '][' + operator + ']';

                    if (Array.isArray(value)) {
                        params.set(paramKey, value.join(','));
                    } else if (value !== null && value !== undefined) {
                        params.set(paramKey, value);
                    }
                });
            });
        }

        $(document).on('click', '.table-export-link', function (e) {
            e.preventDefault();

            const exportType = $(this).data('export-type');
            const exportRoute = $(this).data('export-route');
            const extraParams = $(this).data('exportParams') || {};
            const $group = $(this).closest('.btn-group');
            const defaultPage = $group.data('page') || 1;
            const defaultPerPage = $group.data('perPage') || 50;
            const perPageParam = $group.data('perPageParam') || 'perPage';
            const serverFilters = $group.data('filters') || {};
            const params = new URLSearchParams(window.location.search);

            appendFiltersToParams(params, serverFilters);

            Object.entries(extraParams).forEach(function ([key, value]) {
                if (value !== null && value !== '') {
                    params.set(key, value);
                }
            });

            if (exportType === 'visible') {
                params.set('export', 'page');
                params.set('page', params.get('page') || String(defaultPage));
                params.set(
                    perPageParam,
                    params.get(perPageParam) || String(defaultPerPage)
                );
            } else {
                params.set('export', 'all');
                params.delete('page');
                params.delete('current');
                params.delete(perPageParam);
            }

            window.location.href = exportRoute + '?' + params.toString();
        });
    </script>
    @endpush
@endonce
