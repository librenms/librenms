<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Events\ServicePolled;
use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\Data\Store\Datastore;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ServicesPoll extends LnmsCommand
{
    protected $name = 'services:poll';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::OPTIONAL);
        $this->addOption('no-data', 'x', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->configureOutputOptions();

        if (Config::get('poller_modules.services')) {
            $this->warn(trans('commands.services:poll.module_enabled'));

            return 0;
        }

        if ($this->option('no-data')) {
            Config::set('rrd.enable', false);
            Config::set('influxdb.enable', false);
            Config::set('prometheus.enable', false);
            Config::set('graphite.enable', false);
        }

        Log::info(trans('commands.services:poll.starting'));
        $this->newLine();
        $poller_start = microtime(true);
        $polled_services = 0;
        Event::listen(ServicePolled::class, function () use (&$polled_services) {
            $polled_services++;
        });

        Device::when($this->argument('device spec'), function ($query, $host) {
            if (is_numeric($host)) {
                return $query->where('device_id', $host);
            }

            return $query->where('hostname', 'LIKE', str_replace('*', '%', $host));
        })->where('disabled', 0)->whereHas('services')->pluck('device_id')->each(function ($device_id) use (&$polled_services) {
            DeviceCache::setPrimary($device_id);
            $device = DeviceCache::getPrimary()->attributesToArray();
            Log::info("Device: {$device['hostname']}");
            $os = \LibreNMS\OS::make($device);
            $services = new \LibreNMS\Modules\Services();
            $services->poll($os);
        });

        $poller_end = microtime(true);
        $poller_run = ($poller_end - $poller_start);
        $this->newLine();
        Log::info(trans('commands.services:poll.polled', [
            'timestamp' => date(\LibreNMS\Config::get('dateformat.compact')),
            'count' => $polled_services,
            'duration' => round($poller_run, 3),
        ]));

        Datastore::terminate();

        return 0;
    }
}
