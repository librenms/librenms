<?php
/* Copyright (C) 2018 Drew Hynes <drew.hynes@gmail.com>
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
 * GitLab API Transport
 * @author Drew Hynes <drew.hynes@gmail.com>
 * @copyright 2018 Drew Hynes, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;

class Gitlab extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        if (! empty($this->config)) {
            $opts['project-id'] = $this->config['gitlab-id'];
            $opts['key'] = $this->config['gitlab-key'];
            $opts['host'] = $this->config['gitlab-host'];
        }

        return $this->contactGitlab($obj, $opts);
    }

    public function contactGitlab($obj, $opts)
    {
        // Don't create tickets for resolutions
        if ($obj['state'] != AlertState::CLEAR) {
            $device = device_by_id_cache($obj['device_id']); // for event logging

            $project_id = $opts['project-id'];
            $project_key = $opts['key'];
            $details = 'Librenms alert for: ' . $obj['hostname'];
            $description = $obj['msg'];
            $title = urlencode($details);
            $desc = urlencode($description);
            $url = $opts['host'] . "/api/v4/projects/$project_id/issues?title=$title&description=$desc";
            $curl = curl_init();

            $data = ['title' => $details,
                'description' => $description,
            ];
            $postdata = ['fields' => $data];
            $datastring = json_encode($postdata);

            set_curl_proxy($curl);

            $headers = ['Accept: application/json', 'Content-Type: application/json', 'PRIVATE-TOKEN: ' . $project_key];

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);

            $ret = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($code == 200) {
                $gitlabout = json_decode($ret, true);
                d_echo('Created GitLab issue ' . $gitlabout['key'] . ' for ' . $device);

                return true;
            } else {
                d_echo('GitLab connection error: ' . serialize($ret));

                return false;
            }
        }
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Host',
                    'name' => 'gitlab-host',
                    'descr' => 'GitLab Host',
                    'type' => 'text',
                ],
                [
                    'title' => 'Project ID',
                    'name' => 'gitlab-id',
                    'descr' => 'GitLab Project ID',
                    'type'=> 'text',
                ],
                [
                    'title' => 'Personal Access Token',
                    'name' => 'gitlab-key',
                    'descr' => 'Personal Access Token',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'gitlab-host' => 'required|string',
                'gitlab-id' => 'required|string',
                'gitlab-key' => 'required|string',
            ],
        ];
    }
}
