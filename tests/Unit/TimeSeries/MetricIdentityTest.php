<?php

namespace LibreNMS\Tests\Unit\TimeSeries;

use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\Tests\TestCase;

class MetricIdentityTest extends TestCase
{
    public function test_it_can_be_instantiated_with_measurement_and_tags(): void
    {
        $identity = new MetricIdentity('mempool', ['hostname' => 'host.example.com', 'mempool_index' => 10]);

        $this->assertEquals('mempool', $identity->measurement);
        $this->assertEquals(['hostname' => 'host.example.com', 'mempool_index' => 10], $identity->tags);
    }
}
