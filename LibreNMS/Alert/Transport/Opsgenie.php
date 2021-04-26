<?php
/* Copyright (C) 2017 Celal Emre CICEK <celal.emre@opsgenie.com>
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
 * OpsGenie API Transport
 * @author Celal Emre CICEK <celal.emre@opsgenie.com>
 * @copyright 2017 Celal Emre CICEK
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Opsgenie extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        if (! empty($this->config)) {
            $opts['url'] = $this->config['genie-url'];
        }

        return $this->contactOpsgenie($obj, $opts);
    }

    public function contactOpsgenie($obj, $opts)
    {
        $url = $opts['url'];

        $curl = curl_init();

        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($obj));

        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($code != 200) {
            var_dump('Error when sending post request to OpsGenie. Response code: ' . $code . ' Response body: ' . $ret); //FIXME: proper debugging

            return false;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'genie-url',
                    'descr' => 'OpsGenie Webhook URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'genie-url' => 'required|url',
            ],
        ];
    }
}
