<?php
/* Copyright (C) 2015 James Campbell <neokjames@gmail.com>
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

/* Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 * Boxcar API Transport
 * @author trick77 <jan@trick77.com>
 * @copyright 2015 trick77, neokjames, f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Config;
use LibreNMS\Enum\AlertState;

class Boxcar extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $boxcar_opts = $this->parseUserOptions($this->config['options']);
        $boxcar_opts['access_token'] = $this->config['boxcar-token'];

        return $this->contactBoxcar($obj, $boxcar_opts);
    }

    public static function contactBoxcar($obj, $api)
    {
        $data = [];
        $data['user_credentials'] = $api['access_token'];
        $data['notification[source_name]'] = Config::get('project_id', 'librenms');
        switch ($obj['severity']) {
            case 'critical':
                $severity = 'Critical';
                if (! empty($api['sound_critical'])) {
                    $data['notification[sound]'] = $api['sound_critical'];
                }
                break;
            case 'warning':
                $severity = 'Warning';
                if (! empty($api['sound_warning'])) {
                    $data['notification[sound]'] = $api['sound_warning'];
                }
                break;
            default:
                $severity = 'Unknown';
                break;
        }
        switch ($obj['state']) {
            case AlertState::RECOVERED:
                $title_text = 'OK';
                if (! empty($api['sound_ok'])) {
                    $data['notification[sound]'] = $api['sound_ok'];
                }
                break;
            case AlertState::ACTIVE:
                $title_text = $severity;
                break;
            case AlertState::ACKNOWLEDGED:
                $title_text = 'Acknowledged';
                break;
            default:
                $title_text = $severity;
                break;

        }
        $data['notification[title]'] = $title_text . ' - ' . $obj['hostname'] . ' - ' . $obj['name'];
        $message_text = 'Timestamp: ' . $obj['timestamp'];
        if (! empty($obj['faults'])) {
            $message_text .= "\n\nFaults:\n";
            foreach ($obj['faults'] as $k => $faults) {
                $message_text .= '#' . $k . ' ' . $faults['string'] . "\n";
            }
        }
        $data['notification[long_message]'] = $message_text;
        $curl = curl_init();
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, 'https://new.boxcar.io/api/notifications');
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 201) {
            var_dump('Boxcar returned error'); //FIXME: proper debugging

            return false;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Access Token',
                    'name' => 'boxcar-token',
                    'descr' => 'Boxcar Access Token',
                    'type' => 'text',
                ],
                [
                    'title' => 'Boxcar Options',
                    'name' => 'boxcar-options',
                    'descr' => 'Boxcar Options',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'boxcar-token' => 'required',
            ],
        ];
    }
}
