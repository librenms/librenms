<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Secret;
use App\Models\User;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SelectSecretControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbSetUp();

        Role::findOrCreate('admin');
        Permission::findOrCreate('secret.view');
    }

    protected function tearDown(): void
    {
        $this->dbTearDown();
        parent::tearDown();
    }

    public function testSelectSecretsReturnsPaginatedResults(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        Secret::create([
            'description' => 'SNMP Secret 1',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        Secret::create([
            'description' => 'IPMI Secret 1',
            'secret_type' => SecretType::Ipmi,
            'default' => false,
            'data' => ['user' => 'admin', 'password' => 'secret'],
        ]);

        $response = $this->actingAs($admin)->getJson(route('ajax.select.secret'));

        $response->assertOk();
        $response->assertJsonStructure(['results' => [['id', 'text']], 'pagination' => ['more']]);
        $this->assertCount(2, $response->json('results'));
    }

    public function testSelectSecretsFiltersBySecretType(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        Secret::create([
            'description' => 'SNMP Secret 1',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        Secret::create([
            'description' => 'IPMI Secret 1',
            'secret_type' => SecretType::Ipmi,
            'default' => false,
            'data' => ['user' => 'admin', 'password' => 'secret'],
        ]);

        $response = $this->actingAs($admin)->getJson(route('ajax.select.secret', ['secret_type' => 'snmp']));

        $response->assertOk();
        $results = $response->json('results');
        $this->assertCount(1, $results);
        $this->assertEquals('SNMP Secret 1', $results[0]['text']);
    }

    public function testSelectSecretsFiltersByTypeParam(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        Secret::create([
            'description' => 'SNMP Secret 1',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        Secret::create([
            'description' => 'IPMI Secret 1',
            'secret_type' => SecretType::Ipmi,
            'default' => false,
            'data' => ['user' => 'admin', 'password' => 'secret'],
        ]);

        $response = $this->actingAs($admin)->getJson(route('ajax.select.secret', ['type' => 'ipmi']));

        $response->assertOk();
        $results = $response->json('results');
        $this->assertCount(1, $results);
        $this->assertEquals('IPMI Secret 1', $results[0]['text']);
    }

    public function testSelectSecretsSearchesByDescription(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        Secret::create([
            'description' => 'Production Router Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        Secret::create([
            'description' => 'Staging Switch Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->getJson(route('ajax.select.secret', ['term' => 'Production']));

        $response->assertOk();
        $results = $response->json('results');
        $this->assertCount(1, $results);
        $this->assertEquals('Production Router Secret', $results[0]['text']);
    }
}
