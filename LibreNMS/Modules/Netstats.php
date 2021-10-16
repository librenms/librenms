<?php
/**
 * Netstats.php
 *
 * Poll various netstats. IP, SNMP, ICMP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use LibreNMS\Interfaces\Polling\Netstats\IcmpNetstatsPolling;
use LibreNMS\Interfaces\Polling\Netstats\IpForwardNetstatsPolling;
use LibreNMS\Interfaces\Polling\Netstats\IpNetstatsPolling;
use LibreNMS\Interfaces\Polling\Netstats\SnmpNetstatsPolling;
use LibreNMS\Interfaces\Polling\Netstats\TcpNetstatsPolling;
use LibreNMS\Interfaces\Polling\Netstats\UdpNetstatsPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Netstats implements \LibreNMS\Interfaces\Module
{
    /**
     * @var string[][]
     */
    private $oids = [
        'icmp' => [
            'IP-MIB::icmpInMsgs.0',
            'IP-MIB::icmpOutMsgs.0',
            'IP-MIB::icmpInErrors.0',
            'IP-MIB::icmpOutErrors.0',
            'IP-MIB::icmpInEchos.0',
            'IP-MIB::icmpOutEchos.0',
            'IP-MIB::icmpInEchoReps.0',
            'IP-MIB::icmpOutEchoReps.0',
            'IP-MIB::icmpInDestUnreachs.0',
            'IP-MIB::icmpOutDestUnreachs.0',
            'IP-MIB::icmpInParmProbs.0',
            'IP-MIB::icmpInTimeExcds.0',
            'IP-MIB::icmpInSrcQuenchs.0',
            'IP-MIB::icmpInRedirects.0',
            'IP-MIB::icmpInTimestamps.0',
            'IP-MIB::icmpInTimestampReps.0',
            'IP-MIB::icmpInAddrMasks.0',
            'IP-MIB::icmpInAddrMaskReps.0',
            'IP-MIB::icmpOutTimeExcds.0',
            'IP-MIB::icmpOutParmProbs.0',
            'IP-MIB::icmpOutSrcQuenchs.0',
            'IP-MIB::icmpOutRedirects.0',
            'IP-MIB::icmpOutTimestamps.0',
            'IP-MIB::icmpOutTimestampReps.0',
            'IP-MIB::icmpOutAddrMasks.0',
            'IP-MIB::icmpOutAddrMaskReps.0',
        ],
        'ip' => [
            'IP-MIB::ipForwDatagrams.0',
            'IP-MIB::ipInDelivers.0',
            'IP-MIB::ipInReceives.0',
            'IP-MIB::ipOutRequests.0',
            'IP-MIB::ipInDiscards.0',
            'IP-MIB::ipOutDiscards.0',
            'IP-MIB::ipOutNoRoutes.0',
            'IP-MIB::ipReasmReqds.0',
            'IP-MIB::ipReasmOKs.0',
            'IP-MIB::ipReasmFails.0',
            'IP-MIB::ipFragOKs.0',
            'IP-MIB::ipFragFails.0',
            'IP-MIB::ipFragCreates.0',
            'IP-MIB::ipInUnknownProtos.0',
            'IP-MIB::ipInHdrErrors.0',
            'IP-MIB::ipInAddrErrors.0',
        ],
        'ip_forward' => [
            'IP-FORWARD-MIB::ipCidrRouteNumber.0',
        ],
        'snmp' => [
            'SNMPv2-MIB::snmpInPkts.0',
            'SNMPv2-MIB::snmpOutPkts.0',
            'SNMPv2-MIB::snmpInBadVersions.0',
            'SNMPv2-MIB::snmpInBadCommunityNames.0',
            'SNMPv2-MIB::snmpInBadCommunityUses.0',
            'SNMPv2-MIB::snmpInASNParseErrs.0',
            'SNMPv2-MIB::snmpInTooBigs.0',
            'SNMPv2-MIB::snmpInNoSuchNames.0',
            'SNMPv2-MIB::snmpInBadValues.0',
            'SNMPv2-MIB::snmpInReadOnlys.0',
            'SNMPv2-MIB::snmpInGenErrs.0',
            'SNMPv2-MIB::snmpInTotalReqVars.0',
            'SNMPv2-MIB::snmpInTotalSetVars.0',
            'SNMPv2-MIB::snmpInGetRequests.0',
            'SNMPv2-MIB::snmpInGetNexts.0',
            'SNMPv2-MIB::snmpInSetRequests.0',
            'SNMPv2-MIB::snmpInGetResponses.0',
            'SNMPv2-MIB::snmpInTraps.0',
            'SNMPv2-MIB::snmpOutTooBigs.0',
            'SNMPv2-MIB::snmpOutNoSuchNames.0',
            'SNMPv2-MIB::snmpOutBadValues.0',
            'SNMPv2-MIB::snmpOutGenErrs.0',
            'SNMPv2-MIB::snmpOutGetRequests.0',
            'SNMPv2-MIB::snmpOutGetNexts.0',
            'SNMPv2-MIB::snmpOutSetRequests.0',
            'SNMPv2-MIB::snmpOutGetResponses.0',
            'SNMPv2-MIB::snmpOutTraps.0',
            'SNMPv2-MIB::snmpSilentDrops.0',
            'SNMPv2-MIB::snmpProxyDrops.0',
        ],
        'tcp' => [
            'TCP-MIB::tcpActiveOpens.0',
            'TCP-MIB::tcpPassiveOpens.0',
            'TCP-MIB::tcpAttemptFails.0',
            'TCP-MIB::tcpEstabResets.0',
            'TCP-MIB::tcpCurrEstab.0',
            'TCP-MIB::tcpInSegs.0',
            'TCP-MIB::tcpOutSegs.0',
            'TCP-MIB::tcpRetransSegs.0',
            'TCP-MIB::tcpInErrs.0',
            'TCP-MIB::tcpOutRsts.0',
        ],
        'udp' => [
            'UDP-MIB::udpInDatagrams.0',
            'UDP-MIB::udpOutDatagrams.0',
            'UDP-MIB::udpInErrors.0',
            'UDP-MIB::udpNoPorts.0',
        ],
    ];

    /**
     * @var string[][]
     */
    private $graphs = [
        'icmp' => ['netstat_icmp', 'netstat_icmp_info'],
        'ip' => ['netstat_ip', 'netstat_ip_frag'],
        'ip_forward' => ['netstat_ip_forward'],
        'snmp' => ['netstat_snmp', 'netstat_snmp_pkt'],
        'tcp' => ['netstat_tcp'],
        'udp' => ['netstat_udp'],
    ];

    /**
     * @var string[]
     */
    private $types = [
        'icmp' => IcmpNetstatsPolling::class,
        'ip' => IpNetstatsPolling::class,
        'ip_forward' => IpForwardNetstatsPolling::class,
        'snmp' => SnmpNetstatsPolling::class,
        'udp' => UdpNetstatsPolling::class,
        'tcp' => TcpNetstatsPolling::class,
    ];

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        // no discovery
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os): void
    {
        foreach ($this->types as $type => $interface) {
            if ($os instanceof $interface) {
                echo "$type ";
                $method = (new \ReflectionClass($interface))->getMethods()[0]->getName();
                $data = $os->$method($this->oids[$type]);

                // we have data, update it
                if (! empty($data)) {
                    $rrd_def = new RrdDefinition();
                    $fields = [];
                    foreach ($this->oids[$type] as $oid) {
                        $stat = $this->statName($oid);
                        $rrd_def->addDataset($stat, 'COUNTER', null, 100000000000);
                        $fields[$stat] = $data[$oid] ?? null;
                    }

                    app('Datastore')->put($os->getDeviceArray(), "netstats-$type", ['rrd_def' => $rrd_def], $fields);

                    // enable graphs
                    foreach ($this->graphs[$type] as $graph) {
                        $os->enableGraph($graph);
                    }
                }
            }
        }
        echo PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function cleanup(OS $os): void
    {
        // no cleanup
    }

    private function statName(string $oid): string
    {
        $start = strpos($oid, '::') + 2;
        $length = min(strpos($oid, '.') - $start, 19); // 19 is max RRD ds length

        return substr($oid, $start, $length);
    }
}
