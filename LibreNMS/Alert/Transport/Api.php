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
 * API Transport
 *
 * @author f0o <f0o@devilcode.org>
 * @author PipoCanaja (github.com/PipoCanaja)
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use App\View\SimpleTemplate;
use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Api extends Transport
{
    protected string $name = 'API';

    public function deliverAlert(array $alert_data): bool
    {
        $request_body = $this->config['api-body'];
        $username = $this->config['api-auth-username'];
        $password = $this->config['api-auth-password'];

        $method = strtolower($this->config['api-method']);
        $host = explode('?', $this->config['api-url'], 2)[0]; //we don't use the parameter part, cause we build it out of options.

        //get each line of key-values and process the variables for Options
        $query = $this->parseUserOptions($this->config['api-options'], $alert_data);
        $request_headers = $this->parseUserOptions($this->config['api-headers'], $alert_data);
        $client = Http::client()
        ->withHeaders($request_headers); //get each line of key-values and process the variables for Headers

        if ($method !== 'get') {
            $request_body = SimpleTemplate::parse($this->config['api-body'], $alert_data);
            // withBody always overrides Content-Type so we compute a proper set (with 'Content-Type' => 'text/plain'
            // as default value, and replace all headers with our computed headers
            $client->withBody($request_body)->replaceHeaders(array_merge(['Content-Type' => 'text/plain'], $request_headers));
        }

        if ($username) {
            $client->withBasicAuth($username, $password);
        }

        $client->withOptions([
            'query' => $query,
        ]);

        $res = match ($method) {
            'get' => $client->get($host),
            'put' => $client->put($host),
            default => $client->post($host),
        };

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $request_body ?? $query, [
            'query' => $query,
        ]);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'API Method',
                    'name' => 'api-method',
                    'descr' => 'API Method: GET, POST or PUT',
                    'type' => 'select',
                    'options' => [
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                    ],
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
                ],
                [
                    'title' => 'headers',
                    'name' => 'api-headers',
                    'descr' => 'Enter the headers (format: option=value separated by new lines)',
                    'type' => 'textarea',
                ],
                [
                    'title' => 'body',
                    'name' => 'api-body',
                    'descr' => 'Enter the body (only used by PUT/POST method, discarded GET)',
                    'type' => 'textarea',
                ],
                [
                    'title' => 'Auth Username',
                    'name' => 'api-auth-username',
                    'descr' => 'Auth Username',
                    'type' => 'text',
                ],
                [
                    'title' => 'Auth Password',
                    'name' => 'api-auth-password',
                    'descr' => 'Auth Password',
                    'type' => 'password',
                ],
            ],
            'validation' => [
                'api-method' => 'in:GET,POST,PUT',
                'api-url' => 'required|url',
            ],
        ];
    }
}
