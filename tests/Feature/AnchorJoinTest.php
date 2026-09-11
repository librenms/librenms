<?php

namespace LibreNMS\Tests\Feature;

use App\Models\AlertRule;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Network;
use App\Models\Port;
use LibreNMS\Alert\AlertRules;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;

/**
 * Real-database proof for the anchor-join fix in QueryBuilderParser::toSql()
 * (LEFT JOIN instead of a comma-joined implicit INNER JOIN) beyond just the
 * ip_not_in_prefix case that surfaced it. See InPrefixOperatorTest for the
 * prefix-operator-specific coverage; this file covers the fix itself:
 * the is_null bug it also silently fixed, that it composes correctly with
 * non-joined conditions in the same rule, and that it doesn't change row
 * multiplicity for the ordinary matching case.
 */
class AnchorJoinTest extends TestCase
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

    /**
     * @return array{condition: string, rules: array<array{id: string, field: string, type: string, input: string, operator: string, value: string}>, valid: bool}
     */
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

    /**
     * @param  array{condition: string, rules: array<array{id: string, field: string, type: string, input: string, operator: string, value: string}>, valid: bool}  $builder
     */
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

    // ── 1. is_null / is_not_null on a joined field, device with no related rows ──
    // This is the other real bug the anchor-join fix corrected (found while
    // proving out ip_not_in_prefix, but never given its own coverage until now).

    public function testIsNullOnJoinedFieldTriggersForDeviceWithNoPorts(): void
    {
        $device = Device::factory()->create();
        // deliberately no Port rows at all
        $this->assertRuleTriggers($device, $this->ruleFor('ports.ifDescr', 'is_null', ''), true);
    }

    public function testIsNotNullOnJoinedFieldDoesNotTriggerForDeviceWithNoPorts(): void
    {
        $device = Device::factory()->create();
        $this->assertRuleTriggers($device, $this->ruleFor('ports.ifDescr', 'is_not_null', ''), false);
    }

    public function testIsNullOnJoinedFieldDoesNotTriggerWhenValuePresent(): void
    {
        $device = Device::factory()->create();
        Port::factory()->create(['device_id' => $device->device_id, 'ifDescr' => 'eth0']);
        $this->assertRuleTriggers($device, $this->ruleFor('ports.ifDescr', 'is_null', ''), false);
    }

    public function testIsNotNullOnJoinedFieldTriggersWhenValuePresent(): void
    {
        $device = Device::factory()->create();
        Port::factory()->create(['device_id' => $device->device_id, 'ifDescr' => 'eth0']);
        $this->assertRuleTriggers($device, $this->ruleFor('ports.ifDescr', 'is_not_null', ''), true);
    }

    // ── 2. Compound rules -- IP condition combined with a non-joined condition ──

    /**
     * @return array{condition: string, rules: array<array{id: string, field: string, type: string, input: string, operator: string, value: string}>, valid: bool}
     */
    private function ipAndStatusRule(string $condition, string $ipOperator): array
    {
        return [
            'condition' => $condition,
            'rules' => [
                [
                    'id' => 'ipv4_networks.ipv4_network',
                    'field' => 'ipv4_networks.ipv4_network',
                    'type' => 'string',
                    'input' => 'text',
                    'operator' => $ipOperator,
                    'value' => '10.0.0.0/24',
                ],
                [
                    'id' => 'devices.status',
                    'field' => 'devices.status',
                    'type' => 'integer',
                    'input' => 'radio',
                    'operator' => 'equal',
                    'value' => '0',
                ],
            ],
            'valid' => true,
        ];
    }

    private function deviceWithAddress(int $status, string $address, string $networkCidr = '10.0.0.0/24'): Device
    {
        $device = Device::factory()->create(['status' => $status]);
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        // The ipv4_networks row's own address is what ip_in_prefix actually
        // checks -- it must genuinely correspond to the address being
        // created, not just default to the "in range" network regardless.
        $network = Ipv4Network::factory()->create(['ipv4_network' => $networkCidr]);
        Ipv4Address::factory()->create([
            'port_id' => $port->port_id,
            'ipv4_network_id' => $network->ipv4_network_id,
            'ipv4_address' => $address,
            'ipv4_prefixlen' => (int) explode('/', $networkCidr)[1],
        ]);

        return $device;
    }

    public function testCompoundAndRuleRequiresBothConditions(): void
    {
        // status=0 AND address in 10.0.0.0/24 -> triggers
        $both = $this->deviceWithAddress(0, '10.0.0.5');
        $this->assertRuleTriggers($both, $this->ipAndStatusRule('AND', 'ip_in_prefix'), true);

        // status=1 (disabled) but address in range -> AND fails
        $ipOnly = $this->deviceWithAddress(1, '10.0.0.6');
        $this->assertRuleTriggers($ipOnly, $this->ipAndStatusRule('AND', 'ip_in_prefix'), false);

        // status=0 but address outside range -> AND fails
        $statusOnly = $this->deviceWithAddress(0, '192.0.2.9', '192.0.2.0/24');
        $this->assertRuleTriggers($statusOnly, $this->ipAndStatusRule('AND', 'ip_in_prefix'), false);
    }

    public function testCompoundOrRuleRequiresEitherCondition(): void
    {
        // status=1 but address in range -> OR passes via the IP condition
        $ipOnly = $this->deviceWithAddress(1, '10.0.0.6');
        $this->assertRuleTriggers($ipOnly, $this->ipAndStatusRule('OR', 'ip_in_prefix'), true);

        // status=0 but address outside range -> OR passes via the status condition
        $statusOnly = $this->deviceWithAddress(0, '192.0.2.9', '192.0.2.0/24');
        $this->assertRuleTriggers($statusOnly, $this->ipAndStatusRule('OR', 'ip_in_prefix'), true);

        // neither condition true -> OR fails
        $neither = $this->deviceWithAddress(1, '192.0.2.9', '192.0.2.0/24');
        $this->assertRuleTriggers($neither, $this->ipAndStatusRule('OR', 'ip_in_prefix'), false);
    }

    public function testCompoundAndRuleWithNegatedIpCondition(): void
    {
        // status=0 AND no address in 10.0.0.0/24 -> ip_not_in_prefix + status=0 both true
        $device = Device::factory()->create(['status' => 0]);
        // no address data at all -- exactly the case the anchor-join fix covers
        $this->assertRuleTriggers($device, $this->ipAndStatusRule('AND', 'ip_not_in_prefix'), true);

        // status=1 (disabled), no address either -> ip_not_in_prefix true but status condition fails
        $disabled = Device::factory()->create(['status' => 1]);
        $this->assertRuleTriggers($disabled, $this->ipAndStatusRule('AND', 'ip_not_in_prefix'), false);

        // status=0 but has an address inside the prefix -> ip_not_in_prefix false, AND fails
        $inRange = $this->deviceWithAddress(0, '10.0.0.5');
        $this->assertRuleTriggers($inRange, $this->ipAndStatusRule('AND', 'ip_not_in_prefix'), false);
    }

    // ── 3. Row-multiplication sanity check ──────────────────────────

    public function testMultipleMatchingRowsProduceExpectedRowCountNotDuplicatedOrCollapsed(): void
    {
        $device = Device::factory()->create();
        $network = Ipv4Network::factory()->create(['ipv4_network' => '10.0.0.0/24']);

        // Two independent ports, each with its own address inside the prefix --
        // a genuine multi-row match, the case where LEFT JOIN and the old
        // comma join (implicit INNER JOIN) should behave identically.
        foreach (['10.0.0.5', '10.0.0.6'] as $address) {
            $port = Port::factory()->create(['device_id' => $device->device_id]);
            Ipv4Address::factory()->create([
                'port_id' => $port->port_id,
                'ipv4_network_id' => $network->ipv4_network_id,
                'ipv4_address' => $address,
                'ipv4_prefixlen' => 24,
            ]);
        }

        // A third port with an address outside the prefix -- must not appear
        // in the match count.
        $outsidePort = Port::factory()->create(['device_id' => $device->device_id]);
        $outsideNetwork = Ipv4Network::factory()->create(['ipv4_network' => '192.0.2.0/24']);
        Ipv4Address::factory()->create([
            'port_id' => $outsidePort->port_id,
            'ipv4_network_id' => $outsideNetwork->ipv4_network_id,
            'ipv4_address' => '192.0.2.9',
            'ipv4_prefixlen' => 24,
        ]);

        $builder = $this->ruleFor('ipv4_networks.ipv4_network', 'ip_in_prefix', '10.0.0.0/24');
        $sql = QueryBuilderParser::fromJson($builder)->toSql();
        $rows = \DB::select($sql, [$device->device_id]);

        $this->assertCount(2, $rows,
            'Expected exactly one result row per matching port/address pair (2), ' .
            'matching how the old comma-join (implicit INNER JOIN) would also ' .
            'multiply rows for a genuine multi-row match -- no silent ' .
            'duplication or collapse introduced by switching to LEFT JOIN.');
    }
}
