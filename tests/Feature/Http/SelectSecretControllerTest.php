<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Secret;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class SelectSecretControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Permission::findOrCreate('secret.view');
    }

    public function testSelectSecretsReturnsPaginatedResults(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

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
        $admin = User::factory()->admin()->create(['enabled' => 1]);

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
        $admin = User::factory()->admin()->create(['enabled' => 1]);

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
        $admin = User::factory()->admin()->create(['enabled' => 1]);

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
