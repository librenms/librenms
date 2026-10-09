<?php

/**
 * RoutingController.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Routing;

use App\Http\Controllers\Controller;
use App\View\Components\RoutingTabs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use LibreNMS\Util\Url;

class RoutingController extends Controller
{
    /**
     * Redirect to the first routing page with entries, or translate a legacy
     * routing/protocol=bgp/type=all URL to its new location.
     */
    public function __invoke(): RedirectResponse
    {
        $options = Url::parseOptions();
        $protocol = $options['protocol'] ?? array_key_first(RoutingTabs::getRoutingTabs()) ?? 'bgp';
        unset($options['protocol'], $options['page']);

        abort_unless(Route::has("routing.$protocol"), 404);

        // legacy bgp urls used view=graphs/details alongside graph=NULL|<type>
        // legacy vrf name links used view=detail, which is now the vrf filter on the basic view
        if ($protocol == 'bgp' || ($protocol == 'vrf' && ($options['view'] ?? null) == 'detail')) {
            unset($options['view']);
        }

        $options = array_filter($options, fn ($value) => $value !== 'NULL' && $value !== '');

        return redirect()->route("routing.$protocol", $options);
    }
}
