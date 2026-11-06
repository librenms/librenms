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
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class Twiliophone extends Transport
{
    protected string $name = 'Twilio - Phones';

    private const E164 = '/^\+[1-9]\d{7,14}$/';

    private const TIMEOUT = 15;

    /**
     * @param  array<string, mixed>  $alert_data
     */
    public function deliverAlert(array $alert_data): bool
    {
        if (($alert_data['state'] ?? null) == AlertState::RECOVERED && ! $this->isEnabled('twilio-call-recovery')) {
            return true;
        }

        $numbers = $this->collectNumbers();

        if (empty($numbers)) {
            throw new AlertTransportDeliveryException($alert_data, 0, 'Twilio phone: no valid numbers to call', '', []);
        }

        $from = self::normalize((string) ($this->config['twilio-sender'] ?? ''));
        if ($from === null) {
            throw new AlertTransportDeliveryException($alert_data, 0, 'Invalid Twilio Phone Number: ' . ($this->config['twilio-sender'] ?? ''), '', []);
        }

        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $this->config['twilio-sid'] . '/Calls.json';

        $vars = [];
        foreach (['sysName', 'sysDescr', 'sysContact', 'os', 'ip', 'hostname', 'display', 'title', 'severity'] as $key) {
            $vars["%$key%"] = htmlspecialchars((string) ($alert_data[$key] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        }

        $twiml = strtr((string) $this->config['twilio-ml'], $vars);
        $errors = [];

        foreach ($numbers as $to) {
            try {
                $res = Http::client()
                    ->timeout(self::TIMEOUT)
                    ->asForm()
                    ->withBasicAuth(
                        $this->config['twilio-api-sid'],
                        $this->config['twilio-api-secret']
                    )
                    ->post($url, [
                        'From' => $from,
                        'To' => $to,
                        'Twiml' => $twiml,
                    ]);
            } catch (ConnectionException $e) {
                $errors[] = "$to: connection failed: {$e->getMessage()}";
                continue;
            }

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
        if ($callUser && ($phone = User::whereKey($callUser)->where('enabled', 1)->value('phone'))) {
            $numbers[] = $phone;
        }

        $roleId = (int) ($this->config['role'] ?? 0);
        if ($roleId) {
            try {
                $rolePhones = User::role([$roleId])
                    ->where('enabled', 1)
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->orderBy('username')
                    ->pluck('phone')
                    ->all();
            } catch (RoleDoesNotExist) {
                Log::warning("Twilio phone: role $roleId no longer exists, skipping role recipients");
                $rolePhones = [];
            }
            array_push($numbers, ...$rolePhones);
        }

        $valid = [];
        foreach ($numbers as $raw) {
            $number = self::normalize($raw);
            if ($number === null) {
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

    /**
     * Accept only unambiguous E.164 numbers. Spaces, dots, dashes and
     * parentheses are allowed as formatting; anything else (a missing "+",
     * extensions, letters) makes the number invalid instead of being
     * stripped into a different number.
     */
    private static function normalize(string $raw): ?string
    {
        $number = preg_replace('/[\s().-]/', '', trim($raw));

        if ($number === null || ! preg_match(self::E164, $number)) {
            return null;
        }

        return $number;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function validateRecipients(mixed $value, array $data, Closure $fail): void
    {
        $numbers = self::splitNumbers((string) $value);

        foreach ($numbers as $number) {
            if (self::normalize($number) === null) {
                $fail("Invalid number: $number. Use E.164 format with the country code and no extension, e.g. +15055553333");

                return;
            }
        }

        if (empty($numbers) && ! (int) ($data['user'] ?? 0) && ! (int) ($data['role'] ?? 0)) {
            $fail('Enter at least one number to call, or choose a user or role.');
        }
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
                    'descr' => 'TwiML played when the call is answered. Supported variables: %hostname%, %sysName%, %sysDescr%, %sysContact%, %os%, %ip%, %display%, %title%, %severity%.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Call To - Optional*',
                    'name' => 'twilio-to',
                    'descr' => 'Numbers to call, separated by commas. E.164 format with the country code is required, e.g. +18884443333. Extensions are not supported.',
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
                    new class(self::validateRecipients(...)) implements DataAwareRule, ValidationRule
                    {
                        // Run even when twilio-to is empty, so "no recipients at all" is caught
                        public bool $implicit = true;

                        /** @var array<string, mixed> */
                        private array $data = [];

                        public function __construct(private readonly Closure $check)
                        {
                        }

                        /**
                         * @param  array<string, mixed>  $data
                         */
                        public function setData(array $data): static
                        {
                            $this->data = $data;

                            return $this;
                        }

                        public function validate(string $attribute, mixed $value, Closure $fail): void
                        {
                            ($this->check)($value, $this->data, $fail);
                        }
                    },
                ],
                'twilio-sender' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail): void {
                        if (self::normalize((string) $value) === null) {
                            $fail('Invalid number, Include the country code, e.g. +15055553333');
                        }
                    },
                ],
            ],
        ];
    }
}