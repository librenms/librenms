<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesDeviceArgument;
use App\Console\LnmsCommand;
use App\Facades\DeviceCache;
use LibreNMS\Exceptions\HostRenameException;
use Symfony\Component\Console\Input\InputArgument;

class DeviceRename extends LnmsCommand
{
    use CompletesDeviceArgument;

    protected $name = 'device:rename';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::REQUIRED);
        $this->addArgument('new hostname', InputArgument::REQUIRED);
    }

    public function handle(): int
    {
        $old_hostname = $this->argument('device spec');
        $new_hostname = $this->argument('new hostname');

        $device = DeviceCache::get($old_hostname);
        if (! $device->exists) {
            $this->error(__('commands.device:rename.errors.not_found', ['device' => $old_hostname]));

            return 1;
        }

        try {
            $device->hostname = $new_hostname;
            $device->save();
        } catch (HostRenameException $e) {
            $this->error($e->getMessage());

            return 1;
        } catch (\Throwable $e) {
            $this->error(__('commands.device:rename.errors.failed'));
            $this->line($e->getMessage(), verbosity: 'v');

            return 1;
        }

        $this->info(__('commands.device:rename.renamed', ['old' => $old_hostname, 'new' => $new_hostname]));

        return 0;
    }
}
