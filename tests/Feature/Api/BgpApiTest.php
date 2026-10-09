<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class BgpApiTest extends DBTestCase
{
    use DatabaseTransactions;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->admin()->create();
        $this->token = $user->createToken('test')->plainTextToken;
    }

    /**
     * JSON requests are trimmed and emptied by middleware; the documented curl --data form
     * (no JSON Content-Type) is not, so both must give the same result.
     *
     * @return array<string, array{bool}>
     */
    public static function contentTypes(): array
    {
        return [
            'json' => [true],
            'curl --data' => [false],
        ];
    }

    #[DataProvider('contentTypes')]
    public function testUpdatesDescription(bool $json): void
    {
        $peer = $this->createPeer('old');

        $this->send($json, $peer->bgpPeer_id, ['bgp_descr' => '  uplink  '])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('message', "BGP description for peer $peer->bgpPeerIdentifier on device $peer->device_id updated to uplink.");

        $this->assertSame('uplink', $peer->fresh()->bgpPeerDescr);
    }

    #[DataProvider('contentTypes')]
    public function testEmptyWhitespaceOrNullClearsDescription(bool $json): void
    {
        $peer = $this->createPeer('uplink');

        foreach (['', '   ', str_repeat(' ', 300), null] as $empty) {
            $peer->update(['bgpPeerDescr' => 'uplink']);

            $this->send($json, $peer->bgpPeer_id, ['bgp_descr' => $empty])
                ->assertStatus(200)
                ->assertJsonPath('message', "BGP description for peer $peer->bgpPeerIdentifier on device $peer->device_id cleared.");

            $this->assertSame('', $peer->fresh()->bgpPeerDescr);
        }
    }

    #[DataProvider('contentTypes')]
    public function testAcceptsZeroAndWholeNumbers(bool $json): void
    {
        $peer = $this->createPeer('uplink');

        $this->send($json, $peer->bgpPeer_id, ['bgp_descr' => '0'])->assertStatus(200);
        $this->assertSame('0', $peer->fresh()->bgpPeerDescr);

        // numbers (an AS number, a circuit ID) were accepted before and are stored as text
        $this->send($json, $peer->bgpPeer_id, ['bgp_descr' => 65001])->assertStatus(200);
        $this->assertSame('65001', $peer->fresh()->bgpPeerDescr);

        // whole numbers beyond PHP_INT_MAX are kept exact, not stored as a float
        $this->sendRaw($json, $peer->bgpPeer_id, '{"bgp_descr": 123456789012345678901234}')->assertStatus(200);
        $this->assertSame('123456789012345678901234', $peer->fresh()->bgpPeerDescr);
    }

    #[DataProvider('contentTypes')]
    public function testRejectsMalformedJson(bool $json): void
    {
        $peer = $this->createPeer('uplink');

        foreach (['', '{bgp_descr: "x"}', '"x"'] as $body) {
            $this->sendRaw($json, $peer->bgpPeer_id, $body)
                ->assertStatus(400)
                ->assertJsonPath('status', 'error');
        }

        $this->assertSame('uplink', $peer->fresh()->bgpPeerDescr);
    }

    #[DataProvider('contentTypes')]
    public function testAcceptsMaximumLength(bool $json): void
    {
        $peer = $this->createPeer('uplink');
        $descr = str_repeat('a', 255);

        $this->send($json, $peer->bgpPeer_id, ['bgp_descr' => $descr])->assertStatus(200);

        $this->assertSame($descr, $peer->fresh()->bgpPeerDescr);
    }

    #[DataProvider('contentTypes')]
    public function testRejectsMissingOrInvalidValue(bool $json): void
    {
        $peer = $this->createPeer('uplink');

        $this->send($json, $peer->bgpPeer_id, [])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message.bgp_descr.0', 'The bgp descr field must be present.');

        $this->send($json, $peer->bgpPeer_id, ['bgp_descr' => str_repeat('a', 256)])
            ->assertStatus(422)
            ->assertJsonPath('message.bgp_descr.0', 'The bgp descr field must not be greater than 255 characters.');

        // a float, a boolean or an array is not a description; one type error, not a size error
        foreach ([1e25, true, ['a'], range(1, 300)] as $invalid) {
            $this->send($json, $peer->bgpPeer_id, ['bgp_descr' => $invalid])
                ->assertStatus(422)
                ->assertJsonPath('message.bgp_descr', ['The bgp descr field must be a string.']);
        }

        $this->assertSame('uplink', $peer->fresh()->bgpPeerDescr);
    }

    public function testUnknownPeerIsReportedBeforeTheBody(): void
    {
        $this->send(true, 999999, [])
            ->assertStatus(404)
            ->assertJsonPath('message', 'BGP peer 999999 does not exist');

        $this->send(true, 'abc', [])
            ->assertStatus(400);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return TestResponse<\Illuminate\Http\Response>
     */
    private function send(bool $json, int|string $id, array $body): TestResponse
    {
        if ($json) {
            return $this->postJson("/api/v0/bgp/$id", $body, ['X-Auth-Token' => $this->token]);
        }

        return $this->sendRaw(false, $id, json_encode($body, JSON_PRESERVE_ZERO_FRACTION) ?: '');
    }

    /**
     * @return TestResponse<\Illuminate\Http\Response>
     */
    private function sendRaw(bool $json, int|string $id, string $content): TestResponse
    {
        return $this->call('POST', "/api/v0/bgp/$id", [], [], [], [
            'HTTP_X_AUTH_TOKEN' => $this->token,
            'CONTENT_TYPE' => $json ? 'application/json' : 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
        ], $content);
    }

    private function createPeer(string $descr): BgpPeer
    {
        $device = Device::factory()->create();

        return BgpPeer::factory()->create([
            'device_id' => $device->device_id,
            'bgpPeerDescr' => $descr,
        ]);
    }
}
