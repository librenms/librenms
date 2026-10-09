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

Artisan::command('scan
    {network?* : ' . __('CIDR notation network(s) to scan, can be ommited if \'nets\' config is set') . '}
    {--P|ping-only : ' . __('Add the device as a ping only device if it replies to ping but not SNMP') . '}
    {--o|dns-only : ' . __('Only DNS resolved Devices') . '}
    {--t|threads=32 : ' . __('How many IPs to scan at a time, more will increase the scan speed, but could overload your system') . '}
    {--l|legend : ' . __('Print the legend') . '}
', function () {
    /** @var Illuminate\Console\Command $this */
    $command = [base_path('snmp-scan.py')];

    if (empty($this->argument('network')) && ! LibrenmsConfig::has('nets')) {
        $this->error(__('Network is required if \'nets\' is not set in the config'));

        return 1;
    }

    if ($this->option('dns-only')) {
        $command[] = '-o';
    }

    if ($this->option('ping-only')) {
        $command[] = '-P';
    }

    $command[] = '-t';
    $command[] = $this->option('threads');

    if ($this->option('legend')) {
        $command[] = '-l';
    }

    $verbosity = $this->getOutput()->getVerbosity();
    if ($verbosity >= 64) {
        $command[] = '-v';
        if ($verbosity >= 128) {
            $command[] = '-v';
            if ($verbosity >= 256) {
                $command[] = '-v';
            }
        }
    }

    $command = array_merge($command, $this->argument('network'));

    $scan_process = (new Process($command))
        ->setTimeout(null)
        ->setIdleTimeout(null)
        ->setTty(Process::isTtySupported() && ! $this->option('quiet'));
    $scan_process->run();

    if (! Process::isTtySupported() && ! $this->option('quiet')) {
        // just dump the output after we are done if we couldn't use tty
        $this->line($scan_process->getOutput());
    }

    return $scan_process->getExitCode();
})->purpose(__('Scan the network for hosts and try to add them to LibreNMS'));

// mark schedule working
Schedule::call(function (): void {
    Cache::put('scheduler_working', now()->timestamp, now()->addMinutes(6));

    // the old oneshot unit ran directly under systemd with output to null; systemd-cron logs to the journal and the new unit sets a marker
    $legacy_timer = getenv('INVOCATION_ID') !== false
        && getenv('JOURNAL_STREAM') === false
        && getenv('LIBRENMS_SCHEDULER') === false;
    Cache::put('scheduler_legacy_timer', $legacy_timer, now()->addMinutes(6));
})->name('schedule operational check')->everyFiveMinutes();

Schedule::when(fn (): bool => LibrenmsConfig::get('schedule_type.poller') == 'scheduler' || LibrenmsConfig::get('schedule_type.discovery') == 'scheduler')
    ->everyTenSeconds()
    ->onOneServer()
    ->job(new DispatchPollingWork);

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
