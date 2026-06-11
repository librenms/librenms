<?php

/**
 * ConfigBackupManagerTest.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Unit;

use App\ConfigBackup\ConfigBackupManager;
use App\Models\Device;
use LibreNMS\Interfaces\ConfigBackupProvider;
use LibreNMS\Tests\TestCase;

class ConfigBackupManagerTest extends TestCase
{
    /** @var list<class-string<ConfigBackupProvider>> */
    private array $originalProviders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalProviders = ConfigBackupManager::$providers;
        FirstStubProvider::$configured = false;
        FirstStubProvider::$supports = true;
        SecondStubProvider::$configured = false;
        SecondStubProvider::$supports = true;
        ConfigBackupManager::$providers = [FirstStubProvider::class, SecondStubProvider::class];
    }

    protected function tearDown(): void
    {
        ConfigBackupManager::$providers = $this->originalProviders;

        parent::tearDown();
    }

    private function makeDevice(int $id = 1): Device
    {
        $device = new Device(['hostname' => 'router.example.com']);
        $device->device_id = $id;

        return $device;
    }

    public function testNoProviderWhenNoneConfigured(): void
    {
        $manager = new ConfigBackupManager();

        $this->assertNull($manager->providerFor($this->makeDevice()));
        $this->assertFalse($manager->handles($this->makeDevice()));
    }

    public function testUnconfiguredProviderIsSkipped(): void
    {
        SecondStubProvider::$configured = true;

        $manager = new ConfigBackupManager();

        $this->assertInstanceOf(SecondStubProvider::class, $manager->providerFor($this->makeDevice()));
    }

    public function testConfiguredButUnsupportingProviderIsSkipped(): void
    {
        FirstStubProvider::$configured = true;
        FirstStubProvider::$supports = false;
        SecondStubProvider::$configured = true;

        $manager = new ConfigBackupManager();

        $this->assertInstanceOf(SecondStubProvider::class, $manager->providerFor($this->makeDevice()));
    }

    public function testFirstConfiguredSupportingProviderWins(): void
    {
        FirstStubProvider::$configured = true;
        SecondStubProvider::$configured = true;

        $manager = new ConfigBackupManager();

        $this->assertInstanceOf(FirstStubProvider::class, $manager->providerFor($this->makeDevice()));
    }

    public function testResolutionIsMemoizedPerDevice(): void
    {
        FirstStubProvider::$configured = true;

        $manager = new ConfigBackupManager();
        $device = $this->makeDevice();

        $first = $manager->providerFor($device);

        // config changes within a request do not re-resolve
        FirstStubProvider::$configured = false;

        $this->assertSame($first, $manager->providerFor($device));
        $this->assertNull($manager->providerFor($this->makeDevice(2)), 'other devices resolve independently');
    }
}

abstract class StubProvider implements ConfigBackupProvider
{
    public static bool $configured = false;
    public static bool $supports = true;

    public static function isConfigured(): bool
    {
        return static::$configured;
    }

    public function supportsDevice(Device $device): bool
    {
        return static::$supports;
    }

    public function name(): string
    {
        return 'Stub';
    }

    public function backups(Device $device, int $page = 0): ?array
    {
        return null;
    }

    public function latest(Device $device): ?array
    {
        return null;
    }

    public function content(Device $device, string $backupId, int $pageHint = 0): ?string
    {
        return null;
    }

    public function diff(Device $device, string $origId, string $revId): ?array
    {
        return null;
    }

    public function lastError(): ?string
    {
        return self::ERROR_UNREACHABLE;
    }
}

class FirstStubProvider extends StubProvider
{
    public static bool $configured = false;
    public static bool $supports = true;
}

class SecondStubProvider extends StubProvider
{
    public static bool $configured = false;
    public static bool $supports = true;
}
