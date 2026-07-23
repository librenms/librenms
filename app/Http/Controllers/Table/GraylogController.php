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
use App\Models\Syslog;
use DateInterval;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class GraylogController extends SimpleTableController
{
    private readonly ?DateTimeZone $timezone;
    private array $deviceLinkCache = [];
    private array $fields = [];
    private array $hiddenFieldPrefixes = [];

    public function __construct()
    {
        $timezone = LibrenmsConfig::get('graylog.timezone');
        $this->timezone = $timezone ? new DateTimeZone($timezone) : null;
    }

    public function __invoke(Request $request, GraylogApi $api): JsonResponse
    {
        $this->authorize('viewAny', Syslog::class); // Graylog replaces syslog

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
        ]);

        $search = $request->input('searchPhrase');
        $device_id = (int) $request->input('device');
        $device = $device_id ? Device::find($device_id) : null;
        $range = (int) $request->input('range', 28800);
        $limit = (int) $request->input('rowCount', 10);
        $page = (int) $request->input('current', 1);
        $offset = (int) (($page - 1) * $limit);
        $loglevel = $request->input('loglevel');
        $this->fields = (array) LibrenmsConfig::get('graylog.fields');
        $this->hiddenFieldPrefixes = array_values(array_filter((array) LibrenmsConfig::get('graylog.hidden-fields')));

        $query = $api->buildSimpleQuery($search, $device) .
            (is_numeric($loglevel) ? ' AND level: <=' . (int) $loglevel : '');

        $sort = null;
        foreach ($request->input('sort', []) as $field => $direction) {
            $sort = "$field:$direction";
        }

        $stream = $request->input('stream') ?: $api->defaultStreamId();
        $filter = $stream !== '' ? "streams:$stream" : null;

        try {
            $data = $api->query($query, $range, $limit, $offset, $sort, $filter);
            $messages = $data['messages'] ?? [];

            return $this->formatResponse(
                array_map($this->formatMessage(...), $messages),
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

    private function formatMessage(array $message): array
    {
        $raw = $message['message'] ?? [];

        if ($this->timezone) {
            $graylogTime = new DateTime($raw['timestamp']);
            $offset = $this->timezone->getOffset($graylogTime);

            $timeInterval = DateInterval::createFromDateString((string) $offset . 'seconds');
            $graylogTime->add($timeInterval);
            $displayTime = $graylogTime->format('Y-m-d H:i:s');
        } else {
            $displayTime = $raw['timestamp'] ?? '';
        }

        $row = [
            '_id' => $raw['_id'] ?? null,
            'timestamp' => $displayTime,
        ];

        foreach ($this->fields as $field) {
            $row[$field] = $this->renderField($field, $raw);
        }

        $row['_detail_html'] = $this->renderDetail($raw);

        return $row;
    }

    private function renderField(string $field, array $raw): string
    {
        return match ($field) {
            'severity' => $this->severityLabel($raw['level'] ?? ''),
            'origin' => $this->deviceLinkFromSource($raw['gl2_remote_ip'] ?? ''),
            'source' => $this->deviceLinkFromSource($raw['source'] ?? ''),
            'message' => htmlspecialchars($raw['message'] ?? ''),
            'level' => is_numeric($raw['level'] ?? null) && $raw['level'] >= 0
                ? "({$raw['level']}) " . __("syslog.severity.{$raw['level']}")
                : htmlspecialchars((string) ($raw['level'] ?? '')),
            'facility' => is_numeric($raw['facility'] ?? null)
                ? "({$raw['facility']}) " . __("syslog.facility.{$raw['facility']}")
                : htmlspecialchars((string) ($raw['facility'] ?? '')),
            default => htmlspecialchars($this->stringifyField($raw[$field] ?? '')),
        };
    }

    private function stringifyField($value): string
    {
        if (is_scalar($value) || $value === null) {
            return (string) $value;
        }

        return (string) json_encode($value);
    }

    private function renderDetail(array $raw): string
    {
        ksort($raw);
        $rows = [];
        foreach ($raw as $key => $value) {
            $keyStr = (string) $key;
            foreach ($this->hiddenFieldPrefixes as $prefix) {
                if (str_starts_with($keyStr, (string) $prefix)) {
                    continue 2;
                }
            }
            $rows[$keyStr] = $this->stringifyField($value);
        }

        return Blade::render('graylog._detail', ['rows' => $rows]);
    }

    private function severityLabel(string $severity): string
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
     */
    private function deviceLinkFromSource(?string $source): string
    {
        if (! $source) {
            return '';
        }

        if (! isset($this->deviceLinkCache[$source])) {
            $device = Device::findByIp($source) ?: Device::findByHostname($source);

            $this->deviceLinkCache[$source] = $device
                ? Blade::render('<x-device-link :device="$device"/>', ['device' => $device])
                : htmlspecialchars($source);
        }

        return $this->deviceLinkCache[$source];
    }
}
