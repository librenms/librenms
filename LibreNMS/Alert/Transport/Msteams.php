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
        $translate_color = $alert_data['state'] == '1'
            ? 'Attention'
            : 'Good';
        $data = '
{
       "type":"message",
       "attachments":[
          {
             "contentType":"application/vnd.microsoft.card.adaptive",
             "contentUrl":null,
             "content":{
                "$schema":"http://adaptivecards.io/schemas/adaptive-card.json",
                        "type": "AdaptiveCard",
                        "version": "1.4",
    "body": [
      {
        "type": "Container",
        "items": [
          {
            "type": "TextBlock",
            "text": "' . $alert_data['title'] . '",
            "color": "' . $translate_color . '",
            "weight": "bolder",
            "size": "medium"
          }
        ]
      },
      {
        "type": "Container",
        "items": [
          {
            "type": "TextBlock",
            "text": "' . strip_tags($alert_data['msg'], '<strong><em><h1><h2><h3><strike><ul><ol><li><pre><blockquote><a><img><p>') . '",
            "wrap": true
          }
        ]
      }
    ]
   }
  }
 ]
}  
';
        $client = Http::client();
        $client->withBody($data, 'application/json');
        $res = $client->post($this->config['msteam-url'], $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $data, $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Flow URL',
                    'name' => 'msteam-url',
                    'descr' => 'Microsoft Teams workflow URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'msteam-url' => 'required|url',
            ],
        ];
    }
}
