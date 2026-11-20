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
use LibreNMS\Applications\ApplicationDefinition;

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

    public function definition(): ApplicationDefinition
    {
        return ApplicationDefinition::forType($this->app_type);
    }

    public function displayName(): string
    {
        return $this->definition()->displayName();
    }

    public function deviceAppsUrl(array $options = []): string
    {
        return route('device.apps', [
            'device' => $this->device_id,
            'app' => $this->app_type,
            'instance' => filled($this->app_instance) ? $this->app_id : null,
        ] + $options);
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
        $cutoff = Carbon::now()->subDays($days);
        $appIds = static::onlyTrashed()->where('deleted_at', '<=', $cutoff)->pluck('app_id');

        $metrics = $appIds->isEmpty() ? 0 : ApplicationMetric::whereIn('app_id', $appIds)->delete();
        $applications = static::onlyTrashed()->where('deleted_at', '<=', $cutoff)->forceDelete();

        return ['applications' => $applications, 'metrics' => $metrics];
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
