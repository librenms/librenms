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

use LibreNMS\Interfaces\Alert\Transport;
use LibreNMS\Alert\AlertUtil;

class Ciscospark implements Transport
{
    public function deliverAlert($obj, $opts)
    {
        $details = AlertUtil::getTransportConfig($opts['alert']['transport_id']);
        $text = strip_tags($obj['msg']);
        $data = array (
            'roomId' => $details['room-id'],
            'text' => $text
        );
        $token = $details['api-token'];

        $curl   = curl_init();
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, 'https://api.ciscospark.com/v1/messages');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-type' => 'application/json',
            'Expect:',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $ret  = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($code != 200) {
            echo("Cisco Spark returned Error, retry later\r\n");
            return false;
        }
        return true;
    }

    public static function configTemplate()
    {
        return [
            [
                'title' => 'API Token',
                'name' => 'api-token',
                'descr' => 'CiscoSpark API Token',
                'type' => 'text',
                'required' => true,
                'pattern' => '[a-zA-Z0-9]'
            ],
            [
                'title' => 'RoomID',
                'name' => 'room-id',
                'descr' => 'CiscoSpark Room ID',
                'type' => 'text',
                'required' => true,
                'pattern' => '[a-zA-Z0-9\-]'
            ]
        ];
    }

    public static function configBuilder()
    {
        global $vars;
        $status = 'ok';
        $message  = '';

        if ($vars['api-token'] && $vars['room-id']) {
            $transport_config = [
                'api-token' => $vars['api-token'],
                'room-id' => $vars['room-id']
            ];
        } else {
            $status = 'error';
            $message = 'Missing API token or Room ID';
        }

        return [
            'transport_config' => $transport_config,
            'status' => $status,
            'message' => $message
        ];
    }
}
