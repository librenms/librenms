<?php
/*Copyright (c) 2019 GitStoph <https://github.com/GitStoph>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details. */

/**
 * Jira Webhook & API Transport
 *
 * @author Skylark <https://github.com/LoveSkylark>
 * @copyright 2023 Skylark
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Jira extends Transport
{
    protected string $name = 'Jira';

    public function deliverAlert(array $alert_data): bool
    {
        $webhook_on = $this->config['enable-webhook'] ?? false;

        // Check if messsage is an alert or not
        if ($alert_data['state'] != 0) {
            $url = $this->config['jira-url'];
            // If webhooks are not enabled, append the API info
            if (! $webhook_on) {
                $url .= '/rest/api/latest/issue';
            }
        // Messsage is a recovery
        } else {
            if (! $webhook_on) {
                return false; // Webhooks not enabled, do nothing.
            } else {
                $url = $this->config['jira-close-url'];
            }
        }

        $project_key = $this->config['jira-key'];
        $issue_type = $this->config['jira-type'];
        $title = empty($alert_data['title']) ? 'Librenms alert for: ' . $alert_data['hostname'] : $alert_data['title'];
        $description = $alert_data['msg'];

        // Construct the payload
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

        // Add Custom Webhook ID to the payload
        if ($webhook_on) {
            if (! empty($this->config['webhook-id'])) {
                $data['fields'][$this->config['webhook-id']] = $alert_data['id'];
            } else {
                $data['fields']['alert_id'] = $alert_data['id'];
            }
        }

        // Add Custom fileds to the payload
        $custom = json_decode($this->config['jira-custom'], true);
        if (! empty($custom)) {
            $data['fields'] = array_merge($data['fields'], $custom);
        }

        $res = Http::client()
            ->withBasicAuth($this->config['jira-username'], $this->config['jira-password'])
            ->acceptJson()
            ->post($url, $data);

        if ($res->successful()) {
            return true; // Delivery successful
        }

        // An error occurred, throw an exception
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
                    'name' => 'jira-url',
                    'descr' => 'Create Jira Ticket',
                    'type' => 'text',
                ],
                [
                    'title' => 'Close Ticket URL',
                    'name' => 'jira-close-url',
                    'descr' => 'Close Jira Ticket | Webhook Only"',
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
                    'title' => 'Enable Webhook',
                    'name' => 'enable-webhook',
                    'descr' => 'Use Webhook instead of API',
                    'type' => 'checkbox',
                    'default' => false,
                ],
                [
                    'title' => 'Webhook Identifier',
                    'name' => 'webhook-id',
                    'descr' => 'Jira Webhook Identifier',
                    'type' => 'text',
                ],
                [
                    'title' => 'Custom Fields',
                    'name' => 'jira-custom',
                    'type' => 'textarea',
                    'descr' => '{&quot;components&quot;: [{&quot;id&quot;: &quot;00001&quot;}],&#xA;&quot;customfield_10001&quot;: [{&quot;id&quot;: &quot;00002&quot;}]}',
                ],
            ],
            'validation' => [
                'jira-key' => 'required|string',
                'jira-url' => 'required|url',
                'jira-close-url' => 'nullable|url',
                'webhook-id' => 'nullable|string',
                'jira-type' => 'required|string',
                'jira-username' => 'required|string',
                'jira-password' => 'required|string',
            ],
        ];
    }
}
