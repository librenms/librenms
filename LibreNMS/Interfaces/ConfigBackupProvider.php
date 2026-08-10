<?php

/**
 * ConfigBackupProvider.php
 *
 * Contract for device configuration backup sources (Unimus, Oxidized,
 * RANCID, ...) shown on the device Config tab.
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

namespace LibreNMS\Interfaces;

use App\Models\Device;

interface ConfigBackupProvider
{
    public const ERROR_UNREACHABLE = 'unreachable';
    public const ERROR_API = 'error';
    public const ERROR_DEVICE_NOT_FOUND = 'device_not_found';
    public const ERROR_NO_BACKUPS = 'no_backups';
    public const ERROR_BACKUP_NOT_FOUND = 'backup_not_found';

    public static function isConfigured(): bool;

    public function supportsDevice(Device $device): bool;

    public function name(): string;

    /**
     * @return array{backups: list<array{id: string, date: ?int, until: ?int, type: string, content: ?string}>, total: int, totalPages: int, page: int}|null
     */
    public function backups(Device $device, int $page = 0): ?array;

    /**
     * @return array{id: string, date: ?int, until: ?int, type: string, content: ?string}|null
     */
    public function latest(Device $device): ?array;

    public function content(Device $device, string $backupId, int $pageHint = 0): ?string;

    /**
     * @return list<array{type: string, original: list<array{line: ?int, text: string}>, revised: list<array{line: ?int, text: string}>}>|null
     */
    public function diff(Device $device, string $origId, string $revId): ?array;

    public function lastError(): ?string;
}
