<?php
/**
 * SetInstanceId.php
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
 */

namespace App\Logging\Reporting\Middleware;

use App\Providers\ErrorReportingProvider;
use Spatie\FlareClient\Report;

class SetInstanceId implements \Spatie\FlareClient\FlareMiddleware\FlareMiddleware
{
    /**
     * Middleware to add instance ID, piggybacks on the "user id" feature.
     *
     * @return mixed
     */
    public function handle(Report $report, \Closure $next)
    {
        try {
            $user = $report->getGroup('user', []);
            $user['id'] = ErrorReportingProvider::getInstanceId();

            $report->group('user', $user);
        } catch (\Exception $e) {
        }

        return $next($report);
    }
}
