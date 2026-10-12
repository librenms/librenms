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

    public function testTemplatedLabelsReachEveryRequestWithoutDisturbingExistingLabels(): void
    {
        Http::fake();

        $this->transport([
            'alertmanager-options' => 'stc_source=librenms',
            'alertmanager-labels' => "team=net\npriority={{ \$severity|replace('critical','3')|replace('warning','4') }}",
        ])->deliverAlert($this->alertData());

        Http::assertSentCount(2);
        foreach (Http::recorded() as [$request]) {
            $labels = $request->data()[0]['labels'];
            self::assertSame('net', $labels['team']);
            self::assertSame('3', $labels['priority']);
            self::assertSame('librenms', $labels['source']);
            self::assertSame('Test-Rule', $labels['alertname']);
            self::assertSame('critical', $labels['severity']);
            self::assertSame('host.example.com', $labels['instance']);
        }
    }

    public function testTemplatedLabelResolvingEmptyIsOmittedAndLabelNameIsTrimmed(): void
    {
        Http::fake();

        $this->transport([
            'alertmanager-labels' => "priority={{ \$severity|replace('ok','') }}\n oncall =net",
        ])->deliverAlert(array_merge($this->alertData(), ['severity' => 'ok']));

        foreach (Http::recorded() as [$request]) {
            $labels = $request->data()[0]['labels'];
            self::assertArrayNotHasKey('priority', $labels);
            self::assertSame('net', $labels['oncall']);
            self::assertArrayNotHasKey(' oncall ', $labels);
        }
    }

    public function testTemplatedLabelOverridesAnOptionsLabelOfTheSameName(): void
    {
        Http::fake();

        $this->transport([
            'alertmanager-options' => 'stc_team=unset',
            'alertmanager-labels' => 'team=net',
        ])->deliverAlert($this->alertData());

        foreach (Http::recorded() as [$request]) {
            self::assertSame('net', $request->data()[0]['labels']['team']);
        }
    }

    public function testWhitespaceAroundASubstitutedLabelValueIsRemoved(): void
    {
        Http::fake();

        $this->transport([
            'alertmanager-labels' => 'site={{ $location }}',
        ])->deliverAlert(array_merge($this->alertData(), ['location' => "Rack 4\n"]));

        foreach (Http::recorded() as [$request]) {
            self::assertSame('Rack 4', $request->data()[0]['labels']['site']);
        }
    }

    public function testTemplatedLabelsAreIdenticalOnFiringAndRecovery(): void
    {
        Http::fake();

        $config = ['alertmanager-labels' => "priority={{ \$severity|replace('critical','3') }}\nteam=net"];
        $firing = $this->alertData();
        // RunAlerts rewrites these three for a recovery; none may reach a label
        $recovery = array_merge($this->alertData(), [
            'state' => AlertState::RECOVERED,
            'title' => 'Device host.example.com recovered from Test-Rule',
            'elapsed' => '3h 20m',
            'id' => '001',
        ]);

        $this->transport($config)->deliverAlert($firing);
        $this->transport($config)->deliverAlert($recovery);

        $labelSets = Http::recorded()->map(fn (array $recorded) => $recorded[0]->data()[0]['labels']);

        self::assertCount(4, $labelSets);
        self::assertCount(1, $labelSets->map(fn (array $labels) => json_encode($labels))->unique());
        self::assertSame([
            'alertname' => 'Test-Rule',
            'severity' => 'critical',
            'instance' => 'host.example.com',
            'priority' => '3',
            'team' => 'net',
        ], $labelSets->first());
    }

    public function testTemplatedLabelsDoNotOverrideTheConfiguredAnnotations(): void
    {
        Http::fake();

        $this->transport([
            'alertmanager-labels' => 'team=net',
            'alertmanager-annotations' => 'owner=Net Team',
        ])->deliverAlert($this->alertData());

        foreach (Http::recorded() as [$request]) {
            $alert = $request->data()[0];
            self::assertSame('net', $alert['labels']['team']);
            self::assertArrayNotHasKey('team', $alert['annotations']);
            self::assertSame('Net Team', $alert['annotations']['owner']);
            self::assertArrayNotHasKey('owner', $alert['labels']);
        }
    }

    public function testAbsentTemplatedLabelConfigKeepsCurrentLabels(): void
    {
        Http::fake();

        $this->transport()->deliverAlert($this->alertData());

        foreach (Http::recorded() as [$request]) {
            self::assertSame([
                'alertname' => 'Test-Rule',
                'severity' => 'critical',
                'instance' => 'host.example.com',
            ], $request->data()[0]['labels']);
        }
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
