<?php

namespace LibreNMS\Tests\Unit;

use App\Models\AlertTransport;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http as LaravelHttp;
use LibreNMS\Tests\TestCase;

class ApiTransportTest extends TestCase
{
    public function testGetMultilineVariables(): void
    {
        /** @var AlertTransport $transport */
        $transport = AlertTransport::factory()->api('text={{ $msg }}')->make();

        LaravelHttp::fake([
            '*' => LaravelHttp::response(),
        ]);

        $obj = ['msg' => "This is a multi-line\nalert."];
        $result = $transport->instance()->deliverAlert($obj);

        $this->assertTrue($result);

        LaravelHttp::assertSentCount(1);
        LaravelHttp::assertSent(function (Request $request) {
            return $request->method() == 'GET' &&
                $request->url() == 'https://librenms.org?text=This%20is%20a%20multi-line%0Aalert.';
        });
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

        $obj = ['msg' => "This is a post multi-line\nalert."];
        $result = $transport->instance()->deliverAlert($obj);

        $this->assertTrue($result);

        LaravelHttp::assertSentCount(1);
        LaravelHttp::assertSent(function (Request $request) {
            return $request->method() == 'POST' &&
                $request->url() == 'https://librenms.org?text=This%20is%20a%20post%20multi-line%0Aalert.' &&
                $request->body() == "bodytext=This is a post multi-line\nalert.";
        });
    }
}
