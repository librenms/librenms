<?php

/*
 * LibreNMS
 *
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

class Msteamsworkflows extends Transport
{
    protected string $name = 'Microsoft Teams Workflows';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['msteam-workflows-url'];
        $client = Http::client();

        $stateSymbol = match ($alert_data['state']) {
            0 => "🟢",
            1 => "🔴",
            2 => "🔵",
            default => "🔴"
        };

        $messageCard = [
            "type" => "message",
            "attachments" => [
                [
                    "contentType" => "application/vnd.microsoft.card.adaptive",
                    "contentUrl" => null,
                    "content" => [
                        "schema" => "http://adaptivecards.io/schemas/adaptive-card.json",
                        "type" => "AdaptiveCard",
                        "version" => "1.4",
                        "msTeams" => [
                            "width" => "full"
                        ],
                        "body" => [
                            [
                                "type" => "TextBlock",
                                "text" => $stateSymbol . "  " . $alert_data['title'],
                                "size" => "medium",
                                "weight" => "Bolder",
                                "style" => "heading",
                                "wrap" => true
                            ],
                            [
                                "type" => "RichTextBlock",
                                "inlines" => [
                                    [
                                        "type" => "TextRun",
                                        "text" => strip_tags($alert_data['msg']),
                                        "fontType" => "monospace",
                                        "size" => "small"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $client->withBody(json_encode($messageCard), 'application/json');
        $res = $client->post($url, []);

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

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Workflows Webhook URL',
                    'name' => 'msteam-workflows-url',
                    'descr' => 'Microsoft Teams Workflow Webhook URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'msteam-workflows-url' => 'required|url',
            ],
        ];
    }
}
