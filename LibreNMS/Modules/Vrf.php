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
use LibreNMS\Exceptions\SnmpException;
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

    /** a context that answered before is walked until it fails this many discoveries in a row */
    public const CONTEXT_FAILURE_LIMIT = 3;

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
        $this->syncVrfs($os, fn () => $os->discoverVrfs(), true);
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

        $this->syncVrfs($os, fn () => $os instanceof VrfPolling ? $os->pollVrfs($os->getDevice()->vrfs) : $os->discoverVrfs(), false);
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
            'vrfs' => $device->vrfs()->orderBy('vrf_oid')->get()->map(fn (VrfModel $vrf) => $vrf->makeHidden(['vrf_id', 'device_id', 'context_failures', 'context_enabled'])->toArray() + [
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
     * OS implementations throw SnmpException when the device did not answer a query whose result must be known.
     * VRFs are left as they are then, removing them would remove their contexts and the data of every module.
     *
     * @param  callable(): Collection<int, VrfModel>  $fetch
     */
    private function syncVrfs(OS $os, callable $fetch, bool $checkContexts): void
    {
        $device = $os->getDevice();

        try {
            $vrfs = $fetch();
        } catch (SnmpException $e) {
            Log::warning('Device did not answer, VRFs were not updated: ' . $e->getMessage());

            return;
        }

        $existing = $device->vrfs->keyBy->getCompositeKey();
        $vrfs = $this->addManualContexts($vrfs->filter(fn (VrfModel $vrf) => $vrf->vrf_oid !== null && $vrf->vrf_oid !== '' && $vrf->vrf_name), self::manualContexts($device));

        foreach ($vrfs as $vrf) {
            $vrf->mplsVpnVrfDescription ??= '';
            if ($vrf->isDefault()) {
                $vrf->context_name = null; // the default VRF is walked without a context
            }

            $this->updateContextStatus($vrf, $existing->get($vrf->getCompositeKey()), $checkContexts);
        }

        ModuleModelObserver::observe(VrfModel::class);
        $synced = $this->syncModels($device, 'vrfs', $vrfs)->values();
        ModuleModelObserver::done();
        $device->unsetRelation('vrfs'); // load database defaults of new VRFs

        try {
            $this->syncPorts($device, $os->discoverVrfPorts($synced));
        } catch (SnmpException $e) {
            Log::warning('Device did not answer, VRF interfaces were not updated: ' . $e->getMessage());
        }
    }

    /**
     * Only discovery checks contexts. A context that answered before stays walked until it
     * fails several discoveries in a row, so a single lost reply does not drop a VRF's data.
     */
    private function updateContextStatus(VrfModel $vrf, ?VrfModel $existing, bool $checkContexts): void
    {
        if ($vrf->context_name === null) {
            $vrf->context_verified = false;
            $vrf->context_failures = 0;

            return;
        }

        $enabled = $existing->context_enabled ?? true;
        $changed = $existing === null || $existing->context_name !== $vrf->context_name;

        if (! $checkContexts || ! $enabled) {
            if ($changed) {
                // not checked yet
                $vrf->context_verified = false;
                $vrf->context_failures = 0;
            }

            return; // keep the result of the last discovery
        }

        if ($this->contextAnswers($vrf->context_name)) {
            $vrf->context_verified = true;
            $vrf->context_failures = 0;

            return;
        }

        $vrf->context_failures = $changed ? 1 : min($existing->context_failures + 1, 255);
        $vrf->context_verified = ! $changed && $existing->context_verified && $vrf->context_failures < self::CONTEXT_FAILURE_LIMIT;

        Log::warning("SNMP context $vrf->context_name of VRF $vrf->vrf_name did not answer ($vrf->context_failures in a row)"
            . ($vrf->context_verified ? '' : ', it will not be polled'));
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
    private function contextAnswers(string $context): bool
    {
        return $this->answers[$context] ??= $this->checkContext($context);
    }

    protected function checkContext(string $context): bool
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
