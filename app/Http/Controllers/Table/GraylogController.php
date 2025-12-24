<?php

/**
 * GraylogController.php
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

namespace App\Http\Controllers\Table;

use App\ApiClients\GraylogApi;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class GraylogController extends SimpleTableController
{
    private $timezone;
    private $deviceLinkCache = [];

    public function __construct()
    {
        $timezone = LibrenmsConfig::get('graylog.timezone');
        $this->timezone = $timezone ? new DateTimeZone($timezone) : null;
    }

    public function __invoke(Request $request, GraylogApi $api)
    {
        if (! $api->isConfigured()) {
            return response()->json([
                'error' => 'Graylog is not configured',
            ], 503);
        }

        $this->validate($request, [
            'stream' => 'nullable|alpha_num',
            'device' => 'nullable|int',
            'range' => 'nullable|int',
            'loglevel' => 'nullable|int|min:0|max:7',
            'from' => 'nullable|date_format:Y-m-d H:i',
            'to' => 'nullable|date_format:Y-m-d H:i',
        ]);

        $search = $request->get('searchPhrase');
        $device_id = (int) $request->get('device');
        $device = $device_id ? Device::find($device_id) : null;
        $range = (int) $request->get('range', 0);
        $limit = (int) $request->get('rowCount', 10);
        $page = (int) $request->get('current', 1);
        $offset = (int) (($page - 1) * $limit);
        $loglevel = $request->get('loglevel') ?? LibrenmsConfig::get('graylog.loglevel');

        $from = null;
        $to = null;
        $fromInput = $request->get('from');
        $toInput = $request->get('to');
        $userTz = $this->timezone ? $this->timezone->getName() : (session('preferences.timezone') ?: config('app.timezone'));

        if ($range === 0 && $fromInput && $toInput) {
            $from = Carbon::createFromFormat('Y-m-d H:i', (string) $fromInput, $userTz);
            $to = Carbon::createFromFormat('Y-m-d H:i', (string) $toInput, $userTz);
        }

        $query = $api->buildSimpleQuery($search, $device) .
            ($loglevel !== null ? ' AND level:[0 TO ' . (int) $loglevel . ']' : '');

        $sort = null;
        foreach ($request->get('sort', []) as $field => $direction) {
            $sort = "$field:$direction";
        }

        $stream = $request->get('stream');
        $filter = $stream ? "streams:$stream" : null;

        try {
            if ($from && $to) {
                $fromUtc = $from->clone()->setTimezone('UTC')->format('Y-m-d\TH:i:s.v\Z');
                $toUtc = $to->clone()->setTimezone('UTC')->format('Y-m-d\TH:i:s.v\Z');

                $data = $api->queryAbsolute(
                    $query,
                    $fromUtc,
                    $toUtc,
                    $limit,
                    $offset,
                    $sort,
                    $filter
                );
            } else {
                $data = $api->queryRelative($query, $range, $limit, $offset, $sort, $filter);
            }
            $messages = $data['messages'] ?? [];

            return $this->formatResponse(
                array_map([$this, 'formatMessage'], $messages),
                $page,
                count($messages),
                $data['total_results'] ?? 0,
            );
        } catch (\Exception $se) {
            $error = $se->getMessage();
        }

        return response()->json([
            'error' => $error,
        ], 500);
    }

    private function formatMessage($message)
    {
        $displayTz = $this->timezone ? $this->timezone->getName() : (session('preferences.timezone') ?: config('app.timezone'));

        $displayTime = Carbon::parse($message['message']['timestamp'])
            ->setTimezone($displayTz)
            ->format(LibrenmsConfig::get('dateformat.compact'));

        $level = $message['message']['level'] ?? '';
        $facility = $message['message']['facility'] ?? '';

        return [
            'origin' => $this->deviceLinkFromSource($message['message']['gl2_remote_ip']),
            'severity' => $this->severityLabel($level),
            'timestamp' => $displayTime,
            'source' => $this->deviceLinkFromSource($message['message']['source']),
            'message' => htmlspecialchars($message['message']['message'] ?? ''),
            'facility' => is_numeric($facility) ? "($facility) " . __("syslog.facility.$facility") : $facility,
            'level' => (is_numeric($level) && $level >= 0) ? "($level) " . __("syslog.severity.$level") : $level,
        ];
    }

    private function severityLabel($severity)
    {
        $map = [
            '0' => 'label-danger',
            '1' => 'label-danger',
            '2' => 'label-danger',
            '3' => 'label-danger',
            '4' => 'label-warning',
            '5' => 'label-info',
            '6' => 'label-info',
            '7' => 'label-default',
            '' => 'label-info',
        ];
        $barColor = $map[$severity] ?? 'label-info';

        return '<span class="alert-status ' . $barColor . '" style="margin-right:8px;float:left;"></span>';
    }

    /**
     * Cache device lookups so we don't lookup for every entry
     *
     * @param  mixed  $source
     * @return string
     */
    private function deviceLinkFromSource($source): string
    {
        if (! isset($this->deviceLinkCache[$source])) {
            $device = Device::findByIp($source) ?: Device::findByHostname($source);

            $this->deviceLinkCache[$source] = $device
                ? Blade::render('<x-device-link :device="$device"/>', ['device' => $device])
                : htmlspecialchars((string) $source);
        }

        return $this->deviceLinkCache[$source];
    }
}
