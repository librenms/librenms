<?php

/**
 * CommandController.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace App\Http\Controllers\Device;

use App\BrowserOutput;
use App\Facades\LibrenmsConfig;
use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Validation\Rule;
use LibreNMS\Alert\AlertUtil;
use LibreNMS\Alerting\QueryBuilderParser;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommandController
{
    use AuthorizesRequests;

    /**
     * @param  array{'format': string, 'type': string}  $validated
     * @return array<string, string>
     */
    private function headers(array $validated, Device $device): array
    {
        $headers = [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/plain',
        ];

        switch($validated['format']) {
            case 'text':
                break;
            case 'download':
                $headers += [
                    'Content-Description' => 'File Transfer',
                    'Content-Disposition' => 'attachment; filename=' . $validated['type'] . '-' . $device->hostname . '.txt',
                    'Content-Transfer-Encoding' => 'binary',
                    'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                    'Expires' => '0',
                    'Pragma' => 'public',
                ];
                break;
            default:
                throw new \Exception('Format type ' . $validated['format'] . ' needs to be implemented');
        }

        return $headers;
    }

    public function artisan(Device $device, Request $request): StreamedResponse
    {
        $this->authorize('debug', $device);

        $validated = $request->validate([
            'format' => ['required', Rule::in(['text', 'download'])],
            'type' => ['required', Rule::in(['poller', 'discovery'])],
        ]);

        switch($validated['type']) {
            case 'poller':
                $cmd = 'device:poll';
                $args = ['device spec' => $device->device_id, '-vv' => true, '--no-data' => true];
                break;
            case 'discovery':
                $cmd = 'device:discover';
                $args = ['device spec' => $device->device_id, '-vv' => true];
                break;
            default:
                throw new \Exception('Request type ' . $validated['type'] . ' needs to be implemented');
        }

        $headers = $this->headers($validated, $device);

        return new StreamedResponse(function () use ($cmd, $args): void {
            # Create an unbuffered logging channels with colours disabled
            config(['logging.channels.browser' => [
                'driver' => 'custom',
                'via' => \App\Logging\CreateEchoHandler::class,
                'level' => 'debug',
            ]]);
            Log::setDefaultDriver('browser');

            $exitCode = Artisan::call($cmd, $args, new BrowserOutput());

            echo PHP_EOL . "exit_status:$exitCode" . PHP_EOL;
        }, 200, $headers);
    }

    public function command(Device $device, Request $request): StreamedResponse
    {
        $this->authorize('debug', $device);

        $validated = $request->validate([
            'format' => ['required', Rule::in(['text', 'download'])],
            'type' => ['required', Rule::in(['snmpwalk'])],
        ]);

        switch($validated['type']) {
            case 'snmpwalk':
                include_once base_path('includes/snmp.inc.php');
                $cmd = gen_snmpwalk_cmd($device->toArray(), '.', '-OUneb');
                break;
            default:
                throw new \Exception('Request type ' . $validated['type'] . ' needs to be implemented');
        }

        $headers = $this->headers($validated, $device);

        return new StreamedResponse(function () use ($cmd): void {
            $result = Process::run($cmd, function (string $type, string $output): void {
                echo $output;
                flush();
            });

            echo PHP_EOL . 'exit_status:' . $result->exitCode() . PHP_EOL;
        }, 200, $headers);
    }

    public function query(Device $device, Request $request): StreamedResponse
    {
        $this->authorize('debug', $device);

        $validated = $request->validate([
            'format' => ['required', Rule::in(['text', 'download'])],
            'type' => ['required', Rule::in(['alerts'])],
        ]);

        switch($validated['type']) {
            case 'alerts':
                $rules = AlertRule::enabled()->forDevice($device)->get();
                $output = '';
                $results = [];
                foreach ($rules as $rule) {
                    $output .= 'Rule name: ' . $rule->name . PHP_EOL;

                    $sql = $rule->query ?: QueryBuilderParser::fromJson($rule->builder)->toSql();

                    if (empty($sql)) {
                        $output .= 'SQL Query generation failed' . PHP_EOL;
                        continue;
                    }

                    try {
                        $rows = DB::select($sql, [$device->device_id]);
                    } catch (\Exception) {
                        $output .= 'SQL Query execution failed' . PHP_EOL;
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
                        $output .= 'Alert rule: ' . $qb->toSql(false) . PHP_EOL;
                    } else {
                        $output .= 'Alert rule: Custom SQL Query' . PHP_EOL;
                    }
                    $output .= 'Alert query: ' . ($rule->query ?: $sql) . PHP_EOL;
                    $output .= 'Rule match: ' . $response . PHP_EOL . PHP_EOL;
                }
                if (LibrenmsConfig::get('alert.transports.mail') === true) {
                    $contacts = AlertUtil::getContacts($results);
                    if (count($contacts) > 0) {
                        $output .= 'Found ' . count($contacts) . ' contacts to send alerts to.' . PHP_EOL;
                    }
                    foreach ($contacts as $email => $name) {
                        $output .= $name . '<' . $email . '>' . PHP_EOL;
                    }
                    $output .= PHP_EOL;
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
                    $output .= 'Found ' . $x . ' transports to send alerts to.' . PHP_EOL;
                    $output .= $transports;
                }
                break;
            default:
                throw new \Exception('Request type ' . $validated['type'] . ' needs to be implemented');
        }

        $headers = $this->headers($validated, $device);

        return new StreamedResponse(function () use ($output): void {
            echo $output;
        }, 200, $headers);
    }
}
