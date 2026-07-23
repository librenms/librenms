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
        return __('Config');
    }

    /**
     * @return array{error: ?string, error_message: ?string, latest: ?array{id: string, date: ?int, until: ?int, type: string, content: ?string}, urls: array{backups: string, backup: string, diff: string}, messages: array<string, string>}
     */
    public function data(Device $device, Request $request): array
    {
        $provider = $this->manager->providerFor($device);
        $providerName = $provider?->name();

        $urls = [
            'backups' => route('device.config.backups', $device->device_id),
            'backup' => route('device.config.backup', [$device->device_id, 'BACKUP_ID']),
            'diff' => route('device.config.diff', $device->device_id),
        ];

        $messages = [
            'unreachable' => __(':provider is not reachable.', ['provider' => $providerName ?? __('the backup provider')]),
            'error' => __(':provider returned an error.', ['provider' => $providerName ?? __('the backup provider')]),
            'backup_not_found' => __('This backup could not be loaded from :provider.', ['provider' => $providerName ?? __('the backup provider')]),
            'binary_not_supported' => __('This is a binary backup and cannot be displayed. View it in :provider instead.', ['provider' => $providerName ?? __('the backup provider')]),
            'request_failed' => __('The request failed. Please try again.'),
        ];

        $empty = [
            'error' => null,
            'error_message' => null,
            'latest' => null,
            'urls' => $urls,
            'messages' => $messages,
            'hostname' => $device->hostname,
        ];

        if ($provider === null) {
            $error = ConfigBackupProvider::ERROR_DEVICE_NOT_FOUND;

            return array_merge($empty, ['error' => $error, 'error_message' => $this->errorMessage($error, null)]);
        }

        $latest = $provider->latest($device);
        if ($latest === null) {
            $error = $provider->lastError() ?? ConfigBackupProvider::ERROR_NO_BACKUPS;

            return array_merge($empty, ['error' => $error, 'error_message' => $this->errorMessage($error, $providerName)]);
        }

        return array_merge($empty, [
            'latest' => $latest,
        ]);
    }

    private function errorMessage(string $error, ?string $provider): string
    {
        $provider ??= __('the backup provider');

        return match ($error) {
            ConfigBackupProvider::ERROR_UNREACHABLE => __(':provider is not reachable. Check the configured URL and that :provider is running.', ['provider' => $provider]),
            ConfigBackupProvider::ERROR_API => __(':provider returned an error. Check the configured API token.', ['provider' => $provider]),
            ConfigBackupProvider::ERROR_NO_BACKUPS => __('No configuration backups exist for this device in :provider yet.', ['provider' => $provider]),
            default => __('This device could not be found in :provider. It is matched by hostname or IP address.', ['provider' => $provider]),
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

    public function backup(Device $device, string $backup, Request $request): JsonResponse
    {
        Gate::authorize('show-config', $device);

        $validated = $request->validate([
            'page' => 'nullable|integer|min:0',
        ]);

        $provider = $this->manager->providerFor($device);
        if ($provider === null) {
            return $this->errorResponse(ConfigBackupProvider::ERROR_DEVICE_NOT_FOUND);
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
