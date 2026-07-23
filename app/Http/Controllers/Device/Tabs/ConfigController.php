<?php

/**
 * ConfigController.php
 *
 * Modern device Config tab. Backup data comes from whichever
 * ConfigBackupProvider the ConfigBackupManager resolves for the device
 * (currently Unimus; Oxidized and RANCID can be ported as providers).
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

namespace App\Http\Controllers\Device\Tabs;

use App\ConfigBackup\ConfigBackupManager;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Interfaces\ConfigBackupProvider;
use LibreNMS\Interfaces\UI\DeviceTab;

class ConfigController extends Controller implements DeviceTab
{
    public function __construct(
        private readonly ConfigBackupManager $manager,
    ) {
    }

    public function visible(Device $device): bool
    {
        return Gate::allows('show-config', $device) && $this->manager->handles($device);
    }

    public function slug(): string
    {
        return 'config';
    }

    public function icon(): string
    {
        return 'fa-align-justify';
    }

    public function name(): string
    {
        return __('config_backups.title');
    }

    /**
     * @return array{error: ?string, error_message: ?string, urls: array{backups: string, backup: string, diff: string}, messages: array<string, string>, hostname: string}
     */
    public function data(Device $device, Request $request): array
    {
        $provider = $this->manager->providerFor($device);
        $providerName = $provider?->name();
        $defaultProvider = __('config_backups.default_provider');

        $urls = [
            'backups' => route('device.config.backups', $device->device_id),
            'backup' => route('device.config.backup', $device->device_id),
            'diff' => route('device.config.diff', $device->device_id),
        ];

        $messages = [
            'unreachable' => __('config_backups.messages.unreachable', ['provider' => $providerName ?? $defaultProvider]),
            'error' => __('config_backups.messages.error', ['provider' => $providerName ?? $defaultProvider]),
            'backup_not_found' => __('config_backups.messages.backup_not_found', ['provider' => $providerName ?? $defaultProvider]),
            'binary_not_supported' => __('config_backups.messages.binary_not_supported', ['provider' => $providerName ?? $defaultProvider]),
            'request_failed' => __('config_backups.messages.request_failed'),
        ];

        $empty = [
            'error' => null,
            'error_message' => null,
            'urls' => $urls,
            'messages' => $messages,
            'hostname' => $device->hostname,
        ];

        if ($provider === null) {
            $error = ConfigBackupProvider::ERROR_DEVICE_NOT_FOUND;

            return array_merge($empty, ['error' => $error, 'error_message' => $this->errorMessage($error, null)]);
        }

        return $empty;
    }

    private function errorMessage(string $error, ?string $provider): string
    {
        $provider ??= __('config_backups.default_provider');

        return match ($error) {
            ConfigBackupProvider::ERROR_UNREACHABLE => __('config_backups.messages.unreachable_details', ['provider' => $provider]),
            ConfigBackupProvider::ERROR_API => __('config_backups.messages.error_details', ['provider' => $provider]),
            ConfigBackupProvider::ERROR_NO_BACKUPS => __('config_backups.messages.no_backups', ['provider' => $provider]),
            default => __('config_backups.messages.device_not_found', ['provider' => $provider]),
        };
    }

    public function backups(Device $device, Request $request): JsonResponse
    {
        Gate::authorize('show-config', $device);

        $validated = $request->validate([
            'page' => 'nullable|integer|min:0',
        ]);

        $provider = $this->manager->providerFor($device);
        if ($provider === null) {
            return $this->errorResponse(ConfigBackupProvider::ERROR_DEVICE_NOT_FOUND);
        }

        $list = $provider->backups($device, (int) ($validated['page'] ?? 0));
        if ($list === null) {
            return $this->errorResponse($provider->lastError() ?? ConfigBackupProvider::ERROR_UNREACHABLE);
        }

        return response()->json($list);
    }

    public function backup(Device $device, Request $request): JsonResponse
    {
        Gate::authorize('show-config', $device);

        $validated = $request->validate([
            'backup' => ['nullable', 'string', 'max:191', 'regex:/^[A-Za-z0-9._\\|-]+$/'],
            'page' => ['nullable', 'integer', 'min:0'],
        ]);

        $provider = $this->manager->providerFor($device);
        if ($provider === null) {
            return $this->errorResponse(ConfigBackupProvider::ERROR_DEVICE_NOT_FOUND);
        }

        $backup = $validated['backup'] ?? null;

        if ($backup === null) {
            $latest = $provider->latest($device);
            if ($latest === null) {
                return $this->errorResponse($provider->lastError() ?? ConfigBackupProvider::ERROR_NO_BACKUPS);
            }

            return response()->json($latest);
        }

        $content = $provider->content($device, $backup, (int) ($validated['page'] ?? 0));
        if ($content === null) {
            return $this->errorResponse($provider->lastError() ?? ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND);
        }

        return response()->json([
            'id' => $backup,
            'content' => $content,
        ]);
    }

    public function diff(Device $device, Request $request): JsonResponse
    {
        Gate::authorize('show-config', $device);

        $validated = $request->validate([
            'orig' => 'required|string|max:191',
            'rev' => 'required|string|max:191|different:orig',
        ]);

        $provider = $this->manager->providerFor($device);
        if ($provider === null) {
            return $this->errorResponse(ConfigBackupProvider::ERROR_DEVICE_NOT_FOUND);
        }

        $groups = $provider->diff($device, $validated['orig'], $validated['rev']);
        if ($groups === null) {
            return $this->errorResponse($provider->lastError() ?? ConfigBackupProvider::ERROR_UNREACHABLE);
        }

        return response()->json(['groups' => $groups]);
    }

    private function errorResponse(string $error): JsonResponse
    {
        $status = $error === ConfigBackupProvider::ERROR_UNREACHABLE ? 503 : 404;

        return response()->json(['error' => $error], $status);
    }
}
