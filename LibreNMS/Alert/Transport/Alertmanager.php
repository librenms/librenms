<?php

/* Copyright (C) 2019 LibreNMS
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
 * Alertmanager Transport
 *
 * @copyright 2019 LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use App\View\SimpleTemplate;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use LibreNMS\Alert\KeyValueOptions;
use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;
use LibreNMS\Util\Url;

class Alertmanager extends Transport
{
    protected string $name = 'Alert Manager';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['alertmanager-url'];
        $username = $this->config['alertmanager-username'] ?? '';
        $password = $this->config['alertmanager-password'] ?? '';

        $now = time();
        $recovered = $alert_data['state'] == AlertState::RECOVERED;
        $alertmanager_msg = strip_tags((string) $alert_data['msg']);
        $data = [[
            'startsAt' => date('c', $recovered ? $now - 2 : $now),
            'generatorURL' => Url::deviceUrl($alert_data['device_id']),
            'annotations' => [
                'summary' => $alert_data['name'],
                'title' => $alert_data['title'],
                'description' => $alertmanager_msg,
            ],
            'labels' => [
                'alertname' => $alert_data['name'],
                'severity' => $alert_data['severity'],
                'instance' => $alert_data['hostname'],
            ],
        ]];

        $alertmanager_opts = $this->parseUserOptions($this->config['alertmanager-options'] ?? '');
        foreach ($alertmanager_opts as $label => $value) {
            if (str_starts_with((string) $label, 'stc_')) {
                // Static label: strip the stc_ prefix and use the value as-is
                $cleanLabel = substr((string) $label, 4);
                $data[0]['labels'][$cleanLabel] = strip_tags((string) $value);
            } else {
                // Dynamic label: try to resolve value from alert data, faults, or fall back to literal
                $resolved = $alert_data[$value] ?? current(array_filter(
                    array_column($alert_data['faults'] ?? [], $value),
                    fn ($v) => ! empty($v)
                )) ?: $value;

                $data[0]['labels'][$label] = strip_tags((string) $resolved);

                if (str_starts_with((string) $label, 'dyn_') && $data[0]['labels'][$label] == $value) {
                    unset($data[0]['labels'][$label]);
                }
            }
        }

        try {
            foreach (['labels', 'annotations'] as $section) {
                $config = $this->config['alertmanager-' . $section] ?? null;
                if ($config !== null && ! is_array($config) && ! is_string($config)) {
                    throw new \InvalidArgumentException('Invalid ' . $section . ' configuration; use name/value entries.');
                }
                foreach (KeyValueOptions::fromConfig($config) as $name => $value) {
                    if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/D', (string) $name)) {
                        throw new \InvalidArgumentException('Invalid ' . $section . ' name; use letters, numbers and underscores.');
                    }
                    $value = trim(strip_tags(SimpleTemplate::parse($value, $alert_data)));
                    if ($value !== '') {
                        $data[0][$section][$name] = $value;
                    }
                }
            }

            if ($recovered) {
                $data[0]['endsAt'] = date('c', $now - 1);
            } else {
                $expiry = $this->config['alertmanager-ends-at'] ?? '';
                if (! is_string($expiry)) {
                    throw new \InvalidArgumentException('Firing expiry must be a duration or template string.');
                }
                $expiry = trim(SimpleTemplate::parse($expiry, $alert_data));
                if ($expiry !== '') {
                    $data[0]['endsAt'] = date('c', $now + self::parseExpiry($expiry));
                }
            }
        } catch (\InvalidArgumentException $e) {
            throw new AlertTransportDeliveryException($alert_data, 0, $e->getMessage());
        } catch (\Throwable $e) {
            throw new AlertTransportDeliveryException($alert_data, 0, 'Alertmanager template evaluation failed (' . class_basename($e) . '). Check template filters and their arguments.');
        }

        $urls = array_values(array_filter(array_map(trim(...), explode(',', (string) $url))));
        if ($urls === []) {
            throw new AlertTransportDeliveryException($alert_data, 0, 'At least one Alertmanager URL is required.');
        }
        foreach ($urls as &$endpoint) {
            $parts = parse_url($endpoint);
            if (! $parts || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])) {
                throw new AlertTransportDeliveryException($alert_data, 0, 'Alertmanager URLs must be valid HTTP or HTTPS URLs.');
            }
            $uri = new Uri($endpoint);
            $endpoint = (string) $uri->withPath(rtrim($uri->getPath(), '/') . '/api/v2/alerts')->withFragment('');
        }
        unset($endpoint);

        $options = Http::client()->timeout(30)->getOptions();
        $cert = $this->config['alertmanager-client-cert'] ?? '';
        $key = $this->config['alertmanager-client-key'] ?? '';
        if ($cert || $key) {
            if (! is_string($cert) || ! is_string($key) || ! is_file($cert) || ! is_readable($cert) || ! is_file($key) || ! is_readable($key)) {
                throw new AlertTransportDeliveryException($alert_data, 0, 'Client certificate and private key must both be readable files.');
            }
            $options['cert'] = $cert;
            $options['ssl_key'] = $key;
        }

        try {
            $responses = Http::client()->pool(function (Pool $pool) use ($urls, $options, $username, $password, $data): void {
                foreach ($urls as $index => $endpoint) {
                    $request = $pool->as((string) $index)->withOptions($options);
                    if ($username !== '' && $password !== '') {
                        $request->withBasicAuth($username, $password);
                    }
                    $request->post($endpoint, $data);
                }
            });
        } catch (\Throwable $e) {
            throw new AlertTransportDeliveryException($alert_data, 0, 'Alertmanager request setup failed (' . class_basename($e) . ').');
        }

        $failures = [];
        $failed = 0;
        foreach ($urls as $index => $endpoint) {
            $response = $responses[$index] ?? null;
            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                continue;
            }
            $failed++;
            if (count($failures) < 5) {
                $reason = match (true) {
                    $response instanceof ConnectionException => 'Connection failed',
                    $response instanceof \Throwable => 'Request failed (' . class_basename($response) . ')',
                    $response instanceof \Illuminate\Http\Client\Response => 'HTTP ' . $response->status(),
                    default => 'No response',
                };
                $failures[] = substr(self::endpointName($endpoint) . ': ' . $reason, 0, 200);
            }
        }
        if ($failed > 0) {
            $summary = 'Alertmanager delivery failed for ' . $failed . ' of ' . count($urls) . ' endpoints (' . (count($urls) - $failed) . ' succeeded): ' . implode('; ', $failures);
            if ($failed > count($failures)) {
                $summary .= '; ' . ($failed - count($failures)) . ' more failures omitted';
            }
            throw new AlertTransportDeliveryException($alert_data, 0, substr($summary, 0, 1600));
        }

        return true;
    }

    private static function endpointName(string $endpoint): string
    {
        $uri = new Uri($endpoint);

        return (string) $uri->withUserInfo('')->withQuery('')->withFragment('');
    }

    private static function parseExpiry(string $duration): int
    {
        if (! preg_match('/^([1-9][0-9]*)([smhd])$/D', $duration, $matches)) {
            throw new \InvalidArgumentException('Firing expiry must be a positive whole duration such as 20m (s, m, h or d).');
        }
        $count = filter_var($matches[1], FILTER_VALIDATE_INT);
        $multiplier = ['s' => 1, 'm' => 60, 'h' => 3600, 'd' => 86400][$matches[2]];
        if ($count === false || $count > intdiv(PHP_INT_MAX - time(), $multiplier)) {
            throw new \InvalidArgumentException('Firing expiry is too large.');
        }

        return $count * $multiplier;
    }

    public static function configTemplate(): array
    {
        $validateOptions = static function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_array($value) && ! is_string($value)) {
                $fail('Entries must be a name/value map.');

                return;
            }
            try {
                foreach (KeyValueOptions::fromConfig($value) as $name => $entry) {
                    if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/D', (string) $name)) {
                        $fail('Entry names must contain only letters, numbers and underscores, and cannot start with a number.');
                    }
                }
            } catch (\InvalidArgumentException $e) {
                $fail($e->getMessage());
            }
        };

        return [
            'config' => [
                [
                    'title' => 'Alertmanager URL(s)',
                    'name' => 'alertmanager-url',
                    'descr' => 'Alertmanager Webhook URL(s). Can contain comma-separated URLs',
                    'type' => 'text',
                ],
                [
                    'title' => 'Alertmanager Username',
                    'name' => 'alertmanager-username',
                    'descr' => 'Alertmanager Basic Username to authenticate to Alertmanager',
                    'type' => 'text',
                ],
                [
                    'title' => 'Alertmanager Password',
                    'name' => 'alertmanager-password',
                    'descr' => 'Alertmanager Basic Password to authenticate to Alertmanager',
                    'type' => 'password',
                ],
                [
                    'title' => 'Client certificate',
                    'name' => 'alertmanager-client-cert',
                    'descr' => 'Path to a readable PEM client certificate (requires a separate private key).',
                    'type' => 'text',
                ],
                [
                    'title' => 'Client private key',
                    'name' => 'alertmanager-client-key',
                    'descr' => 'Path to a readable, unencrypted PEM private key.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Firing expiry',
                    'name' => 'alertmanager-ends-at',
                    'descr' => 'Optional duration or template, for example 20m. Blank uses Alertmanager defaults. Active alerts must be resent before expiry.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Legacy Alertmanager Options',
                    'name' => 'alertmanager-options',
                    'descr' => 'Alertmanager Options. You can add any fixed string value or dynamic value from alert details (label name must start with extra_ and value must exists in alert details).',
                    'type' => 'textarea',
                    'legacy' => true,
                ],
                [
                    'title' => 'Labels',
                    'name' => 'alertmanager-labels',
                    'descr' => 'Optional labels. Values may contain SimpleTemplate expressions. Labels must remain stable between firing and recovery.',
                    'type' => 'keyvalue',
                ],
                [
                    'title' => 'Annotations',
                    'name' => 'alertmanager-annotations',
                    'descr' => 'Optional metadata such as runbook or dashboard links. Values may contain SimpleTemplate expressions.',
                    'type' => 'keyvalue',
                ],
            ],
            'validation' => [
                'alertmanager-url' => 'required|string',
                'alertmanager-client-cert' => 'nullable|string|required_with:alertmanager-client-key',
                'alertmanager-client-key' => 'nullable|string|required_with:alertmanager-client-cert',
                'alertmanager-ends-at' => ['nullable', 'string', static function (string $attribute, string $value, \Closure $fail): void {
                    $remainder = preg_replace('/{{ \$?[a-zA-Z0-9\-_.:]+(?:\|[^}]+)? }}/', '', $value, -1, $count);
                    if ($count > 0 && ! str_contains($remainder, '{') && ! str_contains($remainder, '}')) {
                        return; // Expanded durations are validated at delivery time.
                    }
                    try {
                        self::parseExpiry(trim($value));
                    } catch (\InvalidArgumentException $e) {
                        $fail($e->getMessage());
                    }
                }],
                'alertmanager-labels' => ['nullable', $validateOptions],
                'alertmanager-annotations' => ['nullable', $validateOptions],
            ],
        ];
    }
}
