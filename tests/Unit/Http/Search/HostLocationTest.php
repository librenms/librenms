<?php

namespace LibreNMS\Tests\Unit\Http\Search;

use App\Http\Controllers\Search\Data\HostLocation;
use App\Http\Controllers\Search\Data\LocationState;
use App\Models\Port;
use LibreNMS\Tests\TestCase;

class HostLocationTest extends TestCase
{
    public function testOnlyMacOnThePortIsDirect(): void
    {
        $location = new HostLocation('02aaaaaaaa01', 1, new Port, null, 1, null);

        $this->assertSame(LocationState::Direct, $location->state());
        $this->assertSame('label-success', $location->state()->labelClass());
    }

    public function testSeveralMacsOnThePortIsShared(): void
    {
        $location = new HostLocation('02aaaaaaaa01', 1, new Port, null, 3, null);

        $this->assertSame(LocationState::Shared, $location->state());
        $this->assertSame('label-default', $location->state()->labelClass());
    }

    public function testAggregateIsTrunkEvenWithOneMac(): void
    {
        $location = new HostLocation('02aaaaaaaa01', 1, new Port(['ifType' => 'ieee8023adLag']), null, 1, null);

        $this->assertSame(LocationState::Trunk, $location->state());
        $this->assertSame('label-warning', $location->state()->labelClass());
    }

    public function testUplinkIsTrunk(): void
    {
        $port = (new Port)->setAttribute('links_count', 1);
        $location = new HostLocation('02aaaaaaaa01', 1, $port, null, 1, null);

        $this->assertTrue($location->isUplink());
        $this->assertSame(LocationState::Trunk, $location->state());
    }

    public function testUnknownLocationIsAWarning(): void
    {
        $this->assertSame('label-warning', LocationState::Unknown->labelClass());
    }
}
