<?php

/**
 * Graylog.php
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

namespace LibreNMS\Util;

use App\ApiClients\GraylogApi;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Http\Request;

class Graylog
{
    /**
     * View-data shared by the global `/graylog` page and the per-device log tab.
     *
     * @return array<string, mixed>
     */
    public static function viewData(GraylogApi $api, ?Device $device, Request $request): array
    {
        $stream = $request->input('stream') ?: $api->defaultStreamId();
        $range = $request->input('range', '28800');
        $loglevel = $request->input('loglevel', '');

        return [
            'device' => $device,
            'device_selected' => $device ? ['id' => $device->device_id, 'text' => $device->display] : null,
            'timezone' => LibrenmsConfig::has('graylog.timezone'),
            'show_form' => true,
            'stream' => $stream,
            'stream_selected' => $api->findStream($stream),
            'range' => $range,
            'range_selected' => (string) $range,
            'loglevel' => $loglevel,
            'loglevel_selected' => (string) $loglevel,
            'columns' => self::columns(),
            'ranges' => self::ranges(),
            'row_count_default' => (int) LibrenmsConfig::get('graylog.rowCount'),
            'table_url' => route('table.graylog', [
                'stream' => $stream,
                'device' => $device?->device_id,
                'range' => $range,
                'loglevel' => $loglevel,
            ]),
        ];
    }

    /**
     * Ordered time ranges for the search filter, value => label.
     *
     * @return array<int, string>
     */
    public static function ranges(): array
    {
        return [
            0 => 'all time',
            300 => 'last 5 minutes',
            900 => 'last 15 minutes',
            1800 => 'last 30 minutes',
            3600 => 'last 1 hour',
            7200 => 'last 2 hours',
            28800 => 'last 8 hours',
            86400 => 'last 1 day',
            172800 => 'last 2 days',
            432000 => 'last 5 days',
            604800 => 'last 7 days',
            1209600 => 'last 14 days',
            2592000 => 'last 30 days',
        ];
    }

    /**
     * Bootgrid table columns. Always starts with severity (if configured) and
     * timestamp, then the remaining configured fields in `graylog.fields` order.
     *
     * @return array<int, array{field: string, label: string}>
     */
    public static function columns(): array
    {
        $fields = (array) LibrenmsConfig::get('graylog.fields');
        $columns = [];

        if (in_array('severity', $fields, true)) {
            $columns[] = ['field' => 'severity', 'label' => ''];
        }
        $columns[] = ['field' => 'timestamp', 'label' => 'Timestamp'];

        foreach ($fields as $field) {
            if ($field === 'severity') {
                continue;
            }
            $columns[] = [
                'field' => $field,
                'label' => ucwords(str_replace(['_', '-'], ' ', $field)),
            ];
        }

        return $columns;
    }
}
