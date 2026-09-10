<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version. Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Msteams extends Transport
{
    protected string $name = 'Microsoft Teams';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['msteam-url'];
        $useJson = $this->config['use-json'] === 'on';

        $client = Http::client();

        if ($useJson) {
            // JSON mode: template must supply the full payload.
            // For Workflow webhooks the template must include the
            // {"type":"message","attachments":[...]} envelope.
            // For legacy O365 connectors a bare MessageCard JSON is fine.
            $msg = $alert_data['uid'] === '000'
                ? $this->testJsonMessage() // use pre-made JSON for tests
                : $alert_data['msg'];

            $client->withBody($msg, 'application/json');
            $res = $client->post($url, []);
        } else {
            // Markdown/MessageCard mode.
            // Build the MessageCard payload from alert data.
            $messageCard = [
                '@type' => 'MessageCard',
                '@context' => 'http://schema.org/extensions',
                'title' => $alert_data['title'],
                'themeColor' => self::getColorForState($alert_data['state']),
                'text' => strip_tags(
                    (string) $alert_data['msg'],
                    '<strong><em><h1><h2><h3><strike><ul><ol><li><pre><blockquote><a><img><p>'
                ),
                'summary' => $alert_data['title'],
            ];

            if (! $this->isLegacyConnectorWebhook($url)) {
                // New Workflow (Power Automate) webhook:
                // MessageCard must be wrapped in the "message"/"attachments" envelope.
                // Supported as of February 2026 per Microsoft dev blog update.
                $payload = [
                    'type' => 'message',
                    'attachments' => [
                        [
                            'contentType' => 'application/vnd.microsoft.teams.card.o365connector',
                            'content' => $messageCard,
                        ],
                    ],
                ];
                $client->withBody(json_encode($payload), 'application/json');
                $res = $client->post($url, []);
            } else {
                // Legacy O365 Connector webhook: send bare MessageCard directly.
                $res = $client->post($url, $messageCard);
            }
        }

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException(
            $alert_data,
            $res->status(),
            $res->body(),
            $messageCard['text'] ?? $alert_data['msg'],
            $messageCard ?? []
        );
    }

    private function isLegacyConnectorWebhook(string $url): bool
    {
        return str_contains($url, 'outlook.office.com')
            || str_contains($url, 'outlook.office365.com')
            || str_contains($url, '.webhook.office.com');
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'msteam-url',
                    'descr' => 'Microsoft Teams Webhook URL (legacy O365 connector or new Workflow webhook)',
                    'type' => 'text',
                ],
                [
                    'title' => 'Use JSON?',
                    'name' => 'use-json',
                    'descr' => 'Compose MessageCard with JSON rather than Markdown. Your template must be valid MessageCard JSON',
                    'type' => 'checkbox',
                    'default' => false,
                ],
            ],
            'validation' => [
                'msteam-url' => 'required|url',
            ],
        ];
    }

    private function testJsonMessage(): string
    {
        return '{
    "type": "message",
    "attachments": [
        {
            "contentType": "application/vnd.microsoft.card.adaptive",
            "contentUrl": null,
            "content": {
                "type": "AdaptiveCard",
                "body": [
                    {
                        "type": "TextBlock",
                        "size": "Medium",
                        "weight": "Bolder",
                        "text": "LibreNMS Test Adaptive Card"
                    },
                    {
                        "type": "TextBlock",
                        "text": "You have successfully sent a pre-formatted AdaptiveCard message to Teams.",
                        "wrap": true
                    },
                    {
                        "type": "TextBlock",
                        "text": "This does not test if your alert template is valid AdaptiveCard JSON.",
                        "isSubtle": true,
                        "wrap": true
                    }
                ],
                "$schema": "http://adaptivecards.io/schemas/adaptive-card.json",
                "version": "1.4"
            }
        }
    ]
}';
    }
}
