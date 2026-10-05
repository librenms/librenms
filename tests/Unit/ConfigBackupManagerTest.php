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
use App\ConfigBackup\Providers\OxidizedProvider;
use App\ConfigBackup\Providers\UnimusProvider;
use App\Models\Device;
use LibreNMS\Interfaces\ConfigBackupProvider;
use LibreNMS\Tests\TestCase;
use ReflectionClass;

final class ConfigBackupManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetRegistered();
    }

    protected function tearDown(): void
    {
        $this->resetRegistered();
        parent::tearDown();
    }

    public function testBuiltInProvidersPresentByDefault(): void
    {
        $providers = ConfigBackupManager::providers();

        $this->assertContains(UnimusProvider::class, $providers);
        $this->assertContains(OxidizedProvider::class, $providers);
    }

    public function testRegisterAppendsProviderAfterBuiltIns(): void
    {
        ConfigBackupManager::register(StubConfigBackupProvider::class);

        $providers = ConfigBackupManager::providers();

        $this->assertContains(StubConfigBackupProvider::class, $providers);
        $this->assertSame(StubConfigBackupProvider::class, end($providers), 'appended provider must sort last');
    }

    public function testRegisterWithPrependGivesProviderPriority(): void
    {
        ConfigBackupManager::register(StubConfigBackupProvider::class, prepend: true);

        $providers = ConfigBackupManager::providers();

        $this->assertSame(StubConfigBackupProvider::class, $providers[0], 'prepended provider must sort first');
    }

    public function testProvidersListIsDeduplicated(): void
    {
        ConfigBackupManager::register(StubConfigBackupProvider::class);
        ConfigBackupManager::register(StubConfigBackupProvider::class);

        $providers = ConfigBackupManager::providers();

        $this->assertSame(1, array_count_values($providers)[StubConfigBackupProvider::class]);
    }

    public function testRegisterRejectsClassThatDoesNotImplementContract(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @phpstan-ignore-next-line intentionally invalid for the test */
        ConfigBackupManager::register(\stdClass::class);
    }

    public function testProviderForResolvesARegisteredProvider(): void
    {
        ConfigBackupManager::register(StubConfigBackupProvider::class, prepend: true);

        $provider = (new ConfigBackupManager)->providerFor(new Device);

        $this->assertInstanceOf(StubConfigBackupProvider::class, $provider);
    }

    private function resetRegistered(): void
    {
        $reflection = new ReflectionClass(ConfigBackupManager::class);
        foreach (['appended', 'prepended'] as $property) {
            $prop = $reflection->getProperty($property);
            $prop->setValue(null, []);
        }
    }
}

/**
 * Minimal in-test provider used to prove external registration works.
 */
class StubConfigBackupProvider implements ConfigBackupProvider
{
    public static function isConfigured(): bool
    {
        return true;
    }

    public function supportsDevice(Device $device): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'Stub';
    }

    public function backups(Device $device, int $page = 0): ?array
    {
        return ['backups' => [], 'total' => 0, 'totalPages' => 0, 'page' => $page];
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
        return null;
    }
}
