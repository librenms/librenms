<?php

namespace LibreNMS\Tests\Feature;

use App\Models\AlertRule;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Network;
use App\Models\Ipv6Address;
use App\Models\Ipv6Network;
use App\Models\Port;
use LibreNMS\Alert\AlertRules;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\IPv6;

/**
 * Real-database proof that the ip_in_prefix/ip_not_in_prefix operators (see
 * LibreNMS\Alerting\QueryBuilderParser::buildPrefixSql) behave correctly
 * against actual rows -- not just that they generate plausible-looking SQL
 * (that's covered separately in tests/QueryBuilderTest.php's data-driven
 * fixtures). Every case here mirrors a scenario already verified by hand
 * against real MariaDB during development; this turns that into something
 * repeatable a maintainer can run.
 */
class InPrefixOperatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbSetUp();
    }

    protected function tearDown(): void
    {
        $this->dbTearDown();
        parent::tearDown();
    }

    private function ruleFor(string $field, string $operator, string $value): array
    {
        return [
            'condition' => 'AND',
            'rules' => [[
                'id' => $field,
                'field' => $field,
                'type' => 'string',
                'input' => 'text',
                'operator' => $operator,
                'value' => $value,
            ]],
            'valid' => true,
        ];
    }

    private function assertRuleTriggers(Device $device, array $builder, bool $expected, string $message = ''): void
    {
        $rule = AlertRule::factory()->create(['query' => '', 'builder' => $builder]);
        (new AlertRules($device))->run();

        $triggered = \App\Models\Alert::where('device_id', $device->device_id)
            ->where('rule_id', $rule->id)
            ->where('state', AlertState::ACTIVE)
            ->exists();

        $this->assertSame($expected, $triggered, $message ?: (
            $expected
                ? 'Expected the rule to trigger an active alert for this device, but it did not.'
                : 'Expected the rule NOT to trigger an active alert for this device, but it did.'
        ));
    }

    // ── IPv4, binary devices.ip ──────────────────────────────────────

    public function testIpv4BinaryWithinRangeMatches(): void
    {
        $device = Device::factory()->create(['ip' => '10.0.0.5']);
        $this->assertRuleTriggers($device, $this->ruleFor('devices.ip', 'ip_in_prefix', '10.0.0.0/24'), true);
    }

    public function testIpv4BinaryOutsideRangeExcluded(): void
    {
        $device = Device::factory()->create(['ip' => '10.0.1.5']);
        $this->assertRuleTriggers($device, $this->ruleFor('devices.ip', 'ip_in_prefix', '10.0.0.0/24'), false);
    }

    public function testIpv4BinaryZeroPrefixMatchesEverything(): void
    {
        $device = Device::factory()->create(['ip' => '203.0.113.9']);
        $this->assertRuleTriggers($device, $this->ruleFor('devices.ip', 'ip_in_prefix', '0.0.0.0/0'), true);
    }

    public function testIpv4BinaryExactHostMatch(): void
    {
        $match = Device::factory()->create(['ip' => '10.0.0.5']);
        $noMatch = Device::factory()->create(['ip' => '10.0.0.6']);
        $this->assertRuleTriggers($match, $this->ruleFor('devices.ip', 'ip_in_prefix', '10.0.0.5/32'), true);
        $this->assertRuleTriggers($noMatch, $this->ruleFor('devices.ip', 'ip_in_prefix', '10.0.0.5/32'), false);
    }

    public function testIpv4BinaryLeadingZeroByteNotMishandled(): void
    {
        // First octet 0x00 -- proves CONV(HEX(...)) doesn't drop or misalign
        // the leading zero byte (HEX() always emits 2 hex chars per byte
        // regardless of value, but this is exactly the assumption worth
        // proving against a real address rather than trusting by inspection).
        $inRange = Device::factory()->create(['ip' => '0.5.10.20']);
        $outOfRange = Device::factory()->create(['ip' => '1.5.10.20']);
        $this->assertRuleTriggers($inRange, $this->ruleFor('devices.ip', 'ip_in_prefix', '0.0.0.0/8'), true);
        $this->assertRuleTriggers($outOfRange, $this->ruleFor('devices.ip', 'ip_in_prefix', '0.0.0.0/8'), false);
    }

    public function testIpv4BinaryMsbSetByteNotMishandled(): void
    {
        // First octet >= 128 -- proves the CONV(HEX())/bitmask arithmetic
        // doesn't run into signed/unsigned trouble on a high-bit address.
        $inRange = Device::factory()->create(['ip' => '200.51.100.5']);
        $outOfRange = Device::factory()->create(['ip' => '200.51.101.5']);
        $this->assertRuleTriggers($inRange, $this->ruleFor('devices.ip', 'ip_in_prefix', '200.51.100.0/24'), true);
        $this->assertRuleTriggers($outOfRange, $this->ruleFor('devices.ip', 'ip_in_prefix', '200.51.100.0/24'), false);
    }

    public function testIpv4BinaryNegationInverts(): void
    {
        $inRange = Device::factory()->create(['ip' => '10.0.0.5']);
        $outOfRange = Device::factory()->create(['ip' => '10.0.1.5']);
        // devices.ip is on the anchor table itself, so ip_not_in_prefix here
        // is a plain NOT, not a NOT EXISTS subquery -- both paths get
        // covered across this test and the NOT EXISTS ones below.
        $this->assertRuleTriggers($inRange, $this->ruleFor('devices.ip', 'ip_not_in_prefix', '10.0.0.0/24'), false);
        $this->assertRuleTriggers($outOfRange, $this->ruleFor('devices.ip', 'ip_not_in_prefix', '10.0.0.0/24'), true);
    }

    // ── IPv6, binary devices.ip -- byte-aligned and non-aligned boundaries ──

    public function testIpv6BinaryByteAlignedPrefixMatches(): void
    {
        $inRange = Device::factory()->create(['ip' => 'fd42:cafe:d00d::1']);
        $outOfRange = Device::factory()->create(['ip' => 'fd42:cafe:beef::1']);
        $this->assertRuleTriggers($inRange, $this->ruleFor('devices.ip', 'ip_in_prefix', 'fd42:cafe:d00d::/64'), true);
        $this->assertRuleTriggers($outOfRange, $this->ruleFor('devices.ip', 'ip_in_prefix', 'fd42:cafe:d00d::/64'), false);
    }

    public function testIpv6BinaryNonAlignedPrefix100BoundaryByte(): void
    {
        // /100 = 12 full bytes + top nibble of byte 13 (the high byte of the
        // 7th hextet). Network fd00:: has that nibble = 0.
        // g7=0x0fff: high byte 0x0f, top nibble 0 -> masked match.
        $inRange = Device::factory()->create(['ip' => 'fd00:0:0:0:0:0:fff:1']);
        // g7=0x1000: high byte 0x10, top nibble 1 -> masked mismatch.
        $outOfRange = Device::factory()->create(['ip' => 'fd00:0:0:0:0:0:1000:1']);
        $this->assertRuleTriggers($inRange, $this->ruleFor('devices.ip', 'ip_in_prefix', 'fd00::/100'), true,
            'A boundary-byte value differing only below the mask should still match /100.');
        $this->assertRuleTriggers($outOfRange, $this->ruleFor('devices.ip', 'ip_in_prefix', 'fd00::/100'), false,
            'A boundary-byte value differing above the mask must not match /100.');
    }

    public function testIpv6BinaryNonAlignedPrefix127LastBit(): void
    {
        // /127 masks off only the single last bit. fd00::1 (...0001) differs
        // from the network only in that last bit -> must match. fd00::2
        // (...0010) differs in the bit just above it, which IS covered by
        // the mask -> must not match.
        $inRange = Device::factory()->create(['ip' => 'fd00::1']);
        $outOfRange = Device::factory()->create(['ip' => 'fd00::2']);
        $this->assertRuleTriggers($inRange, $this->ruleFor('devices.ip', 'ip_in_prefix', 'fd00::/127'), true,
            'Only the last bit differs, and /127 masks exactly that bit off.');
        $this->assertRuleTriggers($outOfRange, $this->ruleFor('devices.ip', 'ip_in_prefix', 'fd00::/127'), false,
            'The differing bit here is inside the /127 mask, so this must not match.');
    }

    public function testIpv6BinaryZeroPrefixMatchesEverything(): void
    {
        $device = Device::factory()->create(['ip' => '2001:db8::9']);
        $this->assertRuleTriggers($device, $this->ruleFor('devices.ip', 'ip_in_prefix', '::/0'), true);
    }

    public function testIpv4MappedIpv6DoesNotFalselyMatchUnrelatedPrefix(): void
    {
        // ::ffff:192.0.2.1 is 16 real bytes once INET6_ATON'd -- byte-exact
        // comparison must not confuse it with an unrelated native IPv6
        // prefix that happens to share no structure with it at all.
        $device = Device::factory()->create(['ip' => '::ffff:192.0.2.1']);
        $this->assertRuleTriggers($device, $this->ruleFor('devices.ip', 'ip_in_prefix', '2001:db8::/32'), false);
    }

    // ── Text fields: ipv6_addresses.ipv6_compressed ──────────────────

    public function testIpv6CompressedTextFieldNonAlignedPrefix(): void
    {
        $deviceIn = Device::factory()->create();
        $portIn = Port::factory()->create(['device_id' => $deviceIn->device_id]);
        $ipIn = new IPv6('fd00:0:0:0:0:0:fff:1/100');
        Ipv6Address::factory()->create([
            'port_id' => $portIn->port_id,
            'ipv6_address' => $ipIn->uncompressed(),
            'ipv6_compressed' => $ipIn->compressed(),
            'ipv6_prefixlen' => 100,
        ]);

        $deviceOut = Device::factory()->create();
        $portOut = Port::factory()->create(['device_id' => $deviceOut->device_id]);
        $ipOut = new IPv6('fd00:0:0:0:0:0:1000:1/100');
        Ipv6Address::factory()->create([
            'port_id' => $portOut->port_id,
            'ipv6_address' => $ipOut->uncompressed(),
            'ipv6_compressed' => $ipOut->compressed(),
            'ipv6_prefixlen' => 100,
        ]);

        $this->assertRuleTriggers($deviceIn, $this->ruleFor('ipv6_addresses.ipv6_compressed', 'ip_in_prefix', 'fd00::/100'), true);
        $this->assertRuleTriggers($deviceOut, $this->ruleFor('ipv6_addresses.ipv6_compressed', 'ip_in_prefix', 'fd00::/100'), false);
    }

    // ── ipv4_networks / ipv6_networks -- NOT EXISTS path ─────────────

    public function testNotInPrefixIpv4NetworksNoAddressDataTriggers(): void
    {
        // Exactly the scenario that would have hit the fallback-to-plain-NOT
        // bug if the NOT EXISTS path hadn't already been confirmed reachable
        // for this table: a device with no port/address rows at all. NOT
        // EXISTS over zero rows is true, so "not in prefix" should trigger.
        $device = Device::factory()->create();
        $this->assertRuleTriggers($device, $this->ruleFor('ipv4_networks.ipv4_network', 'ip_not_in_prefix', '10.0.0.0/8'), true);
    }

    public function testNotInPrefixIpv6NetworksNoAddressDataTriggers(): void
    {
        $device = Device::factory()->create();
        $this->assertRuleTriggers($device, $this->ruleFor('ipv6_networks.ipv6_network', 'ip_not_in_prefix', '2001:db8::/32'), true);
    }

    public function testNotInPrefixIpv4NetworksWithMatchingAddressDoesNotTrigger(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $network = Ipv4Network::factory()->create(['ipv4_network' => '10.0.0.0/24']);
        Ipv4Address::factory()->create([
            'port_id' => $port->port_id,
            'ipv4_network_id' => $network->ipv4_network_id,
            'ipv4_address' => '10.0.0.5',
            'ipv4_prefixlen' => 24,
        ]);

        $this->assertRuleTriggers($device, $this->ruleFor('ipv4_networks.ipv4_network', 'ip_not_in_prefix', '10.0.0.0/24'), false);
    }

    public function testNotInPrefixIpv4NetworksWithOnlyNonMatchingAddressTriggers(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $network = Ipv4Network::factory()->create(['ipv4_network' => '192.0.2.0/24']);
        Ipv4Address::factory()->create([
            'port_id' => $port->port_id,
            'ipv4_network_id' => $network->ipv4_network_id,
            'ipv4_address' => '192.0.2.5',
            'ipv4_prefixlen' => 24,
        ]);

        // Device has an address, but not one in the queried prefix -- no
        // matching row exists for 10.0.0.0/24, so ip_not_in_prefix triggers.
        $this->assertRuleTriggers($device, $this->ruleFor('ipv4_networks.ipv4_network', 'ip_not_in_prefix', '10.0.0.0/24'), true);
    }

    public function testNotInPrefixIpv6NetworksWithMatchingAddressDoesNotTrigger(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $network = Ipv6Network::factory()->create(['ipv6_network' => '2001:db8::/48']);
        $ip = new IPv6('2001:db8::5/48');
        Ipv6Address::factory()->create([
            'port_id' => $port->port_id,
            'ipv6_network_id' => $network->ipv6_network_id,
            'ipv6_address' => $ip->uncompressed(),
            'ipv6_compressed' => $ip->compressed(),
            'ipv6_prefixlen' => 48,
        ]);

        $this->assertRuleTriggers($device, $this->ruleFor('ipv6_networks.ipv6_network', 'ip_not_in_prefix', '2001:db8::/48'), false);
    }
}
