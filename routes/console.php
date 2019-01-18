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
    /** @var \Illuminate\Console\Command  $this */
    (new Process([
        base_path('renamehost.php'),
        $this->argument('old hostname'),
        $this->argument('new hostname'),
    ]))->setTty(true)->run();
})->describe(__('Rename a device, this can be used to change the hostname or IP of a device'));

Artisan::command('device:add
    {hostname : Hostname or IP to add}
    {--v1 : ' . __('Use SNMP v1') .'}
    {--v2c : ' . __('Use SNMP v2c') .'}
    {--v3 : ' . __('Use SNMP v3') .'}
    {--f|force : ' . __('Just add the device, do not make any safety checks') . '}
    {--g|group= : ' . __('Poller group (for distributed polling)') . '}
    {--P|ping-only : ' . __('Add a ping only device') .'}
    {--b|ping-fallback : ' . __('Add the device as ping only if it does not respond to SNMP') . '}
    {--p|port-association-mode=ifIndex : ' . __('Sets how ports are mapped :modes, ifName is suggested for Linux/Unix', ['modes' => '[ifIndex, ifName, ifDescr, ifAlias]']) . '}
    {--c|community= : ' . __('SNMP v1 or v2 community') .'}
    {--t|transport=udp : ' . __('Transport to connect to the device') .' [udp, udp6, tcp, tcp6]}
    {--r|port=162 : ' . __('SNMP transport port') .'}
    {--N|no-auth= : ' . __('No SNMPv3 authentication (skips credentials in config)') .'}
    {--u|security-name= : ' . __('SNMPv3 security username') .'}
    {--A|auth-password= : ' . __('SNMPv3 authentication password') .'}
    {--a|auth-protocol= : ' . __('SNMPv3 authentication protocol') .' [md5, sha, sha-512, sha-384, sha-256, sha-224]}
    {--x|privacy-protocol= : ' . __('SNMPv3 privacy protocol') .' [des, aes]}
    {--X|privacy-password= : ' . __('SNMPv3 privacy password') .'}
    {--o|os= : ' . __('Ping only: specify OS') .'}
    {--w|hardware= : ' . __('Ping only: specify hardware') .'}
    {--d|debug : ' . __('Enable debug output') . '}
    ', function () {
    /** @var \Illuminate\Console\Command  $this */
    $command = [base_path('addhost.php')];

    if ($this->hasOption('group')) {
        $command[] = '-g';
        $command[] = $this->option('group');
    }

    if ($this->hasOption('force')) {
        $command[] = '-f';
    }

    if ($this->hasOption('ping-fallback')) {
        $command[] = '-b';
    }

    if ($this->hasOption('port-association-mode')) {
        if (!in_array($this->option('port-association-mode'), ['ifIndex', 'ifName', 'ifDescr', 'ifAlias'])) {
            $this->error(__('Invalid port association mode'));
        }
        $command[] = '-p';
        $command[] = $this->option('port-association-mode');
    }

    if ($this->hasOption('ping-only')) {
        $command[] = '-P';
    }

    $command[] = $this->argument('hostname');

    if (!in_array($this->option('transport'), ['udp', 'udp6', 'tcp', 'tcp6'])) {
        $this->error(__('Invalid SNMP transport'));
    }
    if (!is_int($this->option('port'))) {
        $this->error(__('Port should be 1-65535'));
    }

    if ($this->hasOption('v3')) {
        if ($this->hasOption('no-auth')) {
            $command[] = 'nanp';
        } elseif ($this->hasOption('auth-protocol') || $this->hasOption('auth-password')) {
            if ($this->hasOption('privacy-protocol') || $this->hasOption('privacy-protocol')) {
                $command[] = 'ap';
            } else {
                $command[] = 'anp';
            }

            if (!$this->hasOption('security-name') || !$this->hasOption('auth-password')) {
                $this->error(__('Username and password required to use SNMPv3 authentication'));
            }
        } else {
            $command[] = 'any';
        }
        $command[] = $this->option('security-name');
        if ($this->hasOption('auth-password')) {

        }

        if ($this->hasOption('auth-username') || $this->hasOption('auth-password'))

//        if (!in_array($auth, ['any', 'noauthnopriv', 'authnopriv', 'authpriv'])) {
//            $this->error(__('Invalid auth option'));
//        }

        $command[] = 'v3';

    } elseif ($this->hasOption('ping-only')) {
        if ($this->hasOption('os')) {
            $command[] = $this->option('os');
        }
        if ($this->hasOption('hardware')) {
            $command[] = $this->option('hardware');
        }
    } else {
        if (!$this->hasOption('community')) {
            $this->error(__('Community is required for v1 or v2c'));
        }
        $command[] = $this->option('community');
        $command[] = $this->hasOption('v1') ? 'v1' : 'v2c';
    }

    if (!$this->hasOption('ping-only')) {
        $command[] = $this->option('port');
        $command[] = $this->option('transport');
    }
    $additional = [];

    try {
        $init_modules = [];
        include(base_path('includes/init.php'));
        $device_id = addHost(
            $this->option('hostname'),
            $this->option('hostname'),
            $this->option('port'),
            $this->option('transport'),
            $this->option('group'),
            $this->hasOption('force'),
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
    /** @var \Illuminate\Console\Command  $this */
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
