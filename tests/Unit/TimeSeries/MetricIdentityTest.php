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

    public function test_a_series_is_grouped_by_its_hostname(): void
    {
        $identity = new MetricIdentity('processors', ['hostname' => 'host.example.com', 'type' => 'hr', 'index' => 1]);

        $this->assertSame('host.example.com', $identity->group());
        $this->assertSame(['type' => 'hr', 'index' => 1], $identity->tagsInGroup());
    }

    public function test_a_series_without_a_hostname_is_grouped_by_its_first_tag(): void
    {
        $identity = new MetricIdentity('vm.cpu', ['cluster' => 'proxmox-abc', 'id' => 5]);

        $this->assertSame('proxmox-abc', $identity->group());
        $this->assertSame(['id' => 5], $identity->tagsInGroup());
    }

    public function test_a_series_without_tags_has_no_group(): void
    {
        $this->assertSame('', new MetricIdentity('uptime')->group());
    }
}
