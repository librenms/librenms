<?php

namespace LibreNMS\Tests\Unit\Http;

use App\Http\Middleware\AccessLogIdentity;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;
use LibreNMS\Tests\TestCase;

final class AccessLogIdentityTest extends TestCase
{
    public function testMiddlewareIsInstalledForAllRequests(): void
    {
        $this->assertContains(AccessLogIdentity::class, app(Kernel::class)->getGlobalMiddleware());
    }

    public function testAuthenticatedWebRequestExposesLocalUserId(): void
    {
        $user = new User();
        $user->user_id = 42;
        $request = Request::create('/devices', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = (new AccessLogIdentity())->handle($request, fn () => new Response('ok'));

        $this->assertSame('42', $response->headers->get('X-LibreNMS-User-ID'));
        $this->assertNull($response->headers->get('X-LibreNMS-Token-ID'));
    }

    public function testApiRequestExposesTokenIdButNeverTokenValue(): void
    {
        $user = new User();
        $user->user_id = 42;
        $token = new PersonalAccessToken();
        $token->id = 17;
        $token->token = 'hashed-secret';
        $user->withAccessToken($token);
        $request = Request::create('/api/v0/ports?api_token=plain-secret', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = (new AccessLogIdentity())->handle($request, fn () => new Response('ok'));

        $this->assertSame('42', $response->headers->get('X-LibreNMS-User-ID'));
        $this->assertSame('17', $response->headers->get('X-LibreNMS-Token-ID'));
        $this->assertStringNotContainsString('plain-secret', json_encode($response->headers->all()));
        $this->assertStringNotContainsString('hashed-secret', json_encode($response->headers->all()));
    }

    public function testGuestRequestDoesNotInheritIdentityHeaders(): void
    {
        $request = Request::create('/login', 'GET');
        $request->setUserResolver(fn () => null);

        $response = (new AccessLogIdentity())->handle($request, fn () => new Response('ok', 200, [
            'X-LibreNMS-User-ID' => 'spoofed',
            'X-LibreNMS-Token-ID' => 'spoofed',
        ]));

        $this->assertNull($response->headers->get('X-LibreNMS-User-ID'));
        $this->assertNull($response->headers->get('X-LibreNMS-Token-ID'));
    }
}
