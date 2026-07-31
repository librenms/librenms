<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\ModuleList;
use LibreNMS\Util\ModuleTestHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DevCollectSnmprec extends LnmsCommand
{
    protected $name = 'dev:collect-snmprec';
    protected $developer = true;
    protected $description = 'Collect SNMP data from a device for snmpsim test files';

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('device', mode: InputArgument::REQUIRED);
        $this->addOption('variant', null, InputOption::VALUE_REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_OPTIONAL);
        $this->addOption('prefer-new', null, InputOption::VALUE_NONE);
        $this->addOption('os', 'o', InputOption::VALUE_OPTIONAL);
        $this->addOption('file', 'f', InputOption::VALUE_OPTIONAL);
        $this->addOption('debug', 'd', InputOption::VALUE_NONE);
        $this->addOption('full', null, InputOption::VALUE_NONE);
    }

    public function handle(): int
    {
        $deviceSpec = $this->argument('device');
        try {
            $device = DeviceCache::get($deviceSpec);
        } catch (\Exception $e) {
            $device = null;
        }

        if (! $device || ! $device->exists) {
            $this->error("Device '$deviceSpec' not found.");

            return 1;
        }

        $variant = $this->option('variant');
        if ($variant === null) {
            $this->error('The --variant (-v) option is required.');

            return 1;
        }

        if (Str::contains($variant, '_')) {
            $this->error('Variant name cannot contain an underscore (_).');

            return 1;
        }

        if ($device->os !== 'generic') {
            $targetOs = $device->os;
        } else {
            $targetOs = $this->option('os');
            if (! $targetOs) {
                $this->error('OS (-o, --os) is required because the device OS is generic.');

                return 1;
            }
        }

        Debug::set((bool) $this->option('debug'));

        $modulesInput = $this->option('modules');
        if ($modulesInput !== null && $modulesInput !== '') {
            $modules = explode(',', $modulesInput);
        } else {
            $modulesInput = 'all';
            $modules = [];
        }

        $this->line("OS: $targetOs");
        $this->line("Module(s): $modulesInput");
        if ($variant !== '') {
            $this->line("Variant: $variant");
        }
        $this->newLine();

        try {
            $capture = new ModuleTestHelper(ModuleList::fromUserOverrides($modules), $targetOs, $variant);

            if ($file = $this->option('file')) {
                $capture->setSnmprecSavePath($file);
            }

            $preferNewSnmprec = (bool) $this->option('prefer-new');
            $full = (bool) $this->option('full');

            $this->output->write('Capturing Data: ');
            \App\Facades\LibrenmsConfig::invalidateAndReload();
            $capture->captureFromDevice($device->device_id, $preferNewSnmprec, $full);
            $this->newLine();
            $this->info('Verify these file(s) do not contain any private data before sharing!');
        } catch (InvalidModuleException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    public function completeArgument($name, $value): array|false
    {
        if ($name === 'device') {
            return Device::query()
                ->when($value, fn ($query) => $query->where('hostname', 'like', "$value%")->orWhere('device_id', $value))
                ->orderBy('hostname')
                ->limit(25)
                ->pluck('hostname')
                ->all();
        }

        return false;
    }
}
