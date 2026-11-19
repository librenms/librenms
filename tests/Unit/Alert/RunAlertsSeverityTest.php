<?php

namespace LibreNMS\Tests\Unit\Alert;

use App\Models\Eventlog;
use LibreNMS\Alert\RunAlerts;
use LibreNMS\Enum\AlertState;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\TestCase;
use Mockery;

final class RunAlertsSeverityTest extends TestCase
{
    public function testActiveAlertEventUsesRuleSeverity(): void
    {
        $eventlog = Mockery::mock(Eventlog::class);
        $this->app->instance(Eventlog::class, $eventlog);

        foreach (['ok' => Severity::Ok, 'warning' => Severity::Warning, 'critical' => Severity::Error] as $ruleSeverity => $eventSeverity) {
            $eventlog->shouldReceive('_log')
                ->once()
                ->with("Issued $ruleSeverity alert for rule 'test rule' to transport 'test'", 1, 'alert', $eventSeverity, null);

            (new RunAlerts)->alertLog(true, [
                'state' => AlertState::ACTIVE,
                'severity' => $ruleSeverity,
                'name' => 'test rule',
                'device_id' => 1,
            ], 'test');
        }

        $this->expectOutputString('OKOKOK');
    }
}
