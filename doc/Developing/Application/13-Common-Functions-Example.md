---
title: 1.3 Complete Application Handler Example
description: A complete example application handler using LibreNMS\Agent\Application.
tags:
  - developing
  - applications
---

# 1.3 Complete Application Handler Example

This page shows a compact but complete example using `LibreNMS\Agent\Application`.

The example app, `diskmon`, reports disk health and basic I/O counters from a JSON payload.

## File layout

```text
LibreNMS/Agent/Unix/Diskmon/Common.php
resources/definitions/agent/unix.yaml
doc/Extensions/Applications/Diskmon.md
```

Add a migration and model only if the app needs queryable relational data outside sensors, RRDs, `$app->data`, and `application_metrics`.

## Registration

```yaml
diskmon:
  handler: LibreNMS\Agent\Unix\Diskmon\Common
```

## Expected payload

```json
{
  "version": 1,
  "error": 0,
  "errorString": "success",
  "data": {
    "disks": {
      "sda": { "path": "/dev/sda", "state": "healthy", "read_bps": 1000, "write_bps": 500 },
      "sdb": { "path": "/dev/sdb", "state": "degraded", "read_bps": 2000, "write_bps": 700 }
    }
  }
}
```

## Handler

??? example "Diskmon handler"
    ```php
    <?php

    namespace LibreNMS\Agent\Unix\Diskmon;

    use App\Models\StateTranslation;
    use LibreNMS\Agent\Application;
    use LibreNMS\Enum\Severity;
    use LibreNMS\RRD\RrdDefinition;

    class Common extends Application
    {
        public function discover(): void
        {
            $payload = $this->fetchPayload('diskmon', 1);
            if ($payload === null) {
                return;
            }

            app()->forgetInstance('sensor-discovery');

            $expectedOids = [];
            $discovery = ['disks' => []];

            foreach ($payload['data']['disks'] ?? [] as $diskId => $disk) {
                $healthIndex = "{$diskId}_health";
                $oid = "app:diskmon:{$healthIndex}";

                $this->discoverSensor(
                    class: 'state',
                    type: 'diskmon_drive_health',
                    index: $healthIndex,
                    oid: $oid,
                    descr: "Diskmon {$diskId} health",
                    current: $this->mapDriveHealth($disk['state'] ?? 'unknown'),
                    group: 'Diskmon',
                    navigation: 'tab=apps/app=diskmon/',
                )->withStateTranslations('diskmon_drive_health', [
                    StateTranslation::define('Healthy', 0, Severity::Ok),
                    StateTranslation::define('Degraded', 1, Severity::Warning),
                    StateTranslation::define('Failed', 2, Severity::Error),
                    StateTranslation::define('Missing', 3, Severity::Error),
                    StateTranslation::define('Unknown', 4, Severity::Unknown),
                ]);

                $expectedOids[] = $oid;

                $discovery['disks'][$diskId] = [
                    'health_index' => $healthIndex,
                    'path' => (string) ($disk['path'] ?? ''),
                ];
            }

            $this->syncSensors('diskmon_drive_health');

            $this->deleteStaleAgentSensors(
                oidPrefix: 'app:diskmon:',
                knownTypes: ['diskmon_drive_health'],
                expectedOids: $expectedOids,
            );

            $data = $this->getAppData();
            $data['discovery'] = $discovery;
            $this->saveAppData($data);
        }

        public function poll(): void
        {
            $payload = $this->fetchPayload('diskmon', 1);
            if ($payload === null) {
                return;
            }

            $values = [];
            $metrics = [
                'disks_total' => 0,
                'disks_degraded' => 0,
                'disks_failed' => 0,
            ];

            foreach ($payload['data']['disks'] ?? [] as $diskId => $disk) {
                $values["{$diskId}_health"] = $this->mapDriveHealth($disk['state'] ?? 'unknown');

                $metrics['disks_total']++;
                if (($disk['state'] ?? '') === 'degraded') {
                    $metrics['disks_degraded']++;
                }
                if (($disk['state'] ?? '') === 'failed') {
                    $metrics['disks_failed']++;
                }

                $this->putRrd('app', [
                    'name' => 'diskmon',
                    'app_id' => $this->app->app_id,
                    'rrd_name' => ['app', 'diskmon', $this->app->app_id, $diskId, 'io'],
                    'rrd_def' => RrdDefinition::make()
                        ->addDataset('read_bps', 'GAUGE', 0)
                        ->addDataset('write_bps', 'GAUGE', 0),
                ], [
                    'read_bps' => $disk['read_bps'] ?? null,
                    'write_bps' => $disk['write_bps'] ?? null,
                ]);
            }

            $this->updateSensorValues($values, 'app:diskmon:');
            update_application($this->app, 'ok', $metrics);
        }

        private function mapDriveHealth(string $state): int
        {
            return match (strtolower($state)) {
                'healthy', 'ok' => 0,
                'degraded', 'warning' => 1,
                'failed', 'error' => 2,
                'missing' => 3,
                default => 4,
            };
        }
    }
    ```

## Method cheat sheet

| Method | Use for |
| --- | --- |
| `fetchPayload($name, $minVersion)` | Read and validate the JSON app payload |
| `discoverSensor(...)` | Register a sensor during discovery |
| `withStateTranslations(...)` | Define state sensor labels and severities |
| `syncSensors(...)` | Flush discovered sensors to the database |
| `deleteStaleAgentSensors(...)` | Remove sensors no longer reported by the app |
| `updateSensorValues(...)` | Update current sensor values during polling |
| `putRrd(...)` | Write app-level RRD data |
| `getAppData()` | Read `$app->data` as an array |
| `saveAppData($data)` | Persist `$app->data` |

## Notes

- Keep discovery responsible for sensor structure.
- Keep polling responsible for current values.
- Use `null` for missing numeric RRD values.
- Use app-specific state sensor types.
- Use `$app->data['discovery']` for dynamic entity maps.
