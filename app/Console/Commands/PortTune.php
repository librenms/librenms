<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\Rrd;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\Console\Input\InputArgument;

class PortTune extends LnmsCommand
{
    protected $name = 'port:tune';

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('device spec', InputArgument::REQUIRED);
        $this->addArgument('ifname', InputArgument::OPTIONAL);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->configureOutputOptions();

        $port_spec = $this->getPortSpec();

        $devices = Device::whereDeviceSpec($this->argument('device spec'))->get();

        foreach ($devices as $device) {
            $display_name = $device->displayName();
            if ($display_name != $device->hostname) {
                $display_name .= "($device->hostname)";
            }
            $this->info(__('commands.port:tune.device', ['device' => $display_name]));

            $ports = $device->ports()->when($port_spec, function (Builder $query, $port_spec) {
                $query->where('ifName', 'like', $port_spec);
            })->get();

            foreach ($ports as $port) {
                $this->line(' ' . __('commands.port:tune.port', ['port' => $port->ifName]));
                $rrdfile = Rrd::name($device->hostname, Rrd::portName($port->port_id));
                Rrd::tune('port', $rrdfile, $port->ifSpeed);
            }
        }

        Rrd::terminate();

        return 0;
    }

    private function getPortSpec(): string
    {
        $port_spec = $this->argument('ifname');

        if (empty($port_spec) || $port_spec == 'all') {
            return '';
        }

        return str_replace('*', '%', $port_spec);
    }
}
