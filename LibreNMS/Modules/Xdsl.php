<?php
/**
 * Xdsl.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Facades\Rrd;
use App\Models\Device;
use App\Models\PortAdsl;
use App\Models\PortVdsl;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\IntegerType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

class Xdsl implements Module
{
    use SyncsModels;

    /** @var string[] */
    private $trimAdminString = ['adslAtucInvVendorID', 'adslAturInvVendorID', 'adslAturInvVersionNumber', 'adslAtucInvVersionNumber'];
    /** @var string[] */
    private $adslTenthValues = ['adslAtucCurrSnrMgn', 'adslAtucCurrAtn', 'adslAtucCurrOutputPwr', 'adslAturCurrSnrMgn', 'adslAturCurrAtn', 'adslAturCurrOutputPwr'];
    /** @var string[] */
    private $vdslTenthValues = ['xdsl2LineStatusActAtpDs', 'xdsl2LineStatusActAtpUs'];
    /** @var string[] */
    private $ifNameMap;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        //discover if any port has dsl data. We use the pollXdsl functions, with the datastore parameter ommitted
        $this->pollAdsl($os);
        $this->pollVdsl($os);
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  \LibreNMS\OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        //only do polling if at least one portAdsl was discovered
        if ($os->getDevice()->portsAdsl()->exists()) {
            $this->pollAdsl($os, $datastore);
        }

        if ($os->getDevice()->portsVdsl()->exists()) {
            $this->pollVdsl($os, $datastore);
        }
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): void
    {
        $device->portsAdsl()->delete();
        $device->portsVdsl()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'ports_adsl' => $device->portsAdsl()->orderBy('ifIndex')
                ->select(['ports_adsl.*', 'ifIndex'])
                ->get()->map->makeHidden(['laravel_through_key', 'port_adsl_updated', 'port_id']),
            'ports_vdsl' => $device->portsVdsl()->orderBy('ifIndex')
                ->select(['ports_vdsl.*', 'ifIndex'])
                ->get()->map->makeHidden(['laravel_through_key', 'port_vdsl_updated', 'port_id']),
        ];
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     */
    private function pollAdsl(OS $os, ?DataStorageInterface $datastore = null): Collection
    {
        $adsl = \SnmpQuery::hideMib()->walk('ADSL-LINE-MIB::adslMibObjects')->table(1);
        $adslPorts = new Collection;

        foreach ($adsl as $ifIndex => $data) {
            // Values are 1/10
            foreach ($this->adslTenthValues as $oid) {
                if (isset($data[$oid])) {
                    if ($oid == 'adslAtucCurrOutputPwr') {
                        // workaround Cisco Bug CSCvj53634
                        $data[$oid] = Number::constrainInteger($data[$oid], IntegerType::int32);
                    }
                    $data[$oid] = $data[$oid] / 10;
                }
            }

            $portAdsl = new PortAdsl($data);

            // trim SnmpAdminStrings
            foreach ($this->trimAdminString as $oid) {
                $portAdsl->$oid = rtrim($portAdsl->$oid ?? '', '.');
            }

            $portAdsl->port_id = $os->ifIndexToId($ifIndex);

            if ($portAdsl->port_id == 0) {
                // failure of ifIndexToId(), port_id is invalid, and syncModels will crash
                echo ' ADSL( Failed to discover this port, ifIndex invalid : ' . $portAdsl->adslLineCoding . '/' . Number::formatSi($portAdsl->adslAtucChanCurrTxRate, 2, 3, 'bps') . '/' . Number::formatSi($portAdsl->adslAturChanCurrTxRate, 2, 3, 'bps') . ') ';
                continue;
            }

            if ($datastore) {
                $this->storeAdsl($portAdsl, $data, (int) $ifIndex, $os, $datastore);
                echo ' ADSL(' . $portAdsl->adslLineCoding . '/' . Number::formatSi($portAdsl->adslAtucChanCurrTxRate, 2, 3, 'bps') . '/' . Number::formatSi($portAdsl->adslAturChanCurrTxRate, 2, 3, 'bps') . ') ';
            }

            $adslPorts->push($portAdsl);
        }

        ModuleModelObserver::observe(PortAdsl::class);

        return $this->syncModels($os->getDevice(), 'portsAdsl', $adslPorts);
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     */
    private function pollVdsl(OS $os, ?DataStorageInterface $datastore = null): Collection
    {
        $vdsl = \SnmpQuery::hideMib()->walk(['VDSL2-LINE-MIB::xdsl2ChannelStatusTable', 'VDSL2-LINE-MIB::xdsl2LineTable'])->table(1);
        $vdslPorts = new Collection;

        foreach ($vdsl as $ifIndex => $data) {
            $portVdsl = new PortVdsl([
                'port_id' => $os->ifIndexToId($ifIndex),
                'xdsl2ChStatusActDataRateXtur' => $data['xdsl2ChStatusActDataRate']['xtur'] ?? 0,
                'xdsl2ChStatusActDataRateXtuc' => $data['xdsl2ChStatusActDataRate']['xtuc'] ?? 0,
            ]);

            foreach ($this->vdslTenthValues as $oid) {
                if (isset($data[$oid])) {
                    $data[$oid] = $data[$oid] / 10;
                }
            }

            $portVdsl->fill($data); // fill oids that are one to one

            if ($datastore) {
                $this->storeVdsl($portVdsl, $data, (int) $ifIndex, $os, $datastore);
                echo ' VDSL(' . $os->ifIndexToName($ifIndex) . '/' . Number::formatSi($portVdsl->xdsl2LineStatusAttainableRateDs, 2, 3, 'bps') . '/' . Number::formatSi($portVdsl->xdsl2LineStatusAttainableRateUs, 2, 3, 'bps') . ') ';
            }

            $vdslPorts->push($portVdsl);
        }

        ModuleModelObserver::observe(PortVdsl::class);

        return $this->syncModels($os->getDevice(), 'portsVdsl', $vdslPorts);
    }

    private function storeAdsl(PortAdsl $port, array $data, int $ifIndex, OS $os, DataStorageInterface $datastore): void
    {
        $rrd_def = RrdDefinition::make()
            ->addDataset('AtucCurrSnrMgn', 'GAUGE', 0, 635)
            ->addDataset('AtucCurrAtn', 'GAUGE', 0, 635)
            ->addDataset('AtucCurrOutputPwr', 'GAUGE', -100, 635)
            ->addDataset('AtucCurrAttainableR', 'GAUGE', 0)
            ->addDataset('AtucChanCurrTxRate', 'GAUGE', 0)
            ->addDataset('AturCurrSnrMgn', 'GAUGE', 0, 635)
            ->addDataset('AturCurrAtn', 'GAUGE', 0, 635)
            ->addDataset('AturCurrOutputPwr', 'GAUGE', -100, 635)
            ->addDataset('AturCurrAttainableR', 'GAUGE', 0)
            ->addDataset('AturChanCurrTxRate', 'GAUGE', 0)
            ->addDataset('AtucPerfLofs', 'COUNTER', null, 100000000000)
            ->addDataset('AtucPerfLoss', 'COUNTER', null, 100000000000)
            ->addDataset('AtucPerfLprs', 'COUNTER', null, 100000000000)
            ->addDataset('AtucPerfESs', 'COUNTER', null, 100000000000)
            ->addDataset('AtucPerfInits', 'COUNTER', null, 100000000000)
            ->addDataset('AturPerfLofs', 'COUNTER', null, 100000000000)
            ->addDataset('AturPerfLoss', 'COUNTER', null, 100000000000)
            ->addDataset('AturPerfLprs', 'COUNTER', null, 100000000000)
            ->addDataset('AturPerfESs', 'COUNTER', null, 100000000000)
            ->addDataset('AtucChanCorrectedBl', 'COUNTER', null, 100000000000)
            ->addDataset('AtucChanUncorrectBl', 'COUNTER', null, 100000000000)
            ->addDataset('AturChanCorrectedBl', 'COUNTER', null, 100000000000)
            ->addDataset('AturChanUncorrectBl', 'COUNTER', null, 100000000000);

        $fields = [
            'AtucCurrSnrMgn' => ($data['adslAtucCurrSnrMgn'] ?? 0) > 1280 ? null : ($data['adslAtucCurrSnrMgn'] ?? null),
            'AtucCurrAtn' => $data['adslAtucCurrAtn'] ?? null,
            'AtucCurrOutputPwr' => $data['adslAtucCurrOutputPwr'] ?? null,
            'AtucCurrAttainableRate' => $data['adslAtucCurrAttainableRate'] ?? null,
            'AtucChanCurrTxRate' => $data['adslAtucChanCurrTxRate'] ?? null,
            'AturCurrSnrMgn' => ($data['adslAturCurrSnrMgn'] ?? 0) > 1280 ? null : ($data['adslAturCurrSnrMgn'] ?? null),
            'AturCurrAtn' => $data['adslAturCurrAtn'] ?? null,
            'AturCurrOutputPwr' => $data['adslAturCurrOutputPwr'] ?? null,
            'AturCurrAttainableRate' => $data['adslAturCurrAttainableRate'] ?? null,
            'AturChanCurrTxRate' => $data['adslAturChanCurrTxRate'] ?? null,
            'AtucPerfLofs' => $data['adslAtucPerfLofs'] ?? null,
            'AtucPerfLoss' => $data['adslAtucPerfLoss'] ?? null,
            'AtucPerfLprs' => $data['adslAtucPerfLprs'] ?? null,
            'AtucPerfESs' => $data['adslAtucPerfESs'] ?? null,
            'AtucPerfInits' => $data['adslAtucPerfInits'] ?? null,
            'AturPerfLofs' => $data['adslAturPerfLofs'] ?? null,
            'AturPerfLoss' => $data['adslAturPerfLoss'] ?? null,
            'AturPerfLprs' => $data['adslAturPerfLprs'] ?? null,
            'AturPerfESs' => $data['adslAturPerfESs'] ?? null,
            'AtucChanCorrectedBlks' => $data['adslAtucChanCorrectedBlks'] ?? null,
            'AtucChanUncorrectBlks' => $data['adslAtucChanUncorrectBlks'] ?? null,
            'AturChanCorrectedBlks' => $data['adslAturChanCorrectedBlks'] ?? null,
            'AturChanUncorrectBlks' => $data['adslAturChanUncorrectBlks'] ?? null,
        ];

        $datastore->put($os->getDeviceArray(), 'adsl', [
            'ifName' => $os->ifIndexToName($ifIndex),
            'rrd_name' => Rrd::portName($port->port_id, 'adsl'),
            'rrd_def' => $rrd_def,
        ], $fields);
    }

    private function storeVdsl(PortVdsl $port, array $data, int $ifIndex, OS $os, DataStorageInterface $datastore): void
    {
        // Attainable
        $datastore->put($os->getDeviceArray(), 'xdsl2LineStatusAttainableRate', [
            'ifName' => $os->ifIndexToName($ifIndex),
            'rrd_name' => Rrd::portName($port->port_id, 'xdsl2LineStatusAttainableRate'),
            'rrd_def' => RrdDefinition::make()
                ->addDataset('ds', 'GAUGE', 0)
                ->addDataset('us', 'GAUGE', 0),
        ], [
            'ds' => $data['xdsl2LineStatusAttainableRateDs'] ?? null,
            'us' => $data['xdsl2LineStatusAttainableRateUs'] ?? null,
        ]);

        // actual data rates
        $datastore->put($os->getDeviceArray(), 'xdsl2ChStatusActDataRate', [
            'ifName' => $os->ifIndexToName($ifIndex),
            'rrd_name' => Rrd::portName($port->port_id, 'xdsl2ChStatusActDataRate'),
            'rrd_def' => RrdDefinition::make()
                ->addDataset('xtuc', 'GAUGE', 0)
                ->addDataset('xtur', 'GAUGE', 0),
        ], [
            'xtuc' => $data['xdsl2ChStatusActDataRate']['xtuc'] ?? null,
            'xtur' => $data['xdsl2ChStatusActDataRate']['xtur'] ?? null,
        ]);

        // power levels
        $datastore->put($os->getDeviceArray(), 'xdsl2LineStatusActAtp', [
            'ifName' => $os->ifIndexToName($ifIndex),
            'rrd_name' => Rrd::portName($port->port_id, 'xdsl2LineStatusActAtp'),
            'rrd_def' => RrdDefinition::make()
                ->addDataset('ds', 'GAUGE', -100)
                ->addDataset('us', 'GAUGE', -100),
        ], [
            'ds' => $data['xdsl2LineStatusActAtpDs'] ?? null,
            'us' => $data['xdsl2LineStatusActAtpUs'] ?? null,
        ]);
    }
}
