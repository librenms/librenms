<?php

/**
 * AlertOperationRunAlertsTest.php
 *
 * End-to-end tests that drive RunAlerts::runAlerts() against real database rows
 * (device, rule, operation, segments, transports, alert state) and assert which
 * transports get notified and that per-segment timer state is persisted.
 *
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Feature\Alert;

use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\AlertTransport;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LibreNMS\Alert\RunAlerts;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;
use Mockery;

final class AlertOperationRunAlertsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // RunAlerts caches rule validity per device in a global; reset it between tests
        // so rolled-back/re-used device ids don't carry stale state.
        global $rulescache;
        $rulescache = [];
    }

    public function testActiveAlertIssuesDueSegmentTransportAndRespectsTimer(): void
    {
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ]);

        // Run twice in quick succession: the segment is due immediately (start_in = 0) on the
        // first run, but its 3600s repeat means the second run must not fire again.
        $captured = $this->runAlertsCapturing(2);

        $this->assertCount(1, $captured, 'Segment should fire once, then be held off by its timer');
        $this->assertSame(
            ['api'],
            array_map(static fn ($t) => $t['transport_type'], $captured[0]),
            'The due segment\'s transport should be notified'
        );

        // alerts.alerted is advanced to the active state
        $alerted = DB::table('alerts')
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->value('alerted');
        $this->assertEquals(AlertState::ACTIVE, $alerted);

        // Per-segment timer state is persisted in alert_log details
        $segmentId = $context['segments'][0]['segment']->id;
        $details = $this->latestAlertLogDetails($context['rule']->id);
        $this->assertArrayHasKey('op_seg', $details);
        $this->assertSame(1, (int) ($details['op_seg'][(string) $segmentId]['fires'] ?? 0));
    }

    public function testOnlyDueSegmentsAreNotified(): void
    {
        // Two independent segments: one due now, one not due for a long time.
        $this->makeActiveAlert([
            ['type' => 'mail', 'start' => 0, 'dur' => 3600],
            ['type' => 'slack', 'start' => 99999, 'dur' => 3600],
        ]);

        $captured = $this->runAlertsCapturing(1);

        $this->assertCount(1, $captured);
        $this->assertSame(
            ['mail'],
            array_map(static fn ($t) => $t['transport_type'], $captured[0]),
            'Only the segment whose timer is due should be notified'
        );
    }

    public function testMultipleDueSegmentsNotifyUnionOfTransports(): void
    {
        // Two independent segments both due at the same time => union of their transports.
        $this->makeActiveAlert([
            ['type' => 'mail', 'start' => 0, 'dur' => 3600],
            ['type' => 'slack', 'start' => 0, 'dur' => 3600],
        ]);

        $captured = $this->runAlertsCapturing(1);

        $this->assertCount(1, $captured);
        $this->assertEqualsCanonicalizing(
            ['mail', 'slack'],
            array_map(static fn ($t) => $t['transport_type'], $captured[0]),
            'Both due segments should be notified in a single cycle'
        );
    }

    public function testRuleWithoutOperationIsMuted(): void
    {
        $this->makeActiveAlert([], assignOperation: false);

        $captured = $this->runAlertsCapturing(1);

        $this->assertCount(0, $captured, 'A rule with no operation assigned should not notify');
    }

    public function testSuppressedOperationIsMuted(): void
    {
        $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ], suppressed: true);

        $captured = $this->runAlertsCapturing(1);

        $this->assertCount(0, $captured, 'A suppressed operation should not notify');
    }

    /**
     * Build a device + alert rule (optionally with an operation/segments/transports) and an
     * open, active alert ready for RunAlerts to process.
     *
     * @param  array<int, array{type?:string, from?:int, to?:int|null, start?:int, dur?:int}>  $segmentsConfig
     * @return array{device: Device, operation: AlertOperation|null, rule: AlertRule, segments: array<int, array{segment: \App\Models\AlertOperationSegment, transport: AlertTransport}>}
     */
    private function makeActiveAlert(array $segmentsConfig, bool $suppressed = false, bool $assignOperation = true): array
    {
        $device = Device::factory()->create();

        $operation = null;
        $segments = [];

        if ($assignOperation) {
            $operation = AlertOperation::create([
                'name' => 'op ' . uniqid(),
                'default_operation_step_duration_seconds' => 300,
                'notifications_suppressed' => $suppressed,
            ]);

            foreach ($segmentsConfig as $i => $cfg) {
                $segment = $operation->segments()->create([
                    'position' => $i,
                    'operation_phase' => 'problem',
                    'escalation_step_from' => $cfg['from'] ?? 1,
                    'escalation_step_to' => $cfg['to'] ?? null,
                    'start_in_seconds' => $cfg['start'] ?? 0,
                    'step_duration_seconds' => $cfg['dur'] ?? 300,
                ]);

                $transport = AlertTransport::factory()->create([
                    'transport_type' => $cfg['type'] ?? 'api',
                    'transport_name' => ($cfg['type'] ?? 'api') . ' ' . uniqid(),
                ]);

                $segment->transportSingles()->syncWithPivotValues([$transport->transport_id], ['target_type' => 'single']);

                $segments[] = ['segment' => $segment, 'transport' => $transport];
            }
        }

        $rule = AlertRule::create([
            'name' => 'rule ' . uniqid(),
            'severity' => 'critical',
            'extra' => [],
            'disabled' => 0,
            'query' => 'SELECT * FROM devices WHERE device_id = ?',
            'builder' => ['condition' => 'AND', 'rules' => []],
            'proc' => null,
            'invert_map' => 0,
            'alert_operation_id' => $operation?->id,
        ]);

        DB::table('alerts')->insert([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'alerted' => AlertState::CLEAR,
            'open' => 1,
            'info' => '{}',
        ]);

        DB::table('alert_log')->insert([
            'rule_id' => $rule->id,
            'device_id' => $device->device_id,
            'state' => AlertState::ACTIVE,
            'details' => gzcompress((string) json_encode(['rule' => [], 'contacts' => []]), 9),
            'time_logged' => date('Y-m-d H:i:s'),
        ]);

        return compact('device', 'operation', 'rule', 'segments');
    }

    /**
     * Run the alerter $times, capturing the transport list passed to each issueAlert() call
     * (issueAlert is stubbed so no real delivery happens, but the full engine pipeline runs).
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function runAlertsCapturing(int $times = 1): array
    {
        $captured = [];

        /** @var RunAlerts&\Mockery\MockInterface $runAlerts */
        $runAlerts = Mockery::mock(RunAlerts::class)->makePartial();
        $runAlerts->shouldReceive('issueAlert')->andReturnUsing(function ($alert, $transports = null) use (&$captured) {
            $captured[] = $transports ?? [];

            return true;
        });

        for ($i = 0; $i < $times; $i++) {
            $runAlerts->runAlerts();
        }

        return $captured;
    }

    /**
     * @return array<string, mixed>
     */
    private function latestAlertLogDetails(int $ruleId): array
    {
        $row = DB::table('alert_log')->where('rule_id', $ruleId)->orderByDesc('id')->first();
        if ($row === null || empty($row->details)) {
            return [];
        }

        return json_decode((string) gzuncompress($row->details), true) ?? [];
    }
}
