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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/*
 * API Transport
 * @author Paul Heinrichs <pdheinrichs@gmail.com>
 * @copyright 2018 Paul Heinrichs
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */
namespace LibreNMS\Alert\Transport;

use LibreNMS\Interfaces\Alert\Transport;

/**
 * The Hue API currently is fairly limited for alerts.
 * At it's current implementation we can send ['lselect' => "15 second flash", 'select' => "1 second flash"]
 * If a colour request is sent with it it will permenantly change the colour which is less than desired
 */
class Hue implements Transport
{
    public function deliverAlert($obj, $opts)
    {
        // Don't alert on resolve at this time
        if ($obj['state'] == 0) {
            return true;
        } else {
            $device = device_by_id_cache($obj['device_id']); // for event logging
            $hue_user  = $opts['user'];
            $url         = $opts['bridge'] . "/api/$hue_user/groups/0/action";
            $curl        = curl_init();
            $duration  = $opts['duration'];
            $data       = array("alert" => $duration);
            $datastring = json_encode($data);

            set_curl_proxy($curl);

            $headers = array('Accept: application/json', 'Content-Type: application/json');

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);

            $ret  = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($code == 200) {
                d_echo("Sent alert to Phillips Hue Bridge " . $opts['host'] . " for " . $device);
                return true;
            } else {
                d_echo("Hue bridge connection error: " . serialize($ret));
                return false;
            }
        }
    }
}
