<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Http\Middleware\EnforceJsonApi;
use App\Http\Middleware\FormatJsonApiError;
use App\Models\AlertTemplate;
use App\Models\User;
use Binaryk\LaravelRestify\Restify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Tests\Mocks\RestifyAlertTemplateRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class RepositoryAuthorizationTest extends DBTestCase
{
    use DatabaseTransactions;

    private const string URI = '/api/v1/test-alert-templates';

    /** @var list<class-string> */
    private array $registeredRepositories;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('api.v1.enabled', true);
        $this->registeredRepositories = Restify::$repositories;
        Restify::repositories([RestifyAlertTemplateRepository::class]);
    }

    protected function tearDown(): void
    {
        Restify::$repositories = $this->registeredRepositories;

        parent::tearDown();
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
        Sanctum::actingAs($this->userWithPermissions(['alert-template.view']));

        $this->getJson('/api/v1/system')
            ->assertForbidden()
            ->assertJsonPath('errors.0.status', '403');
        $this->getJson(self::URI)->assertForbidden();
    }

    public function testRepositoryRequiresViewAny(): void
    {
        Sanctum::actingAs($this->userWithPermissions(['api.access']));

        $this->getJson('/api/v1/system')->assertOk();
        $this->getJson(self::URI)
            ->assertForbidden()
            ->assertJsonPath('errors.0.status', '403');
    }

    public function testGlobalReadCanListAndShow(): void
    {
        $template = AlertTemplate::factory()->create(['name' => 'Shown Template']);
        Sanctum::actingAs($this->userWithPermissions(['api.access'], 'global-read'));

        $this->getJson(self::URI)
            ->assertOk()
            ->assertJsonFragment(['name' => 'Shown Template']);

        $this->getJson(self::URI . '/' . $template->id)
            ->assertOk()
            ->assertJsonPath('data.type', 'test-alert-templates')
            ->assertJsonPath('data.attributes.name', 'Shown Template');
    }

    public function testGlobalReadCannotWrite(): void
    {
        $template = AlertTemplate::factory()->create();
        Sanctum::actingAs($this->userWithPermissions(['api.access'], 'global-read'));

        $this->postJson(self::URI, ['name' => 'New', 'template' => 'body'])->assertForbidden();
        $this->putJson(self::URI . '/' . $template->id, ['name' => 'Changed', 'template' => 'body'])->assertForbidden();
        $this->deleteJson(self::URI . '/' . $template->id)->assertForbidden();

        $this->assertDatabaseHas('alert_templates', ['id' => $template->id, 'name' => $template->name]);
    }

    public function testCreatePermissionAllowsStore(): void
    {
        Sanctum::actingAs($this->userWithPermissions(['api.access', 'alert-template.create']));

        $this->postJson(self::URI, ['name' => 'Created Template', 'template' => 'body'])
            ->assertCreated()
            ->assertJsonPath('data.attributes.name', 'Created Template');

        $this->assertDatabaseHas('alert_templates', ['name' => 'Created Template']);
    }

    public function testUpdateAndDeletePermissions(): void
    {
        $template = AlertTemplate::factory()->create();
        Sanctum::actingAs($this->userWithPermissions(['api.access', 'alert-template.update', 'alert-template.delete']));

        $this->putJson(self::URI . '/' . $template->id, ['name' => 'Renamed', 'template' => 'body'])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Renamed');

        $this->deleteJson(self::URI . '/' . $template->id)->assertNoContent();
        $this->assertDatabaseMissing('alert_templates', ['id' => $template->id]);
    }

    public function testBulkOperationsAreDeniedForAdmin(): void
    {
        $template = AlertTemplate::factory()->create();
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson(self::URI . '/bulk', [['name' => 'Bulk', 'template' => 'body']])->assertForbidden();
        $this->postJson(self::URI . '/bulk/update', [['id' => $template->id, 'name' => 'Bulk Renamed', 'template' => 'body']])->assertForbidden();
        $this->deleteJson(self::URI . '/bulk/delete', [$template->id])->assertForbidden();

        $this->assertDatabaseMissing('alert_templates', ['name' => 'Bulk']);
        $this->assertDatabaseHas('alert_templates', ['id' => $template->id, 'name' => $template->name]);
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
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->postJson(self::URI, ['name' => str_repeat('a', 256)])
            ->assertUnprocessable()
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE)
            ->assertJsonMissingPath('message')
            ->assertJsonPath('errors.0.status', '422');

        $this->assertEqualsCanonicalizing(['/name', '/template'], array_column(array_column($response->json('errors'), 'source'), 'pointer'));
    }

    public function testUnknownRecordIsJsonApiNotFound(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . '/999999')
            ->assertNotFound()
            ->assertJsonPath('errors.0.status', '404');
    }
}
