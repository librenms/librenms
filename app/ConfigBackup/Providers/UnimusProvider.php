<?php

/**
 * UnimusProvider.php
 *
 * Config backup provider backed by the Unimus API.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace App\ConfigBackup\Providers;

use App\ApiClients\Unimus;
use App\Models\Device;
use LibreNMS\Interfaces\ConfigBackupProvider;

class UnimusProvider implements ConfigBackupProvider
{
    private ?string $lastError = null;

    public function __construct(
        private readonly Unimus $api,
    ) {
    }

    public static function isConfigured(): bool
    {
        return Unimus::isConfigured();
    }

    public function supportsDevice(Device $device): bool
    {
        return true; // Unimus has no per-device exclusions
    }

    public function name(): string
    {
        return 'Unimus';
    }

    public function backups(Device $device, int $page = 0): ?array
    {
        $this->lastError = null;

        $unimusDeviceId = $this->resolveDeviceId($device);
        if ($unimusDeviceId === null) {
            return null;
        }

        $list = $this->api->getBackups($unimusDeviceId, $page);
        if ($list === null) {
            $this->lastError = $this->api->lastError() ?? self::ERROR_UNREACHABLE;

            return null;
        }

        $list['backups'] = array_map($this->stringifyId(...), $list['backups']);

        return $list;
    }

    public function latest(Device $device): ?array
    {
        $this->lastError = null;

        $unimusDeviceId = $this->resolveDeviceId($device);
        if ($unimusDeviceId === null) {
            return null;
        }

        $backup = $this->api->getLatestBackup($unimusDeviceId);
        if ($backup === null) {
            $this->lastError = $this->api->lastError() ?? self::ERROR_UNREACHABLE;

            return null;
        }

        return $this->stringifyId($backup);
    }

    public function content(Device $device, string $backupId, int $pageHint = 0): ?string
    {
        $this->lastError = null;

        if (! ctype_digit($backupId)) {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        $unimusDeviceId = $this->resolveDeviceId($device);
        if ($unimusDeviceId === null) {
            return null;
        }

        $content = $this->api->getBackupContent($unimusDeviceId, (int) $backupId, $pageHint);
        if ($content === null) {
            $this->lastError = $this->api->lastError() ?? self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        return $content;
    }

    public function diff(Device $device, string $origId, string $revId): ?array
    {
        $this->lastError = null;

        if (! ctype_digit($origId) || ! ctype_digit($revId)) {
            $this->lastError = self::ERROR_BACKUP_NOT_FOUND;

            return null;
        }

        $groups = $this->api->getDiff((int) $origId, (int) $revId);
        if ($groups === null) {
            $this->lastError = $this->api->lastError() ?? self::ERROR_UNREACHABLE;

            return null;
        }

        return $groups;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    private function resolveDeviceId(Device $device): ?int
    {
        $id = $this->api->findDeviceId($device);

        if ($id === null) {
            $this->lastError = $this->api->lastError() ?? self::ERROR_DEVICE_NOT_FOUND;
        }

        return $id;
    }

    /**
     * @param  array{id: int|string, date: ?int, until: ?int, type: string, content: ?string}  $backup
     * @return array{id: string, date: ?int, until: ?int, type: string, content: ?string}
     */
    private function stringifyId(array $backup): array
    {
        $backup['id'] = (string) $backup['id'];

        return $backup;
    }
}
