<?php

namespace LibreNMS\Tests\Unit;

use App\Models\AlertTransport;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http as LaravelHttp;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\Transport\Api;
use LibreNMS\Tests\TestCase;

final class ApiTransportTest extends TestCase
{
    public function testGetMultilineVariables(): void
    {
        /** @var AlertTransport $transport */
        $transport = AlertTransport::factory()->api('text={{ $msg }}')->make();

        LaravelHttp::fake([
            '*' => LaravelHttp::response(),
        ]);

        $obj = new AlertData(['msg' => "This is a multi-line\nalert."]);
        $result = new Api($transport)->deliver($obj);

        $this->assertTrue($result);

        LaravelHttp::assertSentCount(1);
        LaravelHttp::assertSent(fn (Request $request) => $request->method() == 'GET' &&
            $request->url() == 'https://librenms.org?text=This%20is%20a%20multi-line%0Aalert.');
    }

    public function testPostMultilineVariables(): void
    {
        /** @var AlertTransport $transport */
        $transport = AlertTransport::factory()->api(
            'text={{ $msg }}',
            'post',
            'bodytext={{ $msg }}',
        )->make();

        LaravelHttp::fake([
            '*' => LaravelHttp::response(),
        ]);

        $obj = new AlertData(['msg' => "This is a post multi-line\nalert."]);
        $result = new Api($transport)->deliver($obj);

        $this->assertTrue($result);

        LaravelHttp::assertSentCount(1);
        LaravelHttp::assertSent(fn (Request $request) => $request->method() == 'POST' &&
            $request->url() == 'https://librenms.org?text=This%20is%20a%20post%20multi-line%0Aalert.' &&
            $request->body() == "bodytext=This is a post multi-line\nalert.");
    }

    public function testNullOptionalConfigFieldsDoNotThrow(): void
    {
        /** @var AlertTransport $transport */
        $transport = AlertTransport::factory()->api('', 'post', '')->make();

        // Optional textarea fields (options, headers, body) are stored as null when left blank
        // on transports created before these fields existed, or cleared via the API. See #20418.
        $config = $transport->transport_config;
        $config['api-options'] = null;
        $config['api-headers'] = null;
        $config['api-body'] = null;
        $transport->transport_config = $config;

        LaravelHttp::fake([
            '*' => LaravelHttp::response(),
        ]);

        $result = new Api($transport)->deliver(new AlertData(['msg' => 'test']));

        $this->assertTrue($result);
        LaravelHttp::assertSentCount(1);
    }
}
