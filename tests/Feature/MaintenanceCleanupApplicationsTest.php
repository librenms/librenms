<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Application;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\InMemoryDbTestCase;

final class MaintenanceCleanupApplicationsTest extends InMemoryDbTestCase
{
    private function makeOldTrashedApplication(): Application
    {
        $device = Device::factory()->create();
        $application = Application::factory()->create([
            'device_id' => $device->device_id,
            'app_type' => 'ntp',
        ]);
        $application->delete();
        DB::table('applications')->where('app_id', $application->app_id)
            ->update(['deleted_at' => Carbon::now()->subDays(30)]);

        return $application;
    }

    public function testPurgesOldTrashedApplications(): void
    {
        $application = $this->makeOldTrashedApplication();
        LibrenmsConfig::set('applications_purge', 14);

        $this->artisan('maintenance:cleanup-applications')->assertExitCode(0);

        $this->assertFalse(Application::withTrashed()->where('app_id', $application->app_id)->exists());
    }

    public function testDoesNotPurgeWhenDisabled(): void
    {
        $application = $this->makeOldTrashedApplication();
        LibrenmsConfig::set('applications_purge', 0);

        $this->artisan('maintenance:cleanup-applications')->assertExitCode(0);

        $this->assertTrue(Application::withTrashed()->where('app_id', $application->app_id)->exists());
    }

    public function testDaysArgumentOverridesTheConfiguredDays(): void
    {
        $application = $this->makeOldTrashedApplication();
        // config would disable the purge, but the argument must take precedence
        LibrenmsConfig::set('applications_purge', 0);

        $this->artisan('maintenance:cleanup-applications', ['days' => 14])->assertExitCode(0);

        $this->assertFalse(Application::withTrashed()->where('app_id', $application->app_id)->exists());
    }
}
