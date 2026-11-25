<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Http\Middleware\EnforceJsonApi;
use App\Http\Middleware\FormatJsonApiError;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Behaviour shared by every v1 endpoint: enabling, authentication, authorization and error documents.
 */
final class ApiV1Test extends DBTestCase
{
    use DatabaseTransactions;

    private const string URI = '/api/v1/devices';

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('api.v1.enabled', true);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions, ?string $role = 'user'): User
    {
        $user = User::factory()->create();
        if ($role !== null) {
            $user->assignRole(Role::findOrCreate($role));
        }
        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission));
        }

        return $user;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function errors(Response $response): array
    {
        return json_decode((string) $response->getContent(), true)['errors'];
    }

    public function testHealthDoesNotRequireApiAccess(): void
    {
        $this->getJson('/api/v1/health')->assertOk();
    }

    public function testUnhealthyIsGenericServiceUnavailable(): void
    {
        Event::listen(DiagnosingHealth::class, fn () => throw new \RuntimeException('database password is wrong'));

        $this->getJson('/api/v1/health')
            ->assertStatus(503)
            ->assertJsonPath('errors.0.detail', 'A required service is unavailable.')
            ->assertDontSee('password');
    }

    public function testMissingTokenIsJsonApiError(): void
    {
        $this->getJson(self::URI)
            ->assertUnauthorized()
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE)
            ->assertJsonMissingPath('message')
            ->assertJsonPath('errors.0.status', '401');
    }

    public function testApiAccessPermissionIsRequired(): void
    {
        Sanctum::actingAs($this->userWithPermissions(['device.viewAll']));

        $this->getJson('/api/v1/system')
            ->assertForbidden()
            ->assertJsonPath('errors.0.status', '403');
        $this->getJson(self::URI)->assertForbidden();
    }

    public function testResourceRequiresViewAny(): void
    {
        Sanctum::actingAs($this->userWithPermissions(['api.access'], role: null));

        $this->getJson('/api/v1/system')->assertOk();
        $this->getJson(self::URI)
            ->assertForbidden()
            ->assertJsonPath('errors.0.status', '403');
    }

    public function testDisabledApiIsNotFound(): void
    {
        LibrenmsConfig::set('api.v1.enabled', false);

        $this->getJson('/api/v1/health')
            ->assertNotFound()
            ->assertJsonPath('errors.0.status', '404');
        $this->getJson('/api/v1/health?unknown=1')->assertNotFound();
        $this->get('/api/v1/health', ['Accept' => 'application/vnd.api+json; ext="https://example.com/ext"'])->assertNotFound();
    }

    public function testUnknownEndpointIsJsonApiNotFound(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson('/api/v1/unknown')
            ->assertNotFound()
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE)
            ->assertJsonPath('errors.0.status', '404');
    }

    public function testUnsupportedMethodIsJsonApiError(): void
    {
        $device = Device::factory()->create();
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson(self::URI, ['data' => []])->assertStatus(405)->assertJsonPath('errors.0.status', '405');
        $this->deleteJson(self::URI . '/' . $device->device_id)->assertStatus(405);
        $this->assertDatabaseHas('devices', ['device_id' => $device->device_id]);
    }

    public function testUnsupportedQueryParametersAreBadRequests(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $parameters = [
            'sort=unknown' => 'sort',
            'filter[unknown]=1' => 'filter[unknown]',
            'filter[os][unknown]=1' => 'filter[os]',
            'filter[os][eq]=ios&filter[os][like]=x' => 'filter[os]',
            'sort=--hostname' => 'sort',
            'filter[isUp]=maybe' => 'filter[isUp]',
            'include=ports' => 'include',
            'page[size]=100000' => 'page[size]',
            'page[number]=0' => 'page[number]',
            'page=2' => 'page',
        ];

        foreach ($parameters as $query => $parameter) {
            $this->getJson(self::URI . '?' . $query)
                ->assertStatus(400)
                ->assertJsonPath('errors.0.status', '400')
                ->assertJsonPath('errors.0.source.parameter', $parameter);
        }
    }

    public function testUnknownLowercaseQueryParametersAreBadRequests(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . '?sorts=hostname')
            ->assertStatus(400)
            ->assertJsonPath('errors.0.source.parameter', 'sorts');

        // implementation specific names must contain a character other than a-z
        $this->getJson(self::URI . '?customParameter=1')->assertOk();
    }

    public function testAcceptNegotiation(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $acceptable = [
            'application/vnd.api+json',
            'application/vnd.api+json; profile="https://example.com/profile"',
            'application/vnd.api+json;q=0.9',
            'application/vnd.api+json; ext="https://example.com/ext", application/vnd.api+json',
            'application/json',
            '*/*',
        ];
        foreach ($acceptable as $accept) {
            $this->get(self::URI, ['Accept' => $accept])->assertOk();
        }

        foreach (['application/vnd.api+json; ext="https://example.com/ext"', 'application/vnd.api+json; charset=utf-8'] as $accept) {
            $this->get(self::URI, ['Accept' => $accept])
                ->assertStatus(406)
                ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE)
                ->assertJsonPath('errors.0.status', '406');
        }
    }

    public function testRequestBodiesMustBeJsonApi(): void
    {
        $send = fn (string $contentType) => (new EnforceJsonApi)->handle(
            Request::create('/api/v1/test', 'POST', server: ['CONTENT_TYPE' => $contentType], content: '{"data":{}}'),
            fn () => response()->noContent(),
        )->getStatusCode();

        $this->assertSame(204, $send('application/vnd.api+json'));
        $this->assertSame(204, $send('application/vnd.api+json; profile="https://example.com/profile"'));

        foreach (['application/json', 'application/vnd.api+json; ext="https://example.com/ext"', 'application/vnd.api+json; charset=utf-8', ''] as $contentType) {
            try {
                $send($contentType);
                $this->fail("$contentType was accepted");
            } catch (HttpException $e) {
                $this->assertSame(415, $e->getStatusCode(), $contentType);
            }
        }
    }

    public function testDocumentsHaveTopLevelMembers(): void
    {
        $device = Device::factory()->create();
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . '/' . $device->device_id)
            ->assertJsonPath('jsonapi.version', '1.1')
            ->assertJsonPath('links.self', url(self::URI . '/' . $device->device_id));
        $this->getJson(self::URI . '?sort=hostname')
            ->assertJsonPath('jsonapi.version', '1.1')
            ->assertJsonPath('links.self', url(self::URI . '?sort=hostname'));
        $this->getJson('/api/v1/system')
            ->assertJsonPath('jsonapi.version', '1.1')
            ->assertJsonPath('links.self', url('/api/v1/system'));
        $this->getJson('/api/v1/health')->assertJsonPath('jsonapi.version', '1.1');
        $this->getJson(self::URI . '/999999')->assertJsonPath('jsonapi.version', '1.1');
    }

    public function testOtherErrorsAreJsonApiErrors(): void
    {
        $middleware = new FormatJsonApiError;
        $request = Request::create('/api/v1/test');
        config(['app.debug' => false]);

        $clientError = $middleware->handle($request, fn () => response()->json(['message' => 'Missing repository key.'], 400));
        $this->assertSame([['status' => '400', 'title' => 'Bad Request', 'detail' => 'Missing repository key.']], $this->errors($clientError));

        $serverError = $middleware->handle($request, fn () => response()->json(['message' => 'SQLSTATE secret details'], 500));
        $this->assertSame([['status' => '500', 'title' => 'Internal Server Error', 'detail' => 'An unexpected error occurred.']], $this->errors($serverError));

        $jsonApiError = response()->json(['errors' => [['status' => '409', 'title' => 'Conflict', 'detail' => 'Kept as is.']]], 409);
        $this->assertSame($jsonApiError, $middleware->handle($request, fn () => $jsonApiError));

        $throttled = $middleware->handle($request, fn () => response()->json(['message' => 'Too Many Attempts.'], 429, ['Retry-After' => '42']));
        $this->assertSame('429', $this->errors($throttled)[0]['status']);
        $this->assertSame('42', $throttled->headers->get('Retry-After'));

        $rendered = $middleware->handle($request, fn () => response()->json(['message' => 'Not here.'], 405, ['Allow' => 'GET, HEAD']));
        $this->assertSame([['status' => '405', 'title' => 'Method Not Allowed', 'detail' => 'Not here.']], $this->errors($rendered));
        $this->assertSame('GET, HEAD', $rendered->headers->get('Allow'));

        $thrown = $middleware->handle($request, fn () => throw new HttpException(405, 'Not here.', null, ['Allow' => 'GET']));
        $this->assertSame([['status' => '405', 'title' => 'Method Not Allowed', 'detail' => 'Not here.']], $this->errors($thrown));
        $this->assertSame('GET', $thrown->headers->get('Allow'));
    }

    public function testServerErrorsKeepDebugDetailsWhenDebugging(): void
    {
        config(['app.debug' => true]);
        $original = response()->json(['message' => 'Something broke'], 500);

        $this->assertSame($original, (new FormatJsonApiError)->handle(Request::create('/api/v1/test'), fn () => $original));
    }

    public function testValidationErrorsPointToAttributes(): void
    {
        $exception = ValidationException::withMessages(['name' => 'The name field is required.', 'tags.0' => 'Invalid tag.']);
        $response = (new FormatJsonApiError)->handle(Request::create('/api/v1/test'), fn () => response()->json([], 422)->withException($exception));

        $this->assertSame('application/vnd.api+json', $response->headers->get('Content-Type'));
        $this->assertSame(['/name', '/tags/0'], array_column(array_column($this->errors($response), 'source'), 'pointer'));
    }

    public function testUnknownRecordIsJsonApiNotFound(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . '/999999')
            ->assertNotFound()
            ->assertJsonPath('errors.0.status', '404');
    }
}
