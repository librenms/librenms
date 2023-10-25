<?php
/* Copyright (C) 2015 Aldemir Akpinar <aldemir.akpinar@gmail.com>
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
 * Jira API Transport
 *
 * @author  Aldemir Akpinar <aldemir.akpinar@gmail.com>
 * @copyright 2017 Aldemir Akpinar, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Jira extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        // Don't create tickets for resolutions
        if ($alert_data['severity'] == 'recovery') {
            return true;
        }

        $prjkey = $this->config['jira-key'];
        $issuetype = $this->config['jira-type'];
        $details = empty($alert_data['title']) ? 'Librenms alert for: ' . $alert_data['hostname'] : $alert_data['title'];
        $description = $alert_data['msg'];
        $url = $this->config['jira-url'] . '/rest/api/latest/issue';

        $data = [
            'fields' => [
                'project' => [
                    'key' => $prjkey,
                ],
                'summary' => $details,
                'description' => $description,
                'issuetype' => [
                    'name' => $issuetype,
                ],
            ],
        ];

        $res = Http::client()
            ->withBasicAuth($this->config['jira-username'], $this->config['jira-password'])
            ->acceptJson()
            ->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $description, $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'URL',
                    'name' => 'jira-url',
                    'descr' => 'Jira URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Project Key',
                    'name' => 'jira-key',
                    'descr' => 'Jira Project Key',
                    'type' => 'text',
                ],
                [
                    'title' => 'Issue Type',
                    'name' => 'jira-type',
                    'descr' => 'Jira Issue Type',
                    'type' => 'text',
                ],
                [
                    'title' => 'Jira Username',
                    'name' => 'jira-username',
                    'descr' => 'Jira Username',
                    'type' => 'text',
                ],
                [
                    'title' => 'Jira Password',
                    'name' => 'jira-password',
                    'descr' => 'Jira Password',
                    'type' => 'password',
                ],
            ],
            'validation' => [
                'jira-key' => 'required|string',
                'jira-url' => 'required|string',
                'jira-type' => 'required|string',
                'jira-username' => 'required|string',
                'jira-password' => 'required|string',
            ],
        ];
    }
}
