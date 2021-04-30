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
use LibreNMS\Util\Debug;
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
})->purpose(__('Rename a device, this can be used to change the hostname or IP of a device'));

Artisan::command('device:add
    {device spec : Hostname or IP to add}
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
    {--s|sysName= : ' . __('Ping only: specify sysName') . '}
', function () {
    /** @var \Illuminate\Console\Command $this */
    // Value Checks
    if (! in_array($this->option('port-association-mode'), ['ifIndex', 'ifName', 'ifDescr', 'ifAlias'])) {
        $this->error(__('Invalid port association mode'));
    }

    if (! in_array($this->option('transport'), ['udp', 'udp6', 'tcp', 'tcp6'])) {
        $this->error(__('Invalid SNMP transport'));
    }

    if (! in_array($this->option('auth-protocol'), ['md5', 'sha', 'sha-512', 'sha-384', 'sha-256', 'sha-224'])) {
        $this->error(__('Invalid authentication protocol'));
    }

    if (! in_array($this->option('privacy-protocol'), ['des', 'aes'])) {
        $this->error(__('Invalid privacy protocol'));
    }

    $port = (int) $this->option('port');
    if ($port < 1 || $port > 65535) {
        $this->error(__('Port should be 1-65535'));
    }

    // build additional
    $additional = [
        'os' => $this->option('os'),
        'hardware' => $this->option('hardware'),
        'sysName' => $this->option('sysName'),
    ];
    if ($this->option('ping-only')) {
        $additional['snmp_disable'] = 1;
    } elseif ($this->option('ping-fallback')) {
        $additional['ping_fallback'] = 1;
    }

    if ($this->option('community')) {
        $community_config = \LibreNMS\Config::get('snmp.community');
        array_unshift($community_config, $this->option('community'));
        \LibreNMS\Config::set('snmp.community', $community_config);
    }
    $auth = $this->option('auth-password');
    $priv = $this->option('privacy-password');
    $v3_config = \LibreNMS\Config::get('snmp.v3');
    array_unshift($v3_config, [
        'authlevel'  => ($auth ? 'auth' : 'noAuth') . (($priv && $auth) ? 'Priv' : 'NoPriv'),
        'authname'   => $this->option('security-name'),
        'authpass'   => $this->option('auth-password'),
        'authalgo'   => $this->option('auth-protocol'),
        'cryptopass' => $this->option('privacy-password'),
        'cryptoalgo' => $this->option('privacy-protocol'),
    ]);
    \LibreNMS\Config::set('snmp.v3', $v3_config);

    try {
        $init_modules = [];
        include base_path('includes/init.php');

        if (($verbosity = $this->getOutput()->getVerbosity()) >= 128) {
            Debug::set();
            if ($verbosity >= 256) {
                global $verbose;
                $verbose = true;
            }
        }

        $device_id = addHost(
            $this->argument('device spec'),
            $this->option('v3') ? 'v3' : ($this->option('v2c') ? 'v2c' : ($this->option('v1') ? 'v1' : '')),
            $port,
            $this->option('transport'),
            $this->option('group'),
            $this->option('force'),
            $this->option('port-association-mode'),
            $additional
        );
        $hostname = Device::where('device_id', $device_id)->value('hostname');
        $this->info("Added device $hostname ($device_id)");

        return 0;
    } catch (HostUnreachableException $e) {
        $this->error($e->getMessage() . PHP_EOL . implode(PHP_EOL, $e->getReasons()));

        return 1;
    } catch (Exception $e) {
        $this->error($e->getMessage());

        return 3;
    }
})->purpose('Add a new device');

Artisan::command('device:remove
    {device spec : ' . __('Hostname, IP, or device id to remove') . '}
', function () {
    /** @var \Illuminate\Console\Command $this */
    (new Process([
        base_path('delhost.php'),
        $this->argument('device spec'),
    ]))->setTty(true)->run();
})->purpose('Remove a device');

Artisan::command('update', function () {
    (new Process([base_path('daily.sh')]))->setTty(true)->run();
})->purpose(__('Update LibreNMS and run maintenance routines'));

Artisan::command('poller:ping
    {groups?* : ' . __('Optional List of distributed poller groups to poll') . '}
', function () {
//    PingCheck::dispatch(new PingCheck($this->argument('groups')));
    $command = [base_path('ping.php')];
    if ($this->argument('groups')) {
        $command[] = '-g';
        $command[] = implode(',', $this->argument('groups'));
    }
    if (($verbosity = $this->getOutput()->getVerbosity()) >= 128) {
        $command[] = '-d';
        if ($verbosity >= 256) {
            $command[] = '-v';
        }
    }
    (new Process($command))->setTty(true)->run();
})->purpose(__('Check if devices are up or down via icmp'));

