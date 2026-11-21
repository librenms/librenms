<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Models\AlertOperation;
use App\Models\AlertOperationTransportMap;
use App\Models\AlertTransport;
use App\Models\AlertTransportGroup;
use App\Models\TransportGroupTransport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

final class AlertTransportApiTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('api.v1.enabled', true);
    }

    /** @return array<string, string> */
    private function headersFor(User $user): array
    {
        auth()->forgetGuards();

        return ['Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken];
    }

    private function hookTransport(): AlertTransport
    {
        $transport = new AlertTransport;
        $transport->transport_name = 'Hook';
        $transport->transport_type = 'api';
        $transport->transport_config = ['api-url' => 'https://example.com'];
        $transport->save();

        return $transport;
    }

    public function testApiTransportIsCreatedListedAndDeleted(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();

        $created = $this->json('POST', '/api/v1/alert/transports', [
            'name' => 'FebNMS Push',
            'type' => 'api',
            'config' => [
                'api-method' => 'POST',
                'api-url' => 'https://example.com/hooks/alerts',
                'api-as-form' => true,
                'api-body' => "alertID={{ \$alert_id }}\nruleID={{ \$rule_id }}",
            ],
        ], $this->headersFor($admin))
            ->assertStatus(201)
            ->assertJsonPath('data.type', 'api')
            ->assertJsonPath('data.config.api-method', 'POST')
            ->assertJsonPath('data.config.api-url', 'https://example.com/hooks/alerts');
        $id = $created->json('data.id');
        $this->assertDatabaseHas('alert_transports', ['transport_id' => $id, 'transport_name' => 'FebNMS Push', 'transport_type' => 'api']);

        $this->json('GET', '/api/v1/alert/transports', [], $this->headersFor($admin))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $id)
            ->assertJsonPath('data.0.config.api-url', 'https://example.com/hooks/alerts');

        $this->json('DELETE', "/api/v1/alert/transports/$id", [], $this->headersFor($admin))
            ->assertStatus(204);
        $this->assertDatabaseMissing('alert_transports', ['transport_id' => $id]);
    }

    public function testDeletingATransportRemovesItFromOperationsAndGroups(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $transport = $this->hookTransport();
        $operation = AlertOperation::create(['name' => 'op']);
        $segment = $operation->segments()->create(['position' => 0, 'operation_phase' => 'problem', 'escalation_step_from' => 1, 'start_in_seconds' => 0, 'step_duration_seconds' => 0]);
        AlertOperationTransportMap::create(['segment_id' => $segment->id, 'transport_or_group_id' => $transport->transport_id, 'target_type' => 'single']);
        $group = new AlertTransportGroup;
        $group->transport_group_name = 'g';
        $group->save();
        $membership = new TransportGroupTransport;
        $membership->transport_group_id = $group->transport_group_id;
        $membership->transport_id = $transport->transport_id;
        $membership->save();

        $this->json('DELETE', "/api/v1/alert/transports/{$transport->transport_id}", [], $this->headersFor($admin))->assertStatus(204);

        $this->assertSame(0, AlertOperationTransportMap::where('transport_or_group_id', $transport->transport_id)->count());
        $this->assertSame(0, TransportGroupTransport::where('transport_id', $transport->transport_id)->count());
    }

    public function testConfigurationIsOnlyShownToUsersWhoMayEditTransports(): void
    {
        $this->hookTransport();
        /** @var User $reader */
        $reader = User::factory()->read()->create();

        $this->json('GET', '/api/v1/alert/transports', [], $this->headersFor($reader))
            ->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Hook')
            ->assertJsonMissingPath('data.0.config');

        $this->json('POST', '/api/v1/alert/transports', ['name' => 'x', 'type' => 'api', 'config' => ['api-url' => 'https://example.com']], $this->headersFor($reader))
            ->assertStatus(403);
    }

    public function testInvalidTransportsAreRejected(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();

        $this->json('POST', '/api/v1/alert/transports', ['name' => 'x', 'type' => 'nosuchtransport', 'config' => []], $this->headersFor($admin))
            ->assertStatus(422)
            ->assertJsonPath('errors.0.status', '422');
        $this->json('POST', '/api/v1/alert/transports', ['name' => 'x', 'type' => 'api', 'config' => ['api-method' => 'POST', 'api-url' => 'not a url']], $this->headersFor($admin))
            ->assertStatus(422);
        $this->assertDatabaseCount('alert_transports', 0);
    }

    public function testTransportIsAttachedToAndDetachedFromEverySegmentOfAnOperation(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $transport = $this->hookTransport();
        $operation = AlertOperation::create(['name' => 'Default operation']);
        $problem = $operation->segments()->create(['position' => 0, 'operation_phase' => 'problem', 'escalation_step_from' => 1, 'start_in_seconds' => 0, 'step_duration_seconds' => 0]);
        $operation->segments()->create(['position' => 1, 'operation_phase' => 'recovery', 'escalation_step_from' => 1, 'start_in_seconds' => 0, 'step_duration_seconds' => 0]);
        AlertOperationTransportMap::create(['segment_id' => $problem->id, 'transport_or_group_id' => $transport->transport_id, 'target_type' => 'single']);

        $this->json('GET', '/api/v1/alert/operations', [], $this->headersFor($admin))
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $operation->id)
            ->assertJsonPath('data.0.segments.0.transports.0.id', (string) $transport->transport_id);

        $this->json('POST', "/api/v1/alert/operations/{$operation->id}/transports", ['transport_id' => $transport->transport_id], $this->headersFor($admin))
            ->assertStatus(200)
            ->assertJsonPath('meta.changed_segments', 1);
        $this->assertSame(2, AlertOperationTransportMap::where('transport_or_group_id', $transport->transport_id)->count());

        $this->json('POST', "/api/v1/alert/operations/{$operation->id}/transports", ['transport_id' => 999999], $this->headersFor($admin))
            ->assertStatus(422);

        $this->json('DELETE', "/api/v1/alert/operations/{$operation->id}/transports/{$transport->transport_id}", [], $this->headersFor($admin))
            ->assertStatus(200)
            ->assertJsonPath('meta.changed_segments', 2);
        $this->assertSame(0, AlertOperationTransportMap::where('transport_or_group_id', $transport->transport_id)->count());
    }

    public function testEndpointsRequireABearerToken(): void
    {
        $this->json('GET', '/api/v1/alert/transports')->assertStatus(401);
    }
}
