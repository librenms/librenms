<?php

namespace LibreNMS\Tests\Unit\Models;

use App\Facades\Rrd;
use App\Models\Application;
use App\Models\Device;
use LibreNMS\Tests\TestCase;

final class ApplicationMetricIdentityTest extends TestCase
{
    public function testTheMainMetricIsTheAppFile(): void
    {
        $this->assertSame('app.example.com/app-ntp-client-5.rrd', Rrd::metricName($this->application('ntp-client')->metricIdentity())->relativePath());
    }

    public function testTagsFollowTheAppIdInOrder(): void
    {
        $metric = $this->application('seafile')->metricIdentity(['category' => 'owner', 'owner' => 'alice']);

        $this->assertSame(['hostname' => 'app.example.com', 'name' => 'seafile', 'app_id' => 5, 'category' => 'owner', 'owner' => 'alice'], $metric->tags);
        $this->assertSame('app.example.com/app-seafile-5-owner-alice.rrd', Rrd::metricName($metric)->relativePath());
    }

    private function application(string $type): Application
    {
        $application = new Application(['app_type' => $type]);
        $application->app_id = 5;
        $device = new Device;
        $device->hostname = 'app.example.com';
        $application->setRelation('device', $device);

        return $application;
    }
}
