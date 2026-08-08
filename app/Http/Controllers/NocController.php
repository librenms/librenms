<?php

/**
 * NocController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Karoly Kohegyi
 * @author     Karoly Kohegyi <kohegyi.karoly@gmail.com>
 */

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Dashboard;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NocController extends Controller
{
    /** @var \Illuminate\Support\Collection<int, \App\Models\Dashboard> */
    private $dashboards;

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function playlists(Request $request)
    {
        $user = $request->user();
        $cleanup_data = $this->getNocCleanupData($user);
        if ($cleanup_data !== null) {
            return view('overview.noc_cleanup', $cleanup_data);
        }

        $dashboard_name_map = $this->getAvailableDashboards($user)
            ->mapWithKeys(fn (Dashboard $dashboard): array => [$dashboard->dashboard_id => $dashboard->dashboard_name]);

        return view('overview.noc_playlists', [
            'playlists' => $this->getNocPlaylists($user),
            'dashboards' => $this->getAvailableDashboards($user)->values(),
            'dashboard_name_map' => $dashboard_name_map,
        ]);
    }

    public function storePlaylist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:64',
            'dashboard_ids' => 'required|array|min:1',
            'dashboard_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $playlists = $this->getNocPlaylists($user);
        $dashboard_ids = $this->filterAllowedDashboardIds($user, $validated['dashboard_ids']);

        if ($dashboard_ids->isEmpty()) {
            return back()->with('error', __('dashboard.noc.playlist_invalid_dashboards'));
        }

        $next_id = ((int) $playlists->max('id')) + 1;
        $playlists->push([
            'id' => $next_id,
            'name' => trim((string) $validated['name']),
            'dashboard_ids' => $dashboard_ids->all(),
        ]);

        /** @phpstan-ignore argument.type */
        $this->saveNocPlaylists($user, $playlists);

        return back()->with('status', __('dashboard.noc.playlist_saved'));
    }

    public function updatePlaylist(Request $request, int $playlistId): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:64',
            'dashboard_ids' => 'required|array|min:1',
            'dashboard_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $playlists = $this->getNocPlaylists($user);
        $dashboard_ids = $this->filterAllowedDashboardIds($user, $validated['dashboard_ids']);

        if ($dashboard_ids->isEmpty()) {
            return back()->with('error', __('dashboard.noc.playlist_invalid_dashboards'));
        }

        $index = $playlists->search(fn (array $playlist): bool => $playlist['id'] === $playlistId);
        if ($index === false) {
            return back()->with('error', __('dashboard.noc.playlist_not_found'));
        }

        $playlists->put((int) $index, [
            'id' => $playlistId,
            'name' => trim((string) $validated['name']),
            'dashboard_ids' => $dashboard_ids->all(),
        ]);

        /** @phpstan-ignore argument.type */
        $this->saveNocPlaylists($user, $playlists);

        return back()->with('status', __('dashboard.noc.playlist_saved'));
    }

    public function destroyPlaylist(Request $request, int $playlistId): RedirectResponse
    {
        $user = $request->user();
        $playlists = $this->getNocPlaylists($user)
            ->reject(fn (array $playlist): bool => $playlist['id'] === $playlistId)
            ->values();

        /** @phpstan-ignore argument.type */
        $this->saveNocPlaylists($user, $playlists);

        return back()->with('status', __('dashboard.noc.playlist_deleted'));
    }

    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function play(Request $request)
    {
        return $this->renderNocPlaylist($request);
    }

    public function cleanupAllPlaylists(Request $request): RedirectResponse
    {
        $user = $request->user();
        $allowed_ids = $this->getAvailableDashboards($user)->keys();

        $playlists = $this->getNocPlaylists($user)
            ->map(function (array $playlist) use ($allowed_ids): array {
                $playlist['dashboard_ids'] = collect($playlist['dashboard_ids'])
                    ->filter(fn (int $id): bool => $allowed_ids->contains($id))
                    ->values()
                    ->all();

                return $playlist;
            })
            ->filter(fn (array $playlist): bool => ! empty($playlist['dashboard_ids']))
            ->values();

        /** @phpstan-ignore argument.type */
        $this->saveNocPlaylists($user, $playlists);

        return redirect()->route('noc.playlists')->with('status', __('dashboard.noc.cleanup_done'));
    }

    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    private function renderNocPlaylist(Request $request)
    {
        $validated = $request->validate([
            'playlist_id' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $playlist_id = (int) $validated['playlist_id'];
        $playlists = $this->getNocPlaylists($user);
        $playlist = $playlists->first(fn (array $item): bool => $item['id'] === $playlist_id);

        if (! is_array($playlist)) {
            return redirect()->route('noc.playlists')->with('error', __('dashboard.noc.playlist_not_found'));
        }

        $dashboards = $this->getAvailableDashboards($user);
        $valid_ids = collect($playlist['dashboard_ids'])
            ->filter(fn (int $id): bool => $dashboards->has($id))
            ->values();

        $noc_dashboards = $valid_ids
            ->map(fn (int $dashboard_id) => $dashboards->get($dashboard_id))
            ->filter()
            ->values();

        if ($noc_dashboards->isEmpty()) {
            return redirect()->route('noc.playlists')->with('error', __('dashboard.noc.empty'));
        }

        return view('overview.noc', [
            'noc_dashboards' => $noc_dashboards,
            'rotate_seconds' => max((int) LibrenmsConfig::get('webui.noc_rotate_seconds', 15), 1),
        ]);
    }

    /**
     * @return array{playlists_with_missing: Collection<int, array{id: int, name: string, missing_ids: non-empty-array<int, int>}>, missing_ids: Collection<int, int>}|null
     */
    private function getNocCleanupData(User $user): ?array
    {
        $allowed_ids = $this->getAvailableDashboards($user)->keys();
        $playlists_with_missing = $this->getNocPlaylists($user)
            ->map(function (array $playlist) use ($allowed_ids): array {
                $missing_ids = collect($playlist['dashboard_ids'])
                    ->filter(fn (int $id): bool => ! $allowed_ids->contains($id))
                    ->values()
                    ->all();

                return [
                    'id' => $playlist['id'],
                    'name' => $playlist['name'],
                    'missing_ids' => $missing_ids,
                ];
            })
            ->filter(fn (array $playlist): bool => ! empty($playlist['missing_ids']))
            ->values();

        if ($playlists_with_missing->isEmpty()) {
            return null;
        }

        $missing_ids = $playlists_with_missing
            ->flatMap(fn (array $playlist): array => $playlist['missing_ids'])
            ->unique()
            ->values();

        return [
            'playlists_with_missing' => $playlists_with_missing,
            'missing_ids' => $missing_ids,
        ];
    }

    /**
     * @param  User  $user
     * @return \Illuminate\Support\Collection<int, \App\Models\Dashboard>
     */
    private function getAvailableDashboards(User $user): Collection
    {
        if ($this->dashboards === null) {
            $this->dashboards = Dashboard::hasAccess($user)->with('user:user_id,username')
                ->orderBy('dashboard_name')->get()->keyBy('dashboard_id');
        }

        return $this->dashboards;
    }

    /**
     * @param  array<int, mixed>  $dashboard_ids
     * @return Collection<int, int<1, max>>
     */
    private function filterAllowedDashboardIds(User $user, array $dashboard_ids): Collection
    {
        $allowed_ids = $this->getAvailableDashboards($user)->keys();

        return collect($dashboard_ids)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0 && $allowed_ids->contains($id))
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, array{id: int<1, max>, name: non-empty-string, dashboard_ids: non-empty-array<int, int>}>
     */
    private function getNocPlaylists(User $user): Collection
    {
        $raw_prefs = UserPref::query()
            ->where('user_id', $user->user_id)
            ->where('pref', 'like', 'noc_playlist_%')
            ->get(['pref', 'value']);

        $playlists = [];

        foreach ($raw_prefs as $pref) {
            $pref_key = (string) $pref->pref;

            if (preg_match('/^noc_playlist_(\d+)_name$/', $pref_key, $matches) === 1) {
                $playlist_id = (int) $matches[1];
                $playlists[$playlist_id] ??= ['id' => $playlist_id, 'name' => '', 'dashboard_ids' => []];
                $playlists[$playlist_id]['name'] = trim((string) $pref->value);
                continue;
            }

            if (preg_match('/^noc_playlist_(\d+)_dash_(\d+)$/', $pref_key, $matches) === 1) {
                $playlist_id = (int) $matches[1];
                $dashboard_id = (int) $matches[2];
                if ($dashboard_id <= 0) {
                    continue;
                }

                $playlists[$playlist_id] ??= ['id' => $playlist_id, 'name' => '', 'dashboard_ids' => []];
                $playlists[$playlist_id]['dashboard_ids'][] = $dashboard_id;
            }
        }

        return collect($playlists)
            ->map(function (array $playlist): array {
                $playlist['dashboard_ids'] = collect($playlist['dashboard_ids'])
                    ->map(fn (int $id): int => (int) $id)
                    ->filter(fn (int $id): bool => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                return $playlist;
            })
            ->filter(fn (array $playlist): bool => $playlist['id'] > 0 && $playlist['name'] !== '' && ! empty($playlist['dashboard_ids']))
            ->sortBy('id')
            ->values();
    }

    /**
     * @param  Collection<int, array{id: int, name: string, dashboard_ids: array<int, int>}>  $playlists
     */
    private function saveNocPlaylists(User $user, Collection $playlists): void
    {
        UserPref::query()
            ->where('user_id', $user->user_id)
            ->where('pref', 'like', 'noc_playlist_%')
            ->delete();

        UserPref::forgetPref($user, 'noc_playlists');

        foreach ($playlists->values() as $playlist) {
            UserPref::setPref($user, "noc_playlist_{$playlist['id']}_name", $playlist['name']);

            foreach ($playlist['dashboard_ids'] as $dashboard_id) {
                UserPref::setPref($user, "noc_playlist_{$playlist['id']}_dash_{$dashboard_id}", 1);
            }
        }
    }
}
