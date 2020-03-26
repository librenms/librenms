<?php

namespace App\Listeners;

use App\Events\CreatingDevice;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Database\Query\Builder;
use Librenms\Config;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostIpExistsException;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Exceptions\HostUnreachablePingException;
use LibreNMS\Exceptions\InvalidPortAssocModeException;
use LibreNMS\Exceptions\SnmpVersionUnsupportedException;
use LibreNMS\Util\DeviceHelper;

/**
 * This Listener Will highjack creating a new Device
 * and validate the request. This should make things eaiser in the future with apis /UI
 *
 */
class ValidateSNMP
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CreatingDevice  $event
     * @return void
     */
    public function handle(CreatingDevice $event)
    {
        $event = $this->preChecks($event);

        $event->device->sysName ?: $event->device->hostname;
        $event->device->hardware ?: null;
        $event->device->status_reason = '';
        $event->device->transport ?: 'udp';
        $event->device->port ?: 161;
        
        if (isset($event->device->snmp_disable) && $event->device->snmp_disable) {
            $event->device->community = '';
            $event->device->force_add = true;
            $event->device->os ?: 'ping';

            return $this->cleanUpModel($event);
        }

        $event->device->os ?: 'generic';

        // if $snmpver isn't set, try each version of snmp
        if (empty($event->device->snmpver)) {
            $snmpvers = Config::get('snmp.version');
        } else {
            $snmpvers = array($event->device->snmpver);
        }

        $host_unreachable_exception = new HostUnreachableException("Could not connect to {$event->device->hostname}, please check the snmp details and snmp reachability");

        $helper = new DeviceHelper($event->device);

        foreach ($snmpvers as $snmpver) {
            if ($snmpver === "v3") {
                // Try each set of parameters from config
                $event->device->snmpver = $snmpver;
                $v3Array = Config::get('snmp.v3');
                if (isset($event->device->authlevel)) {
                    array_unshift($v3Array, [
                        'authlevel'     => $event->device->authlevel,
                        'authname'      => $event->device->authname ?? 'root',
                        'authpass'      => $event->device->authpass ?? '',
                        'authalgo'      => $event->device->authalgo ?? 'MD5',
                        'cryptopass'    => $event->device->cryptopass ?? '',
                        'cryptoalgo'    => $event->device->cryptoalgo ?? 'AES',
                    ]);
                }
                foreach ($v3Array as $v3) {
                    $event->device->authlevel = $v3['authlevel'];
                    $event->device->authname = $v3['authname'];
                    $event->device->authpass = $v3['authpass'];
                    $event->device->authalgo = $v3['authalgo'];
                    $event->device->cryptopass = $v3['cryptopass'];
                    $event->device->cryptoalgo = $v3['cryptoalgo'];

                    if ($event->device->force_add === true || $helper->checkSNMP()) {
                        return $this->cleanUpModel($event);
                    } else {
                        $host_unreachable_exception->addReason("SNMP $snmpver: No reply with credentials " . $v3['authname'] . "/" . $v3['authlevel']);
                    }
                }
            } elseif ($snmpver === "v2c" || $snmpver === "v1") {
                // try each community from config
                $communityStrings = Config::get('snmp.community');
                if ($event->device->community) {
                    array_unshift($communityStrings, $event->device->community); // If community is set already add it to array
                }

                foreach ($communityStrings as $community) {
                    $event->device->community = $community;
                    $event->device->snmpver = $snmpver;

                    if ($event->device->force_add === true || $helper->checkSNMP()) {
                        return $this->cleanUpModel($event);
                    } else {
                        $host_unreachable_exception->addReason("SNMP $snmpver: No reply with community $community");
                    }
                }
            } else {
                throw new SnmpVersionUnsupportedException("Unsupported SNMP Version \"$snmpver\", must be v1, v2c, or v3");
            }
        }

        // This seems like it should be a config option?
        if (isset($event->device->ping_fallback) && $event->device->ping_fallback == 1) {
            $event->device->snmp_disable = 1;
            $event->device->os = "ping";
            return $this->cleanUpModel($event);
        }

        throw $host_unreachable_exception;
    }

    private function preChecks($event)
    {
        if ($this->deviceExists($event->device->hostname)) {
            throw new HostExistsException("Already have host {$event->device->hostname}");
        }
        
        if (!is_numeric($event->device->port_association_mode)) {
            $event->device->port_association_mode = array_search($event->device->port_association_mode ?: 'ifIndex', Port::associationModes());
        }

        // Valid port assoc mode ID
        if (!array_key_exists($event->device->port_association_mode, Port::associationModes())) {
            throw new InvalidPortAssocModeException("Invalid port_association_mode id '{$event->device->port_association_mode}'. Valid modes are: " . join(', ', get_port_assoc_modes()));
        }
        // check if we have the host by IP
        if (Config::get('addhost_alwayscheckip') === true) {
            $ip = gethostbyname($event->device->hostname);
        } else {
            $ip = $event->device->hostname;
        }

        if ($event->device->force_add !== true && $device = Device::findByIp($ip)) {
            $message = "Cannot add {$event->device->hostname}, already have device with this IP $ip";
            if ($ip != $device->hostname) {
                $message .= " ($device->hostname)";
            }
            $message .= '. You may force add to ignore this.';
            throw new HostIpExistsException($message);
        }

        // Test reachability
        if (!$event->device->force_add) {
            $address_family = $this->snmpTransportToAddressFamily($event->device->transport);
            $helper = new DeviceHelper($event->device);
            $ping_result = $helper->checkPing($address_family);
            if (!$ping_result['result']) {
                throw new HostUnreachablePingException("Could not ping {$event->device->hostname}");
            }
        }

        return $event;
    }
    /**
     * Purge any group fields that manipulate the model in the listener
     */
    private function cleanUpModel($event)
    {
        $discovery = new \LibreNMS\Discovery;
        if ($event->device->force_add !== true) {
            $event->device->os = $discovery->os($event->device);

            $snmphost = \LibreNMS\SNMP::get($event->device, "sysName.0", "-Oqv", "SNMPv2-MIB");
            if ($this->deviceExists($event->device->hostname, $snmphost)) {
                throw new HostExistsException("Already have host {$event->device->hostname} ($snmphost) due to duplicate sysName");
            }
        }

        if (isset($event->device->force_add)) {
            unset($event->device->force_add);
        }

        if (isset($event->device->ping_fallback)) {
            unset($event->device->ping_fallback);
        }

        $event->device->status = '1';

        return $event;
    }

    /**
     * Checks if the $hostname provided exists in the DB already
     *
     * @param string $hostname The hostname to check for
     * @param string $sysName The sysName to check
     * @return bool true if hostname already exists
     *              false if hostname doesn't exist
     */
    private function deviceExists($hostname, $sysName = null)
    {
        $check_sysName = !empty($sysName) && !Config::get('allow_duplicate_sysName');

        return Device::query()->where('hostname', $hostname)
            ->when($check_sysName, function ($query) use ($hostname, $sysName) {
                /** @var Builder $query */
                $query->orWhere('sysName', $hostname);

                if (!empty(Config::get('mydomain'))) {
                    $full_sysname = rtrim($sysName, '.') . '.' . Config::get('mydomain');
                    $query->orWhere('sysName', $full_sysname);
                }
            })->exists();
    }

    /**
     * Try to determine the address family (IPv4 or IPv6) associated with an SNMP
     * transport specifier (like "udp", "udp6", etc.).
     *
     * @param string $transport The SNMP transport specifier, for example "udp",
     *                          "udp6", "tcp", or "tcp6". See `man snmpcmd`,
     *                          section "Agent Specification" for a full list.
     *
     * @return int The address family associated with the given transport
     *             specifier: AF_INET for IPv4 (or local connections not associated
     *             with an IP stack), AF_INET6 for IPv6.
     */
    private function snmpTransportToAddressFamily($transport)
    {
        if (!isset($transport)) {
            $transport = 'udp';
        }

        $ipv6_snmp_transport_specifiers = array('udp6', 'udpv6', 'udpipv6', 'tcp6', 'tcpv6', 'tcpipv6');

        if (in_array($transport, $ipv6_snmp_transport_specifiers)) {
            return AF_INET6;
        } else {
            return AF_INET;
        }
    }
}
