<?php

/**
 * Bird2Test.php
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
 */

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Bird2;
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Tests\TestCase;

/**
 * Fixtures are verbatim snmpget captures from BIRD 2.17.5 behind net-snmp, using
 * documentation ranges only (RFC 3849 IPv6, RFC 5737 IPv4, RFC 5398 ASNs).
 */
final class Bird2Test extends TestCase
{
    public function testSimpleSetup(): void
    {
        $peers = $this->parseFixture('simple');

        $this->assertCount(1, $peers);
        $this->assertSame('uplink', $peers[0]['name']);
        $this->assertSame('64497', $peers[0]['neighbor_as']);
        $this->assertSame('2001:db8:0:1::2', $peers[0]['neighbor_address']);
        $this->assertSame('Established', $peers[0]['bgp_state']);
        $this->assertSame('up', $peers[0]['protocol_state']);

        $this->assertArrayNotHasKey('description', $peers[0]);
    }

    public function testNonBgpProtocolsAreIgnored(): void
    {
        // the simple fixture also carries Device and Kernel protocols
        $raw = $this->fixture('simple');
        $this->assertStringContainsString('Device', $raw);
        $this->assertStringContainsString('Kernel', $raw);

        foreach ($this->parseFixture('simple') as $peer) {
            $this->assertSame('BGP', $peer['type']);
        }
    }

    public function testIbgpOnlySetup(): void
    {
        $peers = $this->parseFixture('ibgp');
        $this->assertCount(2, $peers);

        $byName = array_column($peers, null, 'name');
        $this->assertSame(['rr_one', 'rr_two'], array_keys($byName));

        // iBGP: both sides share the local AS
        foreach ($byName as $peer) {
            $this->assertSame($peer['local_as'], $peer['neighbor_as'], 'iBGP peers share the AS');
            $this->assertSame('64496', $peer['neighbor_as']);
        }

        $this->assertSame('route reflector one', $byName['rr_one']['description']);
        $this->assertArrayNotHasKey('description', $byName['rr_two']);
    }

    public function testDescriptionIsOptional(): void
    {
        $byName = array_column($this->parseFixture('full'), null, 'name');

        $this->assertSame('documentation transit', $byName['upstream_a']['description']);
        $this->assertSame('route collector', $byName['collector_c']['description']);
        $this->assertArrayNotHasKey('description', $byName['peering_b']);
    }

    public function testSessionCarryingTwoAddressFamilies(): void
    {
        $byName = array_column($this->parseFixture('full'), null, 'name');

        $this->assertSame(['ipv4.unicast', 'ipv6.unicast'], array_keys($byName['peering_b']['channels']));
        $this->assertSame(['ipv6.unicast'], array_keys($byName['upstream_a']['channels']));
    }

    public function testPrefixCountersPerAddressFamily(): void
    {
        $byName = array_column($this->parseFixture('full'), null, 'name');

        $v4 = Bird2::channelPrefixCounters($byName['peering_b']['channels']['ipv4.unicast']);
        $v6 = Bird2::channelPrefixCounters($byName['peering_b']['channels']['ipv6.unicast']);

        $this->assertSame(18, $v4['AcceptedPrefixes']);
        $this->assertSame(25, $v6['AcceptedPrefixes']);

        // an export only collector imports nothing but still advertises
        $collector = Bird2::channelPrefixCounters($byName['collector_c']['channels']['ipv6.unicast']);
        $this->assertSame(0, $collector['AcceptedPrefixes']);
        $this->assertGreaterThan(0, $collector['AdvertisedPrefixes']);

        // every counter is an int, bird prints --- where one does not apply
        foreach ($collector as $value) {
            $this->assertIsInt($value);
        }
    }

    /**
     * rrd updates are written positionally, so a counter in the wrong slot silently
     * records itself as a different metric.
     */
    public function testPrefixCounterOrderMatchesRrdDatasets(): void
    {
        $byName = array_column($this->parseFixture('full'), null, 'name');
        $counters = Bird2::channelPrefixCounters($byName['peering_b']['channels']['ipv4.unicast']);

        $this->assertSame(Bird2::PREFIX_DATASETS, array_keys($counters));
    }

    public function testPeerThatNeverEstablished(): void
    {
        $byName = array_column($this->parseFixture('down'), null, 'name');
        $this->assertSame(['working', 'unreachable'], array_keys($byName));

        $this->assertSame('Established', $byName['working']['bgp_state']);

        // still reported, but without the session detail the poller needs
        $this->assertSame('Connect', $byName['unreachable']['bgp_state']);
        $this->assertSame('start', $byName['unreachable']['protocol_state']);
        $this->assertArrayNotHasKey('source_address', $byName['unreachable']);
    }

    public function testPeerWithLastError(): void
    {
        $peers = $this->parseFixture('error');
        $this->assertCount(1, $peers);

        $this->assertSame('mismatched', $peers[0]['name']);
        $this->assertSame('Idle', $peers[0]['bgp_state']);
        $this->assertSame('BGP Error: Bad peer AS', $peers[0]['last_error']);
        $this->assertSame('as mismatch peer', $peers[0]['description']);
    }

    /**
     * bird separates protocols with blank lines, but they are decorative: each protocol
     * starts at column 0 and its detail is indented. Parsing must not depend on them.
     */
    public function testParsingDoesNotDependOnBlankLines(): void
    {
        foreach (['simple', 'ibgp', 'full', 'down', 'error'] as $scenario) {
            // straight from the capture, not via the snmp layer
            $output = $this->fixture($scenario);
            $collapsed = preg_replace("/\n{2,}/", "\n", $output);

            $this->assertNotSame($output, $collapsed, "$scenario capture should contain blank lines");
            $this->assertEquals(
                Bird2::parseProtocols($output),
                Bird2::parseProtocols($collapsed),
                "$scenario must parse the same without blank lines"
            );
        }
    }

    public function testGarbageInputIsNotFatal(): void
    {
        $this->assertSame([], Bird2::parseProtocols(''));
        $this->assertSame([], Bird2::parseProtocols('BIRD 2.17.5 ready.'));
        $this->assertSame([], Bird2::parseProtocols(Bird2::HEADER));
        $this->assertSame([], Bird2::parseProtocols(Bird2::HEADER . "\ndevice1    Device     ---        up     2026-01-01 00:00:00\n"));
    }

    /**
     * The first protocol in the table must not be dropped, it may be a BGP session.
     */
    public function testFirstProtocolIsNotDiscarded(): void
    {
        $output = Bird2::HEADER . "\n"
            . "first_peer BGP        ---        up     2026-01-01 00:00:00  Established\n"
            . "  BGP state:          Established\n"
            . "    Neighbor address: 2001:db8:0:1::2\n"
            . "    Neighbor AS:      64497\n"
            . "  Channel ipv6\n"
            . "    Routes:         5 imported, 1 exported, 5 preferred\n";

        $peers = Bird2::parseProtocols($output);

        $this->assertCount(1, $peers);
        $this->assertSame('first_peer', $peers[0]['name']);
    }

    private function fixture(string $scenario): string
    {
        return file_get_contents(base_path("tests/data/misc/bird2-$scenario.snmpget.txt"));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseFixture(string $scenario): array
    {
        return Bird2::parseProtocols((new SnmpResponse($this->fixture($scenario)))->value());
    }
}
