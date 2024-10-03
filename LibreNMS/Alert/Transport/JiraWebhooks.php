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

class JiraWebhooks extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        // build webhook_id
        $webhook_id = "";
        if (!empty($this->config['jira-webhook-close']) && !empty($this->config['webhook-id'])) {
            $webhook_id = [$this->config['webhook-id'] => sprintf("%03d-%05d", $alert_data['id'], $alert_data['device_id'])];
        }
        if ($alert_data['severity'] == 'recovery') {
            $url = $this->config['jira-webhook-open'] . '/rest/api/latest/issue';
        } else {
             // Check if webhooks are disabled
            if (! empty($webhook_id)) {
                // Perform closing ticket actions for when webhooks are enabled
                $url = $this->config['jira-webhook-close'] . '/rest/api/latest/issue';
            } else {
                // Perform no actions when severity is 'recovery' and webhooks are disabled
                return false;
            }
        }

        $project_key = $this->config['jira-key'];
        $issue_type = $this->config['jira-type'];
        $title = empty($alert_data['title']) ? 'Librenms alert for: ' . $alert_data['hostname'] : $alert_data['title'];
        $description = $alert_data['msg'];

        $data = [
            'fields' => [
                'summary' => $title,
                'description' => $description,
                'project' => [
                    'key' => $project_key,
                ],
                'issuetype' => [
                    'name' => $issue_type,
                ],

            ],
        ];

        // Add Custom fileds to the payload
        if (! empty($webhook_id)) {
            $data['fields'] = array_merge($data['fields'], json_encode($webhook_id));
        }

        // Add Custom fileds to the payload
        $custom = json_decode('{' . $this->config['jira-custom'] . '}', true);
        if (! empty($custom)) {
            $data['fields'] = array_merge($data['fields'], $custom);
        }

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
                    'title' => 'Open Ticket URL',
                    'name' => 'jira-webhook-open',
                    'descr' => 'Create Jira Ticket Path',
                    'type' => 'text',
                ],
                [
                    'title' => 'Close Ticket URL',
                    'name' => 'jira-webhook-close',
                    'descr' => 'Only Used with Webhooks',
                    'type' => 'text',
                ],
                [
                    'title' => 'Webhook Identifier',
                    'name' => 'webhook-id',
                    'descr' => 'Jira Webhook Identifier',
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
                [
                    'title' => 'Custom Fileds',
                    'name' => 'jira-custom',
                    'type' => 'textarea',
                    'descr' => '&quot;components&quot;: [{&quot;id&quot;: &quot;00001&quot;}],&#xA;&quot;customfield_10001&quot;: [{&quot;id&quot;: &quot;00002&quot;}]',
                ],
            ],
            'validation' => [
                'jira-key' => 'required|string',
                'jira-webhook-open' => 'required|url',
                'jira-webhook-close' => 'nullable|url',
                'webhook-id' => 'nullable|string',
                'jira-type' => 'required|string',
                'jira-username' => 'required|string',
                'jira-password' => 'required|string',
            ],
        ];
    }
}

