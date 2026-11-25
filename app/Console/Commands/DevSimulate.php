<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Str;
use Laravel\Prompts\Progress;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Util\Snmpsim;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\ProcessSignaledException;

use function Laravel\Prompts\progress;

class DevSimulate extends LnmsCommand
{
    protected $name = 'dev:simulate';
    protected bool $developer = true;
    /**
     * @var Snmpsim
     */
    protected $snmpsim = null;
    /**
     * @var int[] devices saved by this run, removed on exit with --remove
     */
    private array $addedDeviceIds = [];

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

        $this->addArgument('file', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
        $this->addOption('keep', 'k', InputOption::VALUE_NONE);
        $this->addOption('setup-venv', mode: InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // accept paths like tests/snmpsim/*.snmprec, so the shell can do the globbing
        $files = array_unique(array_map(fn ($file) => basename($file, '.snmprec'), $this->argument('file')));
        foreach ($files as $file) {
            if (! file_exists(base_path("tests/snmpsim/$file.snmprec"))) {
                $this->error("$file does not exist");

                return 1;
            }
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
        $this->started($files);
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

    private function started(array $files): void
    {
        // set up removal shutdown function before adding, so a failure part way still cleans up
        if ($files && ! $this->option('keep')) {
            $this->queueRemoval();
        }

        if (count($files) > 1) {
            progress(
                label: trans('commands.dev:simulate.adding'),
                steps: $files,
                callback: fn (string $file, Progress $progress) => $progress->hint($this->addDevice($file)),
            );
        } else {
            foreach ($files as $file) {
                $this->info($this->addDevice($file));
            }
        }

        // after the progress bar, it resets the SIGINT handler when finished
        if ($files && ! $this->option('keep') && function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function (): void {
                exit; // exit normally on SIGINT, so the removal shutdown function runs
            });
        }
    }

    /**
     * @return string added or updated message
     */
    private function addDevice(string $community): string
    {
        $device = Device::with('pollingMethods')->firstOrNew(['hostname' => "snmpsim-$community"]);
        $device->overwrite_ip = $this->snmpsim->ip;
        $action = $device->exists ? 'updated' : 'added';

        $device->last_discovered = null;
        $device->status_reason = '';
        $device->save();
        $this->addedDeviceIds[] = $device->device_id;

        $method = $device->pollingMethod(PollingMethodType::Snmp);
        if ($method === null) {
            $method = new DevicePollingMethod([
                'device_id' => $device->device_id,
                'method_type' => PollingMethodType::Snmp,
                'affects_availability' => true,
            ]);
            $device->setRelation('pollingMethods', collect([$method]));
        } else {
            $device->pollingMethod(PollingMethodType::Icmp)?->delete(); // don't need icmp, cleanup legacy
        }
        $method->settings = [...$method->settings ?? [], 'transport' => 'udp', 'port' => $this->snmpsim->port];

        $secret = Secret::updateOrCreate([
            'description' => "snmpsim secret ($device->hostname)",
            'secret_type' => SecretType::SnmpCommunity,
        ], [
            'data' => ['community' => $community],
        ]);
        $method->secret()->associate($secret)->save();

        return trans("commands.dev:simulate.$action", ['hostname' => $device->hostname, 'id' => $device->device_id]);
    }

    private function queueRemoval(): void
    {
        register_shutdown_function(function (): void {
            $devices = Device::whereIn('device_id', $this->addedDeviceIds)->get();

            if ($devices->count() > 1) {
                progress(
                    label: trans('commands.dev:simulate.removing'),
                    steps: $devices,
                    callback: function (Device $device, Progress $progress): void {
                        $progress->hint($device->hostname);
                        $device->delete();
                    },
                );
            } else {
                foreach ($devices as $device) {
                    $device->delete();
                    $this->info(trans('commands.dev:simulate.removed', ['id' => $device->device_id]));
                }
            }
            exit;
        });
    }

    public function completeArgument($name, $value)
    {
        if ($name == 'file') {
            return collect(glob(base_path('tests/snmpsim/*.snmprec')))->map(fn ($file) => basename($file, '.snmprec'))->filter(fn ($snmprec) => ! $value || Str::startsWith($snmprec, $value))->all();
        }

        return false;
    }
}
