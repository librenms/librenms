<?php

namespace App\Listeners;

use Librenms\Config;
use App\Events\CreatingDevice;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostIpExistsException;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Exceptions\HostUnreachablePingException;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Exceptions\InvalidPortAssocModeException;
use LibreNMS\Exceptions\LockException;
use LibreNMS\Exceptions\SnmpVersionUnsupportedException;

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

        foreach ($snmpvers as $snmpver) {
            if ($snmpver === "v3") {
                // Try each set of parameters from config
                $event->device->snmpver = $snmpver;
                $v3Array = Config::get('snmp.v3');
                if ($event->device->snmpV3AuthSet()) {
                    array_unshift($v3, [
                        'authlevel'     => (isset($event->device->authlevel) ? $event->device->authlevel : 'noAuthNoPriv'),
                        'authname'      => (isset($event->device->authname) ? $event->device->authname : 'root'),
                        'authpass'      => (isset($event->device->authpass) ? $event->device->authpass : ''),
                        'authalgo'      => (isset($event->device->authalgo) ? $event->device->authalgo : 'MD5'),
                        'cryptopass'    => (isset($event->device->cryptopass) ? $event->device->cryptopass : ''),
                        'cryptoalgo'    => (isset($event->device->cryptoalgo) ? $event->device->cryptoalgo : 'AES')
                    ]);
                }
                foreach ($v3Array as $v3) {
                    $event->device->authlevel = $v3['authlevel'];
                    $event->device->authname = $v3['authname'];
                    $event->device->authpass = $v3['authpass'];
                    $event->device->authalgo = $v3['authalgo'];
                    $event->device->cryptopass = $v3['cryptopass'];
                    $event->device->cryptoalgo = $v3['cryptoalgo'];

                    if ($event->device->force_add === true || $event->device->isSNMPable()) {
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
    
                    if ($event->device->force_add === true || $event->device->isSNMPable()) {
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
        if (host_exists($event->device->hostname)) {
            throw new HostExistsException("Already have host {$event->device->hostname}");
        }
        
        if (!is_numeric($event->device->port_association_mode)) {
            $event->device->port_association_mode = ($event->device->port_association_mode ?: 'ifIndex');
            
            try {
                $event->device->port_association_mode = \App\Models\PortAssociationMode::where('name', $event->device->port_association_mode)->first()->pom_id;
            } catch (\Exception $e) {
                throw new InvalidPortAssocModeException("Invalid port_association_mode id '{$event->device->port_association_mode}'. Valid modes are: " . join(', ', get_port_assoc_modes()));
            }
        }

        // Valid port assoc mode ID
        if (!\App\Models\PortAssociationMode::exists($event->device->port_association_mode)) {
            throw new InvalidPortAssocModeException("Invalid port_association_mode id '{$event->device->port_association_mode}'. Valid modes are: " . join(', ', get_port_assoc_modes()));
        }
        // check if we have the host by IP
        if (Config::get('addhost_alwayscheckip') === true) {
            $ip = gethostbyname($event->device->hostname);
        } else {
            $ip = $event->device->hostname;
        }
        if ($event->device->force_add !== true && ip_exists($ip)) {
            throw new HostIpExistsException("Already have host with this IP {$event->device->hostname}");
        }

        // Test reachability
        if (!$event->device->force_add) {
            $address_family = snmpTransportToAddressFamily($event->device->transport);
            $ping_result = isPingable($event->device->hostname, $address_family);
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
            if (host_exists($event->device->hostname, $snmphost)) {
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
}
