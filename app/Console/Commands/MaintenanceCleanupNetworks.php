<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Ipv4Network;
use Symfony\Component\Console\Input\InputOption;

class MaintenanceCleanupNetworks extends LnmsCommand
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'maintenance:cleanup-networks';

    public function __construct()
    {
        parent::__construct();

        $this->addOption('force', null, InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        if (LibrenmsConfig::get('networks_purge') === true || $force) {
            $oldNetworks = Ipv4Network::withCount('ipv4')->having('ipv4_count', 0)->pluck('ipv4_network_id');
            if ($oldNetworks->count() > 0) {
                $this->line(trans('commands.maintenance:cleanup-networks.delete', ['count' => $oldNetworks->count()]));
                Ipv4Network::whereIn('ipv4_network_id', $oldNetworks)->delete();
            }
        }

        return 0;
    }
}
