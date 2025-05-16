<?php

/**
 * AboutController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App;
use App\Models\Callback;
use App\Services\AboutMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Util\Http;
use LibreNMS\Util\Version;

class AboutController extends Controller
{
    public function index(Request $request, AboutMetrics $aboutMetrics)
    {
        $version = Version::get();
        $static = [
            'usage_reporting_status' => Config::get('reporting.usage'),
            'error_reporting_status' => Config::get('reporting.error'),
            'reporting_clearable' => Callback::whereIn('name', ['uuid', 'error_reporting_uuid'])->exists(),

            'db_schema' => $version->database(),
            'git_log' => $version->git->log(),
            'git_date' => $version->date(),
            'project_name' => Config::get('project_name'),

            'version_local' => $version->name(),
            'version_database' => $version->databaseServer(),
            'version_php' => phpversion(),
            'version_laravel' => App::version(),
            'version_python' => $version->python(),
            'version_webserver' => $request->server('SERVER_SOFTWARE'),
            'version_rrdtool' => Rrd::version(),
            'version_netsnmp' => str_replace('version: ', '', rtrim(shell_exec(Config::get('snmpget', 'snmpget') . ' -V 2>&1'))),
        ];

        $metrics = $aboutMetrics->collect();

        return view('about.index', array_merge($static, $metrics));
    }

    public function clearReportingData(): JsonResponse
    {
        $usage_uuid = Callback::get('uuid');

        // try to clear usage data if we have a uuid
        if ($usage_uuid) {
            if (! Http::client()->post(Config::get('callback_clear'), ['uuid' => $usage_uuid])->successful()) {
                return response()->json([], 500); // don't clear if this fails to delete upstream data
            }
        }

        // clear all reporting ids
        Callback::truncate();

        return response()->json();
    }
}
