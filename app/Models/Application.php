<?php

/**
 * Applications.php
 *
 * -Description-
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\StringHelpers;

class Application extends DeviceRelatedModel
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = false;
    protected $primaryKey = 'app_id';
    protected $fillable = ['device_id', 'app_type', 'app_instance', 'app_status', 'app_state', 'data', 'deleted_at', 'discovered'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    // ---- Helper Functions ----

    public function displayName()
    {
        return StringHelpers::niceCase($this->app_type);
    }

    public function getShowNameAttribute()
    {
        return $this->displayName();
    }

    /**
     * Hard-delete applications that were soft-deleted at least $days ago,
     * along with their metrics.
     *
     * @return array{applications: int, metrics: int}
     */
    public static function purgeTrashed(int $days): array
    {
        return DB::transaction(function () use ($days): array {
            $cutoff = Carbon::now()->subDays($days);

            $appIds = static::onlyTrashed()->where('deleted_at', '<=', $cutoff)->pluck('app_id');

            if ($appIds->isEmpty()) {
                return ['applications' => 0, 'metrics' => 0];
            }

            // Re-evaluate the trashed set at delete time so an application restored
            // since the selection never loses its metrics.
            $metrics = ApplicationMetric::whereIn(
                'app_id',
                static::onlyTrashed()->where('deleted_at', '<=', $cutoff)->select('app_id'),
            )->delete();

            $applications = static::onlyTrashed()
                ->where('deleted_at', '<=', $cutoff)
                ->whereIn('app_id', $appIds)
                ->forceDelete();

            return ['applications' => $applications, 'metrics' => $metrics];
        });
    }

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\ApplicationMetric, $this>
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(ApplicationMetric::class, 'app_id', 'app_id');
    }
}
