<?php

/*Copyright (c) 2019 GitStoph <https://github.com/GitStoph>
 * Original Alerta transport author: GitStoph
 * Updated/customised for LibreNMS -> Alerta integration: 2026-04-01
 * Updated by: Pizu (DM)
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version. Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

/**
 * Alerta API Transport
 *
 * Custom LibreNMS -> Alerta transport with generic per-fault handling.
 *
 * Summary:
 * - Sends one Alerta event per LibreNMS fault row
 * - Keeps resource mapped to configured origin
 * - Keeps group mapped to the original LibreNMS alert name by default
 * - Optionally allows group to be mapped to top-level sysContact
 * - Re-renders the LibreNMS alert template per fault for text/description
 * - Uses a stable per-fault event signature for repeats and recovery matching
 *
 * Notes:
 * - The MD5 hash is used only as a compact fault fingerprint for uniqueness
 *   and recovery matching; it is not used for security
 * - Changing "Group by sysContact" only affects newly-created Alerta alerts.
 *   Existing or re-opened Alerta alerts may keep their previous group because
 *   Alerta de-duplicates/re-opens alerts using the alert identity, not the group field
 * - Optional debug fields such as 'fault' and 'rawData' can be enabled
 *   through the "Alerta Debug" transport option for troubleshooting
 */

namespace LibreNMS\Alert\Transport;

