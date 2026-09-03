<?php

/**
 * DebugAlertsController.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Debug;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\StreamsOutputToBrowser;
use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use LibreNMS\Alert\AlertUtil;
use LibreNMS\Alerting\QueryBuilderParser;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DebugAlertsController extends Controller
{
    use StreamsOutputToBrowser;

    public function __invoke(Device $device, Request $request): StreamedResponse
    {
        $this->authorize('debug', $device);

        $validated = $request->validate(['format' => ['required', Rule::in(['text', 'download'])]]);

        if ($validated['format'] == 'download') {
            $this->enableDownload('alerts-' . $device->hostname . '.txt');
        }

        return $this->stream(function () use ($device): void {
            $rules = AlertRule::enabled()->forDevice($device)->get();
            $results = [];
            foreach ($rules as $rule) {
                echo 'Rule name: ' . $rule->name . PHP_EOL;

                $sql = $rule->query ?: QueryBuilderParser::fromJson($rule->builder)->toSql();

                if (empty($sql)) {
                    echo 'SQL Query generation failed' . PHP_EOL;
                    continue;
                }

                try {
                    $rows = DB::select($sql, [$device->device_id]);
                } catch (\Exception) {
                    echo 'SQL Query execution failed' . PHP_EOL;
                    continue;
                }

                if (count($rows)) {
                    $results[] = $rows;
                    $response = 'matches';
                } else {
                    $response = 'no match';
                }

                $extra = $rule->extra;
                if (($extra['options']['override_query'] ?? null) === 'on' || ($extra['options']['override_query'] ?? null) === true) {
                    $qb = $extra['options']['override_query'];
                } else {
                    $qb = QueryBuilderParser::fromJson($rule->builder ?? []);
                }

                if ($qb instanceof QueryBuilderParser) {
                    echo 'Alert rule: ' . $qb->toSql(false) . PHP_EOL;
                } else {
                    echo 'Alert rule: Custom SQL Query' . PHP_EOL;
                }
                echo 'Alert query: ' . ($rule->query ?: $sql) . PHP_EOL;
                echo 'Rule match: ' . $response . PHP_EOL . PHP_EOL;
            }

            if (LibrenmsConfig::get('alert.transports.mail') === true) {
                $contacts = AlertUtil::getContacts($results);
                if (count($contacts) > 0) {
                    echo 'Found ' . count($contacts) . ' contacts to send alerts to.' . PHP_EOL;
                }
                foreach ($contacts as $email => $name) {
                    echo $name . '<' . $email . '>' . PHP_EOL;
                }
                echo PHP_EOL;
            }

            $transports = '';
            $x = 0;
            foreach (LibrenmsConfig::get('alert.transports') as $name => $v) {
                if (LibrenmsConfig::get("alert.transports.$name") === true) {
                    $transports .= 'Transport: ' . $name . PHP_EOL;
                    $x++;
                }
            }
            if (! empty($transports)) {
                echo 'Found ' . $x . ' transports to send alerts to.' . PHP_EOL;
                echo $transports;
            }
        });
    }
}
