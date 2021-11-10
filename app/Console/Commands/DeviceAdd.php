<?php

namespace App\Console\Commands;

use App\Actions\Device\ValidateDeviceAndCreate;
use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\PollerGroup;
use App\Models\Secret;
use Exception;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostnameExistsException;
use LibreNMS\Exceptions\HostUnreachableException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeviceAdd extends LnmsCommand
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'device:add';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->optionValues = [
            'transport' => ['udp', 'udp6', 'tcp', 'tcp6'],
            'port-association-mode' => PortAssociationMode::getModes(...),
            'auth-protocol' => \LibreNMS\SNMPCapabilities::supportedAuthAlgorithms(...),
            'privacy-protocol' => \LibreNMS\SNMPCapabilities::supportedCryptoAlgorithms(...),
        ];

        $this->optionDefaults = [
            'port' => fn () => LibrenmsConfig::get('snmp.port', 161),
            'transport' => fn () => LibrenmsConfig::get('snmp.transports.0', 'udp'),
            'poller-group' => fn () => LibrenmsConfig::get('default_poller_group'),
            'port-association-mode' => fn () => LibrenmsConfig::get('default_port_association_mode'),

        ];

        $this->addArgument('device spec', InputArgument::REQUIRED);
        $this->addOption('v1', '1', InputOption::VALUE_NONE);
        $this->addOption('v2c', '2', InputOption::VALUE_NONE);
        $this->addOption('v3', '3', InputOption::VALUE_NONE);
        $this->addOption('community', 'c', InputOption::VALUE_REQUIRED);
        $this->addOption('port', 'r', InputOption::VALUE_REQUIRED);
        $this->addOption('transport', 't', InputOption::VALUE_REQUIRED);
        $this->addOption('display-name', 'd', InputOption::VALUE_REQUIRED);
        $this->addOption('security-name', 'u', InputOption::VALUE_REQUIRED, '', 'root');
        $this->addOption('auth-password', 'A', InputOption::VALUE_REQUIRED);
        $this->addOption('auth-protocol', 'a', InputOption::VALUE_REQUIRED, '', 'MD5');
        $this->addOption('privacy-password', 'X', InputOption::VALUE_REQUIRED);
        $this->addOption('privacy-protocol', 'x', InputOption::VALUE_REQUIRED, '', 'AES');
        $this->addOption('force', 'f', InputOption::VALUE_NONE);
        $this->addOption('ping-fallback', 'b', InputOption::VALUE_NONE);
        $this->addOption('poller-group', 'g', InputOption::VALUE_REQUIRED);
        $this->addOption('port-association-mode', 'p', InputOption::VALUE_REQUIRED);
        $this->addOption('ping-only', 'P', InputOption::VALUE_NONE);
        $this->addOption('os', 'o', InputOption::VALUE_REQUIRED);
        $this->addOption('hardware', 'w', InputOption::VALUE_REQUIRED);
        $this->addOption('sysName', 's', InputOption::VALUE_REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->validate([
            'port' => 'numeric|between:1,65535',
            'poller-group' => ['numeric', Rule::in(PollerGroup::pluck('id')->prepend(0))],
        ]);

        $auth = $this->option('auth-password');
        $priv = $this->option('privacy-password');
        $device = new Device([
            'hostname' => $this->argument('device spec'),
            'display_template' => $this->option('display-name'),
            'poller_group' => $this->option('poller-group'),
            'port_association_mode' => PortAssociationMode::getId($this->option('port-association-mode')),
        ]);

        $pollingMethods = collect();

        // ICMP polling method is always added
        $pollingMethods->push(new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => false,
        ]));

        if ($this->option('ping-only')) {
            $device->os = $this->option('os');
            $device->hardware = $this->option('hardware');
            $device->sysName = $this->option('sysName');
        } else {
            // SNMP polling method is added if not ping-only
            $snmpPollingMethod = new DevicePollingMethod([
                'method_type' => PollingMethodType::Snmp,
                'enabled' => true,
                'affects_availability' => true,
                'settings' => array_filter([
                    'port' => $this->option('port'),
                    'transport' => $this->option('transport'),
                ]),
            ]);

            // Build SnmpSecret if custom credentials were provided
            $snmpver = $this->option('v3') ? 'v3' : ($this->option('v2c') ? 'v2c' : ($this->option('v1') ? 'v1' : ''));
            $community = $this->option('community');

            if ($snmpver || $community || $auth || $priv) {
                $snmpData = [
                    'version' => $snmpver ?: 'v2c',
                    'community' => $community,
                    'authlevel' => ($auth ? 'auth' : 'noAuth') . (($priv && $auth) ? 'Priv' : 'NoPriv'),
                    'authname' => $this->option('security-name') ?: 'root',
                    'authpass' => $auth,
                    'authalgo' => $this->option('auth-protocol') ?: 'MD5',
                    'cryptopass' => $priv,
                    'cryptoalgo' => $this->option('privacy-protocol') ?: 'AES',
                ];

                $secret = new Secret([
                    'secret_type' => SecretType::Snmp,
                    'description' => 'SNMP ' . $device->hostname,
                    'default' => false,
                    'data' => $snmpData,
                ]);
                $snmpPollingMethod->setRelation('secret', $secret);
            }

            $pollingMethods->push($snmpPollingMethod);
        }

        $device->setRelation('pollingMethods', $pollingMethods);

        try {
            $result = (new ValidateDeviceAndCreate($device, $this->option('force'), $this->option('ping-fallback')))->execute();

            if (! $result) {
                $this->error(trans('commands.device:add.messages.save_failed', ['hostname' => $device->hostname]));

                return 4;
            }

            $this->info(trans('commands.device:add.messages.added', ['hostname' => $device->hostname, 'device_id' => $device->device_id]));

            return 0;
        } catch (HostUnreachableException $e) {
            // host unreachable errors
            $this->error($e->getMessage() . PHP_EOL . implode(PHP_EOL, $e->getReasons()));
            $this->line(trans('commands.device:add.messages.try_force'));

            return 2;
        } catch (HostExistsException $e) {
            // host exists errors
            $this->error($e->getMessage());

            if (! $e instanceof HostnameExistsException) {
                $this->line(trans('commands.device:add.messages.try_force'));
            }

            return 3;
        } catch (Exception $e) {
            // other errors?
            $this->error("Error: $e");

            return 1;
        }
    }
}
