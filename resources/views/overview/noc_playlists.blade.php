@extends('layouts.librenmsv1')

@section('title', __('dashboard.noc.menu'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h3>{{ __('dashboard.noc.menu') }}</h3>

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="margin-bottom: 10px;">
                <button type="button" class="btn btn-success" onclick="toggleNocCreatePlaylist()">
                    {{ __('dashboard.noc.create_playlist') }}
                </button>
            </div>

            <div class="panel panel-default" id="noc-create-playlist-panel" style="display: none;">
                <div class="panel-body">
                    <form method="POST" action="{{ route('dashboard.noc.playlists.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="new_playlist_name">{{ __('dashboard.noc.playlist_name') }}</label>
                            <input id="new_playlist_name" name="name" type="text" class="form-control" maxlength="64" required>
                        </div>
                        <div class="form-group">
                            <label for="new_playlist_dashboards">{{ __('dashboard.noc.dashboards') }}</label>
                            <div id="new_playlist_dashboards" class="form-control" style="height: auto; max-height: 260px; overflow-y: auto; padding: 10px;">
                                @foreach($dashboards as $dashboard)
                                    <div class="checkbox" style="margin: 0 0 8px 0;">
                                        <label style="display: flex; align-items: center; gap: 8px; margin: 0; font-weight: normal;">
                                            <input type="checkbox" name="dashboard_ids[]" value="{{ $dashboard->dashboard_id }}">
                                            <span>{{ $dashboard->dashboard_name }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <p class="help-block">{{ __('dashboard.noc.multi_select_help') }}</p>
                        </div>
                        <button type="submit" class="btn btn-success">{{ __('dashboard.noc.save_playlist') }}</button>
                    </form>
                </div>
            </div>

            <table class="table table-striped table-condensed">
                <thead>
                <tr>
                    <th style="white-space: nowrap; width: 1%;">{{ __('dashboard.noc.playlist_name') }}</th>
                    <th style="white-space: nowrap; width: 1%;">{{ __('dashboard.noc.dashboards') }}</th>
                    <th>{{ __('dashboard.noc.dashboard_names') }}</th>
                    <th class="text-right">{{ __('dashboard.noc.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($playlists as $playlist)
                    <tr>
                        <td style="white-space: nowrap;">{{ $playlist['name'] }}</td>
                        <td style="white-space: nowrap;">{{ count($playlist['dashboard_ids']) }}</td>
                        <td>{{ collect($playlist['dashboard_ids'])->map(fn ($dashboardId) => $dashboard_name_map[$dashboardId] ?? ('#' . $dashboardId))->implode(', ') }}</td>
                        <td class="text-right">
                            <a class="btn btn-xs btn-default" href="{{ route('dashboard.noc.play', ['playlist_id' => $playlist['id']]) }}">{{ __('dashboard.noc.play') }}</a>
                            <button type="button" class="btn btn-xs btn-primary" onclick="toggleNocModifyPlaylist({{ $playlist['id'] }})">
                                {{ __('dashboard.noc.modify_playlist') }}
                            </button>
                            <form method="POST" action="{{ route('dashboard.noc.playlists.destroy', ['playlistId' => $playlist['id']]) }}" style="display: inline-block;" onsubmit="return confirm('{{ __('dashboard.noc.playlist_delete_confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger">{{ __('dashboard.noc.delete_playlist') }}</button>
                            </form>
                        </td>
                    </tr>
                    <tr id="noc-modify-row-{{ $playlist['id'] }}" style="display: none;">
                        <td colspan="3">
                            <div class="panel panel-default" style="margin: 0;">
                                <div class="panel-body">
                                    <form method="POST" action="{{ route('dashboard.noc.playlists.update', ['playlistId' => $playlist['id']]) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-group">
                                            <label>{{ __('dashboard.noc.playlist_name') }}</label>
                                            <input name="name" type="text" class="form-control" maxlength="64" required value="{{ $playlist['name'] }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('dashboard.noc.dashboards') }}</label>
                                            <div class="form-control" style="height: auto; max-height: 260px; overflow-y: auto; padding: 10px;">
                                                @foreach($dashboards as $dashboard)
                                                    <div class="checkbox" style="margin: 0 0 8px 0;">
                                                        <label style="display: flex; align-items: center; gap: 8px; margin: 0; font-weight: normal;">
                                                            <input type="checkbox" name="dashboard_ids[]" value="{{ $dashboard->dashboard_id }}" @checked(in_array($dashboard->dashboard_id, $playlist['dashboard_ids'], true))>
                                                            <span>{{ $dashboard->dashboard_name }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">{{ __('dashboard.noc.save_playlist') }}</button>
                                        <button type="button" class="btn btn-default" onclick="toggleNocModifyPlaylist({{ $playlist['id'] }})">{{ __('dashboard.noc.cleanup_cancel') }}</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleNocCreatePlaylist() {
        $('#noc-create-playlist-panel').toggle();
    }

    function toggleNocModifyPlaylist(playlistId) {
        $('#noc-modify-row-' + playlistId).toggle();
    }
</script>
@endpush
