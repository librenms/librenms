<?php

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

use App\Models\Device;
use LibreNMS\Exceptions\HostUnreachableException;
use Symfony\Component\Process\Process;

Artisan::command('device:rename 
    {old hostname : ' . __('The existing hostname, IP, or device id') . '}
    {new hostname : ' . __('The new hostname or IP') . '}
    ', function () {
    /** @var \Illuminate\Console\Command $this */
    (new Process([
        base_path('renamehost.php'),
        $this->argument('old hostname'),
        $this->argument('new hostname'),
    ]))->setTty(true)->run();
})->describe(__('Rename a device, this can be used to change the hostname or IP of a device'));

Artisan::command('device:add
    {hostname : Hostname or IP to add}
    {--v1 : ' . __('Use SNMP v1') . '}
    {--v2c : ' . __('Use SNMP v2c') . '}
    {--v3 : ' . __('Use SNMP v3') . '}
    {--f|force : ' . __('Just add the device, do not make any safety checks') . '}
    {--g|group= : ' . __('Poller group (for distributed polling)') . '}
    {--b|ping-fallback : ' . __('Add the device as ping only if it does not respond to SNMP') . '}
    {--p|port-association-mode=ifIndex : ' . __('Sets how ports are mapped :modes, ifName is suggested for Linux/Unix', ['modes' => '[ifIndex, ifName, ifDescr, ifAlias]']) . '}
    {--c|community= : ' . __('SNMP v1 or v2 community') . '}
    {--t|transport=udp : ' . __('Transport to connect to the device') . ' [udp, udp6, tcp, tcp6]}
    {--r|port=161 : ' . __('SNMP transport port') . '}
    {--u|security-name=root : ' . __('SNMPv3 security username') . '}
    {--A|auth-password= : ' . __('SNMPv3 authentication password') . '}
    {--a|auth-protocol=md5 : ' . __('SNMPv3 authentication protocol') . ' [md5, sha, sha-512, sha-384, sha-256, sha-224]}
    {--x|privacy-protocol=aes : ' . __('SNMPv3 privacy protocol') . ' [des, aes]}
    {--X|privacy-password= : ' . __('SNMPv3 privacy password') . '}
    {--P|ping-only : ' . __('Add a ping only device') . '}
    {--o|os=ping : ' . __('Ping only: specify OS') . '}
    {--w|hardware= : ' . __('Ping only: specify hardware') . '}
    {--d|debug : ' . __('Enable debug output') . '}
    ', function () {
    /** @var \Illuminate\Console\Command $this */
    // Value Checks
    if (!in_array($this->option('port-association-mode'), ['ifIndex', 'ifName', 'ifDescr', 'ifAlias'])) {
        $this->error(__('Invalid port association mode'));
    }

    if (!in_array($this->option('transport'), ['udp', 'udp6', 'tcp', 'tcp6'])) {
        $this->error(__('Invalid SNMP transport'));
    }

    if (!in_array($this->option('auth-protocol'), ['md5', 'sha', 'sha-512', 'sha-384', 'sha-256', 'sha-224'])) {
        $this->error(__('Invalid authentication protocol'));
    }

    if (!in_array($this->option('privacy-protocol'), ['des', 'aes'])) {
        $this->error(__('Invalid privacy protocol'));
    }

    $port = (int)$this->option('port');
    if ($port < 1 || $port > 65535) {
        $this->error(__('Port should be 1-65535'));
    }

    // build additional
    $additional = [
        'os' => $this->option('os'),
        'hardware' => $this->option('hardware'),
    ];
    if ($this->option('ping-only')) {
        $additional['snmp_disable'] = 1;
    } elseif ($this->option('ping-fallback')) {
        $additional['ping_fallback'] = 1;
    }

    global $config;

    if ($this->option('community')) {
        array_unshift($config['snmp']['community'], $this->option('community'));
    }
    $auth = $this->option('auth-password');
    $priv = $this->option('privacy-password');
    array_unshift($config['snmp']['v3'], [
        'authlevel'  => ($auth ? 'auth' : 'noAuth') . (($priv && $auth) ? 'Priv' : 'NoPriv'),
        'authname'   => $this->option('security-name'),
        'authpass'   => $this->option('auth-password'),
        'authalgo'   => $this->option('auth-protocol'),
        'cryptopass' => $this->option('privacy-password'),
        'cryptoalgo' => $this->option('privacy-protocol'),
    ]);

    try {
        $init_modules = [];
        include(base_path('includes/init.php'));
        $device_id = addHost(
            $this->argument('hostname'),
            $this->option('v3') ? 'v3' : ($this->option('v2c') ? 'v2c' : ($this->option('v1') ? 'v1' : '')),
            $port,
            $this->option('transport'),
            $this->option('group'),
            $this->option('force'),
            $this->option('port-association-mode'),
            $additional
        );
        $hostname = Device::where('device_id', $device_id)->value('hostname');
        echo "Added device $hostname ($device_id)\n";
        return 0;
    } catch (HostUnreachableException $e) {
        $this->error($e->getMessage() . PHP_EOL . implode(PHP_EOL, $e->getReasons()));
        return 1;
    } catch (Exception $e) {
        $this->error($e->getMessage());
        return 3;
    }
})->describe('Add a new device');

Artisan::command('device:remove {hostname : ' . __('Hostname, IP, or device id to remove') . '}', function () {
    /** @var \Illuminate\Console\Command $this */
    (new Process([
        base_path('delhost.php'),
        $this->argument('hostname'),
    ]))->setTty(true)->run();
})->describe('Remove a device');


Artisan::command('ping {--d|debug} {groups?* : ' . __('Optional List of distributed poller groups to poll') . '}', function () {
    $this->alert("Do not use this command yet, use ./ping.php");
//    PingCheck::dispatch(new PingCheck($this->argument('groups')));
})->describe(__('Check if devices are up or down via icmp'));

Artisan::command('update', function () {
    (new Process(base_path('daily.sh')))->setTty(true)->run();
})->describe(__('Update LibreNMS and run maintenance routines'));
