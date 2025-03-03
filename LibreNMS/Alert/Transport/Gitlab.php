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
 *
 * @author Drew Hynes <drew.hynes@gmail.com>
 * @copyright 2018 Drew Hynes, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Gitlab extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        // Don't create tickets for resolutions
        if ($alert_data['state'] == AlertState::RECOVERED) {
            return true;
        }

        $project_id = $this->config['gitlab-id'];
        $url = $this->config['gitlab-host'] . "/api/v4/projects/$project_id/issues";
        $data = [
            'title' => 'Librenms alert for: ' . $alert_data['hostname'],
            'description' => $alert_data['msg'],
        ];

        $res = Http::client()
            ->withHeaders([
                'PRIVATE-TOKEN' => $this->config['gitlab-key'],
            ])
            ->acceptJson()
            ->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $data['description'], $data);
    }

    public static function configTemplate(): array
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
                    'type' => 'text',
                ],
                [
                    'title' => 'Personal Access Token',
                    'name' => 'gitlab-key',
                    'descr' => 'Personal Access Token',
                    'type' => 'password',
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
