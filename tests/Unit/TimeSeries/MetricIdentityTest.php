<?php

namespace LibreNMS\Tests\Unit\TimeSeries;

use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\Tests\TestCase;

class MetricIdentityTest extends TestCase
{
    public function test_it_can_be_instantiated_with_measurement_and_tags(): void
    {
        $identity = new MetricIdentity(1, 'mempool', ['mempool_index' => 10], ['mempool', 'hr', 10]);

        $this->assertEquals(1, $identity->deviceId);
        $this->assertEquals('mempool', $identity->measurement);
        $this->assertEquals(['mempool_index' => 10], $identity->tags);
        $this->assertEquals(['mempool', 'hr', 10], $identity->rrdName);
    }
}
