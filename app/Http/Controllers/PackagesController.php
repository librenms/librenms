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
        $query = self::filteredQuery($request);

        $names = (clone $query)->select('name')->groupBy('name')->orderBy('name')->paginate(50)->withQueryString();

        $packages = (clone $query)->with('device')
            ->whereIn('name', $names->pluck('name'))
            ->orderBy('name')->orderBy('version')->orderBy('build')
            ->get()
            ->groupBy('name')
            ->map(fn (Collection $rows) => [
                'arch' => $rows->pluck('arch')->filter()->unique()->sort()->values(),
                'managers' => $rows->pluck('manager')->filter()->unique()->values(),
                'device_count' => $rows->pluck('device_id')->unique()->count(),
                'versions' => $rows->groupBy(fn (Package $package) => $package->displayVersion())
                    ->map(fn (Collection $versionRows) => $versionRows->pluck('device')->filter()->unique('device_id')->sortBy('display')->values()),
            ]);

        return view('packages.index', [
            'names' => $names,
            'packages' => $packages,
            'filter' => $request->array('filter'),
            'filterFields' => Package::filterFieldDefinitions(),
        ]);
    }

    /**
     * @return Builder<Package>
     */
    public static function filteredQuery(Request $request, ?int $deviceId = null): Builder
    {
        $request->validate(Package::filterValidationRules());

        /** @var User $user */
        $user = $request->user();
        $filter = $request->array('filter');

        if ($deviceId) {
            unset($filter['device_id']); // a device page only ever shows that device
        }

        return Package::hasAccess($user)
            ->when($deviceId, fn (Builder $q) => $q->where('packages.device_id', $deviceId))
            ->when($filter, fn (Builder $q, array $filters) => $q->applyFilters($filters));
    }

    /**
     * Translate the old packages/name=foo pages
     */
    public function legacy(): RedirectResponse
    {
        $name = Url::parseOptions('name');

        return redirect()->to($name ? Package::urlForName($name) : route('packages.index'));
    }
}
