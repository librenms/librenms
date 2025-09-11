<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Util\Snmpsim;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\ProcessSignaledException;

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
        $this->addOption('setup-venv', mode: InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $file = $this->argument('file');
        if ($file && ! file_exists(base_path("tests/snmpsim/$file.snmprec"))) {
            $this->error("$file does not exist");

            return 1;
        }

        $this->snmpsim = new Snmpsim;
        if (! $this->snmpsim->isVenvSetUp()) {
            $this->line(trans('commands.dev:simulate.setup', ['dir' => $this->snmpsim->getVenvPath()]));
            $this->snmpsim->setupVenv($this->getOutput()->isVeryVerbose());
        }

        if ($this->option('setup-venv')) {
            return 0; // venv is set up exit
        }

        $this->snmpsim->start();
        $this->line($this->snmpsim->waitForStartup());
        $this->started();
        $this->line(trans('commands.dev:simulate.exit'));
        try {
            $this->snmpsim->wait();
        } catch(ProcessSignaledException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        if (! $this->snmpsim->isSuccessful()) {
            $this->line($this->snmpsim->getErrorOutput());

            return 1;
        }

        return 0;
    }

    private function started(): void
    {
        if ($file = $this->argument('file')) {
            $this->addDevice($file);
        }
    }

    private function addDevice($community): void
    {
        $hostname = $this->option('multiple') ? $community : 'snmpsim';
        $device = Device::firstOrNew(['hostname' => $hostname]);
        $action = $device->exists ? 'updated' : 'added';

        $device->overwrite_ip = $this->snmpsim->ip;
        $device->port = $this->snmpsim->port;
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

    private function queueRemoval($device_id): void
    {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function () {
                exit; // exit normally on SIGINT
            });
        }

        register_shutdown_function(function () use ($device_id) {
            Device::findOrNew($device_id)->delete();
            $this->info(trans('commands.dev:simulate.removed', ['id' => $device_id]));
            exit;
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
