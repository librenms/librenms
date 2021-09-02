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

class Kayako extends Transport
{
    public function deliverAlert($host, $kayako)
    {
        if (! empty($this->config)) {
            $kayako['url'] = $this->config['kayako-url'];
            $kayako['key'] = $this->config['kayako-key'];
            $kayako['secret'] = $this->config['kayako-secret'];
            $kayako['department'] = $this->config['kayako-department'];
        }

        return $this->contactKayako($host, $kayako);
    }

    public function contactKayako($host, $kayako)
    {
        $url = $kayako['url'] . '/Tickets/Ticket';
        $key = $kayako['key'];
        $secret = $kayako['secret'];
        $user = Config::get('email_from');
        $department = $kayako['department'];
        $ticket_type = 1;
        $ticket_status = 1;
        $ticket_prio = 1;
        $salt = bin2hex(random_bytes(20));
        $signature = base64_encode(hash_hmac('sha256', $salt, $secret, true));

        $protocol = [
            'subject' => ($host['name'] ? $host['name'] . ' on ' . $host['hostname'] : $host['title']),
            'fullname' => 'LibreNMS Alert',
            'email' => $user,
            'contents' => strip_tags($host['msg']),
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
        $post_data = http_build_query($protocol, '', '&');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_exec($curl);

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            var_dump('Kayako returned Error, retry later');

            return false;
        }

        return true;
    }

    public static function configTemplate()
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
                    'type' => 'text',
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
