<?php
/* Copyright (C) 2014 Daniel Preussker <f0o>
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
 * along with this program.  If not, see <http>. */

/**
 * API Transport
 * @author f0o <f0o>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Api extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        if (empty($this->config)) {
            return $this->deliverAlertOld($obj, $opts);
        }
        $url = $this->config['api-url'];
        $method = $this->config['api-method'];
        return $this->contactAPI($obj, $url, $method);
    }

    private function deliverAlertOld($obj, $opts)
    {
        foreach ($opts as $method => $apis) {
            foreach ($apis as $api) {
                $this->contactAPI($obj, $api, $method);
            }
        }
        return true;
    }

    private function contactAPI($obj, $api, $method)
    {
        $method = strtolower($method);
        list($host, $api) = explode("?", $api, 2);
        foreach ($obj as $k => $v) {
            $api = str_replace("%" . $k, $method == "get" ? urlencode($v) : $v, $api);
        }
        //  var_dump($api); //FIXME: propper debuging
        $curl = curl_init();
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, ($method == "get" ? $host."?".$api : $host));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if (json_decode($api) !== null) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $api);
        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            var_dump("API '$host' returned Error"); //FIXME: propper debuging
            var_dump("Params: ".$api); //FIXME: propper debuging
            var_dump("Return: ".$ret); //FIXME: propper debuging
            return 'HTTP Status code '.$code;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'API Method',
                    'name' => 'api-method',
                    'descr' => 'API Method: GET or POST',
                    'type' => 'select',
                    'options' => [
                        'GET' => 'GET',
                        'POST' => 'POST'
                    ]
                ],
                [
                    'title' => 'API URL',
                    'name' => 'api-url',
                    'descr' => 'API URL',
                    'type' => 'text',
                ]
            ],
            'validation' => [
                'api-method' => 'in:GET,POST',
                'api-url' => 'required|url'
            ]
        ];
    }
}
