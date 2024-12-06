<?php
/* LibreNMS
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

namespace LibreNMS\Alert\Transport;

use App\Facades\DeviceCache;
use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;
use LibreNMS\Util\Url;

class Grafana extends Transport
{
    protected string $name = 'Grafana Oncall';

    public function deliverAlert(array $alert_data): bool
    {
        $device = DeviceCache::get($alert_data['device_id']);

        $graph_args = [
            'type' => 'device_bits', // FIXME use graph url related to alert
            'device' => $device['device_id'],
            'height' => 150,
            'width' => 300,
            'legend' => 'no',
            'title' => 'yes',
        ];

        //$graph_url = url('graph.php') . '?' . http_build_query($graph_args);
        // FIXME - workaround for https://github.com/grafana/oncall/issues/3031
        $graph_url = url('graph.php') . '/' . str_replace('&', '/', http_build_query($graph_args));

        $data = [
            'alert_uid' => $alert_data['id'] ?: $alert_data['uid'],
            'title' => $alert_data['title'] ?? null,
            'message' => $alert_data['msg'],
            'image_url' => $graph_url,
            'link_to_upstream_details' => Url::deviceUrl($device),
            'state' => ($alert_data['state'] == AlertState::ACTIVE) ? 'alerting' : 'ok',
        ];

        $res = Http::client()->post($this->config['url'] ?? '', $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'url',
                    'descr' => 'Grafana Oncall Webhook URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'url' => 'required|url',
            ],
        ];
    }
}
