<?php

namespace LibreNMS\Tests\Unit\Alert\Transports;

use App\Models\AlertTransport;
use App\Models\Device;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\Transport\Alertmanager;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;
use Psr\Http\Message\RequestInterface;

final class AlertmanagerTest extends TestCase
{
    public function testClientCertificateAndClientSettingsReachEveryPooledRequest(): void
    {
        $optionsByUrl = [];
        Http::fake();
        Http::globalMiddleware(function (callable $handler) use (&$optionsByUrl): callable {
            return function (RequestInterface $request, array $options) use ($handler, &$optionsByUrl): PromiseInterface {
                $optionsByUrl[(string) $request->getUri()] = $options;

                return $handler($request, $options);
            };
        });

        $this->transport([
            'alertmanager-client-cert' => '/certs/tls.crt',
            'alertmanager-client-key' => '/certs/tls.key',
        ])->deliverAlert($this->alertData());

        Http::assertSentCount(2);
        self::assertCount(2, $optionsByUrl);
        foreach ($optionsByUrl as $options) {
            self::assertSame('/certs/tls.crt', $options['cert']);
            self::assertSame('/certs/tls.key', $options['ssl_key']);
            self::assertSame(2, $options['timeout']);
            self::assertArrayHasKey('proxy', $options);
        }

        Http::assertSent(fn (Request $request) => $request->hasHeader('User-Agent'));
    }

    public function testCertificateIsOptional(): void
    {
        $optionsByUrl = [];
        Http::fake();
        Http::globalMiddleware(function (callable $handler) use (&$optionsByUrl): callable {
            return function (RequestInterface $request, array $options) use ($handler, &$optionsByUrl): PromiseInterface {
                $optionsByUrl[(string) $request->getUri()] = $options;

                return $handler($request, $options);
            };
        });

        $this->transport()->deliverAlert($this->alertData());

        self::assertCount(2, $optionsByUrl);
        foreach ($optionsByUrl as $options) {
            self::assertArrayNotHasKey('cert', $options);
            self::assertArrayNotHasKey('ssl_key', $options);
        }
    }

    public function testBasicAuthStillReachesEveryRequest(): void
    {
        Http::fake();

        $this->transport([
            'alertmanager-username' => 'user',
            'alertmanager-password' => 'secret',
        ])->deliverAlert($this->alertData());

        Http::assertSentCount(2);
        foreach (Http::recorded() as [$request]) {
            self::assertTrue($request->hasHeader('Authorization', 'Basic ' . base64_encode('user:secret')));
        }
    }

    public function testEmptyOptionalFieldsDoNotInterruptDelivery(): void
    {
        Http::fake();

        $this->transport([
            'alertmanager-username' => null,
            'alertmanager-password' => null,
            'alertmanager-options' => null,
            'alertmanager-annotations' => null,
            'alertmanager-client-cert' => null,
            'alertmanager-client-key' => null,
        ])->deliverAlert($this->alertData());

        Http::assertSentCount(2);
    }

    public function testFiringAlertHasFutureEndTime(): void
    {
        Http::fake();

        $this->transport()->deliverAlert($this->alertData());

        Http::assertSent(function (Request $request): bool {
            $alert = $request->data()[0];

            return isset($alert['startsAt'], $alert['endsAt'])
                && strtotime($alert['startsAt']) < strtotime($alert['endsAt'])
                && strtotime($alert['endsAt']) >= time() + 19 * 60;
        });
    }

    public function testRecoveryHasPastEndTimeAfterStartTime(): void
    {
        Http::fake();

        $data = $this->alertData();
        $data['state'] = AlertState::RECOVERED;
        $this->transport()->deliverAlert($data);

        Http::assertSent(function (Request $request): bool {
            $alert = $request->data()[0];

            return isset($alert['startsAt'], $alert['endsAt'])
                && strtotime($alert['startsAt']) < strtotime($alert['endsAt'])
                && strtotime($alert['endsAt']) < time();
        });
    }

    public function testConfiguredAnnotationsReachEveryRequestWithoutChangingLabels(): void
    {
        Http::fake();

        $data = $this->alertData();
        $data['proc'] = 'https://runbooks.example.com/chassis';
        $this->transport([
            'alertmanager-options' => "stc_team=net\nann_legacy=still-a-label",
            'alertmanager-annotations' => "runbook={{ \$proc }}\ndashboard=https://grafana.example.com/d/device?var-device={{ \$hostname|url_encode }}\nowner=Net Team",
        ])->deliverAlert($data);

        Http::assertSentCount(2);
        foreach (Http::recorded() as [$request]) {
            $alert = $request->data()[0];
            self::assertSame('https://runbooks.example.com/chassis', $alert['annotations']['runbook']);
            self::assertSame('https://grafana.example.com/d/device?var-device=host.example.com', $alert['annotations']['dashboard']);
            self::assertSame('Net Team', $alert['annotations']['owner']);
            self::assertSame('net', $alert['labels']['team']);
            self::assertSame('still-a-label', $alert['labels']['ann_legacy']);
            self::assertArrayNotHasKey('runbook', $alert['labels']);
        }
    }

    public function testMissingTemplateValueDoesNotAddEmptyAnnotation(): void
    {
        Http::fake();

        $data = $this->alertData();
        $data['proc'] = '';
        $this->transport([
            'alertmanager-annotations' => "runbook={{ \$proc }}\nowner=Net Team",
        ])->deliverAlert($data);

        foreach (Http::recorded() as [$request]) {
            $annotations = $request->data()[0]['annotations'];
            self::assertArrayNotHasKey('runbook', $annotations);
            self::assertSame('Net Team', $annotations['owner']);
        }
    }

    public function testAbsentAnnotationConfigKeepsCurrentPayload(): void
    {
        Http::fake();

        $this->transport()->deliverAlert($this->alertData());

        foreach (Http::recorded() as [$request]) {
            self::assertSame([
                'summary' => 'Test-Rule',
                'title' => 'Testing transport from LibreNMS',
                'description' => 'This is a test alert',
            ], $request->data()[0]['annotations']);
        }
    }

    public function testAnnotationFieldIsExposedInTransportConfiguration(): void
    {
        $fields = array_column(Alertmanager::configTemplate()['config'], 'type', 'name');

        self::assertSame('textarea', $fields['alertmanager-annotations']);
    }

    /** @param array<string, string|null> $extraConfig */
    private function transport(array $extraConfig = []): Alertmanager
    {
        return new Alertmanager(new AlertTransport([
            'transport_config' => array_merge([
                'alertmanager-url' => 'https://alertmanager-0.example.com,https://alertmanager-1.example.com',
                'alertmanager-username' => '',
                'alertmanager-password' => '',
                'alertmanager-options' => '',
            ], $extraConfig),
        ]));
    }

    /** @return array<string, mixed> */
    private function alertData(): array
    {
        /** @var Device $device */
        $device = Device::factory()->make(['hostname' => 'host.example.com']);

        return AlertData::testData($device);
    }
}
