<?php

/**
 * PackagesController.php
 *
 * Installed packages across all devices
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use LibreNMS\Util\Url;

class PackagesController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:128',
            'version' => 'nullable|string|max:255',
            'arch' => 'nullable|string|max:16',
            'device_id' => 'nullable|integer',
        ]);

        /** @var User $user */
        $user = $request->user();
        [$operator, $version] = $this->parseVersion($validated['version'] ?? '');

        $query = Package::hasAccess($user)
            ->when($validated['name'] ?? null, fn (Builder $q, $name) => $q->where('name', 'like', "%$name%"))
            ->when($validated['arch'] ?? null, fn (Builder $q, $arch) => $q->where('arch', $arch))
            ->when($validated['device_id'] ?? null, fn (Builder $q, $deviceId) => $q->where('packages.device_id', $deviceId))
            ->when($version && ! $operator, fn (Builder $q) => $q->where('version', 'like', "%$version%"));

        $names = (clone $query)->select('name')->distinct()->orderBy('name')->paginate(50)->withQueryString();

        $packages = (clone $query)->with('device')
            ->whereIn('name', $names->pluck('name'))
            ->orderBy('name')->orderBy('version')->orderBy('build')
            ->get()
            ->when($operator, fn (Collection $rows) => $rows->filter(fn (Package $package) => version_compare($this->normalizeVersion($package->version), $version, $operator)))
            ->groupBy('name')
            ->map(fn (Collection $rows) => [
                'arch' => $rows->pluck('arch')->filter()->unique()->sort()->values(),
                'managers' => $rows->pluck('manager')->filter()->unique()->values(),
                'device_count' => $rows->pluck('device_id')->unique()->count(),
                'versions' => $rows->groupBy(fn (Package $package) => $package->version . ($package->build ? "-$package->build" : ''))
                    ->map(fn (Collection $versionRows) => $versionRows->pluck('device')->filter()->unique('device_id')->sortBy('display')->values()),
            ]);

        return view('packages.index', [
            'names' => $names,
            'packages' => $packages,
            'filters' => $validated,
            'device' => isset($validated['device_id']) ? Device::hasAccess($user)->find($validated['device_id']) : null,
        ]);
    }

    /**
     * Translate the old packages/name=foo pages
     */
    public function legacy(): RedirectResponse
    {
        return redirect()->route('packages.index', array_filter(['name' => Url::parseOptions('name')]));
    }

    /**
     * Split an optional comparison operator from the version, ">= 1.2" becomes ['>=', '1.2']
     *
     * @return array{?string, string}
     */
    private function parseVersion(string $version): array
    {
        if (preg_match('/^(<=|>=|<|>|==|=|!=)\s*(\S+)$/', trim($version), $matches)) {
            return [$matches[1] == '=' ? '==' : $matches[1], $this->normalizeVersion($matches[2])];
        }

        return [null, trim($version)];
    }

    /**
     * Debian epochs (1:2.3) confuse version_compare
     */
    private function normalizeVersion(string $version): string
    {
        return str_replace(':', '.', $version);
    }
}