Artisan::command('poller:discovery
    {device spec : ' . __('Device spec to discover: device_id, hostname, wildcard, odd, even, all, new') . '}
    {--o|os= : ' . __('Only devices with the specified operating system') . '}
    {--t|type= : ' . __('Only devices with the specified type') . '}
    {--m|modules= : ' . __('Specify single module to be run. Comma separate modules, submodules may be added with /') . '}
', function () {
    $command = [base_path('discovery.php'), '-h', $this->argument('device spec')];
    if ($this->option('os')) {
        $command[] = '-o';
        $command[] = $this->option('os');
    }
    if ($this->option('type')) {
        $command[] = '-t';
        $command[] = $this->option('type');
    }
    if ($this->option('modules')) {
        $command[] = '-m';
        $command[] = $this->option('modules');
    }
    if (($verbosity = $this->getOutput()->getVerbosity()) >= 128) {
        $command[] = '-d';
        if ($verbosity >= 256) {
            $command[] = '-v';
        }
    }
    (new Process($command))->setTty(true)->run();
})->purpose(__('Discover information about existing devices, defines what will be polled'));

Artisan::command('poller:poll
    {device spec : ' . __('Device spec to poll: device_id, hostname, wildcard, odd, even, all') . '}
    {--m|modules= : ' . __('Specify single module to be run. Comma separate modules, submodules may be added with /') . '}
    {--x|no-data : ' . __('Do not update datastores (RRD, InfluxDB, etc)') . '}
', function () {
    $command = [base_path('poller.php'), '-h', $this->argument('device spec')];
    if ($this->option('no-data')) {
        array_push($command, '-r', '-f', '-p');
    }
    if ($this->option('modules')) {
        $command[] = '-m';
        $command[] = $this->option('modules');
    }
    if (($verbosity = $this->getOutput()->getVerbosity()) >= 128) {
        $command[] = '-d';
        if ($verbosity >= 256) {
            $command[] = '-v';
        }
    }
    (new Process($command))->setTty(true)->run();
})->purpose(__('Poll data from devices as defined by discovery'));

Artisan::command('poller:alerts', function () {
    $command = [base_path('alerts.php')];
    if (($verbosity = $this->getOutput()->getVerbosity()) >= 128) {
        $command[] = '-d';
        if ($verbosity >= 256) {
            $command[] = '-v';
        }
    }

    (new Process($command))->setTty(true)->run();
})->purpose(__('Check for any pending alerts and deliver them via defined transports'));

Artisan::command('poller:billing
    {bill id? : ' . __('The bill id to poll') . '}
', function () {
    /** @var \Illuminate\Console\Command $this */
    $command = [base_path('poll-billing.php')];
    if ($this->argument('bill id')) {
        $command[] = '-b';
        $command[] = $this->argument('bill id');
    }

    if (($verbosity = $this->getOutput()->getVerbosity()) >= 128) {
        $command[] = '-d';
        if ($verbosity >= 256) {
            $command[] = '-v';
        }
    }
    (new Process($command))->setTty(true)->run();
})->purpose(__('Collect billing data'));

Artisan::command('poller:services
    {device spec : ' . __('Device spec to poll: device_id, hostname, wildcard, all') . '}
    {--x|no-data : ' . __('Do not update datastores (RRD, InfluxDB, etc)') . '}
', function () {
    /** @var \Illuminate\Console\Command $this */
    $command = [base_path('check-services.php')];
    if ($this->option('no-data')) {
        array_push($command, '-r', '-f', '-p');
    }
    if ($this->argument('device spec') !== 'all') {
        $command[] = '-h';
        $command[] = $this->argument('device spec');
    }

    if (($verbosity = $this->getOutput()->getVerbosity()) >= 128) {
        $command[] = '-d';
        if ($verbosity >= 256) {
            $command[] = '-v';
        }
    }
    (new Process($command))->setTty(true)->run();
})->purpose(__('Update LibreNMS and run maintenance routines'));

Artisan::command('poller:billing-calculate
    {--c|clear-history : ' . __('Delete all billing history') . '}
', function () {
    /** @var \Illuminate\Console\Command $this */
    $command = [base_path('billing-calculate.php')];
    if ($this->option('clear-history')) {
        $command[] = '-r';
    }

    (new Process($command))->setTty(true)->run();
})->purpose(__('Run billing calculations'));

Artisan::command('scan
    {network?* : ' . __('CIDR notation network(s) to scan, can be ommited if \'nets\' config is set') . '}
    {--P|ping-only : ' . __('Add the device as a ping only device if it replies to ping but not SNMP') . '}
    {--t|threads=32 : ' . __('How many IPs to scan at a time, more will increase the scan speed, but could overload your system') . '}
    {--l|legend : ' . __('Print the legend') . '}
', function () {
    /** @var \Illuminate\Console\Command $this */
    $command = [base_path('snmp-scan.py')];

    if (empty($this->argument('network')) && ! Config::has('nets')) {
        $this->error(__('Network is required if \'nets\' is not set in the config'));

        return 1;
    }

    if ($this->option('ping-only')) {
        $command[] = '-P';
    }

    $command[] = '-t';
    $command[] = $this->option('threads');

    if ($this->option('legend')) {
        $command[] = '-l';
    }

    $verbosity = $this->getOutput()->getVerbosity();
    if ($verbosity >= 64) {
        $command[] = '-v';
        if ($verbosity >= 128) {
            $command[] = '-v';
            if ($verbosity >= 256) {
                $command[] = '-v';
            }
        }
    }

    $command = array_merge($command, $this->argument('network'));

    (new Process($command))->setTty(true)->run();
})->purpose(__('Scan the network for hosts and try to add them to LibreNMS'));
