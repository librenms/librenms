<?php

/**
 * PortGroup.php
 *
 * Groups of ports
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 *
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Models;

use App\Facades\Permissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Alerting\QueryBuilderFluentParser;

class PortGroup extends BaseModel
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['name', 'desc', 'type'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function (PortGroup $portGroup): void {
            $portGroup->ports()->detach();
        });

        static::saving(function (PortGroup $portGroup): void {
            if ($portGroup->isDirty('rules')) {
                $portGroup->rules = $portGroup->getParser()->generateJoins()->toArray();
            }
        });

        static::saved(function (PortGroup $portGroup): void {
            if ($portGroup->isDirty('rules')) {
                $portGroup->updatePorts();
            }
        });
    }

    /**
     * @return array{rules: 'array'}
     */
    protected function casts(): array
    {
        return [
            'rules' => 'array',
        ];
    }

    // ---- Helper Functions ----

    /**
     * Update ports included in this group (dynamic only)
     */
    public function updatePorts(): void
    {
        if ($this->type == 'dynamic') {
            $this->ports()->sync($this->getPortIdQuery()?->pluck('ports.port_id') ?? []);
        }
    }

    /**
     * Query returning the port ids matching this group's rules.
     * Rules are anchored on devices, so ports is joined in explicitly and the
     * rows for devices without ports are discarded.
     */
    public function getPortIdQuery(): ?\Illuminate\Database\Query\Builder
    {
        return $this->getParser()->toQuery()
            ?->whereNotNull('ports.port_id')
            ->distinct();
    }

    /**
     * Get a query builder parser instance from this port group
     */
    public function getParser(): QueryBuilderFluentParser
    {
        return QueryBuilderFluentParser::fromJson($this->rules ?: [])->requireTables('ports');
    }

    public function scopeHasAccess($query, User $user)
    {
        if (Gate::allows('viewAll', PortGroup::class)) {
            return $query;
        }

        return $query->whereIntegerInRaw('id', Permissions::portGroupsForUser($user));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Port, $this>
     */
    public function ports(): BelongsToMany
    {
        return $this->belongsToMany(Port::class, 'port_group_port', 'port_group_id', 'port_id');
    }
}
