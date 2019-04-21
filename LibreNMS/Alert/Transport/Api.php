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
use GuzzleHttp\Client;

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

        //get the key-values and process blades
        foreach ($params_lines as $current_line) {
            list($k, $v) = explode('=', $current_line, 2);
            try {
                // process the variables on the value
                $value_processed = view(['template' => $v], $obj)->__toString();
            } catch (\Exception $e) {
                echo "Exception e";
                var_dump($e);
            }
            //store the parameter in the array for HTTP query
            $query[$k] = $value_processed;
        }

        $client = new \GuzzleHttp\Client();

        if ($method == "get") {
            $res = $client->request('GET', $host, ['query' => $query]);
        } else {
            $res = $client->request('POST', $host, ['form_params' => $query]);
        }

        $code = $res->getStatusCode();

        if ($code != 200) {
            var_dump("API '$host' returned Error");
            var_dump("Params:");
            var_dump($query);
            var_dump("Response headers:");
            var_dump($res->getHeaders());
            var_dump("Return: ".$res->getReasonPhrase());
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
