<?php

/**
 * Vrf.php
 *
 * Discovers VRFs, the SNMP contexts that expose them and the interfaces in them.
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

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Port;
use App\Models\Vrf as VrfModel;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\VrfPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use SnmpQuery;

class Vrf implements Module
{
    use SyncsModels;

    /** contexts that should be walked, set by users under Device > Edit > Routing */
    public const MANUAL_CONTEXTS_ATTRIB = 'routing_snmp_contexts';

    /** @var array<string, bool> */
    private array $answers = [];

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $this->answers = [];
        $this->syncVrfs($os, $os->discoverVrfs(), true);
    }

    /**
     * VRFs are polled with the OS polling if it has one, otherwise discovery runs again.
     * Contexts are only checked by discovery, so a context that does not answer only delays discovery.
     *
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if ($os->getDevice()->vrfs->isEmpty()) {
            return;
        }

        if ($os instanceof VrfPolling) {
            ModuleModelObserver::observe(VrfModel::class);
            $this->syncModels($os->getDevice(), 'vrfs', $os->pollVrfs($os->getDevice()->vrfs));
            ModuleModelObserver::done();

            return;
        }

        $this->syncVrfs($os, $os->discoverVrfs(), false);
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->vrfs()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        $device->ports()->where('ifVrf', '!=', 0)->update(['ifVrf' => 0]);

        return $device->vrfs()->delete();
    }

    /**
     * @return array{vrfs: array<int, array<string, mixed>>}
     */
    public function dump(Device $device, string $type): ?array
    {
        $ifIndexes = $device->ports()->where('ifVrf', '!=', 0)->orderBy('ifIndex')->get(['ifVrf', 'ifIndex'])->groupBy('ifVrf');

        return [
            'vrfs' => $device->vrfs()->orderBy('vrf_oid')->get()->map(fn (VrfModel $vrf) => $vrf->makeHidden(['vrf_id', 'device_id', 'context_enabled'])->toArray() + [
                'ifIndices' => $ifIndexes->has($vrf->vrf_id) ? $ifIndexes->get($vrf->vrf_id)->pluck('ifIndex')->implode(',') : null,
            ])->all(),
        ];
    }

    /**
     * Contexts users set because the device does not map its contexts to VRFs
     *
     * @return string[]
     */
    public static function manualContexts(Device $device): array
    {
        $contexts = json_decode((string) $device->getAttrib(self::MANUAL_CONTEXTS_ATTRIB, '[]'), true);

        return is_array($contexts) ? array_values(array_filter($contexts, fn ($context) => is_string($context) && $context !== '')) : [];
    }

    /**
     * @param  Collection<int, VrfModel>  $vrfs
     */
    private function syncVrfs(OS $os, Collection $vrfs, bool $checkContexts): void
    {
        $device = $os->getDevice();
        $existing = $device->vrfs->keyBy->getCompositeKey();
        $vrfs = $this->addManualContexts($vrfs->filter(fn (VrfModel $vrf) => $vrf->vrf_oid !== null && $vrf->vrf_oid !== '' && $vrf->vrf_name), self::manualContexts($device));

        foreach ($vrfs as $vrf) {
            $vrf->mplsVpnVrfDescription ??= '';

            if ($vrf->isDefault()) {
                $vrf->context_name = null; // the default VRF is walked without a context
            }

            if ($vrf->context_name === null) {
                $vrf->context_verified = false;
            } elseif ($checkContexts && ($existing->get($vrf->getCompositeKey())->context_enabled ?? true)) {
                $vrf->context_verified = $this->contextVerified($vrf->context_name, (string) $vrf->vrf_name);
            } // contexts users disabled are not checked, polling keeps the result of discovery
        }

        ModuleModelObserver::observe(VrfModel::class);
        $synced = $this->syncModels($device, 'vrfs', $vrfs)->values();
        ModuleModelObserver::done();
        $device->setRelation('vrfs', $synced);

        $this->syncPorts($device, $os->discoverVrfPorts($synced));
    }

    /**
     * Manual contexts expose the VRF with the same name, or a VRF the device only told us about through the context
     *
     * @param  Collection<int, VrfModel>  $vrfs
     * @param  string[]  $contexts
     * @return Collection<int, VrfModel>
     */
    private function addManualContexts(Collection $vrfs, array $contexts): Collection
    {
        foreach ($contexts as $context) {
            if ($vrfs->contains('context_name', $context)) {
                continue; // the device mapped it
            }

            $vrf = $vrfs->firstWhere('vrf_name', $context)
                ?? $vrfs->first(fn (VrfModel $vrf) => strcasecmp((string) $vrf->vrf_name, $context) == 0);

            if ($vrf === null) {
                if (! VrfModel::isDefaultName($context) && ! $vrfs->contains('vrf_oid', $context)) {
                    $vrfs->push(new VrfModel(['vrf_oid' => $context, 'vrf_name' => $context, 'context_name' => $context]));
                }
            } elseif ($vrf->context_name === null) {
                $vrf->context_name = $context;
            } else {
                Log::debug("Ignoring context $context, VRF $vrf->vrf_name is mapped to context $vrf->context_name");
            }
        }

        return $vrfs;
    }

    /**
     * Contexts that don't answer would make every module that walks them wait for a timeout,
     * so a context is only walked after it answered a single request.
     */
    private function contextVerified(string $context, string $vrfName): bool
    {
        $this->answers[$context] ??= $this->contextAnswers($context);

        if (! $this->answers[$context]) {
            Log::warning("SNMP context $context of VRF $vrfName did not answer, it will not be polled");
        }

        return $this->answers[$context];
    }

    protected function contextAnswers(string $context): bool
    {
        return SnmpQuery::context($context)->get('SNMPv2-MIB::sysUpTime.0')->isValid();
    }

    /**
     * Save the VRF of member ports and clear it from ports that left their VRF
     *
     * @param  Collection<int, Port>  $members  ports with ifVrf set
     */
    private function syncPorts(Device $device, Collection $members): void
    {
        $members->each(fn (Port $port) => $port->isDirty('ifVrf') && $port->save());

        $device->ports()->where('ifVrf', '!=', 0)->whereNotIn('port_id', $members->pluck('port_id'))->get()
            ->each(function (Port $port): void {
                $port->ifVrf = 0;
                $port->save();
            });
    }
}