use Illuminate\Support\Facades\Cache;
use LibreNMS\Alert\Template as LibreNmsTemplate;
use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Alerta extends Transport
{
    protected string $name = 'Alerta';

    private const CACHE_DAYS = 30;

    private const FAULT_SIGNATURE_IGNORE = [
        'string',
        'sysDescr',
        'last_polled',
        'last_poll_attempted',
        'last_polled_timetaken',
        'last_discovered',
        'last_discovered_timetaken',
        'last_ping',
        'last_ping_timetaken',
        'poll_time',
        'poll_prev',
        'poll_period',
        'uptime',
        'agent_uptime',
    ];

    /**
     * Generic IDs that describe the alert/device context, not the actual
     * monitored fault object. These must not be selected as per-fault
     * identity fields.
     */
    private const FAULT_SIGNATURE_SKIP_ID_KEYS = [
        'id',
        'uid',
        'device_id',
        'rule_id',
        'alert_id',
        'location_id',
        'poller_group',
        'vrf_id',
    ];

    /**
     * Stable scalar fields used only when a LibreNMS fault row does not expose
     * a useful object *_id field. This keeps the transport generic while still
     * avoiding volatile counters/timestamps/status history in signatures.
     */
    private const FAULT_SIGNATURE_STABLE_FALLBACK_KEYS = [
        'ifIndex',
        'ifName',
        'ifDescr',
        'sensor_index',
        'sensor_descr',
        'service_ip',
        'service_type',
        'service_desc',
        'name',
        'descr',
    ];

    /**
     * Deliver a LibreNMS alert to Alerta.
     *
     * Clears any missing fault signatures first, then sends the current active
     * fault set and refreshes the cache used for recovery matching.
     */
    public function deliverAlert(array $alert_data): bool
    {
        $resource = $this->cleanString($this->config['origin'] ?? 'LibreNMS') ?: 'LibreNMS';
        $environment = $this->cleanString($this->config['environment'] ?? '');
        // Preserve the original LibreNMS Alerta behaviour by default:
        // group = alert/rule name. sysContact grouping is optional because
        // not every device has sysContact populated.
        $defaultGroup = $this->cleanString($alert_data['name'] ?? 'LibreNMS') ?: 'LibreNMS';
        $group = $defaultGroup;

        if (! empty($this->config['group-by-syscontact'])) {
            $group = $this->cleanString($alert_data['sysContact'] ?? '') ?: $defaultGroup;
        }
        $service = [$this->cleanString($alert_data['type'] ?? 'LibreNMS') ?: 'LibreNMS'];

        $cacheKey = $this->buildCacheKey($alert_data);
        $previousIndexedFaults = Cache::get($cacheKey, []);

        $state = $alert_data['state'] ?? null;
        $isRecovered = ($state == AlertState::RECOVERED);

        $currentFaults = $this->extractFaults($alert_data, ! $isRecovered);
        $currentIndexedFaults = $this->indexFaultsBySignature($alert_data, $currentFaults);

        $faultsToClear = [];
        foreach ($previousIndexedFaults as $signature => $fault) {
            if (! array_key_exists($signature, $currentIndexedFaults)) {
                $faultsToClear[$signature] = $fault;
            }
        }

        if ($isRecovered) {
            foreach ($currentIndexedFaults as $signature => $fault) {
                $faultsToClear[$signature] = $fault;
            }

            if (empty($faultsToClear) && empty($previousIndexedFaults)) {
                $fallbackSignature = $this->buildFaultSignature($alert_data, []);
                $faultsToClear[$fallbackSignature] = [];
            }
        }

        foreach ($faultsToClear as $signature => $fault) {
            $this->sendToAlerta(
                $alert_data,
                $fault,
                $signature,
                $resource,
                $environment,
                $group,
                $service,
                $this->config['recoverstate'] ?? 'normal'
            );
        }

        if ($isRecovered) {
            Cache::forget($cacheKey);

            return true;
        }

        if (empty($currentIndexedFaults)) {
            $currentIndexedFaults = [
                $this->buildFaultSignature($alert_data, []) => [],
            ];
        }

        foreach ($currentIndexedFaults as $signature => $fault) {
            $this->sendToAlerta(
                $alert_data,
                $fault,
                $signature,
                $resource,
                $environment,
                $group,
                $service,
                $this->config['alertstate'] ?? 'major'
            );
        }

        Cache::put($cacheKey, $currentIndexedFaults, now()->addDays(self::CACHE_DAYS));

        return true;
    }

    /**
     * Send one Alerta event.
     */
    private function sendToAlerta(
        array $alertData,
        array $fault,
        string $faultSignature,
        string $resource,
        string $environment,
        string $group,
        array $service,
        string $severity
    ): void {
        $text = $this->buildAlertText($alertData, $fault);
        $event = $this->buildEventName($alertData, $faultSignature);
        $alertaDebug = ! empty($this->config['alerta-debug']);

        $payload = [
            'resource' => $resource,
            'event' => $event,
            'environment' => $environment,
            'severity' => $severity,
            'service' => $service,
            'group' => $group,
            'value' => (string) ($alertData['state'] ?? ''),
            'text' => $text,
            'attributes' => array_merge([
                'alert_id' => $alertData['alert_id'] ?? null,
                'alert_uid' => $alertData['uid'] ?? ($alertData['id'] ?? null),
                'deviceName' => $alertData['sysName'] ?? ($alertData['hostname'] ?? null),
                'description' => $text,
                'sysName' => $alertData['sysName'] ?? null,
                'hostname' => $alertData['hostname'] ?? null,
                'title' => $alertData['title'] ?? null,
                'display' => $alertData['display'] ?? null,
                'rule' => $alertData['name'] ?? null,
                'rule_id' => $alertData['rule_id'] ?? null,
                'device_id' => $alertData['device_id'] ?? null,
                'sysDescr' => $alertData['sysDescr'] ?? null,
                'os' => $alertData['os'] ?? null,
                'ip' => $alertData['ip'] ?? null,
                'uptime' => $alertData['uptime_long'] ?? ($alertData['uptime'] ?? null),
                'state' => $alertData['state'] ?? null,
                'timestamp' => $alertData['timestamp'] ?? null,
                'proc' => $alertData['proc'] ?? null,
                'fault_signature' => $faultSignature,
            ], $this->buildFaultSignatureAttributes($fault)),
            'origin' => $resource,
        ];

        if ($alertaDebug) {
            $payload['attributes']['fault'] = $fault;
            $payload['rawData'] = json_encode(
                $alertData,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }

        $res = Http::client()
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Key ' . $this->config['apikey'],
            ])
            ->post($this->config['alerta-url'], $payload);

        if ($res->successful()) {
            return;
        }

        throw new AlertTransportDeliveryException(
            $alertData,
            $res->status(),
            $res->body(),
            $payload['text'],
            $payload
        );
    }

    /**
     * Normalize LibreNMS faults to a list of fault rows.
     */
    private function extractFaults(array $alertData, bool $includeEmptyFallback = true): array
    {
        $faults = $alertData['faults'] ?? null;

        if (! is_array($faults) || empty($faults)) {
            return $includeEmptyFallback ? [[]] : [];
        }

        $rows = [];

        $first = reset($faults);
        if (is_array($first)) {
            foreach ($faults as $fault) {
                if (is_array($fault)) {
                    $rows[] = $fault;
                }
            }
        } else {
            $rows[] = $faults;
        }

        if (empty($rows)) {
            return $includeEmptyFallback ? [[]] : [];
        }

        return $rows;
    }

    /**
     * Build a lookup table of fault signature => fault payload.
     */
    private function indexFaultsBySignature(array $alertData, array $faults): array
    {
        $indexed = [];

        foreach ($faults as $fault) {
            $signature = $this->buildFaultSignature($alertData, $fault);
            $indexed[$signature] = is_array($fault) ? $fault : [];
        }

        return $indexed;
    }

    /**
     * Build the cache key used for active fault tracking.
     */
    private function buildCacheKey(array $alertData): string
    {
        $parts = [
            'alerta_faults',
            $this->cleanString($this->config['origin'] ?? 'LibreNMS') ?: 'LibreNMS',
            $alertData['device_id'] ?? 'unknown-device',
            $alertData['rule_id'] ?? 'unknown-rule',
        ];

        return implode(':', array_map(fn ($v) => (string) $v, $parts));
    }

    /**
     * Build the Alerta event name.
     */
    private function buildEventName(array $alertData, string $faultSignature): string
    {
        $ruleName = $this->cleanString(
            $alertData['name']
            ?? $alertData['rule']
            ?? $alertData['type']
            ?? 'LibreNMSAlert'
        ) ?: 'LibreNMSAlert';

        $deviceScope = $this->cleanString(
            (string) (
                $alertData['device_id']
                ?? $alertData['sysName']
                ?? $alertData['hostname']
                ?? $alertData['ip']
                ?? 'unknown-device'
            )
        ) ?: 'unknown-device';

        return sprintf('%s|dev=%s|sig=%s', $ruleName, $deviceScope, $faultSignature);
    }

    /**
     * Build a compact signature for one fault.
     *
     * The normal path uses only a stable fault object identity instead of the
     * full fault row. This prevents changing counters, poll timestamps, status
     * history, and current values from creating a new Alerta event signature for
     * the same still-active LibreNMS fault.
     */
    private function buildFaultSignature(array $alertData, array $fault): string
    {
        $signatureData = $this->buildFaultSignatureData($alertData, $fault);

        return md5(json_encode($signatureData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Build deterministic data used to generate the per-fault signature.
     */
    private function buildFaultSignatureData(array $alertData, array $fault): array
    {
        $identity = ! empty($fault) ? $this->findStableFaultIdentity($fault) : null;

        if ($identity !== null) {
            return [
                'scope' => 'fault_object',
                'device_id' => (string) ($alertData['device_id'] ?? ($fault['device_id'] ?? '')),
                'rule_id' => (string) ($alertData['rule_id'] ?? ($fault['rule_id'] ?? '')),
                'fault_key' => $identity['key'],
                'fault_value' => $identity['value'],
            ];
        }

        if (! empty($fault)) {
            $normalizedFault = $this->normalizeForSignature($fault, self::FAULT_SIGNATURE_IGNORE);

            if (empty($normalizedFault) && ! empty($fault['string'])) {
                $normalizedFault = ['string' => $this->cleanString((string) $fault['string'])];
            }

            if (! empty($normalizedFault)) {
                return [
                    'scope' => 'normalized_fault_fallback',
                    'device_id' => (string) ($alertData['device_id'] ?? ($fault['device_id'] ?? '')),
                    'rule_id' => (string) ($alertData['rule_id'] ?? ($fault['rule_id'] ?? '')),
                    'fault' => $normalizedFault,
                ];
            }
        }

        return [
            'scope' => 'alert_fallback',
            'device_id' => (string) ($alertData['device_id'] ?? ''),
            'rule_id' => (string) ($alertData['rule_id'] ?? ''),
            'name' => $this->cleanString((string) ($alertData['name'] ?? '')),
            'type' => $this->cleanString((string) ($alertData['type'] ?? '')),
        ];
    }

    /**
     * Add non-sensitive signature metadata to Alerta attributes for visibility.
     */
    private function buildFaultSignatureAttributes(array $fault): array
    {
        $identity = ! empty($fault) ? $this->findStableFaultIdentity($fault) : null;

        if ($identity !== null) {
            return [
                'fault_signature_source' => $identity['source'],
                'fault_identity_key' => $identity['key'],
                'fault_identity_value' => $identity['value'],
            ];
        }

        return [
            'fault_signature_source' => ! empty($fault) ? 'normalized_fault_fallback' : 'alert_fallback',
            'fault_identity_key' => null,
            'fault_identity_value' => null,
        ];
    }

    /**
     * Find the best stable monitored-object identity in a LibreNMS fault row.
     *
     * This is intentionally generic: the transport does not check alert type.
     * It first prefers useful object *_id fields, then falls back to known stable
     * index/name fields if no object ID is present.
     */
    private function findStableFaultIdentity(array $fault): ?array
    {
        foreach ($fault as $key => $value) {
            $key = (string) $key;

            if (! $this->isUsableSignatureScalar($value)) {
                continue;
            }

            if (! preg_match('/_id$/i', $key)) {
                continue;
            }

            if (in_array($key, self::FAULT_SIGNATURE_SKIP_ID_KEYS, true)) {
                continue;
            }

            return [
                'key' => $key,
                'value' => $this->cleanString((string) $value),
                'source' => 'object_id',
            ];
        }

        foreach (self::FAULT_SIGNATURE_STABLE_FALLBACK_KEYS as $key) {
            if (! array_key_exists($key, $fault)) {
                continue;
            }

            $value = $fault[$key];
            if (! $this->isUsableSignatureScalar($value)) {
                continue;
            }

            return [
                'key' => $key,
                'value' => $this->cleanString((string) $value),
                'source' => 'stable_field',
            ];
        }

        return null;
    }

    /**
     * Determine if a fault value can be safely used in an identity signature.
     */
    private function isUsableSignatureScalar($value): bool
    {
        if (! is_scalar($value)) {
            return false;
        }

        return trim((string) $value) !== '';
    }

    /**
     * Normalize scalar fields for stable signature generation.
     */
    private function normalizeForSignature(array $data, array $ignoreKeys = []): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            if (in_array((string) $key, $ignoreKeys, true)) {
                continue;
            }

            if (is_array($value) || is_object($value)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $normalized[(string) $key] = $this->cleanString((string) $value);
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * Build the Alerta text / description for one fault.
     */
    private function buildAlertText(array $alertData, array $fault): string
    {
        $rendered = $this->renderTemplateBodyForFault($alertData, $fault);
        if ($rendered !== '') {
            return $rendered;
        }

        if (! empty($fault['string'])) {
            return $this->cleanString((string) $fault['string']);
        }

        if (! empty($fault)) {
            $parts = [];

            foreach ($fault as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    continue;
                }

                if ($value === null || $value === '') {
                    continue;
                }

                $parts[] = $key . ': ' . $this->cleanString((string) $value);
            }

            if (! empty($parts)) {
                return implode(' | ', $parts);
            }
        }

        $parts = [];
        foreach (['title', 'msg', 'name'] as $field) {
            if (! empty($alertData[$field])) {
                $parts[] = $this->cleanString((string) $alertData[$field]);
            }
        }

        $parts = array_values(array_unique(array_filter($parts)));

        return ! empty($parts) ? implode(' | ', $parts) : 'LibreNMS alert';
    }

    /**
     * Re-render the LibreNMS alert template body for a single fault.
     */
    private function renderTemplateBodyForFault(array $alertData, array $fault): string
    {
        try {
            $templateEngine = new LibreNmsTemplate();
            $templateModel = $templateEngine->getTemplate($alertData);

            if (! $templateModel) {
                return '';
            }

            $renderAlert = $alertData;
            $renderAlert['faults'] = ! empty($fault) ? [$fault] : [];

            $body = $templateEngine->getBody([
                'alert' => $renderAlert,
                'template' => $templateModel,
            ]);

            return $this->cleanMultilineText((string) $body);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Normalize a single-line string.
     */
    private function cleanString(string $value): string
    {
        $value = strip_tags($value);
        $value = preg_replace('/\s+/', ' ', trim($value));

        return $value ?? '';
    }

    /**
     * Normalize a multi-line text block.
     */
    private function cleanMultilineText(string $value): string
    {
        $value = strip_tags($value);
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        $lines = explode("\n", $value);
        $cleaned = [];

        foreach ($lines as $line) {
            $line = preg_replace('/[ \t]+/', ' ', trim($line));
            if ($line !== null && $line !== '') {
                $cleaned[] = $line;
            }
        }

        return trim(implode("\n", $cleaned));
    }

    /**
     * LibreNMS transport configuration form.
     */
    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'API Endpoint',
                    'name' => 'alerta-url',
                    'descr' => 'Alerta API URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Api Key',
                    'name' => 'apikey',
                    'descr' => 'Your Alerta API key with minimally write:alert permissions.',
                    'type' => 'password',
                ],
                [
                    'title' => 'Origin',
                    'name' => 'origin',
                    'descr' => 'Name of this monitoring source, e.g. LibreNMS.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Environment',
                    'name' => 'environment',
                    'descr' => 'An allowed environment from your Alerta configuration.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Alert State',
                    'name' => 'alertstate',
                    'descr' => 'Severity to send to Alerta when the alert is active.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Recover State',
                    'name' => 'recoverstate',
                    'descr' => 'Severity to send to Alerta when the alert recovers.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Group by sysContact',
                    'name' => 'group-by-syscontact',
                    'descr' => 'Use the device sysContact as the Alerta group. Disabled keeps the original LibreNMS behaviour: group is the alert rule name. Note: changing this option affects newly-created Alerta alerts; existing or re-opened alerts may keep their previous group due to Alerta de-duplication.',
                    'type' => 'checkbox',
                    'default' => false,
                ],
                [
                    'title' => 'Alerta Debug',
                    'name' => 'alerta-debug',
                    'descr' => 'Enable optional debug fields in the payload sent to Alerta. This debug is on the Alerta side and should normally stay disabled.',
                    'type' => 'checkbox',
                    'default' => false,
                ],
            ],
            'validation' => [
                'alerta-url' => 'required|url',
                'apikey' => 'required|string',
            ],
        ];
    }
}
