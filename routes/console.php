<?php

use App\Console\Commands\MaintenanceCachePeeringdb;
use App\Console\Commands\MaintenanceCleanupNetworks;
use App\Console\Commands\MaintenanceCleanupSyslog;
use App\Console\Commands\MaintenanceDiscoverSslCertificates;
use App\Console\Commands\MaintenanceFetchOuis;
use App\Console\Commands\MaintenanceFetchRSS;
use App\Console\Commands\MaintenanceRefreshSslCertificates;
use App\Facades\LibrenmsConfig;
use App\Jobs\DispatchPollingWork;
use App\Models\Eventlog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Time;
use Symfony\Component\Process\Process;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('update', function (): void {
    (new Process([base_path('daily.sh')]))->setTimeout(null)->setIdleTimeout(null)->setTty(true)->run();
})->purpose(__('Update LibreNMS and run maintenance routines'));

// mark schedule working
Schedule::call(function (): void {
    Cache::put('scheduler_working', now()->timestamp, now()->addMinutes(6));
})->name('schedule operational check')->everyFiveMinutes();

// Only register the sub-minute polling dispatch when the scheduler is
// actually in use. A registered everyTenSeconds() event keeps
// `schedule:run` alive until the end of the minute even when its
// when() filter is false, which makes busybox crond skip every other
// run on legacy-schedule installs (see #21026). schedule:run boots
// fresh on every cron invocation, so evaluating the config here is
// equivalent to the previous when() filter for scheduler installs.
if (LibrenmsConfig::get('schedule_type.poller') == 'scheduler' || LibrenmsConfig::get('schedule_type.discovery') == 'scheduler') {
    Schedule::job(new DispatchPollingWork)
        ->everyTenSeconds() // DispatchPollingWork::INTERVAL
        ->onOneServer();
}

// schedule maintenance, should be after all others
$maintenance_log_file = LibrenmsConfig::get('log_dir') . '/maintenance.log';

Schedule::command(MaintenanceFetchOuis::class)
    ->weeklyOn(0, Time::pseudoRandomBetween('01:00', '01:59'))
    ->onOneServer()
    ->appendOutputTo($maintenance_log_file)
    ->onFailure(fn () => Eventlog::log('The scheduled command maintenance:fetch-ouis failed to run. Check the maintenance.log for details.', null, 'maintenance', Severity::Error));

Schedule::command(MaintenanceCleanupNetworks::class)
    ->weeklyOn(0, Time::pseudoRandomBetween('02:00', '02:59'))
    ->onOneServer()
    ->appendOutputTo($maintenance_log_file)
    ->onFailure(fn () => Eventlog::log('The scheduled command maintenance:cleanup-networks failed to run. Check the maintenance.log for details.', null, 'maintenance', Severity::Error));

Schedule::command(MaintenanceFetchRSS::class)
    ->dailyAt(Time::pseudoRandomBetween('03:00', '03:59'))
    ->onOneServer()
    ->appendOutputTo($maintenance_log_file)
    ->onFailure(fn () => Eventlog::log('The scheduled command maintenance:fetch-rss failed to run. Check the maintenance.log for details.', null, 'maintenance', Severity::Error));

Schedule::command(MaintenanceCleanupSyslog::class)
    ->hourlyAt(17)
    ->onOneServer()
    ->withoutOverlapping()
    ->appendOutputTo($maintenance_log_file)
    ->onFailure(fn () => Eventlog::log('The scheduled command maintenance:cleanup-syslog failed to run. Check the maintenance.log for details.', null, 'maintenance', Severity::Error));

Schedule::command(MaintenanceDiscoverSslCertificates::class)
    ->dailyAt(Time::pseudoRandomBetween('04:00', '04:59'))
    ->onOneServer()
    ->appendOutputTo($maintenance_log_file)
    ->when(fn () => LibrenmsConfig::get('ssl_certificates.auto_discover', false))
    ->onFailure(fn () => Eventlog::log('The scheduled command maintenance:discover-ssl-certificates failed to run. Check the maintenance.log for details.', null, 'maintenance', Severity::Error));

Schedule::command(MaintenanceRefreshSslCertificates::class)
    ->dailyAt(Time::pseudoRandomBetween('05:00', '05:59'))
    ->onOneServer()
    ->appendOutputTo($maintenance_log_file)
    ->onFailure(fn () => Eventlog::log('The scheduled command maintenance:refresh-ssl-certificates failed to run. Check the maintenance.log for details.', null, 'maintenance', Severity::Error));

Schedule::command(MaintenanceCachePeeringdb::class)
    ->dailyAt(Time::pseudoRandomBetween('06:00', '06:59'))
    ->onOneServer()
    ->withoutOverlapping()
    ->appendOutputTo($maintenance_log_file)
    ->when(fn () => LibrenmsConfig::get('peeringdb.enabled'))
    ->onFailure(fn () => Eventlog::log('The scheduled command maintenance:cache-peeringdb failed to run. Check the maintenance.log for details.', null, 'maintenance', Severity::Error));

Schedule::command('queue:prune-failed', ['--hours' => 168])
    ->dailyAt(Time::pseudoRandomBetween('07:00', '07:59'))
    ->onOneServer()
    ->appendOutputTo($maintenance_log_file);
