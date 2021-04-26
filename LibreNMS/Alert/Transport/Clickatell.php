<?php
/* Copyright (C) 2015 Daniel Preussker <f0o@librenms.org>
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
 * Clickatell REST-API Transport
 * @author f0o <f0o@librenms.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Clickatell extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $clickatell_opts['token'] = $this->config['clickatell-token'];
        $clickatell_opts['to'] = preg_split('/([,\r\n]+)/', $this->config['clickatell-numbers']);

        return $this->contactClickatell($obj, $clickatell_opts);
    }

    public static function contactClickatell($obj, $opts)
    {
        $url = 'https://platform.clickatell.com/messages/http/send?apiKey=' . $opts['token'] . '&to=' . implode(',', $opts['to']) . '&content=' . urlencode($obj['title']);

        $curl = curl_init($url);
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code > 200) {
            var_dump($ret);

            return;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Token',
                    'name'  => 'clickatell-token',
                    'descr' => 'Clickatell Token',
                    'type'  => 'text',
                ],
                [
                    'title' => 'Mobile Numbers',
                    'name'  => 'clickatell-numbers',
                    'descr' => 'Enter mobile numbers, can be new line or comma separated',
                    'type'  => 'textarea',
                ],
            ],
            'validation' => [
                'clickatell-token'   => 'required|string',
                'clickatell-numbers' => 'required|string',
            ],
        ];
    }
}
