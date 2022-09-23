<?php
/*
 * BgpSearchController.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Ajax;

use App\Models\BgpPeer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LibreNMS\Util\Color;
use LibreNMS\Util\Url;

class BgpSearchController extends SearchController
{
    public function buildQuery(string $search, Request $request): Builder
    {
        return BgpPeer::hasAccess($request->user())
            ->with('device')
            ->where(function (Builder $query) use ($search) {
                $like_search = "%$search%";

                return $query->orWhere('astext', 'LIKE', $like_search)
                    ->orWhere('bgpPeerDescr', 'LIKE', $like_search)
                    ->orWhere('bgpPeerIdentifier', 'LIKE', $like_search)
                    ->orWhere('bgpPeerRemoteAs', 'LIKE', $like_search);
            })
            ->orderBy('astext');
    }

    /**
     * @param  \App\Models\BgpPeer  $peer
     * @return array
     */
    public function formatItem($peer): array
    {
        $bgp_image = $peer->bgpPeerRemoteAs == $peer->device->bgpLocalAs
            ? 'fa fa-square fa-lg icon-theme'
            : 'fa fa-external-link-square fa-lg icon-theme';

        return [
            'url'         => Url::deviceUrl($peer->device, ['tab' => 'routing', 'proto' => 'bgp']),
            'name'        => $peer->bgpPeerIdentifier,
            'description' => $peer->astext,
            'localas'     => $peer->device->bgpLocalAs,
            'bgp_image'   => $bgp_image,
            'remoteas'    => $peer->bgpPeerRemoteAs,
            'colours'     => Color::forBgpPeerStatus($peer),
            'hostname'    => $peer->device->displayName(),
        ];
    }
}
