<?php

namespace LibreNMS\Tests\Feature\Logging;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LogLevel;

final class EventLogTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testEventIsStoredForDevice(): void
    {
        $device = Device::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        Eventlog::log('Port went down', $device, 'interface', Severity::Error, 42);

        $event = $device->eventlogs()->where('message', 'Port went down')->sole();
        $this->assertSame('interface', $event->type);
        $this->assertSame(Severity::Error, $event->severity);
        $this->assertEquals(42, $event->reference);
        $this->assertSame($user->username, $event->username);
        $this->assertNotNull($event->datetime);
    }

    public function testEventIsStoredForDeviceId(): void
    {
        $device = Device::factory()->create();

        Eventlog::log('Polled', $device->device_id);

        $event = $device->eventlogs()->where('message', 'Polled')->sole();
        $this->assertSame(Severity::Info, $event->severity);
        $this->assertSame('', $event->username);
    }

    public function testEventIsStoredWithoutDevice(): void
    {
        Eventlog::log('Global event', null, 'system', Severity::Unknown);

        $event = Eventlog::where('message', 'Global event')->sole();
        $this->assertNull($event->device_id);
        $this->assertSame(Severity::Unknown, $event->severity);
    }

    #[DataProvider('logLevels')]
    public function testSeverityMapsToLogLevel(Severity $severity, string $level): void
    {
        $this->assertSame($level, $severity->toLogLevel());
    }

    /**
     * @return array<string, array{Severity, string}>
     */
    public static function logLevels(): array
    {
        return [
            'unknown' => [Severity::Unknown, LogLevel::DEBUG],
            'ok' => [Severity::Ok, LogLevel::INFO],
            'info' => [Severity::Info, LogLevel::INFO],
            'notice' => [Severity::Notice, LogLevel::NOTICE],
            'warning' => [Severity::Warning, LogLevel::WARNING],
            'error' => [Severity::Error, LogLevel::ERROR],
        ];
    }
}
