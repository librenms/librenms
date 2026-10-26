<?php

namespace LibreNMS\Tests\Feature\Alert;

use App\Http\Requests\AlertRuleRequest;
use App\Models\AlertRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AlertRuleAttributesTest extends TestCase
{
    use RefreshDatabase;

    public function testRuleStoresLiteralStringMetadataSeparatelyFromSeverity(): void
    {
        $rule = AlertRule::factory()->create(['severity' => 'critical']);
        $metadata = ['team' => 'network', 'zero' => '0', 'empty' => '', 'literal' => '{{ $hostname }}', 'severity' => 'custom'];
        $rule->fill(['custom_attributes' => $metadata])->save();

        self::assertSame($metadata, $rule->fresh()->custom_attributes);
        self::assertSame('critical', $rule->fresh()->severity);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function invalidMaps(): array
    {
        return [
            'dot' => [['site.id' => 'lax']],
            'hyphen' => [['team-name' => 'net']],
            'long key' => [[str_repeat('k', 65) => 'v']],
            'too many' => [array_fill_keys(array_map(fn ($n) => 'key' . $n, range(1, 51)), 'v')],
            'long value' => [['team' => str_repeat('v', 4097)]],
            'long whitespace value' => [['team' => str_repeat(' ', 4097)]],
            'integer' => [['team' => 3]],
            'null' => [['team' => null]],
            'nested' => [['team' => ['net']]],
        ];
    }

    /** @param array<string, mixed> $metadata */
    #[DataProvider('invalidMaps')]
    public function testInvalidMetadataIsRejected(array $metadata): void
    {
        self::assertTrue(Validator::make(['custom_attributes' => $metadata], AlertRuleRequest::customAttributeRules())->fails());
    }

    public function testBoundaryValuesAndEmptyMapAreAccepted(): void
    {
        $metadata = array_fill_keys(array_map(fn ($n) => 'key' . $n, range(1, 49)), '');
        $metadata[str_repeat('k', 64)] = str_repeat('v', 4096);
        self::assertTrue(Validator::make(['custom_attributes' => $metadata], AlertRuleRequest::customAttributeRules())->passes());
        self::assertTrue(Validator::make(['custom_attributes' => []], AlertRuleRequest::customAttributeRules())->passes());
    }

    public function testWebRowsRoundTripAndExplicitEmptyRowsClearMetadata(): void
    {
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));
        $payload = ['name' => 'Metadata rule', 'severity' => 'critical', 'override_query' => true, 'adv_query' => 'SELECT * FROM devices WHERE status = 0', 'builder_json' => '[]'];
        $this->postJson(route('alert-rule.store'), $payload + ['custom_attributes-rows' => [
            ['name' => 'team', 'value' => 'network'], ['name' => 'empty', 'value' => ''], ['name' => 'zero', 'value' => '0'],
            ['name' => 'padding', 'value' => ' maintenance '], ['name' => 'spaces', 'value' => '   '],
        ]])->assertOk()->assertJsonPath('status', 'ok');
        $rule = AlertRule::firstOrFail();
        self::assertSame(['team' => 'network', 'empty' => '', 'zero' => '0', 'padding' => ' maintenance ', 'spaces' => '   '], $rule->custom_attributes);
        $this->getJson(route('alert-rule.show', $rule->id))->assertOk()->assertJsonPath('custom_attributes.team', 'network');
        $this->putJson(route('alert-rule.update', $rule->id), $payload)->assertOk();
        self::assertSame('network', $rule->fresh()->custom_attributes['team']);
        $this->putJson(route('alert-rule.update', $rule->id), $payload + ['custom_attributes-rows' => ''])->assertOk();
        self::assertSame([], $rule->fresh()->custom_attributes);
    }

    public function testWebDuplicateRowsAreRejectedWithoutSaving(): void
    {
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));
        $this->postJson(route('alert-rule.store'), [
            'name' => 'Duplicate rule', 'severity' => 'critical', 'override_query' => true,
            'adv_query' => 'SELECT * FROM devices', 'builder_json' => '[]',
            'custom_attributes-rows' => [['name' => 'team', 'value' => 'a'], ['name' => 'team', 'value' => 'b']],
        ])->assertUnprocessable()->assertJsonValidationErrors('custom_attributes');
        self::assertSame(0, AlertRule::count());
    }

    public function testApiRoundTripOmissionPreservesAndExplicitEmptyMapClears(): void
    {
        $user = User::factory()->admin()->create(['enabled' => 1]);
        $headers = ['X-Auth-Token' => $user->createToken('attribute tests')->plainTextToken];
        $payload = [
            'name' => 'API metadata rule', 'severity' => 'critical', 'devices' => ['-1'],
            'builder' => ['condition' => 'AND', 'rules' => [['id' => 'devices.status', 'field' => 'devices.status', 'type' => 'integer', 'input' => 'text', 'operator' => 'equal', 'value' => '0']]],
        ];
        $this->json('POST', '/api/v0/rules', $payload + ['custom_attributes' => ['team' => 'network', 'empty' => '', 'zero' => '0']], $headers)->assertOk();
        $rule = AlertRule::firstOrFail();
        self::assertSame(['team' => 'network', 'empty' => '', 'zero' => '0'], $rule->custom_attributes);
        $this->json('GET', '/api/v0/rules/' . $rule->id, [], $headers)->assertOk()->assertJsonPath('rules.0.custom_attributes.team', 'network');
        $this->json('PUT', '/api/v0/rules', $payload + ['rule_id' => $rule->id], $headers)->assertOk();
        self::assertSame('network', $rule->fresh()->custom_attributes['team']);
        $this->json('PUT', '/api/v0/rules', $payload + ['rule_id' => $rule->id, 'custom_attributes' => []], $headers)->assertOk();
        self::assertSame([], $rule->fresh()->custom_attributes);
        $this->json('PUT', '/api/v0/rules', $payload + ['rule_id' => $rule->id, 'custom_attributes' => ['team' => null]], $headers)->assertStatus(400);
        self::assertSame([], $rule->fresh()->custom_attributes);
    }

    public function testCopyRulePreservesMetadata(): void
    {
        $this->actingAs(User::factory()->admin()->create(['enabled' => 1]));
        $rule = AlertRule::factory()->create(['custom_attributes' => ['team' => 'network']]);
        $response = (new \App\Http\Controllers\AlertRuleTemplateController)->rule($rule);
        self::assertSame(['team' => 'network'], $response->getData(true)['custom_attributes'] ?? null);
    }

    public function testEmptyRootScalarsCannotOverwriteMetadata(): void
    {
        foreach (['', ' ', null, 3, false] as $invalid) {
            self::assertTrue(Validator::make(['custom_attributes' => $invalid], AlertRuleRequest::customAttributeRules())->fails(), 'Root metadata must be a map.');
        }
        $user = User::factory()->admin()->create(['enabled' => 1]);
        $rule = AlertRule::factory()->create(['custom_attributes' => ['team' => 'network']]);
        $headers = ['X-Auth-Token' => $user->createToken('invalid map tests')->plainTextToken];
        foreach (['', ' '] as $invalid) {
            $this->json('PUT', '/api/v0/rules', [
                'rule_id' => $rule->id, 'custom_attributes' => $invalid,
                'name' => $rule->name, 'severity' => 'critical', 'builder' => ['condition' => 'AND', 'rules' => []],
            ], $headers)->assertStatus(400);
            self::assertSame(['team' => 'network'], $rule->fresh()->custom_attributes);
        }
    }
}
