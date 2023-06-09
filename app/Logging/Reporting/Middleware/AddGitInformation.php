<?php
/**
 * AddGitInformation.php
 *
 * Add git information to Flare report, but use a cache so we don't destroy servers
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Logging\Reporting\Middleware;

use LibreNMS\Util\Git;
use Spatie\FlareClient\Report;

class AddGitInformation implements \Spatie\FlareClient\FlareMiddleware\FlareMiddleware
{
    /**
     * @return mixed
     */
    public function handle(Report $report, \Closure $next)
    {
        $git = Git::make(180);

        $report->group('git', [
            'hash' => $git->commitHash(),
            'message' => $git->message(),
            'tag' => $git->shortTag(),
            'remote' => $git->remoteUrl(),
        ]);

        return $next($report);
    }
}
