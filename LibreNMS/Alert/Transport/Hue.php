<?php
/* Copyright (C) 2018 Paul Heinrichs <pdheinrichs@gmail.com>
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

/*
 * API Transport
 * @author Paul Heinrichs <pdheinrichs@gmail.com>
 * @copyright 2018 Paul Heinrichs
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;

/**
 * The Hue API currently is fairly limited for alerts.
 * At it's current implementation we can send ['lselect' => "15 second flash", 'select' => "1 second flash"]
 * If a colour request is sent with it it will permenantly change the colour which is less than desired
 */
class Hue extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        if (! empty($this->config)) {
            $opts['user'] = $this->config['hue-user'];
            $opts['bridge'] = $this->config['hue-host'];
            $opts['duration'] = $this->config['hue-duration'];
        }

        return $this->contactHue($obj, $opts);
    }

    public function contactHue($obj, $opts)
    {
        // Don't alert on resolve at this time
        if ($obj['state'] == AlertState::RECOVERED) {
            return true;
        } else {
            $device = device_by_id_cache($obj['device_id']); // for event logging
            $hue_user = $opts['user'];
            $url = $opts['bridge'] . "/api/$hue_user/groups/0/action";
            $curl = curl_init();
            $duration = $opts['duration'];
            $data = ['alert' => $duration];
            $datastring = json_encode($data);

            set_curl_proxy($curl);

            $headers = ['Accept: application/json', 'Content-Type: application/json'];

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);

            $ret = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($code == 200) {
                d_echo('Sent alert to Phillips Hue Bridge ' . $opts['host'] . ' for ' . $device);

                return true;
            } else {
                d_echo('Hue bridge connection error: ' . serialize($ret));

                return false;
            }
        }
    }

    public static function configTemplate()
    {
        return [
            'config'=>[
                [
                    'title'=> 'Host',
                    'name' => 'hue-host',
                    'descr' => 'Hue Host',
                    'type' => 'text',
                ],
                [
                    'title'=> 'Hue User',
                    'name' => 'hue-user',
                    'descr' => 'Phillips Hue Host',
                    'type' => 'text',
                ],
                [
                    'title'=> 'Duration',
                    'name' => 'hue-duration',
                    'descr' => 'Phillips Hue Duration',
                    'type' => 'select',
                    'options' => [
                        '1 Second' => 'select',
                        '15 Seconds' => 'lselect',
                    ],
                ],
            ],
            'validation' => [
                'hue-host' => 'required|string',
                'hue-user' => 'required|string',
                'hue-duration' => 'required|string',
            ],
        ];
    }
}
