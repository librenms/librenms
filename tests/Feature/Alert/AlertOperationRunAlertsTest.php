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

use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertLog;
use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\AlertTransport;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Processor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LibreNMS\Alert\AlertRules;
use LibreNMS\Alert\AlertUtil;
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

    public function testAcknowledgedAlertIssuesAcknowledgementNotification(): void
    {
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ]);

        $objs = [];
        /** @var RunAlerts&\Mockery\MockInterface $runAlerts */
        $runAlerts = Mockery::mock(RunAlerts::class)->makePartial();
        $runAlerts->shouldReceive('extTransports')->andReturnUsing(function ($obj) use (&$objs): void {
            $objs[] = $obj;
        });

        $runAlerts->runAlerts();
        $this->assertContains(AlertState::ACTIVE, $this->issuedStates($objs));

        AlertFault::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->where('open', 1)
            ->update(['state' => AlertState::ACKNOWLEDGED]);
        (new AlertRules($context['device']->device_id))->syncAlertState($context['rule']);

        $objs = [];
        $runAlerts->runAlerts();
        $runAlerts->runAcks();

        $this->assertContains(
            AlertState::ACKNOWLEDGED,
            $this->issuedStates($objs),
            'Acknowledging must send an acknowledgement notification'
        );
        $alerted = DB::table('alerts')
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->value('alerted');
        $this->assertEquals(AlertState::ACKNOWLEDGED, $alerted);
    }

    public function testAcknowledgedFaultsSendWhileAggregateStillActive(): void
    {
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ]);

        $objs = [];
        /** @var RunAlerts&\Mockery\MockInterface $runAlerts */
        $runAlerts = Mockery::mock(RunAlerts::class)->makePartial();
        $runAlerts->shouldReceive('extTransports')->andReturnUsing(function ($obj) use (&$objs): void {
            $objs[] = $obj;
        });

        $runAlerts->runAlerts();

        AlertFault::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->where('open', 1)
            ->update(['state' => AlertState::ACKNOWLEDGED]);

        $objs = [];
        $runAlerts->runAlerts();

        $this->assertContains(
            AlertState::ACKNOWLEDGED,
            $this->issuedStates($objs),
            'All-ack remaining faults must notify even if the alerts row is still ACTIVE'
        );
    }

    public function testPerEntityAcknowledgementIssuesItsOwnNotification(): void
    {
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ], seedAlert: false);
        $context['rule']->update([
            'notify_per_entity' => true,
            'max_entities' => 10,
            'query' => 'SELECT * FROM processors WHERE device_id = ? AND processor_usage >= 90',
        ]);

        Processor::factory()->for($context['device'])->create([
            'processor_index' => '1',
            'processor_type' => 'hr',
            'processor_usage' => 95,
        ]);
        Processor::factory()->for($context['device'])->create([
            'processor_index' => '2',
            'processor_type' => 'hr',
            'processor_usage' => 96,
        ]);

        (new AlertRules($context['device']))->run();

        AlertFault::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->update(['alerted' => AlertState::ACTIVE]);

        $acked = AlertFault::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->where('open', 1)
            ->orderBy('id')
            ->firstOrFail();
        $acked->state = AlertState::ACKNOWLEDGED;
        $acked->save();

        $objs = [];
        /** @var RunAlerts&\Mockery\MockInterface $runAlerts */
        $runAlerts = Mockery::mock(RunAlerts::class)->makePartial();
        $runAlerts->shouldReceive('extTransports')->andReturnUsing(function ($obj) use (&$objs): void {
            $objs[] = $obj;
        });
        $runAlerts->runAlerts();

        $this->assertContains(
            AlertState::ACKNOWLEDGED,
            $this->issuedStates($objs),
            'Per-entity acknowledge must send an acknowledgement for the acked fault'
        );
        $this->assertSame(AlertState::ACKNOWLEDGED, (int) $acked->fresh()->alerted);
        $this->assertContains(AlertState::ACTIVE, $this->issuedStates($objs));
    }

    public function testPartialRecoveryIssuesRecoveryAndClosesRecoveredFault(): void
    {
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ], seedAlert: false);
        $context['rule']->update([
            'query' => 'SELECT * FROM processors WHERE device_id = ? AND processor_usage >= 90',
        ]);

        $keepProcessor = Processor::factory()->for($context['device'])->create([
            'processor_index' => '1',
            'processor_type' => 'hr',
            'processor_usage' => 95,
        ]);
        $recoverProcessor = Processor::factory()->for($context['device'])->create([
            'processor_index' => '2',
            'processor_type' => 'hr',
            'processor_usage' => 96,
        ]);

        $alertRules = new AlertRules($context['device']);
        $alertRules->run();

        AlertFault::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->update(['alerted' => AlertState::ACTIVE]);

        $recoverProcessor->processor_usage = 10;
        $recoverProcessor->save();
        $alertRules->run();

        $recovered = AlertFault::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->where('state', AlertState::RECOVERED)
            ->firstOrFail();
        $recoveredActiveLog = AlertLog::query()
            ->where('fault_id', $recovered->id)
            ->where('state', AlertState::ACTIVE)
            ->orderBy('id')
            ->firstOrFail();
        $recoveredLog = AlertLog::query()
            ->where('fault_id', $recovered->id)
            ->where('state', AlertState::RECOVERED)
            ->orderByDesc('id')
            ->firstOrFail();
        $betterLog = AlertLog::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->whereNull('fault_id')
            ->where('state', AlertState::BETTER)
            ->orderByDesc('id')
            ->firstOrFail();

        $this->assertGreaterThan(
            $recoveredLog->id,
            $betterLog->id,
            'syncAlertState must write the rule-level BETTER log after the fault RECOVERED log'
        );

        $betterDetails = $betterLog->details;
        $betterDetails['count'] = 5;
        $betterLog->details = $betterDetails;
        $betterLog->save();

        $objs = [];

        /** @var RunAlerts&\Mockery\MockInterface $runAlerts */
        $runAlerts = Mockery::mock(RunAlerts::class)->makePartial();
        $runAlerts->shouldReceive('extTransports')->andReturnUsing(function ($obj) use (&$objs): void {
            $objs[] = $obj;
        });
        $runAlerts->runAlerts();

        $this->assertContains(AlertState::BETTER, array_column($objs, 'state'));
        $recovery = collect($objs)->first(fn ($obj) => (int) $obj['state'] === AlertState::RECOVERED);
        $this->assertNotNull($recovery, 'The recovered entity should get a recovery notification');
        $this->assertSame($recoveredLog->id, $recovery['uid'], 'Recovery must use the fault RECOVERED log, not the later BETTER log');
        $this->assertSame($recoveredActiveLog->id, $recovery['id'], 'elapsed/previousLog must be the recovered fault\'s prior incident');
        $this->assertSame(0, (int) $recovered->fresh()->open, 'The recovered fault must be closed after delivery');
        $this->assertNotSame(0, (int) ($betterLog->fresh()->details['count'] ?? 0), 'BETTER log escalation count must not be reset to 0');
        $keep = AlertFault::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->where('state', AlertState::ACTIVE)
            ->first();
        $this->assertNotNull($keep);
        $this->assertSame($keepProcessor->processor_id, (int) $keep->entity_id);
        $this->assertSame(AlertState::BETTER, (int) $keep->alerted, 'Remaining faults must be marked alerted when the problem notification is sent');
    }

    public function testPendingRecoveryIsSkippedWhenProblemWasNeverNotified(): void
    {
        // First problem segment is delayed; the entity recovers before that timer is due.
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 300, 'dur' => 3600],
        ]);

        $recovered = AlertFault::create([
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'entity_key' => 'entity|recovered',
            'state' => AlertState::RECOVERED,
            'open' => 1,
            'alerted' => 0,
            'details' => ['rule' => [['entity' => 'recovered']], 'contacts' => []],
        ]);

        AlertLog::create([
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'fault_id' => $recovered->id,
            'state' => AlertState::ACTIVE,
            'details' => ['rule' => [['entity' => 'recovered']], 'contacts' => []],
            'time_logged' => now()->subMinutes(2),
        ]);
        AlertLog::create([
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'fault_id' => $recovered->id,
            'state' => AlertState::RECOVERED,
            'details' => ['rule' => [['entity' => 'recovered']], 'contacts' => []],
        ]);
        AlertLog::create([
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'fault_id' => null,
            'state' => AlertState::BETTER,
            'details' => ['rule' => [['entity' => 'keep']], 'contacts' => []],
        ]);

        Alert::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->firstOrFail()
            ->update([
                'state' => AlertState::BETTER,
                'alerted' => AlertState::CLEAR,
                'open' => 1,
                'info' => ['open_fault_count' => 1],
            ]);

        $objs = [];

        /** @var RunAlerts&\Mockery\MockInterface $runAlerts */
        $runAlerts = Mockery::mock(RunAlerts::class)->makePartial();
        $runAlerts->shouldReceive('extTransports')->andReturnUsing(function ($obj) use (&$objs): void {
            $objs[] = $obj;
        });
        $runAlerts->runAlerts();

        $this->assertEmpty(
            array_filter($objs, fn ($obj) => (int) $obj['state'] === AlertState::RECOVERED),
            'A recovered entity must not get a recovery notification when its problem was never sent'
        );
        $this->assertSame(0, (int) $recovered->fresh()->open, 'The unnotified recovered fault must still be closed');
    }

    public function testOrphanedOperationIdDoesNotSendRecovery(): void
    {
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ]);
        $context['rule']->update(['alert_operation_id' => 2147483647]);

        $recovered = AlertFault::create([
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'entity_key' => 'entity|recovered',
            'state' => AlertState::RECOVERED,
            'open' => 1,
            'alerted' => AlertState::ACTIVE,
            'details' => ['rule' => [['entity' => 'recovered']], 'contacts' => []],
        ]);
        AlertLog::create([
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'fault_id' => $recovered->id,
            'state' => AlertState::RECOVERED,
            'details' => ['rule' => [['entity' => 'recovered']], 'contacts' => []],
        ]);
        Alert::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->update([
                'state' => AlertState::BETTER,
                'alerted' => AlertState::CLEAR,
                'open' => 1,
                'info' => ['open_fault_count' => 1],
            ]);

        $this->assertTrue(AlertUtil::operationNotificationsSuppressed($context['rule']->id));
        $this->assertFalse(AlertUtil::shouldSendRecovery($context['rule']->id, $context['device']->device_id));

        $objs = [];
        /** @var RunAlerts&\Mockery\MockInterface $runAlerts */
        $runAlerts = Mockery::mock(RunAlerts::class)->makePartial();
        $runAlerts->shouldReceive('extTransports')->andReturnUsing(function ($obj) use (&$objs): void {
            $objs[] = $obj;
        });
        $runAlerts->runAlerts();

        $this->assertEmpty($objs, 'An orphaned alert_operation_id must be treated as suppressed');
        $this->assertSame(0, (int) $recovered->fresh()->open);
    }

    public function testFullRecoveryDoesNotResendAfterIntervalContinue(): void
    {
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ]);
        $context['rule']->update(['extra' => ['interval' => 3600]]);

        $fault = AlertFault::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->firstOrFail();
        $fault->update([
            'state' => AlertState::RECOVERED,
            'open' => 1,
            'alerted' => AlertState::ACTIVE,
        ]);

        $recoveredLog = AlertLog::create([
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'fault_id' => $fault->id,
            'state' => AlertState::RECOVERED,
            'details' => ['rule' => [], 'contacts' => [], 'interval' => time()],
        ]);

        Alert::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->firstOrFail()
            ->update([
                'state' => AlertState::RECOVERED,
                'alerted' => AlertState::ACTIVE,
                'open' => 1,
                'info' => ['open_fault_count' => 0],
            ]);

        $objs = [];

        /** @var RunAlerts&\Mockery\MockInterface $runAlerts */
        $runAlerts = Mockery::mock(RunAlerts::class)->makePartial();
        $runAlerts->shouldReceive('extTransports')->andReturnUsing(function ($obj) use (&$objs): void {
            $objs[] = $obj;
        });
        $runAlerts->runAlerts();

        $this->assertEmpty(
            array_filter($objs, fn ($obj) => (int) $obj['state'] === AlertState::RECOVERED),
            'Interval gate must hold the recovery in the main loop, not a second dispatcher'
        );
        $this->assertSame(1, (int) $fault->fresh()->open);
        $this->assertDatabaseHas('alerts', [
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'state' => AlertState::RECOVERED,
            'open' => 1,
            'alerted' => AlertState::ACTIVE,
        ]);

        $details = $recoveredLog->fresh()->details;
        $details['interval'] = time() - 10000;
        $recoveredLog->details = $details;
        $recoveredLog->save();

        $runAlerts->runAlerts();

        $this->assertCount(1, array_filter($objs, fn ($obj) => (int) $obj['state'] === AlertState::RECOVERED));
        $this->assertSame(0, (int) $fault->fresh()->open);
        $this->assertDatabaseHas('alerts', [
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'state' => AlertState::RECOVERED,
            'open' => 0,
            'alerted' => AlertState::RECOVERED,
        ]);
    }

    public function testParentDownSuppressesAlertWithoutAdvancingSegmentTimers(): void
    {
        // Parent is down; child has an active, unnotified alert.
        $parent = Device::factory()->create(['status' => 0, 'ignore' => 0, 'disabled' => 0]);
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ]);

        DB::table('device_relationships')->insert([
            'parent_device_id' => $parent->device_id,
            'child_device_id' => $context['device']->device_id,
        ]);

        // Run several times while parent is down.
        // The alert must be suppressed and segment timer state must NOT advance.
        $captured = $this->runAlertsCapturing(3);
        $this->assertCount(0, $captured, 'No alert should fire while parent is down');
        $this->assertSame(
            1,
            Eventlog::query()
                ->where('device_id', $context['device']->device_id)
                ->where('message', 'Skipped alerts because all parent devices are down')
                ->count(),
            'Parent-down skip must be eventlogged once, not every cycle'
        );

        $segmentId = $context['segments'][0]['segment']->id;
        $details = $this->latestAlertLogDetails($context['rule']->id);
        $fires = (int) ($details['op_seg'][(string) $segmentId]['fires'] ?? 0);
        $this->assertSame(0, $fires, 'Segment timer must not advance while parent is suppressing the alert');

        // Bring parent back up.  The child alert must fire immediately on the next cycle.
        DB::table('devices')
            ->where('device_id', $parent->device_id)
            ->update(['status' => 1]);

        $captured = $this->runAlertsCapturing(1);
        $this->assertCount(1, $captured, 'Alert must fire as soon as parent recovers with child still down');
        $this->assertSame(
            ['api'],
            array_map(static fn ($t) => $t['transport_type'], $captured[0]),
            'The correct transport should be notified on the first unsuppressed cycle'
        );

        $alerted = DB::table('alerts')
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->value('alerted');
        $this->assertEquals(AlertState::ACTIVE, $alerted, 'alerts.alerted must be advanced after the notification fires');
    }

    public function testParentDownClosesRecoveriesWithoutRetry(): void
    {
        $parent = Device::factory()->create(['status' => 0, 'ignore' => 0, 'disabled' => 0]);
        $context = $this->makeActiveAlert([
            ['type' => 'api', 'start' => 0, 'dur' => 3600],
        ]);

        DB::table('device_relationships')->insert([
            'parent_device_id' => $parent->device_id,
            'child_device_id' => $context['device']->device_id,
        ]);

        $recovered = AlertFault::create([
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'entity_key' => 'entity|recovered',
            'state' => AlertState::RECOVERED,
            'open' => 1,
            'alerted' => AlertState::ACTIVE,
            'details' => ['rule' => [['entity' => 'recovered']], 'contacts' => []],
        ]);
        AlertLog::create([
            'rule_id' => $context['rule']->id,
            'device_id' => $context['device']->device_id,
            'fault_id' => $recovered->id,
            'state' => AlertState::RECOVERED,
            'details' => ['rule' => [['entity' => 'recovered']], 'contacts' => []],
        ]);

        Alert::query()
            ->where('rule_id', $context['rule']->id)
            ->where('device_id', $context['device']->device_id)
            ->update([
                'state' => AlertState::BETTER,
                'alerted' => AlertState::CLEAR,
                'open' => 1,
                'info' => ['open_fault_count' => 1],
            ]);

        $objs = [];

        /** @var RunAlerts&\Mockery\MockInterface $runAlerts */
        $runAlerts = Mockery::mock(RunAlerts::class)->makePartial();
        $runAlerts->shouldReceive('extTransports')->andReturnUsing(function ($obj) use (&$objs): void {
            $objs[] = $obj;
        });
        $runAlerts->runAlerts();
        $runAlerts->runAlerts();

        $this->assertSame(0, (int) $recovered->fresh()->open, 'Parent-down recoveries must be closed, not retried');
        $this->assertCount(
            0,
            collect($objs)->where('state', AlertState::RECOVERED),
            'No recovery notification while the parent is down'
        );

        DB::table('devices')
            ->where('device_id', $parent->device_id)
            ->update(['status' => 1]);

        $runAlerts->runAlerts();

        $this->assertCount(
            0,
            collect($objs)->where('state', AlertState::RECOVERED),
            'Closed parent-down recoveries must not burst when the parent returns'
        );
    }

    /**
     * Build a device + alert rule (optionally with an operation/segments/transports) and an
     * open, active alert ready for RunAlerts to process.
     *
     * @param  array<int, array{type?:string, from?:int, to?:int|null, start?:int, dur?:int}>  $segmentsConfig
     * @return array{device: Device, operation: AlertOperation|null, rule: AlertRule, segments: array<int, array{segment: \App\Models\AlertOperationSegment, transport: AlertTransport}>}
     */
    private function makeActiveAlert(array $segmentsConfig, bool $suppressed = false, bool $assignOperation = true, bool $seedAlert = true): array
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

        if ($seedAlert) {
            DB::table('alerts')->insert([
                'device_id' => $device->device_id,
                'rule_id' => $rule->id,
                'state' => AlertState::ACTIVE,
                'alerted' => AlertState::CLEAR,
                'open' => 1,
                'info' => json_encode(['open_fault_count' => 1]),
            ]);

            $incident = ['id' => (int) $device->device_id, 'msg' => 'down'];
            $fault = AlertFault::create([
                'rule_id' => $rule->id,
                'device_id' => $device->device_id,
                'entity_key' => (string) $device->device_id,
                'state' => AlertState::ACTIVE,
                'open' => 1,
                'alerted' => 0,
                'details' => ['rule' => [$incident], 'contacts' => []],
            ]);

            DB::table('alert_log')->insert([
                'rule_id' => $rule->id,
                'device_id' => $device->device_id,
                'fault_id' => $fault->id,
                'state' => AlertState::ACTIVE,
                'details' => gzcompress((string) json_encode(['rule' => [$incident], 'contacts' => []]), 9),
                'time_logged' => date('Y-m-d H:i:s'),
            ]);
        }

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
     * @param  array<int, array<string, mixed>>  $objs
     * @return list<int>
     */
    private function issuedStates(array $objs): array
    {
        return array_map(static fn ($state) => (int) $state, array_column($objs, 'state'));
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
