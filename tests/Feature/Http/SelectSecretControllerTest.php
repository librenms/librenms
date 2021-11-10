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

        $snmpSecret = Secret::create([
            'description' => 'SNMP Secret 1',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $ipmiSecret = Secret::create([
            'description' => 'IPMI Secret 1',
            'secret_type' => SecretType::Ipmi,
            'data' => ['user' => 'admin', 'password' => 'secret'],
        ]);

        $response = $this->actingAs($admin)->getJson(route('ajax.select.secret'));

        $response->assertOk();
        $response->assertJsonStructure(['results' => [['id', 'text']], 'pagination' => ['more']]);

        $results = $response->collect('results');
        $this->assertTrue($results->contains('id', $snmpSecret->id));
        $this->assertTrue($results->contains('id', $ipmiSecret->id));
    }

    public function testSelectSecretsFiltersBySecretType(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $snmpSecret = Secret::create([
            'description' => 'SNMP Secret 1',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $ipmiSecret = Secret::create([
            'description' => 'IPMI Secret 1',
            'secret_type' => SecretType::Ipmi,
            'data' => ['user' => 'admin', 'password' => 'secret'],
        ]);

        $response = $this->actingAs($admin)->getJson(route('ajax.select.secret', ['secret_type' => 'snmp']));

        $response->assertOk();
        $results = $response->collect('results');
        $this->assertTrue($results->contains('id', $snmpSecret->id));
        $this->assertFalse($results->contains('id', $ipmiSecret->id));
    }

    public function testSelectSecretsFiltersByTypeParam(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $snmpSecret = Secret::create([
            'description' => 'SNMP Secret 1',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $ipmiSecret = Secret::create([
            'description' => 'IPMI Secret 1',
            'secret_type' => SecretType::Ipmi,
            'data' => ['user' => 'admin', 'password' => 'secret'],
        ]);

        $response = $this->actingAs($admin)->getJson(route('ajax.select.secret', ['type' => 'ipmi']));

        $response->assertOk();
        $results = $response->collect('results');
        $this->assertTrue($results->contains('id', $ipmiSecret->id));
        $this->assertFalse($results->contains('id', $snmpSecret->id));
    }

    public function testSelectSecretsSearchesByDescription(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $prodSecret = Secret::create([
            'description' => 'Unique Production Router Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $stageSecret = Secret::create([
            'description' => 'Unique Staging Switch Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->getJson(route('ajax.select.secret', ['term' => 'Unique Production Router']));

        $response->assertOk();
        $results = $response->collect('results');
        $this->assertTrue($results->contains('id', $prodSecret->id));
        $this->assertFalse($results->contains('id', $stageSecret->id));
    }
}
