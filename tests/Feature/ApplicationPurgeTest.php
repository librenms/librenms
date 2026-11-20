<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationMetric;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\InMemoryDbTestCase;

class ApplicationPurgeTest extends InMemoryDbTestCase
{
    public function testPurgeTrashedRemovesOldDeletedApplicationsAndTheirMetrics(): void
    {
        $device = Device::factory()->create();

        $old = Application::factory()->create([
            'device_id' => $device->device_id,
            'app_type' => 'ntp',
        ]);
        $old->delete();
        DB::table('applications')->where('app_id', $old->app_id)
            ->update(['deleted_at' => Carbon::now()->subDays(30)]);

        $metric = ApplicationMetric::forceCreate([
            'app_id' => $old->app_id,
            'metric' => 'ntp_offset',
            'value' => 1.5,
        ]);

        $purged = Application::purgeTrashed(14);

        $this->assertSame(1, $purged['applications']);
        $this->assertSame(1, $purged['metrics']);
        $this->assertFalse(Application::withTrashed()->where('app_id', $old->app_id)->exists());
        $this->assertNull(ApplicationMetric::find($metric->id));
    }

    public function testPurgeTrashedKeepsRecentlyDeletedAndActiveApplications(): void
    {
        $device = Device::factory()->create();

        $recent = Application::factory()->create([
            'device_id' => $device->device_id,
            'app_type' => 'mysql',
        ]);
        $recent->delete();
        DB::table('applications')->where('app_id', $recent->app_id)
            ->update(['deleted_at' => Carbon::now()->subDay()]);

        $active = Application::factory()->create([
            'device_id' => $device->device_id,
            'app_type' => 'nginx',
        ]);

        $purged = Application::purgeTrashed(14);

        $this->assertSame(0, $purged['applications']);
        $this->assertSame(0, $purged['metrics']);
        $this->assertTrue(Application::withTrashed()->where('app_id', $recent->app_id)->exists());
        $this->assertTrue(Application::where('app_id', $active->app_id)->exists());
    }
}
