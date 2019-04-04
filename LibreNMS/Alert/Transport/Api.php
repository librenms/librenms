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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * API Transport
 * @author f0o <f0o@devilcode.org>
 * @author PipoCanaja (github.com/PipoCanaja)
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use App\Models\AlertTemplate;

class Api extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $url = $this->config['api-url'];
        $options = $this->config['api-options'];
        $method = $this->config['api-method'];
        return $this->contactAPI($obj, $url, $options, $method);
    }

    private function contactAPI($obj, $api, $options, $method)
    {
        $method = strtolower($method);
        $host = explode("?", $api, 2)[0]; //we don't use the parameter part, cause we build it out of options. 

        //Split the line of options
        $params_lines = preg_split("/\\r\\n|\\r|\\n/", $options);

        //get the key-values
        
        foreach ($params_lines as $current_line) {
            list($k, $v) = explode('=', $current_line, 2);
            try {
                // process the variables on the value
                $value_processed = view(['template' => $v], $obj)->__toString();
            } catch (\Exception $e) {
                echo "Exception e";
                var_dump($e);
            }
            //store the parameter in the array
            $params[] = $k . '=' . rawurlencode($value_processed);
        }

        $params_string = '';
        
        if (isset($params)) {
            //We have at least one param
            $params_string = '?' . implode('&', $params);
        }

        $curl = curl_init();
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, ($method == "get" ? $host.$params_string : $host));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if (json_decode($api) !== null) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        }
        if (isset($params)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, implode('&', $params));
        }
        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            var_dump("API '$host' returned Error"); //FIXME: propper debuging
            var_dump("Params: ".implode(PHP_EOL, $params); //FIXME: propper debuging
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
                ],
                [
                    'title' => 'Options',
                    'name' => 'api-options',
                    'descr' => 'Enter the options (format: option=value separated by new lines)',
                    'type' => 'textarea',
                ]
            ],
            'validation' => [
                'api-method' => 'in:GET,POST',
                'api-url' => 'required|url',
                'api-options' => 'required|string'
            ]
        ];
    }
}
