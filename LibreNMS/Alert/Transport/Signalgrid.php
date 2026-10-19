<?php

/**
 * LibreNMS Signalgrid alerting transport
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link        https://www.librenms.org
 *
 * @copyright   2026 Signalgrid
 * @license     GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Signalgrid extends Transport
{
    /**
     * @param  array<string, mixed>  $alert_data
     */
    public function deliverAlert(array $alert_data): bool
    {
        $type = match (true) {
            $alert_data['state'] == AlertState::RECOVERED => 'SUCCESS',
            $alert_data['severity'] === 'critical' => 'CRIT',
            $alert_data['severity'] === 'warning' => 'WARN',
            default => 'INFO',
        };

        $data = [
            'client_key' => $this->config['signalgrid-client-key'],
            'channel' => $this->config['signalgrid-channel'],
            'title' => $alert_data['title'],
            'body' => $alert_data['msg'],
            'type' => $type,
        ];

        $res = Http::client()
            ->asForm()
            ->post('https://api.signalgrid.co/v1/push', $data);

        $response = $res->json();

        if ($res->successful() && isset($response['code']) && (string) $response['code'] === '200') {
            return true;
        }

        throw new AlertTransportDeliveryException(
            $alert_data,
            $res->status(),
            $response['text'] ?? $res->body(),
            $alert_data['msg'],
            $data
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Client Key',
                    'name' => 'signalgrid-client-key',
                    'descr' => 'Signalgrid Client Key',
                    'type' => 'password',
                ],
                [
                    'title' => 'Channel',
                    'name' => 'signalgrid-channel',
                    'descr' => 'Signalgrid Channel',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'signalgrid-client-key' => 'required|string',
                'signalgrid-channel' => 'required|string',
            ],
        ];
    }
}
