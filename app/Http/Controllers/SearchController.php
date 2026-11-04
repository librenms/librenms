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

use App\Models\Port;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Url;

class SearchController extends Controller
{
    /** Old search pages, new source filter value */
    private const LEGACY_SOURCES = ['ipv4' => 'interface', 'ipv6' => 'interface', 'mac' => 'interface', 'arp' => 'neighbor', 'fdb' => 'fdb'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Port::class);

        /** @var User $user */
        $user = $request->user();

        return view('search.index', [
            'query' => (string) $request->input('q'),
            'hasQuery' => $request->has('q'),
            'openHost' => $request->input('view') == 'host',
            'macFormat' => UserPref::getPref($user, 'network_search_mac_format') ?? 'colon',
            'filter' => $request->array('filter'),
            'filterFields' => $this->filterFields(),
            'resultsUrl' => route('search.results'),
        ]);
    }

    /**
     * @return array<array{key: string, label: string, type: string, endpoint?: string, options?: array<string, string>, params?: array<string, string>}>
     */
    private function filterFields(): array
    {
        return [
            [
                'key' => 'source',
                'label' => __('search.filter.source'),
                'type' => 'select',
                'options' => [
                    'neighbor' => __('search.source.neighbor'),
                    'fdb' => __('search.source.fdb'),
                    'interface' => __('search.source.interface'),
                    'device' => __('search.source.device'),
                ],
            ],
            [
                'key' => 'device_id',
                'label' => __('search.filter.device'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device'),
            ],
            [
                'key' => 'device.groups.id',
                'label' => __('device.device_group'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device-group'),
            ],
            [
                'key' => 'port_id',
                'label' => __('search.filter.port'),
                'type' => 'select',
                'endpoint' => route('ajax.select.port'),
            ],
            [
                'key' => 'ifAlias',
                'label' => __('search.filter.port_description'),
                'type' => 'text',
            ],
            [
                'key' => 'vlan',
                'label' => __('search.filter.vlan'),
                'type' => 'number',
            ],
            [
                'key' => 'ifType',
                'label' => __('search.filter.interface_type'),
                'type' => 'select',
                'endpoint' => route('ajax.select.port-field'),
                'params' => ['field' => 'ifType'],
            ],
        ];
    }

    /**
     * Translate the old search/search=arp/searchPhrase=1.2.3.4 pages
     */
    public function legacy(): RedirectResponse
    {
        $options = Url::parseOptions();
        $type = $options['search'] ?? '';

        if ($type == 'packages') {
            return redirect()->route('packages.index');
        }

        $filter = array_filter([
            'source' => isset(self::LEGACY_SOURCES[$type]) ? ['eq' => self::LEGACY_SOURCES[$type]] : null,
            'device_id' => ! empty($options['device_id']) ? ['eq' => $options['device_id']] : null,
        ]);

        // without a search phrase the old pages listed every entry
        return redirect()->route('search', array_filter([
            'q' => $options['searchPhrase'] ?? $options['address'] ?? '',
            'filter' => $filter,
        ], fn ($value) => $value !== []));
    }
}
