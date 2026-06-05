<?php

namespace App\Actions\Oxidized;

use App\Facades\LibrenmsConfig;
use App\Models\Device;

class BuildGitHistoryViewData
{
    public function __construct(
        private readonly BuildDeviceOutput $buildDeviceOutput,
        private readonly FindGitHistoryConfig $findGitHistoryConfig,
        private readonly ReadGitHistoryConfig $readGitHistoryConfig,
    ) {
    }

    /**
     * @param array<string, mixed> $input
     * @return array{
     *     text: string,
     *     config_versions: array<int, array<string, mixed>>,
     *     config_total: int,
     *     current_config: array{oid: string, date: string, version: int},
     *     previous_config?: array{oid: string, date: string, version: int},
     *     warning?: string,
     *     node_info: array<string, mixed>,
     *     author: string,
     *     message: string,
     *     source: array{repo: string, file: string, source: string}
     * }|null
     */
    public function execute(Device $device, array $input = []): ?array
    {
        if (! $this->eligible($device)) {
            return null;
        }

        $historyConfig = $this->findGitHistoryConfig->execute($device);
        if ($historyConfig === null) {
            return null;
        }

        $configVersions = $this->readGitHistoryConfig->versions($historyConfig['repo'], $historyConfig['file']);
        $configTotal = count($configVersions);

        if ($configTotal === 0) {
            return null;
        }

        foreach ($configVersions as $key => $version) {
            $configVersions[$key]['version'] = $configTotal - $key;
        }

        $versionsByOid = [];
        foreach ($configVersions as $version) {
            $versionsByOid[$version['oid']] = $version;
        }

        $currentConfig = $this->currentConfig($input, $configVersions, $versionsByOid);
        $previousConfig = $this->previousConfig($input, $currentConfig, $configVersions, $versionsByOid);

        $selectedVersion = $versionsByOid[$currentConfig['oid']] ?? [];
        $text = $this->configText($historyConfig, $currentConfig, $previousConfig);

        $oxidizedOutput = $this->buildDeviceOutput->execute($device);

        $data = [
            'text' => $text,
            'config_versions' => $configVersions,
            'config_total' => $configTotal,
            'current_config' => $currentConfig,
            'node_info' => [
                'name' => $oxidizedOutput['hostname'] ?? $device->hostname,
                'ip' => $oxidizedOutput['ip'] ?? $device->ip,
                'model' => $oxidizedOutput['os'] ?? $device->os,
                'last_sync' => $configVersions[0]['date'],
                'status' => 'historical',
                'source' => 'Local Oxidized Git history',
            ],
            'author' => $selectedVersion['author']['name'] ?? '',
            'message' => $selectedVersion['message'] ?? '',
            'source' => $historyConfig,
        ];

        if ($previousConfig !== null) {
            $data['previous_config'] = $previousConfig;
        } elseif (isset($input['diff'])) {
            $data['warning'] = 'No previous version, please select a different version.';
        }

        return $data;
    }

    public function eligible(Device $device): bool
    {
        if (LibrenmsConfig::get('oxidized.history.enabled') !== true) {
            return false;
        }

        $output = $this->buildDeviceOutput->execute($device);

        return (bool) $device->disabled
            || $device->getAttrib('override_Oxidized_disable') === 'true'
            || in_array($device->type, LibrenmsConfig::get('oxidized.ignore_types', []))
            || in_array($device->os, LibrenmsConfig::get('oxidized.ignore_os', []))
            || (
                isset($output['group'])
                && in_array($output['group'], LibrenmsConfig::get('oxidized.ignore_groups', []))
            );
    }

    /**
     * @param array<string, mixed> $input
     * @param array<int, array<string, mixed>> $configVersions
     * @param array<string, array<string, mixed>> $versionsByOid
     * @return array{oid: string, date: string, version: int}
     */
    private function currentConfig(array $input, array $configVersions, array $versionsByOid): array
    {
        if (isset($input['config'])) {
            $postedConfig = explode('|', (string) $input['config']);
            $postedOid = $postedConfig[0] ?? '';

            if (isset($versionsByOid[$postedOid])) {
                return [
                    'oid' => $versionsByOid[$postedOid]['oid'],
                    'date' => $versionsByOid[$postedOid]['date'],
                    'version' => (int) $versionsByOid[$postedOid]['version'],
                ];
            }
        }

        return [
            'oid' => $configVersions[0]['oid'],
            'date' => $configVersions[0]['date'],
            'version' => (int) $configVersions[0]['version'],
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @param array{oid: string, date: string, version: int} $currentConfig
     * @param array<int, array<string, mixed>> $configVersions
     * @param array<string, array<string, mixed>> $versionsByOid
     * @return array{oid: string, date: string, version: int}|null
     */
    private function previousConfig(array $input, array $currentConfig, array $configVersions, array $versionsByOid): ?array
    {
        if (! isset($input['diff'])) {
            return null;
        }

        $postedPrevious = explode('|', (string) ($input['prevconfig'] ?? ''));
        $postedPreviousOid = $postedPrevious[0] ?? '';

        if (isset($versionsByOid[$postedPreviousOid]) && $postedPreviousOid !== $currentConfig['oid']) {
            return [
                'oid' => $versionsByOid[$postedPreviousOid]['oid'],
                'date' => $versionsByOid[$postedPreviousOid]['date'],
                'version' => (int) $versionsByOid[$postedPreviousOid]['version'],
            ];
        }

        if ($currentConfig['version'] === 1) {
            return null;
        }

        foreach ($configVersions as $key => $version) {
            if ($version['oid'] === $currentConfig['oid'] && isset($configVersions[$key + 1])) {
                return [
                    'oid' => $configVersions[$key + 1]['oid'],
                    'date' => $configVersions[$key + 1]['date'],
                    'version' => (int) $configVersions[$key + 1]['version'],
                ];
            }
        }

        return null;
    }

    /**
     * @param array{repo: string, file: string, source: string} $historyConfig
     * @param array{oid: string, date: string, version: int} $currentConfig
     * @param array{oid: string, date: string, version: int}|null $previousConfig
     */
    private function configText(array $historyConfig, array $currentConfig, ?array $previousConfig): string
    {
        if ($previousConfig !== null) {
            $diff = $this->readGitHistoryConfig->diff(
                $historyConfig['repo'],
                $historyConfig['file'],
                $currentConfig['oid'],
                $previousConfig['oid']
            );

            return $diff ?: 'No Difference';
        }

        return $this->readGitHistoryConfig->config(
            $historyConfig['repo'],
            $historyConfig['file'],
            $currentConfig['oid']
        );
    }
}
