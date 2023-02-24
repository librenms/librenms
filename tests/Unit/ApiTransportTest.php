<?php

namespace LibreNMS\Tests\Unit;

use App\Models\AlertTransport;
use GuzzleHttp\Psr7\Response;
use LibreNMS\Config;
use LibreNMS\Tests\TestCase;
use LibreNMS\Tests\Traits\MockGuzzleClient;

class ApiTransportTest extends TestCase
{
    use MockGuzzleClient;

    public function testGetMultilineVariables(): void
    {
        /** @var AlertTransport $transport */
        $transport = AlertTransport::factory()->api('text={{ $msg }}')->make();

        $this->mockGuzzleClient([
            new Response(200),
        ]);

        $obj = ['msg' => "This is a multi-line\nalert."];
        $opts = Config::get('alert.transports.' . $transport->transport_type);
        $result = $transport->instance()->deliverAlert($obj, $opts);

        $this->assertTrue($result);

        $history = $this->guzzleRequestHistory();
        $this->assertCount(1, $history);
        $this->assertEquals('GET', $history[0]->getMethod());
        $this->assertEquals('text=This%20is%20a%20multi-line%0Aalert.', $history[0]->getUri()->getQuery());
    }

    public function testPostMultilineVariables(): void
    {
        /** @var AlertTransport $transport */
        $transport = AlertTransport::factory()->api(
            'text={{ $msg }}',
            'post',
            'bodytext={{ $msg }}',
        )->make();

        $this->mockGuzzleClient([
            new Response(200),
        ]);

        $obj = ['msg' => "This is a post multi-line\nalert."];
        $opts = Config::get('alert.transports.' . $transport->transport_type);
        $result = $transport->instance()->deliverAlert($obj, $opts);

        $this->assertTrue($result);

        $history = $this->guzzleRequestHistory();
        $this->assertCount(1, $history);
        $this->assertEquals('POST', $history[0]->getMethod());
        // FUBAR
        $this->assertEquals('text=This%20is%20a%20post%20multi-line%0Aalert.', $history[0]->getUri()->getQuery());
        $this->assertEquals("bodytext=This is a post multi-line\nalert.", (string) $history[0]->getBody());
    }
}
