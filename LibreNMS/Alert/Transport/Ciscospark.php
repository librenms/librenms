<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Ciscospark extends Transport
{
    protected string $name = 'Cisco Webex Teams';

    public function deliverAlert(array $alert_data): bool
    {
        $room_id = $this->config['room-id'];
        $token = $this->config['api-token'];
        $url = 'https://api.ciscospark.com/v1/messages';
        $data = [
            'roomId' => $room_id,
        ];

        if ($this->config['use-markdown'] === 'on') {
            // Remove blank lines as they create weird markdown behaviors.
            $data['markdown'] = preg_replace('/^\s+/m', '', $alert_data['msg']);
        } else {
            $data['text'] = strip_tags($alert_data['msg']);
        }

        $res = Http::client()
            ->withToken($token)
            ->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $data['text'] ?? $data['markdown'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'API Token',
                    'name' => 'api-token',
                    'descr' => 'CiscoSpark API Token',
                    'type' => 'text',
                ],
                [
                    'title' => 'RoomID',
                    'name' => 'room-id',
                    'descr' => 'CiscoSpark Room ID',
                    'type' => 'text',
                ],
                [
                    'title' => 'Use Markdown?',
                    'name' => 'use-markdown',
                    'descr' => 'Use Markdown when sending the alert',
                    'type' => 'checkbox',
                    'default' => false,
                ],
            ],
            'validation' => [
                'api-token' => 'required|string',
                'room-id' => 'required|string',
            ],
        ];
    }
}
