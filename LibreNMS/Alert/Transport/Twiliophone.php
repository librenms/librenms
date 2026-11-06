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
 *
 * @author Jack Nolen <jnolen@psl.nmsu.edu>
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Spatie\Permission\Models\Role;
use LibreNMS\Enum\AlertState;

class Twiliophone extends Transport
{
    protected string $name = 'Twilio - Phones';

    private const E164 = '/^\+[1-9]\d{7,14}$/';

    /**
    * @param  array<string, mixed>  $alert_data
    */
    public function deliverAlert(array $alert_data): bool
    {
        if (($alert_data['state'] ?? null) == AlertState::RECOVERED && ! $this->isEnabled('twilio-call-recovery')){
            return true;
        }

        $numbers = $this->collectNumbers();

        if (empty($numbers)) {
            return true;
        }

        $from = self::normalize((string) ($this->config['twilio-sender'] ?? ''));
        if ($from === null){
            throw new AlertTransportDeliveryException($alert_data, 0, 'Invalid Twilio Phone Number: ' . ($this->config['twilio-sender'] ?? ''), '', []);
        }

        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $this->config['twilio-sid'] . '/Calls.json';

        $vars = [];
        foreach(['sysName', 'sysDescr', 'sysContact', 'os', 'ip', 'hostname', 'display', 'title', 'severity'] as $key) {
            $vars["%$key%"] = htmlspecialchars((string) ($alert_data[$key] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        }

        $errors = [];

        foreach ($numbers as $to) {
            Log::warning($to);

            $twiml = strtr((string) $this->config['twilio-ml'], $vars);
            $data = [
                'From' => $this->config['twilio-sender'],
                'To' => $to,
                'Twiml' => $twiml,
            ];

            $res = Http::client()->asForm()
                ->withBasicAuth(
                        $this->config['twilio-api-sid'],
                        $this->config['twilio-api-secret']
                )
            ->post($url, $data);

            if (! $res->successful()) {
                $errors[] = "$to: HTTP {$res->status()} {$res->body()}";
            }
        }

        if (count($errors) === count($numbers)) {
            throw new AlertTransportDeliveryException($alert_data, 0, implode("\n", $errors), $twiml, $numbers);
        }

        foreach ($errors as $error) {
            Log::warning("Twilio phone: call failed $error");
        }

        return true;
    }

    /**
    * @return list<string>
    */
    private function collectNumbers(): array
    {
        $numbers = self::splitNumbers((string) ($this->config['twilio-to'] ?? ''));

        $callUser = $this->config['user'] ?? 0;
        if ($callUser && ($phone = User::whereKey($callUser)->where('enabled', 1)->value('phone'))){
            $numbers[] = $phone;
        }

        $roleId = (int) ($this->config['role'] ?? 0);
        if ($roleId) {
            $rolePhones = User::role([$roleId])
                ->where('enabled', 1)
                ->whereNotNull('phone')
                ->orderBy('username')
                ->pluck('phone')
                ->all();
            array_push($numbers, ...$rolePhones);
        }

        $valid = [];
        foreach ($numbers as $raw){
            $number = self::normalize($raw);
            if ($number === null){
                Log::warning("Twilio phone: Skipping invalid number '$raw'");
                continue;
            }
            $valid[$number] = true;
        }

        return array_keys($valid);
    }

    private function isEnabled(string $key): bool
    {
        return filter_var($this->config[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    /**
    * @return list<string>
    */
    private static function splitNumbers(string $value): array
    {
        return array_values(array_filter(array_map(trim(...), preg_split('/[,;\r\n]+/', $value))));
    }

    private static function normalize(string $raw): ?string
    {
        $number = '+' . preg_replace('/\D/', '', $raw);

        if (! preg_match(self::E164, $number)){
            return null;
        }

        return $number;
    }
    
    /**
    * @return array<string, mixed>
    */
    public static function configTemplate(): array
    {
        $roles = [__('None') => 0];
        foreach (Role::orderBy('name')->get(['id', 'name']) as $role) {
            $roles[htmlentities(ucwords(str_replace(['-', '_'], ' ', $role->name)))] = $role->id;
        }

        $users = [__('None') => 0];
        foreach (User::orderBy('username')->get(['user_id', 'username', 'realname']) as $user) {
            $label = $user->realname ? "$user->realname ($user->username)" : $user->username;
            $users[htmlentities((string) $label)] = $user->user_id;
        }

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
                [
                    'title' => 'Call To - Optional*',
                    'name' => 'twilio-to',
                    'descr' => 'Person being called, sepearted by comma. E164 is required ex: +18884443333',
                    'type' => 'text',
                ],
                [
                    'title' => 'Call by Roles - Optional*',
                    'name' => 'role',
                    'descr' => 'Choose a user role group to call based on their phone field. WARNING! This can get expensive!',
                    'type' => 'select',
                    'options' => $roles,
                ],
                [
                    'title' => 'Call by Users - Optional*',
                    'name' => 'user',
                    'descr' => 'Choose a list of Users to call based on their phone field',
                    'type' => 'select',
                    'options' => $users,
                ],
                [
                    'title' => 'Call on recovery',
                    'name' => 'twilio-call-recovery',
                    'descr' => 'Place calls when an alert clears',
                    'type' => 'checkbox',
                    'default' => false,
                ],
            ],
            'validation' => [
                'twilio-sid' => 'required|string',
                'twilio-api-sid' => 'required|string',
                'twilio-api-secret' => 'required|string',
                'twilio-ml' => 'required|string',
                'twilio-to' => [
                    'nullable',
                    'string',
                    'required_without_all:user,role',
                    function ($attribute, $value, $fail): void {
                            foreach (self::splitNumbers((string) $value) as $number) {
                                if (self::normalize($number) === null) {
                                    $fail("Invalid number: $number. Include the country code, e.g. +15055553333");

                                    return;
                                }
                            }
                        },
                ],
                'twilio-sender' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail): void {
                        if (self::normalize((string) $value) === null){
                            $fail('Invalid number, Include the country code, e.g. +15055553333');
                        }
                    },
                ],
            ],
        ];
    }
}
