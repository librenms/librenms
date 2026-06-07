<?php

/**
 * AlertOperationTimingTest.php
 *
 * Tests for the independent per-segment alert operation scheduler.
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

namespace LibreNMS\Tests\Unit\Alert;

use LibreNMS\Alert\AlertUtil;
use LibreNMS\Tests\TestCase;

final class AlertOperationTimingTest extends TestCase
{
    /**
     * Run the per-segment scheduler over a simulated timeline, anchored at t=0.
     *
     * @param  array<int, array{id:int, escalation_step_from:int, escalation_step_to:int|null, start_in_seconds:int, step_duration_seconds:int}>  $segments
     * @return array<int, array<int, int>>  fire timestamps keyed by segment id
     */
    private function simulate(array $segments, int $pollInterval, int $duration, int $tolerance = 0, int $defaultStep = 300): array
    {
        $state = [];
        $fired = [];

        for ($now = 0; $now <= $duration; $now += $pollInterval) {
            [$due, $state] = AlertUtil::evaluateSegmentTimers($segments, $state, 0, $now, $tolerance, $defaultStep);
            foreach ($due as $id) {
                $fired[$id][] = $now;
            }
        }

        return $fired;
    }

    /**
     * A single open-ended segment fires after start_in_seconds, then repeats every step_duration_seconds.
     */
    public function testSingleSegment(): void
    {
        $segments = [
            ['id' => 1, 'escalation_step_from' => 1, 'escalation_step_to' => null, 'start_in_seconds' => 300, 'step_duration_seconds' => 600],
        ];

        $fired = $this->simulate($segments, pollInterval: 60, duration: 1800);

        $this->assertSame([300, 900, 1500], $fired[1] ?? []);
    }

    /**
     * Two open-ended segments with different start/duration run on completely independent clocks.
     * This is the "mail vs slack" scenario: mail every 900s from 180s, slack every 1800s from 900s.
     */
    public function testTwoSegmentsIndependentDifferentTimes(): void
    {
        $segments = [
            ['id' => 1, 'escalation_step_from' => 1, 'escalation_step_to' => null, 'start_in_seconds' => 180, 'step_duration_seconds' => 900],
            ['id' => 2, 'escalation_step_from' => 2, 'escalation_step_to' => null, 'start_in_seconds' => 900, 'step_duration_seconds' => 1800],
        ];

        $fired = $this->simulate($segments, pollInterval: 60, duration: 3600);

        // segment 1 keeps its own 900s beat from 180s; segment 2 runs independently every 1800s from 900s
        $this->assertSame([180, 1080, 1980, 2880], $fired[1] ?? []);
        $this->assertSame([900, 2700], $fired[2] ?? []);
    }

    /**
     * Two segments with different escalation step ranges but identical start/duration fire on the
     * same schedule - escalation_step_from/to do not influence timing, only how many times they fire.
     */
    public function testTwoSegmentsDifferentStepsSameTiming(): void
    {
        $segments = [
            ['id' => 1, 'escalation_step_from' => 1, 'escalation_step_to' => null, 'start_in_seconds' => 300, 'step_duration_seconds' => 600],
            ['id' => 2, 'escalation_step_from' => 2, 'escalation_step_to' => null, 'start_in_seconds' => 300, 'step_duration_seconds' => 600],
        ];

        $fired = $this->simulate($segments, pollInterval: 60, duration: 1800);

        $this->assertSame([300, 900, 1500], $fired[1] ?? []);
        $this->assertSame($fired[1], $fired[2], 'Segments with identical timing should fire at the same times');
    }

    /**
     * escalation_step_to caps the number of times a segment fires (count = to - from + 1).
     */
    public function testEscalationStepToCapsFireCount(): void
    {
        $segments = [
            ['id' => 1, 'escalation_step_from' => 1, 'escalation_step_to' => 3, 'start_in_seconds' => 0, 'step_duration_seconds' => 600],
        ];

        $fired = $this->simulate($segments, pollInterval: 60, duration: 3600);

        // from=1, to=3 -> exactly 3 fires: at 0, 600, 1200, then it stops
        $this->assertSame([0, 600, 1200], $fired[1] ?? []);
    }
}
