<?php

namespace App\Services\Alerting;

use App\Models\AlertOperation;
use Illuminate\Support\Str;
use LibreNMS\Enum\AlertRuleOperationPhase;

class AlertOperationSyncer
{
    /**
     * Replace an operation's segments and their transport mappings.
     *
     * Each row may contain: position, escalation_step_from, escalation_step_to,
     * start_in_seconds, step_duration_seconds, transports (array of transport ids;
     * group ids are prefixed with "g").
     *
     * @param  array<int, array<string, mixed>>  $rows
     *
     * @throws \InvalidArgumentException when a segment has no transports
     */
    public static function sync(AlertOperation $operation, array $rows): void
    {
        $operation->segments()->delete();

        foreach (array_values($rows) as $idx => $row) {
            $from = max(1, (int) ($row['escalation_step_from'] ?? 1));
            $toRaw = $row['escalation_step_to'] ?? null;
            $to = ($toRaw === '' || $toRaw === null) ? null : max($from, (int) $toRaw);

            $segment = $operation->segments()->create([
                'position' => (int) ($row['position'] ?? $idx),
                'operation_phase' => AlertRuleOperationPhase::PROBLEM,
                'escalation_step_from' => $from,
                'escalation_step_to' => $to,
                'start_in_seconds' => max(0, (int) ($row['start_in_seconds'] ?? 0)),
                'step_duration_seconds' => max(0, (int) ($row['step_duration_seconds'] ?? 0)),
            ]);

            $transportsRaw = $row['transports'] ?? [];
            if (! is_array($transportsRaw)) {
                $transportsRaw = [];
            }
            $transportsRaw = array_values(array_filter($transportsRaw, fn ($t) => $t !== null && $t !== ''));
            if ($transportsRaw === []) {
                throw new \InvalidArgumentException('Each segment must have at least one transport or transport group.');
            }

            $transportIds = [];
            $transportGroupIds = [];
            foreach ($transportsRaw as $transport) {
                if (Str::startsWith((string) $transport, 'g')) {
                    $transportGroupIds[] = (int) substr((string) $transport, 1);
                } else {
                    $transportIds[] = (int) $transport;
                }
            }

            $segment->transportSingles()->syncWithPivotValues($transportIds, ['target_type' => 'single']);
            $segment->transportGroups()->syncWithPivotValues($transportGroupIds, ['target_type' => 'group']);
        }
    }
}
