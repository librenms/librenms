<?php
/* Copyright (C) 2020 Raphael Dannecker <rdannecker@gmail.com>
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
 * Matrix Transport
 * @author Raphael Dannecker (github.com/raphael247)
 * @copyright 2020 , LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Matrix extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $server = $this->config['matrix-server'];
        $room = $this->config['matrix-room'];
        $authtoken = $this->config['matrix-authtoken'];
        $message = $this->config['matrix-message'];

        return $this->contactMatrix($obj, $server, $room, $authtoken, $message);
    }

    private function contactMatrix($obj, $server, $room, $authtoken, $message)
    {
        $request_opts = [];
        $request_heads = [];
        $txnid = rand(1111, 9999) . time();

        $server = preg_replace('/\/$/', '', $server);
        $host = $server . '/_matrix/client/r0/rooms/' . urlencode($room) . '/send/m.room.message/' . $txnid;

        $request_heads['Authorization'] = "Bearer $authtoken";
        $request_heads['Content-Type'] = 'application/json';
        $request_heads['Accept'] = 'application/json';

        foreach ($obj as $p_key => $p_val) {
            $message = str_replace('{{ $' . $p_key . ' }}', $p_val, $message);
        }

        $body = ['body'=>$message, 'msgtype'=>'m.text'];

        $client = new \GuzzleHttp\Client();
        $request_opts['proxy'] = get_guzzle_proxy();
        $request_opts['headers'] = $request_heads;
        $request_opts['body'] = json_encode($body);
        $res = $client->request('PUT', $host, $request_opts);

        $code = $res->getStatusCode();
        if ($code != 200) {
            var_dump("Matrix '$host' returned Error");
            var_dump('Params:');
            var_dump('Response headers:');
            var_dump($res->getHeaders());
            var_dump('Return: ' . $res->getReasonPhrase());

            return 'HTTP Status code ' . $code;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Matrix-Server URL',
                    'name' => 'matrix-server',
                    'descr' => 'Matrix server URL up to the matrix api-part (for example: https://matrix.example.com/)',
                    'type' => 'text',
                ],
                [
                    'title' => 'Room',
                    'name' => 'matrix-room',
                    'descr' => 'Enter the room',
                    'type' => 'text',
                ],
                [
                    'title' => 'Auth_token',
                    'name' => 'matrix-authtoken',
                    'descr' => 'Enter the auth_token',
                    'type' => 'text',
                ],
                [
                    'title' => 'Message',
                    'name' => 'matrix-message',
                    'descr' => 'Enter the message',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'matrix-server' => 'required',
                'matrix-room' => 'required',
                'matrix-authtoken' => 'required',
            ],
        ];
    }
}
