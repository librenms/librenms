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
 *
 * @author Raphael Dannecker (github.com/raphael247)
 * @copyright 2020 , LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use App\View\SimpleTemplate;
use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Matrix extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $server = $this->config['matrix-server'];
        $room = $this->config['matrix-room'];
        $authtoken = $this->config['matrix-authtoken'];
        $message = $this->config['matrix-message'];

        $txnid = rand(1111, 9999) . time();

        $server = preg_replace('/\/$/', '', $server);
        $host = $server . '/_matrix/client/r0/rooms/' . urlencode($room) . '/send/m.room.message/' . $txnid;

        $message = SimpleTemplate::parse($message, $alert_data);

        $body = ['body' => strip_tags($message), 'formatted_body' => "$message", 'msgtype' => 'm.text', 'format' => 'org.matrix.custom.html'];

        $res = Http::client()
            ->withToken($authtoken)
            ->acceptJson()
            ->put($host, $body);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $message, $body);
    }

    public static function configTemplate(): array
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
                    'type' => 'password',
                ],
                [
                    'title' => 'Message',
                    'name' => 'matrix-message',
                    'descr' => 'Enter the message',
                    'type' => 'text',
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
