<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class ZiggyRoutesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testServesRoutesAndRouteFunctionAsJavascript(): void
    {
        $response = $this->actingAs(User::factory()->create(['enabled' => 1]))->get(route('js.routes'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertHeader('ETag');
        $this->assertStringStartsWith('globalThis.Ziggy={', $response->getContent());
        $this->assertStringContainsString('"dashboard.widget.add":', $response->getContent());
        $this->assertStringContainsString('.route=', $response->getContent());

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
    }

    public function testReturnsNotModifiedWhenEtagMatches(): void
    {
        $this->actingAs(User::factory()->create(['enabled' => 1]));
        $etag = $this->get(route('js.routes'))->headers->get('ETag');

        $this->get(route('js.routes'), ['If-None-Match' => $etag])
            ->assertStatus(304)
            ->assertContent('');
    }

    public function testRequiresAuthentication(): void
    {
        $this->get(route('js.routes'))
            ->assertRedirect(route('login'));
    }

    public function testGuestLayoutDoesNotLoadRoutesScript(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(route('js.routes'), false);
    }

    public function testPendingTwoFactorUserCanLoadRoutesScript(): void
    {
        LibrenmsConfig::set('twofactor', true);
        $twoFactorPending = ['twofactoradd' => ['key' => 'AAAAAAAAAAAAAAAA', 'counter' => false, 'fails' => 0]];

        $this->actingAs(User::factory()->create(['enabled' => 1]))
            ->withSession($twoFactorPending)
            ->get(route('2fa.form'))
            ->assertOk()
            ->assertSee('<script src="' . route('js.routes') . '"></script>', false);

        $this->get(route('js.routes'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');

        $this->get(route('about'))
            ->assertRedirect('/2fa');
    }
}
