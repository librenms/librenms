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

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Port;
use App\Models\Vrf as VrfModel;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Discovery\Vrf\VrfTableReader;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use SnmpQuery;

/**
 * @phpstan-import-type VrfEntry from VrfTableReader
 */
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
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();
        $this->answers = [];

        $discovered = $this->addManualContexts(
            $this->reader($os)->read(),
            self::manualContexts($device),
        );

        $existing = $device->vrfs->keyBy->getCompositeKey();
        $vrfs = new Collection;
        foreach ($discovered as $vrf) {
            $context = VrfModel::isDefaultName($vrf['name']) ? null : $vrf['context']; // the default VRF is walked without a context
            $model = new VrfModel([
                'vrf_oid' => $vrf['vrf_oid'],
                'vrf_name' => $vrf['name'],
                'context_name' => $context,
                'bgpLocalAs' => $vrf['local_as'],
                'mplsVpnVrfRouteDistinguisher' => $vrf['rd'],
                'mplsVpnVrfDescription' => $vrf['description'],
            ]);

            if ($context === null) {
                $model->context_verified = false;
            } elseif ($existing->get($vrf['vrf_oid'])->context_enabled ?? true) {
                $model->context_verified = $this->contextVerified($context, $vrf['name']);
            } // contexts users disabled are not checked

            $vrfs->push($model);
        }

        ModuleModelObserver::observe(VrfModel::class);
        $synced = $this->syncModels($device, 'vrfs', $vrfs)->values();
        ModuleModelObserver::done();
        $device->setRelation('vrfs', $synced);

        $this->syncPorts($device, $synced, $discovered);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // no polling
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
     * Manual contexts expose the VRF with the same name, if there is one
     *
     * @param  array<array-key, VrfEntry>  $vrfs
     * @param  string[]  $contexts
     * @return array<array-key, VrfEntry>
     */
    private function addManualContexts(array $vrfs, array $contexts): array
    {
        foreach ($contexts as $context) {
            if (in_array($context, array_column($vrfs, 'context'), true)) {
                continue; // the device mapped it
            }

            $match = array_key_first(array_filter($vrfs, fn (array $vrf) => $vrf['name'] === $context))
                ?? array_key_first(array_filter($vrfs, fn (array $vrf) => strcasecmp($vrf['name'], $context) == 0));

            if ($match !== null) {
                if ($vrfs[$match]['context'] === null) {
                    $vrfs[$match]['context'] = $context;
                } else {
                    Log::debug("Ignoring context $context, VRF {$vrfs[$match]['name']} is mapped to context {$vrfs[$match]['context']}");
                }
            } elseif (! VrfModel::isDefaultName($context) && ! isset($vrfs[$context])) {
                // the device only told us about this VRF through the context
                $vrfs[$context] = ['vrf_oid' => $context, 'name' => $context, 'rd' => null, 'description' => '', 'local_as' => null, 'context' => $context, 'ifIndexes' => []];
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

    protected function reader(OS $os): VrfTableReader
    {
        return VrfTableReader::forOs($os->getName());
    }

    protected function contextAnswers(string $context): bool
    {
        return SnmpQuery::context($context)->get('SNMPv2-MIB::sysUpTime.0')->isValid();
    }

    /**
     * Set the VRF of member interfaces and clear it from interfaces that left
     *
     * @param  Collection<int, VrfModel>  $vrfs
     * @param  array<array-key, VrfEntry>  $discovered
     */
    private function syncPorts(Device $device, Collection $vrfs, array $discovered): void
    {
        $members = [];
        foreach ($vrfs as $vrf) {
            foreach ($discovered[$vrf->vrf_oid]['ifIndexes'] ?? [] as $ifIndex) {
                $port = PortCache::getByIfIndex($ifIndex, $device);
                if ($port === null) {
                    Log::debug("Interface $ifIndex of VRF $vrf->vrf_name not found");
                    continue;
                }

                $members[$port->port_id] = $port;
                if ($port->ifVrf != $vrf->vrf_id) {
                    $port->ifVrf = $vrf->vrf_id;
                    $port->save();
                }
            }
        }

        $device->ports()->where('ifVrf', '!=', 0)->whereNotIn('port_id', array_keys($members))->get()
            ->each(function (Port $port): void {
                $port->ifVrf = 0;
                $port->save();
            });
    }
}
