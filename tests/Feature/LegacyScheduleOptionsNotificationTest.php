<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Util\LegacyScheduleOptions;
use LibreNMS\Validations\Configuration;
use LibreNMS\Validator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class LegacyScheduleOptionsNotificationTest extends InMemoryDbTestCase
{
    private const array OPTIONS = [
        'service_alerting_enabled',
        'service_billing_enabled',
        'service_discovery_enabled',
        'service_ping_enabled',
        'service_poller_enabled',
        'service_services_enabled',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::forget(self::OPTIONS);
        config(['librenms.node_id' => 'node-one']);
    }

    public function testNoNotificationWithoutExplicitOptions(): void
    {
        LegacyScheduleOptions::notify();

        $this->assertSame([], LegacyScheduleOptions::configured());
        $this->assertSame(0, Notification::count());
    }

    /**
     * @return list<array{string}>
     */
    public static function legacyOptions(): array
    {
        return array_map(fn (string $option): array => [$option], self::OPTIONS);
    }

    #[DataProvider('legacyOptions')]
    public function testExplicitFalseOptionCreatesWarning(string $option): void
    {
        LibrenmsConfig::set($option, false);

        LegacyScheduleOptions::notify();

        $notification = Notification::sole();
        $this->assertSame([$option], LegacyScheduleOptions::configured());
        $this->assertStringContainsString($option, $notification->body);
        $this->assertStringContainsString('node-one', $notification->title);
        $this->assertSame(1, $notification->severity);
    }

    public function testNotificationIsOncePerNodeEvenWhenOptionsChange(): void
    {
        LibrenmsConfig::set('service_ping_enabled', true);
        LegacyScheduleOptions::notify();
        LibrenmsConfig::set('service_poller_enabled', false);
        LegacyScheduleOptions::notify();

        $this->assertSame(1, Notification::count());

        config(['librenms.node_id' => 'node-two']);
        LegacyScheduleOptions::notify();
        LegacyScheduleOptions::notify();

        $this->assertSame(2, Notification::count());
        $this->assertDatabaseHas('notifications', ['title' => 'Legacy scheduling options on node-two']);
    }

    public function testNullIsExplicitAndNodeFallsBackToHostname(): void
    {
        LibrenmsConfig::set('service_services_enabled', null);
        config(['librenms.node_id' => null]);

        LegacyScheduleOptions::notify();

        $this->assertSame(['service_services_enabled'], LegacyScheduleOptions::configured());
        $this->assertDatabaseHas('notifications', ['title' => 'Legacy scheduling options on ' . php_uname('n')]);
    }

    public function testNotificationFailureIsNotSuppressed(): void
    {
        LibrenmsConfig::set('service_ping_enabled', false);
        Schema::rename('notifications', 'notifications_for_test');

        try {
            $this->expectException(QueryException::class);
            LegacyScheduleOptions::notify();
        } finally {
            Schema::rename('notifications_for_test', 'notifications');
        }
    }

    public function testConfigurationDumpDoesNotCreateNotification(): void
    {
        LibrenmsConfig::set('service_ping_enabled', false);

        $this->artisan('config:get', ['--dump' => true])
            ->expectsOutput(LibrenmsConfig::toJson())
            ->assertExitCode(0);

        $this->assertSame(0, Notification::count());
    }

    public function testValidationFixNamesTheMatchingScheduleOptions(): void
    {
        LibrenmsConfig::set('service_ping_enabled', false);
        LibrenmsConfig::set('service_poller_enabled', true);
        LibrenmsConfig::set('validation.encryption.test', \Crypt::encryptString('librenms'));

        $validator = Mockery::mock(Validator::class)->shouldIgnoreMissing();
        $messages = [];
        $validator->shouldReceive('warn')
            ->andReturnUsing(function (string $message, ?string $fix = null) use (&$messages): void {
                $messages[$message] = $fix;
            });

        (new Configuration)->validate($validator);

        $this->assertArrayHasKey('Legacy scheduling options are explicitly configured: service_ping_enabled, service_poller_enabled.', $messages);
        $fix = $messages['Legacy scheduling options are explicitly configured: service_ping_enabled, service_poller_enabled.'];
        $this->assertStringContainsString('lnms config:set schedule_type.ping cron', $fix);
        $this->assertStringContainsString('lnms config:set schedule_type.poller dispatcher', $fix);
        $this->assertStringContainsString('Remove the legacy options from config.php', $fix);
        $this->assertStringContainsString('lnms config:set service_ping_enabled, lnms config:set service_poller_enabled', $fix);
        $this->assertStringContainsString('omit the value and confirm', $fix);
    }
}
