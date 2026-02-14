<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2026 Edward Herr <elherr@gmail.com>
 *
 * Based on Msteams.php Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

/*
 * Pagertree.php generates webhook to PagerTree webhook integration by --
 * 1. Mapping LibreNMS alert states to PagerTree event types
 * 2. Sending LibreNMS alert_id as PagerTree Id.
 * 3. Sending LibreNMS alert title as PagerTree Title
 * 4. Sending LibreNMS alert msg as Pagertree Description
 *
 * PagerTree webhook integration documentation --
 * https://pagertree.com/docs/integration-guides/webhook
 *
 * When LibreNMS alert state changes, webhook updates corresponding
 * PagerTree alert status.
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class PagerTree extends Transport
{
    protected string $name = 'PagerTree';

    public function deliverAlert(array $alert_data): bool
    {
        $event_type = 'create';

        if ($alert_data['state'] === 0)
            $event_type = 'resolve';
        if ($alert_data['state'] === 1)
            $event_type = 'create';
        if ($alert_data['state'] === 2)
            $event_type = 'acknowledge';


        $event = [
            'event_type' => $event_type,
            'Id' => $alert_data['alert_id'],
            'Title' => $alert_data['title'],
            'Description' => strip_tags((string) $alert_data['msg'], '<strong><em><h1><h2><h3><strike><ul><ol><li><pre><blockquote><a><img><p>'),
        ];

        $event_json = json_encode($event);

        $client = Http::client();

        $msg = $alert_data['uid'] === '000'
            ? $this->testMessage() // use static JSON for tests
            : $event_json;

        $client->withBody($msg, 'application/json');

        $res = $client->post($this->config['pagertree-url']);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $event['Description'], $event);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'pagertree-url',
                    'descr' => 'PagerTree Webhook URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'pagertree-url' => 'required|url',
            ],
        ];
    }

    private function testMessage(): string
    {
        $test_id = 'TEST-' . strval(rand(100000,200000));
        $event = [
            "event_type" => 'create',
            "Id" => $test_id,
            "Title" => 'LibreNMS PagerTree Test Alert',
            "Description" => 'Testing PagerTree Transport',
          ];
        $event_json = json_encode($event);
        return $event_json;
    }
}
