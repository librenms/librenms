<?php

namespace LibreNMS\Tests\Unit\Alert\Transports;

use App\Models\AlertTransport;
use App\Models\Device;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\Transport;
use LibreNMS\Tests\TestCase;

final class MsteamsTest extends TestCase
{
    private const string WORKFLOW_URL = 'https://prod-12.westeurope.logic.azure.com/workflows/abc123/triggers/manual/paths/invoke';

    public function testWorkflowDelivery(): void
    {
        Http::fake();

        $this->deliver(self::WORKFLOW_URL, null, [
            'msg' => "<b>Device</b> is down\nPort: eth0\r\n\r\n<br />Rule: Test-Rule\n",
        ]);

        Http::assertSent(fn (Request $request) => $request->url() == self::WORKFLOW_URL &&
            $request->method() == 'POST' &&
            $request->hasHeader('Content-Type', 'application/json') &&
            $request->data() == [
                'type' => 'message',
                'attachments' => [
                    [
                        'contentType' => 'application/vnd.microsoft.card.adaptive',
                        'content' => [
                            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                            'type' => 'AdaptiveCard',
                            'version' => '1.4',
                            'body' => [
                                [
                                    'type' => 'TextBlock',
                                    'size' => 'Medium',
                                    'weight' => 'Bolder',
                                    'color' => 'Attention',
                                    'text' => 'Testing transport from LibreNMS',
                                    'wrap' => true,
                                ],
                                [
                                    'type' => 'TextBlock',
                                    'text' => "Device is down\n\nPort: eth0\n\nRule: Test-Rule",
                                    'wrap' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function testWorkflowDeliveryCutsLongMessage(): void
    {
        Http::fake();

        $this->deliver(self::WORKFLOW_URL, null, ['msg' => str_repeat('é', 20000)]);

        Http::assertSent(function (Request $request) {
            $text = $request->data()['attachments'][0]['content']['body'][1]['text'];

            return $text === str_repeat('é', 10000);
        });
    }

    public function testWorkflowDeliveryKeepsHtmlBlockBreaks(): void
    {
        Http::fake();

        $this->deliver(self::WORKFLOW_URL, null, ['msg' => '<p>Device: router1</p><p>State: <b>down</b><br>Port: eth0</p>']);

        Http::assertSent(function (Request $request) {
            $text = $request->data()['attachments'][0]['content']['body'][1]['text'];

            return $text === "Device: router1\n\nState: down\n\nPort: eth0";
        });
    }

    public function testJsonDelivery(): void
    {
        Http::fake();

        $template = '{"type": "message", "attachments": []}';
        $this->deliver(self::WORKFLOW_URL, 'on', ['msg' => $template, 'uid' => '1']);

        Http::assertSent(fn (Request $request) => $request->url() == self::WORKFLOW_URL &&
            $request->method() == 'POST' &&
            $request->hasHeader('Content-Type', 'application/json') &&
            $request->body() === $template);
    }

    /**
     * @param  array<string, mixed>  $alert
     */
    private function deliver(string $url, ?string $useJson, array $alert): void
    {
        $msteams = new Transport\Msteams(new AlertTransport([
            'transport_config' => [
                'msteam-url' => $url,
                'use-json' => $useJson,
            ],
        ]));

        /** @var Device $mock_device */
        $mock_device = Device::factory()->make();
        $msteams->deliverAlert([...AlertData::testData($mock_device), ...$alert]);
    }
}
