<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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

/**
 * iLert Transport
 *
 * @author t.plueer <t.plueer@first-colo.net>, l.prosch <l.prosch@first-colo.net>
 * @copyright 2024 firstcolo
 * @copyright 2022 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;

class Ilert extends Transport
{
    public function name(): string
    {
        return 'ilert';
    }

    public function deliverAlert(array $alert_data): bool
    {
        if ($alert_data['state'] == AlertState::RECOVERED) {
            $alert_data['event_type'] = 'RESOLVE';
        } elseif ($alert_data['state'] == AlertState::ACKNOWLEDGED) {
            $alert_data['event_type'] = 'ACCEPT';
        } else {
            $alert_data['event_type'] = 'ALERT';
        }
        $opts = '';
        if (! empty($this->config)) {
            $opts = $this->config['api-token'];
        }

        return $this->contactILert($alert_data, $opts);
    }

    public function contactILert($alert_data, $opts): bool
    {
        $data = ['apiKey' => $opts, 'eventType' => $alert_data['event_type'], 'summary' => $alert_data['msg']];
        $data = json_encode($data);

        $curl = curl_init('https://api.ilert.com/api/events');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ]);

        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($code != 202) {
            var_dump($ret);

            return false;
        }

        return true;
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'API Token',
                    'name' => 'api-token',
                    'descr' => 'iLert API Token',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'api-token' => 'required|string',
            ],
        ];
    }
}
