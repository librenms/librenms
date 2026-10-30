<?php

/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/
/**
 * Twilio Wake-up Call API Transport
 * This transport is designed to leverage your Twilio application to take alerts, and turn it into an annoying buzz.
 * @author Jack Nolen <jnolen@psl.nmsu.edu>
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;
use Illuminate\Support\Facades\Log;

class Twiliophone extends Transport
{
    protected string $name = 'Twilio - Phones';

    public function deliverAlert(array $alert_data): bool
    {
        // Build a userDefinedVariable list, allowing users to inject values into th TwilML field.
        $userDefinedVariables = [
                '%sysName%' => $alert_data['sysName'] ?? '',
                '%sysDsecr%' => $alert_data['sysDescr'] ?? '',
                '%sysContact%' => $alert_data['sysContact'] ?? '',
                '%os%' => $alert_data['os'] ?? '',
                '%ip%' => $alert_data['ip'] ?? '',
                '%hostname%' => $alert_data['hostname'] ?? '',
                '%display%' => $alert_data['display'] ?? '',
        ];

        // To not incure high fees, this filter is here to block emergency calls.
        // The 911 number will charge you $75/call if its not configuerd correctly, not including criminal charges.
        $targetNumber = preg_replace('/\D/', '', $this->config['twilio-to']);
        $expensiveNumbers = ["911","411","511","611","988","311","711","811"];
        $found = array_any($expensiveNumbers, function ($number) use ($targetNumber) {
                return str_ends_with($targetNumber, $number) || str_contains($targetNumber, $number);
        });

        if ($found) {
                throw new InvalidArgumentException(
                        'Destination number is blocked due to potential emergency or premium charges.'
                );
        }

        // Below lives the final API call.
        $url = 'https://api.twilio.com/2010-04-01/Accounts/'
                . $this->config['twilio-sid']
                . '/Calls.json';

        $data = [
                'From' => $this->config['twilio-sender'],
                'To' => $this->config['twilio-to'],
                'Twiml' => strtr($this->config['twilio-ml'], $userDefinedVariables),
        ];

        $res = Http::client()->asForm()
                ->withBasicAuth(
                        $this->config['twilio-api-sid'],
                        $this->config['twilio-api-secret']
                )
        ->post($url, $data);

        if ($res->successful()) {
                return true;
        }

        throw new AlertTransportDeliveryException(
                $alert_data,
                $res->status(),
                $res->body(),
                $alert_data['msg'] ?? '',
                $data
        );
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Account SID',
                    'name' => 'twilio-sid',
                    'descr' => 'Twilio SID',
                    'type' => 'text',
                ],
                [
                    'title' => 'API SID',
                    'name' => 'twilio-api-sid',
                    'descr' => 'Twilio Api SID',
                    'type' => 'password',
                ],
                [
                    'title' => 'Secret',
                    'name' => 'twilio-api-secret',
                    'descr' => 'Twilio Api Secret',
                    'type' => 'password',
                ],
                [
                    'title' => 'Call To - Number',
                    'name' => 'twilio-to',
                    'descr' => 'Who to call? E164 is required ex: +18884443333',
                    'type' => 'text',
                ],
                [
                    'title' => 'Twilio Phone Number',
                    'name' => 'twilio-sender',
                    'descr' => 'Twilio sending number',
                    'type' => 'text',
                ],
                [
                    'title' => 'TwiML',
                    'name' => 'twilio-ml',
                    'descr' => 'TwiML is used in the markup language for playing audio. Include these varaibles: %sysName%, %sysDescr%, %sysContact%, %os%, %ip%, %honame%, %display% to inject values to the call prompt.',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'twilio-sid' => 'required|string',
                'twilio-api-sid' => 'required|string',
                'twilio-api-secret' => 'required|string',
                'twilio-ml' => 'required|string',
                'twilio-to' => 'required',
                'twilio-sender' => 'required',
            ],
        ];
    }
}