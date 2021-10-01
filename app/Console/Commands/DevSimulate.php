<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Util\Snmpsim;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class DevSimulate extends LnmsCommand
{
    protected $name = 'dev:simulate';
    protected $developer = true;
    /**
     * @var Snmpsim
     */
    protected $snmpsim = null;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate devices using test data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addArgument('file', InputArgument::OPTIONAL);
        $this->addOption('multiple', 'm', InputOption::VALUE_NONE);
        $this->addOption('remove', 'r', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->snmpsim = new Snmpsim();
        $snmprec_dir = $this->snmpsim->getDir();
        $listen = $this->snmpsim->getIp() . ':' . $this->snmpsim->getPort();

        $snmpsim = new Process([
            $this->snmpsim->findSnmpsimd(),
            "--data-dir=$snmprec_dir",
            "--agent-udpv4-endpoint=$listen",
        ]);
        $snmpsim->setTimeout(null);

        $snmpsim->run(function ($type, $buffer) use ($listen) {
            if (Process::ERR === $type) {
                if (Str::contains($buffer, $listen)) {
                    $this->line(trim($buffer));
                    $this->started();
                    $this->line(trans('commands.dev:simulate.exit'));
                }
            }
        });

        if (! $snmpsim->isSuccessful()) {
            $this->line($snmpsim->getErrorOutput());
        }

        return 0;
    }

    private function started()
    {
        if ($file = $this->argument('file')) {
            $this->addDevice($file);
        }
    }

    private function addDevice($community)
    {
        $hostname = $this->option('multiple') ? $community : 'snmpsim';
        $device = Device::firstOrNew(['hostname' => $hostname]);
        $action = $device->exists ? 'updated' : 'added';

        $device->overwrite_ip = $this->snmpsim->getIp();
        $device->port = $this->snmpsim->getPort();
        $device->snmpver = 'v2c';
        $device->transport = 'udp';
        $device->community = $community;
        $device->last_discovered = null;
        $device->status_reason = '';
        $device->save();

        $this->info(trans("commands.dev:simulate.$action", ['hostname' => $device->hostname, 'id' => $device->device_id]));

        // set up removal shutdown function if requested
        if ($this->option('remove')) {
            $this->queueRemoval($device->device_id);
        }
    }

    private function queueRemoval($device_id)
    {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function () {
                exit(); // exit normally on SIGINT
            });
        }

        register_shutdown_function(function () use ($device_id) {
            Device::findOrNew($device_id)->delete();
            $this->info(trans('commands.dev:simulate.removed', ['id' => $device_id]));
            exit();
        });
    }

    public function completeArgument($name, $value)
    {
        if ($name == 'file') {
            return collect(glob(base_path('tests/snmpsim/*.snmprec')))->map(function ($file) {
                return basename($file, '.snmprec');
            })->filter(function ($snmprec) use ($value) {
                return ! $value || Str::startsWith($snmprec, $value);
            })->all();
        }

        return false;
    }
}
