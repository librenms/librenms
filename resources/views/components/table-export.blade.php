@props([
    'exportRoute',
    'params' => [],
])

<div {{ $attributes->merge(['class' => 'btn-group pull-right']) }}>
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
        $(document).on('click', '.table-export-link', function (e) {
            e.preventDefault();

            const exportType = $(this).data('export-type');
            const exportRoute = $(this).data('export-route');
            const extraParams = $(this).data('export-params') || {};
            const params = new URLSearchParams(window.location.search);

            Object.entries(extraParams).forEach(([key, value]) => {
                if (value !== null && value !== '') {
                    params.set(key, value);
                }
            });

            if (exportType === 'visible') {
                if (! params.has('page') && ! params.has('current')) {
                    params.set('page', '1');
                }
            } else {
                params.delete('page');
                params.delete('current');
                params.delete('per_page');
                params.delete('perPage');
                params.delete('rowCount');
            }

            window.location.href = exportRoute + '?' + params.toString();
        });
    </script>
    @endpush
@endonce
