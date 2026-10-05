<?php

namespace LibreNMS\Tests\Unit\Services;

use App\Services\CurrentRrdMetricService;
use LibreNMS\Tests\TestCase;

final class CurrentRrdMetricServiceTest extends TestCase
{
    public function testParseLatestValueSkipsUnknownRows(): void
    {
        $output = <<<'RRD'
                                 value

        1787419800: 1.0228556516e+00
        1787420100: 9.3197620270e-01
        1787420400: -nan
        RRD;

        $point = CurrentRrdMetricService::parseLatestValue($output, 'value', 1787420400, 900);

        $this->assertNotNull($point);
        $this->assertSame(1787420100, $point->timestamp);
        $this->assertEqualsWithDelta(0.9319762027, $point->get('value'), 0.0000000001);
    }

    public function testParseLatestValueRejectsStaleSamples(): void
    {
        $output = "value\n1787410000: 1.0000000000e+00\n";

        $this->assertNull(CurrentRrdMetricService::parseLatestValue($output, 'value', 1787420400, 900));
    }

    public function testParseLatestValueSelectsRequestedDataset(): void
    {
        $output = "user wait idle\n1787420100: 2.0 0.5 97.5\n";

        $point = CurrentRrdMetricService::parseLatestValue($output, 'wait', 1787420400, 900);

        $this->assertNotNull($point);
        $this->assertSame(0.5, $point->get('wait'));
    }
}
