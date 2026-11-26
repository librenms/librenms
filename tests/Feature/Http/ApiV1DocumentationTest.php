<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Yaml\Yaml;

final class ApiV1DocumentationTest extends DBTestCase
{
    use DatabaseTransactions;

    private const string REFERENCE = '/api-docs/v1';
    private const string SPECIFICATION = self::REFERENCE . '/openapi.json';

    public function testSpecListsAllAndOnlyApiV1Operations(): void
    {
        $expected = [];
        foreach (Route::getRoutes()->getRoutes() as $route) {
            if ($route->named('api.v1.*')) {
                $methods = $route->methods();
                foreach ($methods as $method) {
                    if ($method !== 'HEAD' || ! in_array('GET', $methods, true)) {
                        $expected[$method . ' ' . str($route->uri())->after('api/v1')] = in_array('auth:sanctum', $route->gatherMiddleware(), true);
                    }
                }
            }
        }

        $specification = Yaml::parseFile(resource_path('openapi/v1.yaml'));
        $documented = [];
        foreach ($specification['paths'] as $path => $pathItem) {
            foreach (array_intersect_key($pathItem, array_flip(['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'])) as $method => $operation) {
                $security = $operation['security'] ?? $specification['security'];
                $documented[strtoupper($method) . ' ' . $path] = isset($security[0]['bearerAuth']);
            }
        }

        ksort($expected);
        ksort($documented);
        $this->assertSame($expected, $documented);
    }

    public function testDocumentationRequiresAWebSessionAndApiAccess(): void
    {
        LibrenmsConfig::set('api.v1.enabled', true);

        $this->get(self::REFERENCE)->assertRedirect(route('login'));
        $this->get(self::SPECIFICATION)->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['enabled' => true]));
        $this->get(self::REFERENCE)->assertForbidden();
        $this->get(self::SPECIFICATION)->assertForbidden();
    }

    public function testDocumentationIsHiddenWhenApiV1IsDisabled(): void
    {
        LibrenmsConfig::set('api.v1.enabled', false);
        $this->actingAs(User::factory()->admin()->create(['enabled' => true]));

        $this->get(self::REFERENCE)->assertNotFound();
        $this->get(self::SPECIFICATION)->assertNotFound();
        $this->get(route('api-access.index'))->assertOk()->assertDontSeeText('API v1 Reference');
    }

    public function testApiUserCanUseDocumentationUnderASubdirectory(): void
    {
        LibrenmsConfig::set('api.v1.enabled', true);
        $user = User::factory()->create(['enabled' => true]);
        $user->givePermissionTo(Permission::findOrCreate('api.access'));
        $this->actingAs($user);

        $root = 'https://monitoring.example.test/librenms';
        $server = [
            'SCRIPT_NAME' => '/librenms/index.php',
            'SCRIPT_FILENAME' => '/var/www/librenms/html/index.php',
        ];
        $this->call('GET', $root . self::REFERENCE, server: $server)
            ->assertOk()
            ->assertSee('navbar-sticky-top')
            ->assertSee($root . '/api-access')
            ->assertSee($root . self::SPECIFICATION)
            ->assertSee('download="librenms-api-v1.json"', escape: false)
            ->assertSee('id="librenms-api-reference"', escape: false)
            ->assertDontSee('id="api-reference"', escape: false)
            ->assertSee($root . '/js/scalar-api-reference-1.84.0.min.js');
        $response = $this->call('GET', $root . self::SPECIFICATION, server: $server)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('servers.0.url', $root . '/api/v1');

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->call('GET', $root . '/api-access', server: $server)
            ->assertOk()
            ->assertSeeText('API v1 Reference');
    }
}
