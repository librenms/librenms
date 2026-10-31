<?php

/**
 * SearchController.php
 *
 * Network search page, finds devices and hosts in addresses, ARP, ND and FDB tables
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Search\SearchResultsController;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Url;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $sources = [];
        foreach (SearchResultsController::SOURCES as $key => $controller) {
            if ($controller::authorized($user)) {
                $sources[] = [
                    'key' => $key,
                    'label' => $controller::label(),
                    'icon' => $controller::icon(),
                    'description' => $controller::description(),
                    'url' => route("search.results.$key"),
                ];
            }
        }
        $sources = collect($sources);

        // sources in the url (shared links) win over the ones the user last picked
        $selected = array_values(array_intersect(
            explode(',', (string) ($request->input('sources') ?? UserPref::getPref($user, 'network_search_sources'))),
            $sources->pluck('key')->all(),
        ));

        $deviceId = $request->integer('device_id') ?: null;
        $device = $deviceId ? Device::hasAccess($user)->find($deviceId) : null;

        return view('search.index', [
            'query' => (string) $request->input('q'),
            'browse' => $request->has('q'),
            'macFormat' => UserPref::getPref($user, 'network_search_mac_format') ?? 'colon',
            'sources' => $sources,
            'selected' => $selected ?: $sources->pluck('key')->all(),
            'device' => $device,
            'locateUrl' => $user->can('viewAny', Port::class) ? route('search.locate') : null,
        ]);
    }

    /**
     * Translate the old search/search=arp/searchPhrase=1.2.3.4 pages
     */
    public function legacy(): RedirectResponse
    {
        $options = Url::parseOptions();
        $type = $options['search'] ?? '';

        if ($type == 'packages') {
            return redirect()->route('packages.index', array_filter([
                'name' => $options['package'] ?? null,
                'version' => $options['version'] ?? null,
                'arch' => $options['arch'] ?? null,
            ]));
        }

        // without a search phrase the old pages listed every entry
        return redirect()->route('search', [
            'q' => $options['searchPhrase'] ?? $options['address'] ?? '',
            ...array_filter([
                'sources' => array_key_exists($type, SearchResultsController::SOURCES) ? $type : null,
                'device_id' => $options['device_id'] ?? null,
            ]),
        ]);
    }
}
