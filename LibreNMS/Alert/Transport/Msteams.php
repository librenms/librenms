<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
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
        $data = [
            'title' => $alert_data['title'],
            'themeColor' => self::getColorForState($alert_data['state']),
            'text' => strip_tags($alert_data['msg'], '<strong><em><h1><h2><h3><strike><ul><ol><li><pre><blockquote><a><img><p>'),
            'summary' => $alert_data['title'],
        ];

        $client = Http::client();

        // template will contain raw json
        if ($this->config['use-json'] === 'on') {
            $msg = $alert_data['uid'] === '000'
                ? $this->messageCard() // use pre-made MessageCard for tests
                : $alert_data['msg'];

            $client->withBody($msg, 'application/json');
        }

        $res = $client->post($this->config['msteam-url'], $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $data['text'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'msteam-url',
                    'descr' => 'Microsoft Teams Webhook URL',
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

    private function messageCard(): string
    {
        return '{
    "@context": "https://schema.org/extensions",
    "@type": "MessageCard",
    "potentialAction": [
        {
            "@type": "OpenUri",
            "name": "View MessageCard Reference",
            "targets": [
                {
                    "os": "default",
                    "uri": "https://learn.microsoft.com/en-us/outlook/actionable-messages/message-card-reference"
                }
            ]
        },
        {
            "@type": "OpenUri",
            "name": "View LibreNMS Website",
            "targets": [
                {
                    "os": "default",
                    "uri": "https://www.librenms.org/"
                }
            ]
        }
    ],
    "sections": [
        {
            "facts": [
                {
                    "name": "Next Action:",
                    "value": "Make your alert template emit valid MessageCard Json"
                }
            ],
            "text": "You have successfully sent a pre-formatted MessageCard message to teams."
        }
    ],
    "summary": "Test Successful",
    "themeColor": "0072C6",
    "title": "Test MessageCard"
}';
    }
}
