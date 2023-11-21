<?php
/**
 * TimezoneController.php
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
 * @copyright  2021 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimezoneController extends Controller
{
    public function set(Request $request): string
    {
        $request->session()->put('preferences.timezone_static', $request->boolean('static'));

        // laravel session
        if ($request->timezone) {
            // Only accept valid timezones
            if (! in_array($request->timezone, timezone_identifiers_list())) {
                return session('preferences.timezone', '');
            }

            $request->session()->put('preferences.timezone', $request->timezone);

            return $request->timezone;
        }

        return session('preferences.timezone', '');
    }
}
