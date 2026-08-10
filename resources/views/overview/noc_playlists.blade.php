@extends('layouts.librenmsv1')

@section('title', __('dashboard.noc.menu'))

@section('content')
<div class="container-fluid">
    <div class="row tw:mt-4">
        <div class="col-md-12">
            <x-panel title="{{ __('dashboard.noc.menu') }}">
                @if (session('status'))
                    <div class="alert alert-success js-noc-flash-alert">
                        {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger js-noc-flash-alert">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger js-noc-flash-alert">
                        <ul class="tw:m-0 tw:pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bootgrid-header tw:mb-4">
                    <div class="row">
                        <div class="col-sm-12 actionBar tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2">
                            <div class="tw:flex tw:items-center tw:gap-2">
                                <button type="button" class="btn btn-success btn-sm" onclick="toggleNocCreatePlaylist()">
                                    {{ __('dashboard.noc.create_playlist') }}
                                </button>
                            </div>
                            <div class="actions tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                                <div class="search form-group tw:m-0">
                                    <div class="input-group input-group-sm tw:min-w-[220px]">
                                        <span class="input-group-addon">
                                            <i class="fa fa-search fa-fw" aria-hidden="true"></i>
                                        </span>
                                        <input id="noc-playlist-search" type="search" class="search-field form-control" placeholder="{{ __('Search') }}" aria-label="{{ __('Search') }}">
                                    </div>
                                </div>
                                <div class="btn-group tw:m-0">
                                    <button id="noc-playlist-refresh" type="button" class="btn btn-default btn-sm" title="{{ __('Refresh') }}" aria-label="{{ __('Refresh') }}">
                                        <i class="fa fa-refresh fa-fw" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="btn-group tw:m-0">
                                    <select id="noc-playlist-row-count" class="form-control input-sm" aria-label="{{ __('Rows') }}">
                                        <option value="10">10</option>
                                        <option value="25" selected>25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="-1">{{ __('All') }}</option>
                                    </select>
                                </div>
                                <div class="btn-group tw:m-0">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="{{ __('Columns') }}" aria-label="{{ __('Columns') }}">
                                        <i class="fa fa-columns fa-fw" aria-hidden="true"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" id="noc-playlist-columns-menu">
                                        <li><label class="tw:flex tw:items-center tw:gap-2 tw:px-3 tw:py-2 tw:m-0"><input type="checkbox" data-column="name" checked> {{ __('dashboard.noc.playlist_name') }}</label></li>
                                        <li><label class="tw:flex tw:items-center tw:gap-2 tw:px-3 tw:py-2 tw:m-0"><input type="checkbox" data-column="dashboard-count" checked> {{ __('dashboard.noc.dashboards') }}</label></li>
                                        <li><label class="tw:flex tw:items-center tw:gap-2 tw:px-3 tw:py-2 tw:m-0"><input type="checkbox" data-column="dashboard-names" checked> {{ __('dashboard.noc.dashboard_names') }}</label></li>
                                        <li><label class="tw:flex tw:items-center tw:gap-2 tw:px-3 tw:py-2 tw:m-0"><input type="checkbox" data-column="actions" checked> {{ __('dashboard.noc.actions') }}</label></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default tw:hidden" id="noc-create-playlist-panel">
                    <div class="panel-body">
                        <form method="POST" action="{{ route('noc.playlists.store') }}">
                            @csrf
                            <div class="form-group">
                                <label for="new_playlist_name">
                                    {{ __('dashboard.noc.playlist_name') }}
                                </label>
                                <input id="new_playlist_name" name="name" type="text" class="form-control" maxlength="64" required>
                            </div>
                            <div class="form-group">
                                <label for="new_playlist_dashboards">
                                    {{ __('dashboard.noc.dashboards') }}
                                </label>
                                <div id="new_playlist_dashboards" class="form-control tw:h-auto tw:max-h-[260px] tw:overflow-y-auto tw:p-2.5">
                                    @foreach ($dashboards as $dashboard)
                                        <div class="checkbox tw:mb-2 last:tw:mb-0">
                                            <label class="tw:flex tw:items-center tw:gap-2 tw:m-0 tw:font-normal">
                                                <input type="checkbox" name="dashboard_ids[]" value="{{ $dashboard->dashboard_id }}">
                                                <span>{{ $dashboard->dashboard_name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="help-block">
                                    {{ __('dashboard.noc.multi_select_help') }}
                                </p>
                            </div>
                            <button type="submit" class="btn btn-success">
                                {{ __('dashboard.noc.save_playlist') }}
                            </button>
                            <button type="button" class="btn btn-default" onclick="toggleNocCreatePlaylist()">
                                {{ __('dashboard.noc.cleanup_cancel') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-condensed table-striped">
                        <thead>
                        <tr>
                            <th class="tw:whitespace-nowrap tw:w-[1%]" data-column="name">
                                {{ __('dashboard.noc.playlist_name') }}
                            </th>
                            <th class="tw:whitespace-nowrap tw:w-[1%]" data-column="dashboard-count">
                                {{ __('dashboard.noc.dashboards') }}
                            </th>
                            <th data-column="dashboard-names">
                                {{ __('dashboard.noc.dashboard_names') }}
                            </th>
                            <th class="text-right" data-column="actions">
                                {{ __('dashboard.noc.actions') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($playlists as $playlist)
                            <tr class="noc-playlist-row" data-playlist-id="{{ $playlist['id'] }}">
                                <td class="tw:whitespace-nowrap" data-column="name">{{ $playlist['name'] }}</td>
                                <td class="tw:whitespace-nowrap" data-column="dashboard-count">{{ count($playlist['dashboard_ids']) }}</td>
                                <td data-column="dashboard-names">
                                    {{ collect($playlist['dashboard_ids'])->map(fn ($dashboardId) => $dashboard_name_map[$dashboardId] ?? ('#' . $dashboardId))->implode(', ') }}
                                </td>
                                <td class="text-right tw:whitespace-nowrap" data-column="actions">
                                    <a class="btn btn-xs btn-default" href="{{ route('noc.play', ['playlist_id' => $playlist['id']]) }}">
                                        {{ __('dashboard.noc.play') }}
                                    </a>
                                    <button type="button" class="btn btn-xs btn-primary" onclick="toggleNocModifyPlaylist({{ $playlist['id'] }})">
                                        {{ __('dashboard.noc.modify_playlist') }}
                                    </button>
                                    <form method="POST" action="{{ route('noc.playlists.destroy', ['playlistId' => $playlist['id']]) }}" class="tw:inline-block" onsubmit="return confirm('{{ __('dashboard.noc.playlist_delete_confirm') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">
                                            {{ __('dashboard.noc.delete_playlist') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="noc-modify-row-{{ $playlist['id'] }}" class="tw:hidden noc-playlist-detail-row" data-playlist-id="{{ $playlist['id'] }}">
                                <td colspan="4" class="tw:p-0">
                                    <div class="panel panel-default tw:m-0">
                                        <div class="panel-body">
                                            <form method="POST" action="{{ route('noc.playlists.update', ['playlistId' => $playlist['id']]) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="form-group">
                                                    <label>
                                                        {{ __('dashboard.noc.playlist_name') }}
                                                    </label>
                                                    <input name="name" type="text" class="form-control" maxlength="64" required value="{{ $playlist['name'] }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        {{ __('dashboard.noc.dashboards') }}
                                                    </label>
                                                    <div class="form-control tw:h-auto tw:max-h-[260px] tw:overflow-y-auto tw:p-2.5">
                                                        @foreach ($dashboards as $dashboard)
                                                            <div class="checkbox tw:mb-2 last:tw:mb-0">
                                                                <label class="tw:flex tw:items-center tw:gap-2 tw:m-0 tw:font-normal">
                                                                    <input type="checkbox" name="dashboard_ids[]" value="{{ $dashboard->dashboard_id }}" @checked(in_array($dashboard->dashboard_id, $playlist['dashboard_ids'], true))>
                                                                    <span>{{ $dashboard->dashboard_name }}</span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary">
                                                    {{ __('dashboard.noc.save_playlist') }}
                                                </button>
                                                <button type="button" class="btn btn-default" onclick="toggleNocModifyPlaylist({{ $playlist['id'] }})">
                                                    {{ __('dashboard.noc.cleanup_cancel') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bootgrid-footer tw:mt-2">
                    <div class="row">
                        <div class="col-sm-6">
                            <ul id="noc-playlist-pagination" class="pagination pagination-sm tw:m-0"></ul>
                        </div>
                        <div class="col-sm-6 infoBar">
                            <div class="text-right">
                                <span id="noc-playlist-info" class="text-muted">{{ __('Showing') }} 0 {{ __('to') }} 0 {{ __('of') }} 0 {{ __('entries') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-panel>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .dark #noc-create-playlist-panel .form-control,
    .dark .noc-playlist-detail-row .form-control {
        color: #e5e7eb;
        background-color: #3e444c;
        border-color: #222;
    }

    .dark #noc-create-playlist-panel .form-control::placeholder,
    .dark .noc-playlist-detail-row .form-control::placeholder {
        color: #cbd5e1;
    }
</style>
@endpush

@push('scripts')
<script>
    var nocCurrentPage = 1;

    function toggleNocCreatePlaylist() {
        $('#noc-create-playlist-panel').toggleClass('tw:hidden');
    }

    function toggleNocModifyPlaylist(playlistId) {
        var row = $('#noc-modify-row-' + playlistId);

        if (row.is(':visible')) {
            row.addClass('tw:hidden').hide();
        } else {
            row.removeClass('tw:hidden').show();
        }
    }

    function renderNocPagination(totalRows, rowCount) {
        var pagination = $('#noc-playlist-pagination');
        pagination.empty();

        if (rowCount === -1 || totalRows === 0) {
            return;
        }

        var totalPages = Math.max(1, Math.ceil(totalRows / rowCount));
        var previousDisabled = nocCurrentPage <= 1 ? ' class="disabled"' : '';
        var nextDisabled = nocCurrentPage >= totalPages ? ' class="disabled"' : '';

        pagination.append('<li' + previousDisabled + '><a href="#" data-page="' + (nocCurrentPage - 1) + '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>');

        for (var page = 1; page <= totalPages; page++) {
            var activeClass = page === nocCurrentPage ? ' class="active"' : '';
            pagination.append('<li' + activeClass + '><a href="#" data-page="' + page + '">' + page + '</a></li>');
        }

        pagination.append('<li' + nextDisabled + '><a href="#" data-page="' + (nocCurrentPage + 1) + '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>');
    }

    function updateNocPlaylistView() {
        var searchTerm = $('#noc-playlist-search').val().toLowerCase();
        var rowCount = parseInt($('#noc-playlist-row-count').val(), 10);
        var matchedRows = [];

        $('.noc-playlist-row').each(function () {
            var row = $(this);
            var rowText = row.text().toLowerCase();
            var matches = searchTerm === '' || rowText.indexOf(searchTerm) !== -1;

            if (matches) {
                matchedRows.push(row);
            }
        });

        var totalRows = matchedRows.length;
        var totalPages = rowCount === -1 ? 1 : Math.max(1, Math.ceil(totalRows / rowCount));

        if (nocCurrentPage > totalPages) {
            nocCurrentPage = totalPages;
        }

        if (nocCurrentPage < 1) {
            nocCurrentPage = 1;
        }

        $('.noc-playlist-row').hide();
        $('.noc-playlist-detail-row').hide();

        var startIndex = rowCount === -1 ? 0 : (nocCurrentPage - 1) * rowCount;
        var endIndex = rowCount === -1 ? totalRows : Math.min(startIndex + rowCount, totalRows);

        for (var i = startIndex; i < endIndex; i++) {
            matchedRows[i].show();
        }

        var start = totalRows === 0 ? 0 : startIndex + 1;
        var end = totalRows === 0 ? 0 : endIndex;
        $('#noc-playlist-info').text('{{ __('Showing') }} ' + start + ' {{ __('to') }} ' + end + ' {{ __('of') }} ' + totalRows + ' {{ __('entries') }}');

        renderNocPagination(totalRows, rowCount);
    }

    $(document).ready(function () {
        window.setTimeout(function () {
            $('.js-noc-flash-alert').fadeOut(400, function () {
                $(this).remove();
            });
        }, 3000);

        $('#noc-playlist-search').on('input', function () {
            nocCurrentPage = 1;
            updateNocPlaylistView();
        });

        $('#noc-playlist-row-count').on('change', function () {
            nocCurrentPage = 1;
            updateNocPlaylistView();
        });

        $('#noc-playlist-refresh').on('click', function () {
            $('#noc-playlist-search').val('');
            $('#noc-playlist-row-count').val('25');
            nocCurrentPage = 1;
            updateNocPlaylistView();
        });

        $('#noc-playlist-pagination').on('click', 'a[data-page]', function (event) {
            event.preventDefault();
            var item = $(this).parent();
            if (item.hasClass('disabled') || item.hasClass('active')) {
                return;
            }

            nocCurrentPage = parseInt($(this).attr('data-page'), 10);
            updateNocPlaylistView();
        });

        $('#noc-playlist-columns-menu input[type="checkbox"]').on('change', function () {
            var column = $(this).attr('data-column');
            var visible = $(this).is(':checked');

            $('.table thead th[data-column="' + column + '"]').toggle(visible);
            $('.table tbody td[data-column="' + column + '"]').toggle(visible);
        });

        updateNocPlaylistView();
    });
</script>
@endpush
