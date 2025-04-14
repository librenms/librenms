<?php

/* Copyright (C) 2025 Raphaël Aubry <aubryr@asperience.fr>
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
 * IRC Transport
 *
 * @author Raphaël Aubry <aubryr@asperience.fr>
 * @copyright 2025 ASPerience
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Glpi extends Transport
{
    protected string $name = 'GLPI';

    public function deliverAlert(array $alert_data): bool
    {
        // Connect to the API with app/user tokens
        $headers = [
            'Content-Type' => 'application/json',
            'App-Token' => $this->config['app-token'],
        ];

        $data = [
            'user_token' => $this->config['user-token'],
            'get_full_session' => true,
        ];

        $res = Http::client()
            ->withHeaders($headers)
            ->get($this->config['api-url'] . '/initSession', $data);

        if (! $res->successful()) {
            throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(),
                $alert_data['msg'], $data);
        }

        $headers['Session-Token'] = $res->json()['session_token'];
        $userID = $res->json()['session']['glpiID'];
        $profileID = $res->json()['session']['glpiactiveprofile']['id'];

        // Change the active profile to super-admin (always 4 in GLPI)
        if ($profileID != 4) {
            $data = [
                'profiles_id' => 4,
            ];

            $res = Http::client()
                ->withHeaders($headers)
                ->post($this->config['api-url'] . '/changeActiveProfile/', $data);
        }

        // Retrieve the ticket for the alert (by title)
        $ticketURL = $this->config['api-url'] . '/Ticket';
        $searchURL = $this->config['api-url'] .
            '/search/Ticket?' .
            'forcedisplay[0]=2&' .
            'forcedisplay[1]=12&' .
            'criteria[0][field]=1&' .
            'criteria[0][searchtype]=contains&' .
            'criteria[0][value]=^[LibreNMS: ' . $alert_data['sysName'] . '] ' . $alert_data['name'] . '$&' .
            'criteria[1][link]=AND&' .
            'criteria[1][field]=12&' .
            'criteria[1][searchtype]=equals&' .
            'criteria[1][value]=notclosed';

        $res = Http::client()
            ->withHeaders($headers)
            ->get($searchURL);

        if (! array_key_exists('data', $res->json())) {
            // No ticket for the alert found, create a new one

            // Retrieve the device in GLPI
            $deviceSearchURL = $this->config['api-url'] .
                '/search/AllAssets?' .
                'forcedisplay[0]=2&' .
                'forcedisplay[1]=80&' .
                'criteria[0][field]=1&' .
                'criteria[0][searchtype]=contains&' .
                'criteria[0][value]=^' . $alert_data['sysName'] . '$';

            $res = Http::client()
                ->withHeaders($headers)
                ->get($deviceSearchURL);

            $deviceID = $res->json()['data'][0]['2'] ?? null;
            $itemtype = $res->json()['data'][0]['itemtype'] ?? null;

            // Retrieve the entity in GLPI
            $entityName = $res->json()['data'][0]['80'] ?? null;
            $entityID = null;
            if ($entityName != null) {
                $entitySearchURL = $this->config['api-url'] .
                    '/Entity?searchText[completename]=^' . $entityName . '$';

                $res = Http::client()
                    ->withHeaders($headers)
                    ->get($entitySearchURL);

                $entityID = $res->json()[0]['id'] ?? null;
            }

            // Create the ticket
            $data = [
                'input' => [
                    'name' => '[LibreNMS: ' . $alert_data['sysName'] . '] ' . $alert_data['name'],
                    'content' => $alert_data['msg'],
                    '_users_id_requester' => $userID,
                ],
            ];

            if ($entityID != null) {
                $data['input']['entities_id'] = $entityID;
            }

            $res = Http::client()
                ->withHeaders($headers)
                ->post($ticketURL, $data);

            // Associate GLPI device to the ticket
            if ($res->successful() && $deviceID != null) {
                $ticketID = $res->json()['id'];

                $data = [
                    'input' => [
                        'items_id' => $deviceID,
                        'itemtype' => $itemtype,
                        'tickets_id' => $ticketID,
                    ],
                ];

                $res = Http::client()
                    ->withHeaders($headers)
                    ->post($this->config['api-url'] . '/Item_Ticket', $data);
            }
        } else {
            $ticketID = $res->json()['data'][0]['2'];
            $ticketStatus = $res->json()['data'][0]['12'];

            // Add followup to ticket
            $data = [
                'input' => [
                    'content' => $alert_data['msg'],
                    'itemtype' => 'Ticket',
                    'items_id' => $ticketID,
                ],
            ];

            $followupURL = $this->config['api-url'] . '/ITILFollowup';

            $res = Http::client()
                ->withHeaders($headers)
                ->post($followupURL, $data);

            if ($ticketStatus == 5) {
                // Reopen the ticket if it was resolved or close it if the state is 0
                $data = [
                    'input' => [
                        'status' => 2,
                    ],
                ];

                if ($alert_data['state'] == 0) {
                    $data['input']['status'] = 6;
                }

                $res = Http::client()
                    ->withHeaders($headers)
                    ->patch($this->config['api-url'] . '/Ticket/' . $ticketID, $data);
            }
        }

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(),
            $alert_data['msg'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'GLPI API URL',
                    'name' => 'api-url',
                    'descr' => 'API URL of GLPI (typically ending in apirest.php)',
                    'type' => 'text',
                ],
                [
                    'title' => 'User Token',
                    'name' => 'user-token',
                    'descr' => 'GLPI user token for API access (to generate: User preferences > API)',
                    'type' => 'text',
                ],
                [
                    'title' => 'App Token',
                    'name' => 'app-token',
                    'descr' => 'App token for API access (to generate: Configuration > General > API)',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'api-url' => 'required|url',
                'user-token' => 'required|string',
                'app-token' => 'required|string',
            ],
        ];
    }
}
