<?php

declare(strict_types=1);

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Exceptions\ErrorReporting;
use App\Http\Middleware\EnforceJsonApi;
use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ErrorResponseTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);

        $apiAccess = Permission::findOrCreate('api.access');
        Role::findOrCreate('admin')->givePermissionTo($apiAccess);
        Role::findOrCreate('global-read')->givePermissionTo($apiAccess);
    }

    public function testWrongContentTypeReturns415JsonApiError(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/v1/alert-templates', [
            'name' => 'x',
            'template' => 'y',
        ], ['Content-Type' => 'text/plain']);

        $response->assertStatus(415)
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE)
            ->assertJsonPath('errors.0.status', '415')
            ->assertJsonPath('errors.0.code', 'unsupported_media_type')
            ->assertJsonPath('errors.0.title', 'Unsupported Media Type')
            ->assertJsonStructure(['errors' => [['detail']]]);
    }

    public function testUnauthenticatedRequestReturns401Envelope(): void
    {
        $response = $this->getJson('/api/v1/devices');

        $response->assertStatus(401)
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE)
            ->assertJsonPath('errors.0.status', '401')
            ->assertJsonPath('errors.0.code', 'unauthenticated')
            ->assertJsonPath('errors.0.title', 'Unauthenticated');
    }

    public function testForbiddenRequestReturns403Envelope(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/v1/alert-templates', [
            'name' => 'forbidden',
            'template' => 'x',
        ], [
            'Content-Type' => EnforceJsonApi::CONTENT_TYPE,
            'Accept' => EnforceJsonApi::CONTENT_TYPE,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('errors.0.status', '403')
            ->assertJsonPath('errors.0.code', 'forbidden');
    }

    public function testNotFoundReturns404Envelope(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/devices/999999999');

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.status', '404')
            ->assertJsonPath('errors.0.code', 'not_found');
    }

    public function testUnknownRepositoryReturns404Envelope(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/v1/devices/1/deviceGrups/', ['x' => 1], [
            'Content-Type' => EnforceJsonApi::CONTENT_TYPE,
            'Accept' => EnforceJsonApi::CONTENT_TYPE,
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.status', '404')
            ->assertJsonPath('errors.0.code', 'not_found');
    }

    public function testValidationFailureReturnsPerFieldEntries(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/v1/alert-templates', [], [
            'Content-Type' => EnforceJsonApi::CONTENT_TYPE,
            'Accept' => EnforceJsonApi::CONTENT_TYPE,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.0.status', '422')
            ->assertJsonPath('errors.0.code', 'validation_failed')
            ->assertJsonPath('errors.0.title', 'Validation Failed');

        $body = $response->json();
        $pointers = array_map(static fn ($e) => $e['source']['pointer'] ?? null, $body['errors']);
        $this->assertContains('/name', $pointers);
        $this->assertContains('/template', $pointers);
    }

    public function testBusinessValidationOnAlertOperationDelete(): void
    {
        $user = User::factory()->admin()->create();
        $op = AlertOperation::create(['name' => 'Referenced']);
        AlertRule::factory()->create(['alert_operation_id' => $op->id]);
        Sanctum::actingAs($user);

        $response = $this->json('DELETE', "/api/v1/alert-operations/{$op->id}", [], [
            'Accept' => EnforceJsonApi::CONTENT_TYPE,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.0.status', '422')
            ->assertJsonPath('errors.0.code', 'validation_failed')
            ->assertJsonPath('errors.0.source.pointer', '/id');
    }

    public function testServerErrorWithDebugIncludesMeta(): void
    {
        config(['app.debug' => true]);

        $response = ErrorReporting::renderApiException(new \RuntimeException('boom from test'));
        $body = $response->getData(true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(EnforceJsonApi::CONTENT_TYPE, $response->headers->get('Content-Type'));
        $this->assertSame('500', $body['errors'][0]['status']);
        $this->assertSame('server_error', $body['errors'][0]['code']);
        $this->assertSame('Server Error', $body['errors'][0]['title']);
        $this->assertSame('boom from test', $body['errors'][0]['detail']);
        $this->assertSame(\RuntimeException::class, $body['errors'][0]['meta']['exception']);
        $this->assertArrayHasKey('file', $body['errors'][0]['meta']);
        $this->assertArrayHasKey('line', $body['errors'][0]['meta']);
        $this->assertArrayHasKey('trace', $body['errors'][0]['meta']);
    }

    public function testServerErrorWithoutDebugHidesMeta(): void
    {
        config(['app.debug' => false]);

        $response = ErrorReporting::renderApiException(new \RuntimeException('internal detail'));
        $body = $response->getData(true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('500', $body['errors'][0]['status']);
        $this->assertSame('server_error', $body['errors'][0]['code']);
        $this->assertSame('An unexpected error occurred.', $body['errors'][0]['detail']);
        $this->assertArrayNotHasKey('meta', $body['errors'][0]);
    }

    public function testQueryExceptionIsClassifiedAsServerError(): void
    {
        config(['app.debug' => true]);

        $e = new \Illuminate\Database\QueryException('mysql', 'INSERT ...', [], new \PDOException('FK violation', 23000));
        $response = ErrorReporting::renderApiException($e);
        $body = $response->getData(true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('server_error', $body['errors'][0]['code']);
        $this->assertSame(\Illuminate\Database\QueryException::class, $body['errors'][0]['meta']['exception']);
    }
}
