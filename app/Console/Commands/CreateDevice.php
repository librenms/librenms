<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;

class CreateDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:create 
    {hostname} 
    {port=161} 
    {--community= : SNMP Community string}
    {--snmpver= : SNMP version [v1, v2c, v3]}
    {--transport= : Transport [udp, udp6, tcp, tcp6]}
    {--port_association_mode= : Port association mode}
    {--force_add : Forces the device to be added by skipping the icmp and snmp check against the host.}
    {--group= : Allows you to add a device to be pinned to a specific poller when using distributed polling. X can be any number associated with a poller group}
    {--icmp : Add the host by only ICMP}
    {--authlevel= : SNMP V3 configurable setting}
    {--authname= : SNMP V3 configurable setting}
    {--authpass= : SNMP V3 configurable setting}
    {--authalgo= : SNMP V3 configurable setting}
    {--cryptopass= : SNMP V3 configurable setting}
    {--cryptoalgo= : SNMP V3 configurable setting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new device in LibreNMS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $deviceArray = [
            'hostname'  => $this->argument('hostname'),
            'port'      => $this->argument('port'),
            'status_reason' => '',
            'os' => 'generic'
        ];
        
        $customizable = [
            'port_association_mode' => 'port_association_mode',
            'snmpver'               => 'snmpver',
            'community'             => 'community',
            'icmp'                  => 'snmp_disable',
            'transport'             => 'transport',
            'group'                 => 'poller_group',
            'force_add'             => 'force_add',
            'authlevel'             => 'authlevel',
            'authname'              => 'authname',
            'authpass'              => 'authpass',
            'authalgo'              => 'authalgo',
            'cryptopass'            => 'cryptopass',
            'cryptoalgo'            => 'cryptoalgo',
        ];
        
        foreach ($customizable as $key => $value) {
            if ($this->option($key) != null) {
                $deviceArray[$value] = $this->option($key);
            }
        }
        
        try {
            $device = Device::create($deviceArray);
            $this->info("Created device {$device->hostname}");
            $this->line(json_encode($device));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getFile() . ': ' . $e->getLine());
            }
            return 1;
        }

        return 0;
    }
}
