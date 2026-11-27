<?php

/**
 * HostNameController.php
 *
 * The reverse DNS name of a host, asked for after its summary is shown because the lookup can be slow
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

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Util\Dns;

class HostNameController extends Controller
{
    /**
     * The signed URL holds the addresses a search showed this user, so it can't be used to look up others
     */
    public function __invoke(Request $request, Dns $dns): JsonResponse
    {
        $this->authorize('viewAny', Port::class);

        /** @var array{ips: list<string>} $validated */
        $validated = $request->validate([
            'ips' => 'required|array|max:3',
            'ips.*' => 'ip',
        ]);

        foreach ($validated['ips'] as $ip) {
            if ($name = $dns->reverseHostname($ip)) {
                $matches = array_any($dns->getAddresses($name), fn (string $address): bool => @inet_pton($address) === inet_pton($ip));

                return response()->json(['name' => $name, 'ip' => $ip, 'forward_match' => $matches]);
            }
        }

        return response()->json(['name' => null, 'ip' => null, 'forward_match' => null]);
    }
}
