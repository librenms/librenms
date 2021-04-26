<?php
/**
 * RipeNccApiController.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Ajax;

use App\ApiClients\RipeApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LibreNMS\Exceptions\ApiException;

class RipeNccApiController extends Controller
{
    public function raw(Request $request, RipeApi $api)
    {
        $this->validate($request, [
            'data_param' => 'required|in:whois,abuse-contact-finder',
            'query_param' => 'required|ip_or_hostname',
        ]);

        $is_whois = $request->get('data_param') == 'whois';

        try {
            $resource = $request->get('query_param');
            $output = $is_whois ? $api->getWhois($resource) : $api->getAbuseContact($resource);

            return response()->json([
                'status' => 'ok',
                'message' => 'Queried',
                'output' => $output,
            ]);
        } catch (ApiException $e) {
            $response = $e->getOutput();
            $message = $e->getMessage();

            if (isset($response['messages'])) {
                $message .= ': ' . collect($response['messages'])
                        ->flatten()
                        ->reject('error')
                        ->implode(', ');
            }

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'output' => $response,
            ], 503);
        }
    }
}
