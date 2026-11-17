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
use LibreNMS\Enum\AlertState;
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
            // JSON mode: the template must supply the full payload, including
            // the {"type":"message","attachments":[...]} envelope.
            $msg = $alert_data['uid'] === '000'
                ? $this->testJsonMessage() // use pre-made JSON for tests
                : $alert_data['msg'];

            $client->withBody($msg, 'application/json');
        } else {
            $payload = $this->adaptiveCard($alert_data);
            $client->withBody(json_encode($payload), 'application/json');
        }

        $res = $client->post($url, []);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException(
            $alert_data,
            $res->status(),
            $res->body(),
            $alert_data['msg'],
            $payload ?? []
        );
    }

    private function adaptiveCard(array $alert_data): array
    {
        $color = match ((int) $alert_data['state']) {
            AlertState::CLEAR => 'Good',
            AlertState::ACKNOWLEDGED => 'Accent',
            AlertState::BETTER, AlertState::CHANGED => 'Warning',
            default => 'Attention',
        };

        // A TextBlock renders markdown but not HTML and needs a blank line for a line break.
        $text = preg_replace('#<br\s*/?>|</(p|div|li|h[1-6])>#i', "\n", (string) $alert_data['msg']);
        $text = preg_replace('/\R+/', "\n\n", trim(strip_tags($text)));
        // Teams rejects messages over 28 KB.
        $text = mb_strcut($text, 0, 20000);

        return [
            'type' => 'message',
            'attachments' => [
                [
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'content' => [
                        '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                        'type' => 'AdaptiveCard',
                        'version' => '1.4',
                        'body' => [
                            [
                                'type' => 'TextBlock',
                                'size' => 'Medium',
                                'weight' => 'Bolder',
                                'color' => $color,
                                'text' => $alert_data['title'],
                                'wrap' => true,
                            ],
                            [
                                'type' => 'TextBlock',
                                'text' => $text,
                                'wrap' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'msteam-url',
                    'descr' => 'Microsoft Teams Workflow webhook URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Use JSON?',
                    'name' => 'use-json',
                    'descr' => 'Send the alert template as the raw JSON payload instead of the built-in card. Your template must be valid JSON with the message/attachments envelope',
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
