<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Config;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Kayako extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['kayako-url'] . '/Tickets/Ticket';
        $key = $this->config['kayako-key'];
        $secret = $this->config['kayako-secret'];
        $user = Config::get('email_from');
        $department = $this->config['kayako-department'];
        $ticket_type = 1;
        $ticket_status = 1;
        $ticket_prio = 1;
        $salt = bin2hex(random_bytes(20));
        $signature = base64_encode(hash_hmac('sha256', $salt, $secret, true));

        $protocol = [
            'subject' => ($alert_data['name'] ? $alert_data['name'] . ' on ' . $alert_data['hostname'] : $alert_data['title']),
            'fullname' => 'LibreNMS Alert',
            'email' => $user,
            'contents' => strip_tags($alert_data['msg']),
            'departmentid' => $department,
            'ticketstatusid' => $ticket_status,
            'ticketpriorityid' => $ticket_prio,
            'tickettypeid' => $ticket_type,
            'autouserid' => 1,
            'ignoreautoresponder' => true,
            'apikey' => $key,
            'salt' => $salt,
            'signature' => $signature,
        ];

        $res = Http::client()
            ->asForm() // unsure if this is needed, can't access docs
            ->post($url, $protocol);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $protocol['contents'], $protocol);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Kayako URL',
                    'name' => 'kayako-url',
                    'descr' => 'ServiceDesk API URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Kayako API Key',
                    'name' => 'kayako-key',
                    'descr' => 'ServiceDesk API Key',
                    'type' => 'text',
                ],
                [
                    'title' => 'Kayako API Secret',
                    'name' => 'kayako-secret',
                    'descr' => 'ServiceDesk API Secret Key',
                    'type' => 'password',
                ],
                [
                    'title' => 'Kayako Department',
                    'name' => 'kayako-department',
                    'descr' => 'Department to post a ticket',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'kayako-url' => 'required|url',
                'kayako-key' => 'required|string',
                'kayako-secret' => 'required|string',
                'kayako-department' => 'required|string',
            ],
        ];
    }
}
