<?php

namespace LibreNMS\Tests\Feature\Alert;

use App\Models\AlertTemplate;
use App\Models\AlertTransport;
use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\RunAlerts;
use LibreNMS\Tests\TestCase;

final class AlertmanagerDeliveryLogTest extends TestCase
{
    use RefreshDatabase;

    public function testPartialFailureLogsOnceAndLaterTransportsStillRun(): void
    {
        $device = Device::factory()->create();
        $template = AlertTemplate::factory()->create(['template' => 'Test alert body']);
        $failed = $this->transport('Partial transport', 'https://ok.example.net,https://bad.example.net');
        $later = $this->transport('Later transport', 'https://later.example.net');
        Http::fake([
            'bad.example.net/*' => Http::response('private response', 503),
            '*' => Http::response('', 200),
        ]);
        $logger = Log::spy();
        $lastEventId = Eventlog::max('event_id') ?? 0;
        $data = AlertData::testData($device);
        $data['template'] = $template;
        ob_start();
        try {
            (new RunAlerts)->extTransports($data, [$failed->toArray(), $later->toArray()]);
        } finally {
            ob_end_clean();
        }

        $events = Eventlog::where('device_id', $device->device_id)->where('event_id', '>', $lastEventId)->get();
        self::assertCount(2, $events);
        $errors = $events->filter(fn (Eventlog $event) => str_contains($event->message, 'Could not issue'));
        self::assertCount(1, $errors);
        self::assertStringContainsString('1 of 2', $errors->first()->message);
        self::assertStringContainsString('Partial transport', $errors->first()->message);
        self::assertStringContainsString('bad.example.net', $errors->first()->message);
        self::assertStringContainsString('HTTP 503', $errors->first()->message);
        self::assertStringNotContainsString('private response', $errors->first()->message);
        $logger->shouldHaveReceived('warning')->once();
        Http::assertSentCount(3);
    }

    public function testTemplateFilterErrorDoesNotStopLaterTransports(): void
    {
        $device = Device::factory()->create();
        $template = AlertTemplate::factory()->create(['template' => 'Test alert body']);
        $failed = $this->transport('Invalid template transport', 'https://bad.example.net');
        $config = $failed->transport_config;
        $config['alertmanager-ends-at'] = '{{ $expiry|slice(garbage) }}';
        $failed->transport_config = $config;
        $failed->save();
        $later = $this->transport('Later transport', 'https://later.example.net');
        Http::fake();
        $logger = Log::spy();
        $data = AlertData::testData($device);
        $data['template'] = $template;
        $data['expiry'] = '20m';
        $lastEventId = Eventlog::max('event_id') ?? 0;
        ob_start();
        try {
            (new RunAlerts)->extTransports($data, [$failed->toArray(), $later->toArray()]);
        } finally {
            ob_end_clean();
        }
        $events = Eventlog::where('device_id', $device->device_id)->where('event_id', '>', $lastEventId)->get();
        self::assertCount(2, $events);
        self::assertStringContainsString('template evaluation failed', $events->first()->message);
        $logger->shouldHaveReceived('warning')->once();
        Http::assertSentCount(1);
    }

    private function transport(string $name, string $urls): AlertTransport
    {
        $transport = new AlertTransport(['transport_config' => ['alertmanager-url' => $urls]]);
        $transport->transport_name = $name;
        $transport->transport_type = 'alertmanager';
        $transport->save();

        return $transport;
    }
}
