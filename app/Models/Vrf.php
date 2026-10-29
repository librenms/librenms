<?php

/**
 * Vrf.php
 *
 * A VRF (routing instance) of a device.
 * vrf_oid is the VRF index used by the vendor MIB that lists it (string indexes in their
 * length prefixed OID form), or the VRF name when the device only references VRFs by name.
 * context_name is the SNMP context that exposes the VRF. Routing modules only walk it when it
 * answered discovery (context_verified) and the user did not disable it (context_enabled).
 * context_failures counts consecutive discoveries it did not answer.
 * context_manual marks contexts set by users, vrf_manual VRFs that only exist because of such a context.
 * Those VRFs have no index in a MIB, their vrf_oid is the context with a prefix so it never matches one.
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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\HasSyncProtectedAttributes;
use LibreNMS\Interfaces\Models\Keyable;

class Vrf extends DeviceRelatedModel implements HasSyncProtectedAttributes, Keyable
{
    /** @use HasFactory<\Database\Factories\VrfFactory> */
    use HasFactory;

    public $timestamps = false;
    protected $table = 'vrfs';
    protected $primaryKey = 'vrf_id';
    protected $fillable = [
        'vrf_oid',
        'vrf_name',
        'context_name',
        'context_manual',
        'vrf_manual',
        'context_verified',
        'context_failures',
        'context_enabled',
        'bgpLocalAs',
        'mplsVpnVrfRouteDistinguisher',
        'mplsVpnVrfDescription',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context_manual' => 'boolean',
            'vrf_manual' => 'boolean',
            'context_verified' => 'boolean',
            'context_failures' => 'integer',
            'context_enabled' => 'boolean',
        ];
    }

    /**
     * vrf_oid of a VRF only known through its SNMP context
     */
    public static function contextOnlyOid(string $context): string
    {
        return 'context:' . $context;
    }

    /**
     * The default VRF is reached without a context, so its context is never walked
     */
    public static function isDefaultName(?string $name): bool
    {
        return $name !== null && strcasecmp($name, 'default') == 0;
    }

    public function isDefault(): bool
    {
        return self::isDefaultName($this->vrf_name);
    }

    /**
     * Walking a context is a user setting
     */
    public function getSyncProtectedAttributes(): array
    {
        return ['context_enabled'];
    }

    /**
     * Contexts routing modules walk
     */
    public function isContextWalked(): bool
    {
        return $this->context_name !== null && $this->context_verified && $this->context_enabled && ! $this->isDefault();
    }

    public function getCompositeKey(): string
    {
        return (string) $this->vrf_oid;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Port, $this>
     */
    public function ports(): HasMany
    {
        return $this->hasMany(Port::class, 'ifVrf', 'vrf_id');
    }
}
