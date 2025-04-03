<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Application;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use App\Models\Sensor;
use App\Models\Storage;
use LibreNMS\Config;
use Symfony\Component\Console\Input\InputOption;

class DeviceList extends LnmsCommand
{
    protected $name = 'device:list';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addOption('json', 'j', InputOption::VALUE_NONE);
        $this->addOption('apps', 'a', InputOption::VALUE_NONE);
        $this->addOption('ports', 'p', InputOption::VALUE_NONE);
        $this->addOption('ip', 'i', InputOption::VALUE_NONE);
        $this->addOption('storage', 's', InputOption::VALUE_NONE);
        $this->addOption('sensors', 'S', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $devices = Device::where('device_id', '>', 0)->get();

        if ($this->option('json')) {
            // skip this to we don't need to rebuild the returned array unless additional info is wanted
            if ($this->option('apps')||$this->option('ports')||$this->option('storage')||$this->option('sensors')) {
                $new_devices=array();
                foreach ($devices as $device) {
                    if ($this->option('apps')) {
                        $device['apps']=Application::where('device_id', $device['device_id'])->get();
                    }
                    if ($this->option('storage')) {
                        $device['storage']=Storage::where('device_id', $device['device_id'])->get();
                    }
                    if ($this->option('sensors')) {
                        $device['sensors']=Sensor::where('device_id', $device['device_id'])->get();
                    }
                    if ($this->option('ports')) {
                        $ports=Port::where('device_id', $device['device_id'])->get();
                        if ($this->option('ip')) {
                            $new_ports=array();
                            foreach ($ports as $port) {
                                $Ipv4Addresses=Ipv4Address::where('port_id', $port['port_id'])->get();
                                $Ipv6Addresses=Ipv6Address::where('port_id', $port['port_id'])->get();
                                $port['Ipv6Addresses']=$Ipv6Addresses;
                                $port['Ipv4Addresses']=$Ipv4Addresses;
                                $new_ports[]=$port;
                            }
                            $ports=$new_ports;
                        }
                        $device['ports']=$ports;
                    }
                    $new_devices[]=$device;
                }
                $devices=$new_devices;
            }
            echo json_encode($devices)."\n";
            return 0;
        }

        foreach ($devices as $device) {
            print $device['device_id'].','.$device['hostname']."\n";
        }

        return 0;
    }
}
