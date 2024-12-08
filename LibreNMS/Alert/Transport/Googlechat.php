<?php
/**
 * LibreNMS Google Chat alerting transport
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 *
 * @copyright  2021 Pablo Baldovi
 * @author     Pablo Baldovi <pbaldovi@gmail.com>
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Googlechat extends Transport
{
    protected string $name = 'Google Chat';

    public function deliverAlert(array $alert_data): bool
    {
        $data = ['text' => $alert_data['msg']];
        $res = Http::client()->post($this->config['googlechat-webhook'], $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $data['text'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'googlechat-webhook',
                    'descr' => 'Google Chat Room Webhook',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'googlechat-webhook' => 'required|string',
            ],
        ];
    }
}
