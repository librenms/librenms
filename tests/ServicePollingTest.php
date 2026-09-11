<?php

namespace LibreNMS\Tests;

use App\Facades\LibrenmsConfig;
use App\Models\Service;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;

final class ServicePollingTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $pluginDirectory = sys_get_temp_dir() . '/librenms-services-' . bin2hex(random_bytes(8));
        mkdir($pluginDirectory);
        $plugin = $pluginDirectory . '/check_test_timing';
        file_put_contents($plugin, <<<'SH'
#!/bin/sh
printf 'Test service result\n'
exit "$3"
SH
        );
        chmod($plugin, 0700);

        $originalPluginDirectory = LibrenmsConfig::get('nagios_plugins');
        LibrenmsConfig::set('nagios_plugins', $pluginDirectory);
        $this->beforeApplicationDestroyed(function () use ($originalPluginDirectory, $plugin, $pluginDirectory): void {
            LibrenmsConfig::set('nagios_plugins', $originalPluginDirectory);
            unlink($plugin);
            rmdir($pluginDirectory);
        });
    }

    #[DataProvider('checkResultsProvider')]
    public function testCompletedCheckUpdatesTiming(int $oldStatus, int $newStatus, string $oldMessage): void
    {
        $previouslyChecked = time() - 900;
        $previouslyChanged = time() - 3600;
        $service = Service::factory()->create([
            'service_type' => 'test_timing',
            'service_ip' => '127.0.0.1',
            'service_param' => (string) $newStatus,
            'service_status' => $oldStatus,
            'service_message' => $oldMessage,
            'service_checked' => $previouslyChecked,
            'service_changed' => $previouslyChanged,
        ]);

        $started = time();
        poll_service($service->toArray() + ['hostname' => 'localhost', 'overwrite_ip' => '']);
        $completed = time();
        $service->refresh();

        $this->assertGreaterThan($previouslyChecked, $service->service_checked);
        $this->assertGreaterThanOrEqual($started, $service->service_checked);
        $this->assertLessThanOrEqual($completed, $service->service_checked);
        $this->assertSame($newStatus, $service->service_status);
        $this->assertSame('Test service result', $service->service_message);

        if ($oldStatus === $newStatus) {
            $this->assertSame($previouslyChanged, $service->service_changed);
        } else {
            $this->assertGreaterThanOrEqual($started, $service->service_changed);
            $this->assertLessThanOrEqual($completed, $service->service_changed);
        }
    }

    public static function checkResultsProvider(): array
    {
        return [
            'unchanged OK result' => [0, 0, 'Test service result'],
            'unchanged Warning result' => [1, 1, 'Test service result'],
            'unchanged Critical result' => [2, 2, 'Test service result'],
            'changed status' => [0, 2, 'Test service result'],
            'changed message only' => [0, 0, 'Previous service result'],
        ];
    }
}
