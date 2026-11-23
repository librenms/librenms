<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class RipeToolTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    public function testWhoisResultsAreEscaped(): void
    {
        Http::fake(['stat.ripe.net/data/whois/*' => Http::response([
            'status' => 'ok',
            'data' => ['records' => [[
                ['key' => 'netname', 'value' => 'EXAMPLE-NET'],
                ['key' => 'descr', 'value' => '<script>alert(1)</script>'],
            ]]],
        ])]);

        $this->actingAs($this->admin())
            ->get(route('tool.ripe', ['type' => 'whois', 'query' => '192.0.2.1']))
            ->assertOk()
            ->assertSee('EXAMPLE-NET')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function testAbuseContacts(): void
    {
        Http::fake(['stat.ripe.net/data/abuse-contact-finder/*' => Http::response([
            'status' => 'ok',
            'data' => ['abuse_contacts' => ['abuse@example.com']],
        ])]);

        $this->actingAs($this->admin())
            ->get(route('tool.ripe', ['type' => 'abuse-contact-finder', 'query' => '192.0.2.1']))
            ->assertOk()
            ->assertSee('abuse@example.com');
    }

    public function testApiErrorIsShown(): void
    {
        Http::fake(['stat.ripe.net/*' => Http::response(['status' => 'error', 'messages' => [['error', 'bad resource']]], 400)]);

        $this->actingAs($this->admin())
            ->get(route('tool.ripe', ['query' => '192.0.2.1']))
            ->assertOk()
            ->assertSee('bad resource');
    }

    public function testRequiresPermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)->get(route('tool.ripe'))->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
