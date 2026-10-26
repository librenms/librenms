<?php

namespace LibreNMS\Tests\Unit\Alert\Transports;

use App\Models\AlertTransport;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Alert\Transport\Alertmanager;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AlertmanagerTest extends TestCase
{
    /** @param array<string, mixed> $value */
    #[DataProvider('invalidOptions')]
    public function testInvalidStructuredOptionsAreRejected(array $value): void
    {
        $validator = Validator::make([
            'alertmanager-url' => 'https://am.example.net',
            'alertmanager-labels' => $value,
        ], Alertmanager::configTemplate()['validation']);

        self::assertTrue($validator->fails());
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function invalidOptions(): array
    {
        return [
            'hyphen' => [['team-name' => 'net']],
            'dot' => [['site.id' => 'lax']],
            'nested' => [['team' => ['net']]],
        ];
    }

    public function testOptionalConfigAndLegacyOptionsRemainValid(): void
    {
        self::assertTrue(Validator::make([
            'alertmanager-url' => 'https://am.example.net',
            'alertmanager-options' => 'stc_team=net',
        ], Alertmanager::configTemplate()['validation'])->passes());
    }

    public function testStructuredConfigIsReadableInTransportDetails(): void
    {
        $transport = new Alertmanager(new AlertTransport(['transport_config' => [
            'alertmanager-labels' => ['team' => 'network'],
        ]]));

        self::assertStringContainsString('team=network', $transport->displayDetails());
    }

    public function testStructuredTemplatesOverrideLegacyAndOmitEmptyValues(): void
    {
        Http::fake();
        $this->transport([
            'alertmanager-options' => "stc_team=legacy\ndyn_missing=missing\ninterface=ifName",
            'alertmanager-labels' => ['team' => 'network', 'priority' => "{{ \$severity|replace('critical','3') }}", 'empty' => '{{ $missing }}', 'zero' => '0'],
            'alertmanager-annotations' => ['runbook' => '{{ $proc }}', 'details' => ' {{ $msg }} '],
        ])->deliverAlert($this->alertData());
        $payload = Http::recorded()[0][0]->data()[0];
        self::assertSame('network', $payload['labels']['team']);
        self::assertSame('3', $payload['labels']['priority']);
        self::assertSame('0', $payload['labels']['zero']);
        self::assertSame('xe-0/0/0', $payload['labels']['interface']);
        self::assertArrayNotHasKey('dyn_missing', $payload['labels']);
        self::assertArrayNotHasKey('empty', $payload['labels']);
        self::assertSame('https://runbooks.example.net', $payload['annotations']['runbook']);
        self::assertSame('Test message', $payload['annotations']['details']);
    }

    /** @return array<string, array{string, int}> */
    public static function expiryDurations(): array
    {
        return ['minutes' => ['20m', 1200], 'seconds' => ['30s', 30], 'hours' => ['2h', 7200], 'days' => ['1d', 86400], 'template' => ['{{ $expiry }}', 1200]];
    }

    #[DataProvider('expiryDurations')]
    public function testFiringExpiry(string $expiry, int $seconds): void
    {
        Http::fake();
        $before = time();
        $this->transport(['alertmanager-ends-at' => $expiry])->deliverAlert($this->alertData());
        $payload = Http::recorded()[0][0]->data()[0];
        self::assertArrayHasKey('endsAt', $payload);
        self::assertGreaterThanOrEqual($before + $seconds, strtotime($payload['endsAt']));
        self::assertLessThanOrEqual(time() + $seconds, strtotime($payload['endsAt']));
    }

    /** @return array<string, array{string}> */
    public static function invalidExpiries(): array
    {
        return array_combine(
            ['zero', 'negative', 'bare', 'fraction', 'compound', 'trailing', 'overflow', 'malformed'],
            array_map(fn ($s) => [$s], ['0m', '-1m', '20', '1.5h', '1h30m', '20m garbage', '999999999999999999999d', '{{ $expiry'])
        );
    }

    #[DataProvider('invalidExpiries')]
    public function testInvalidExpiryFailsBeforeSending(string $expiry): void
    {
        Http::fake();
        self::assertTrue(Validator::make(['alertmanager-url' => 'https://am.example.net', 'alertmanager-ends-at' => $expiry], Alertmanager::configTemplate()['validation'])->fails());
        try {
            $this->transport(['alertmanager-ends-at' => $expiry])->deliverAlert($this->alertData());
            self::fail('Invalid expiry must fail delivery.');
        } catch (AlertTransportDeliveryException $e) {
            self::assertStringContainsString('expiry', $e->getMessage());
            Http::assertNothingSent();
        }
    }

    public function testBlankAndMissingExpiryDoNotSendFiringEndsAt(): void
    {
        Http::fake();
        foreach (['', '{{ $missing }}'] as $expiry) {
            $this->transport(['alertmanager-ends-at' => $expiry])->deliverAlert($this->alertData());
        }
        foreach (Http::recorded() as [$request]) {
            self::assertArrayNotHasKey('endsAt', $request->data()[0]);
        }
    }

    public function testRecoveryIgnoresInvalidFiringExpiry(): void
    {
        Http::fake();
        $this->transport(['alertmanager-ends-at' => 'broken'])->deliverAlert(array_merge($this->alertData(), ['state' => AlertState::RECOVERED]));
        $payload = Http::recorded()[0][0]->data()[0];
        self::assertLessThan(time(), strtotime($payload['endsAt']));
        self::assertLessThan(strtotime($payload['endsAt']), strtotime($payload['startsAt']));
    }

    public function testAllEndpointFailuresAreSummarisedWithoutSecrets(): void
    {
        $attempted = [];
        Http::fake(function (Request $request) use (&$attempted) {
            $attempted[] = $request->url();

            return match (parse_url($request->url(), PHP_URL_HOST)) {
                'good.example.net' => Http::response('', 200),
                'bad.example.net' => Http::response('secret-response', 503),
                default => Create::rejectionFor(new \InvalidArgumentException('secret-exception')),
            };
        });
        try {
            $this->transport(['alertmanager-url' => 'https://good.example.net,https://bad.example.net,https://broken.example.net'])->deliverAlert($this->alertData());
            self::fail('Partial delivery must fail.');
        } catch (AlertTransportDeliveryException $e) {
            self::assertStringContainsString('2 of 3', $e->getMessage());
            self::assertStringContainsString('1 succeeded', $e->getMessage());
            self::assertStringContainsString('bad.example.net', $e->getMessage());
            self::assertStringContainsString('broken.example.net', $e->getMessage());
            self::assertStringContainsString('HTTP 503', $e->getMessage());
            self::assertStringNotContainsString('secret-', $e->getMessage());
            self::assertCount(3, $attempted);
        }
    }

    public function testFailureSummaryIsBoundedAndRemovesUrlCredentialsAndQueries(): void
    {
        Http::fake(['*' => Http::response('secret body', 500)]);
        $urls = array_map(fn ($i) => 'https://user:password@am-' . $i . '.example.net/private?token=secret', range(1, 8));
        try {
            $this->transport(['alertmanager-url' => implode(',', $urls)])->deliverAlert($this->alertData());
            self::fail('All endpoints must fail.');
        } catch (AlertTransportDeliveryException $e) {
            self::assertStringContainsString('8 of 8', $e->getMessage());
            self::assertStringContainsString('3 more', $e->getMessage());
            self::assertLessThanOrEqual(1700, strlen($e->getMessage()));
            self::assertStringNotContainsString('password', $e->getMessage());
            self::assertStringNotContainsString('token=', $e->getMessage());
            self::assertStringNotContainsString('secret', $e->getMessage());
            Http::assertSentCount(8);
        }
    }

    public function testEmptyEndpointListFails(): void
    {
        Http::fake();
        $this->expectException(AlertTransportDeliveryException::class);
        $this->transport(['alertmanager-url' => ' , '])->deliverAlert($this->alertData());
    }

    public function testPooledRequestsReceiveTimeoutProxyHeadersAndCertificateOptions(): void
    {
        $options = [];
        $certificate = tempnam(sys_get_temp_dir(), 'lnms-cert-');
        $key = tempnam(sys_get_temp_dir(), 'lnms-key-');
        Http::fake();
        Http::globalMiddleware(function (callable $handler) use (&$options): callable {
            return function (RequestInterface $request, array $requestOptions) use ($handler, &$options): PromiseInterface {
                $options[] = $requestOptions;

                return $handler($request, $requestOptions);
            };
        });
        try {
            $this->transport([
                'alertmanager-url' => 'https://a.example.net,https://b.example.net',
                'alertmanager-username' => 'user', 'alertmanager-password' => 'password',
                'alertmanager-client-cert' => $certificate, 'alertmanager-client-key' => $key,
            ])->deliverAlert($this->alertData());
            self::assertCount(2, $options);
            foreach ($options as $entry) {
                self::assertSame(30, $entry['timeout']);
                self::assertArrayHasKey('proxy', $entry);
                self::assertSame($certificate, $entry['cert']);
                self::assertSame($key, $entry['ssl_key']);
            }
            foreach (Http::recorded() as [$request]) {
                self::assertTrue($request->hasHeader('User-Agent'));
                self::assertTrue($request->hasHeader('Authorization', 'Basic ' . base64_encode('user:password')));
            }
        } finally {
            unlink($certificate);
            unlink($key);
        }
    }

    public function testMissingCertificateIsANormalDeliveryFailure(): void
    {
        Http::fake();
        try {
            $this->transport([
                'alertmanager-client-cert' => '/missing/secret-cert.pem',
                'alertmanager-client-key' => '/missing/secret-key.pem',
            ])->deliverAlert($this->alertData());
            self::fail('Missing certificates must fail.');
        } catch (AlertTransportDeliveryException $e) {
            self::assertStringContainsString('certificate', $e->getMessage());
            self::assertStringNotContainsString('/missing/', $e->getMessage());
            Http::assertNothingSent();
        }
    }

    public function testInvalidExternallyWrittenConfigIsADeliveryFailure(): void
    {
        Http::fake();
        foreach ([['alertmanager-labels' => 42], ['alertmanager-ends-at' => []], ['alertmanager-annotations' => ['bad-name' => 'x']]] as $config) {
            try {
                $this->transport($config)->deliverAlert($this->alertData());
                self::fail('Invalid stored config must fail normally.');
            } catch (AlertTransportDeliveryException $e) {
                self::assertNotSame('', $e->getMessage());
            }
        }
        Http::assertNothingSent();
    }

    public function testDuplicateEndpointsAreBothAttemptedAndStableLabelsSurviveRecovery(): void
    {
        Http::fake();
        $transport = $this->transport([
            'alertmanager-url' => 'https://am.example.net,https://am.example.net',
            'alertmanager-labels' => ['team' => 'network', 'priority' => "{{ \$severity|replace('critical','3') }}"],
        ]);
        $transport->deliverAlert($this->alertData());
        $transport->deliverAlert(array_merge($this->alertData(), ['state' => AlertState::RECOVERED, 'title' => 'Recovered']));
        Http::assertSentCount(4);
        self::assertSame(Http::recorded()[0][0]->data()[0]['labels'], Http::recorded()[3][0]->data()[0]['labels']);
    }

    public function testInvalidTemplateFiltersAreNormalDeliveryFailures(): void
    {
        Http::fake();
        foreach (['labels', 'annotations', 'ends-at'] as $field) {
            $value = '{{ $expiry|slice(garbage) }}';
            try {
                $this->transport(['alertmanager-' . $field => $field === 'ends-at' ? $value : ['value' => $value]])->deliverAlert($this->alertData());
                self::fail('Invalid filters must fail this transport.');
            } catch (AlertTransportDeliveryException $e) {
                self::assertStringContainsString('template', $e->getMessage());
                self::assertStringNotContainsString('garbage', $e->getMessage());
            }
        }
        Http::assertNothingSent();
    }

    /** @param array<string, mixed> $config */
    private function transport(array $config = []): Alertmanager
    {
        return new Alertmanager(new AlertTransport(['transport_config' => array_merge([
            'alertmanager-url' => 'https://am.example.net',
            'alertmanager-username' => '',
            'alertmanager-password' => '',
        ], $config)]));
    }

    /** @return array<string, mixed> */
    private function alertData(): array
    {
        return [
            'device_id' => 1, 'hostname' => 'switch.example.net', 'state' => AlertState::ACTIVE,
            'name' => 'Device down', 'severity' => 'critical', 'title' => 'Device down',
            'msg' => '<b>Test message</b>', 'proc' => 'https://runbooks.example.net',
            'expiry' => '20m', 'faults' => [['ifName' => 'xe-0/0/0']],
        ];
    }
}
