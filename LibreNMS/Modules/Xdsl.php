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
use App\Models\PortAdsl;
use App\Models\PortVdsl;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
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
    public function discover(OS $os)
    {
        //discover if any port has dsl data.
//        $this->pollAdsl($os, false);
        $this->pollVdsl($os, false);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os)
    {
        if ($os->getDevice()->portsAdsl()->exists()) {
            $this->pollAdsl($os);
        }

        if ($os->getDevice()->portsVdsl()->exists()) {
            $this->pollVdsl($os);
        }
    }

    /**
     * @inheritDoc
     */
    public function cleanup(OS $os)
    {
        $os->getDevice()->portsAdsl()->delete();
        $os->getDevice()->portsVdsl()->delete();
    }

    private function pollAdsl(OS $os, $store = true): Collection
    {
        $adsl = \SnmpQuery::hideMib()->walk('ADSL-LINE-MIB::adslMibObjects')->mapTable(function ($data, $ifIndex) use ($os, $store) {
            $port = new PortAdsl($data);

            // trim SnmpAdminStrings
            foreach ($this->trimAdminString as $oid) {
                $port->$oid = rtrim($port->$oid, '.');
            }

            // Values are 1/10
            foreach ($this->adslTenthValues as $oid) {
                $port->$oid = $port->$oid / 10;
            }

            $port->port_id = $os->ifIndexToId($ifIndex);

            if ($store) {
                $this->storeAdsl($port, $data, (int) $ifIndex, $os);
                echo 'ADSL (' . $port->adslLineCoding . '/' . Number::formatSi($port->adslAtucChanCurrTxRate, 2, 3, 'bps') . '/' . Number::formatSi($port->adslAturChanCurrTxRate, 2, 3, 'bps') . ')';
            }

            return $port;
        });

        ModuleModelObserver::observe(PortAdsl::class);
        return $this->syncModels($os->getDevice(), 'portsAdsl', $adsl);
    }

    private function pollVdsl(OS $os, $store = true): Collection
    {
        $vdsl = \SnmpQuery::hideMib()->walk(['VDSL2-LINE-MIB::xdsl2ChannelStatusTable', 'VDSL2-LINE-MIB::xdsl2LineTable'])->table(1);

        $vdslPorts = new Collection;
        foreach ($vdsl as $ifIndex => $data) {
            $portVdsl = new PortVdsl([
                'port_id' => $os->ifIndexToId($ifIndex),
                'xdsl2ChStatusActDataRateXtur' => $data['xdsl2ChStatusActDataRate']['xtur'] ?? 0,
                'xdsl2ChStatusActDataRateXtuc' => $data['xdsl2ChStatusActDataRate']['xtuc'] ?? 0,
            ]);
            $portVdsl->fill($data); // fill oids that are one to one

            foreach ($this->adslTenthValues as $oid) {
                $portVdsl->$oid = $portVdsl->$oid / 10;
            }

            if ($store) {
                $this->storeVdsl($portVdsl, $data, (int) $ifIndex, $os);
                echo 'VDSL (' . $os->ifIndexToName($ifIndex) . '/' . Number::formatSi($portVdsl->xdsl2LineStatusAttainableRateDs, 2, 3, 'bps') . '/' . Number::formatSi($portVdsl->xdsl2LineStatusAttainableRateUs, 2, 3, 'bps') . ') \n';
            }

            $vdslPorts->push($portVdsl);
        }

        ModuleModelObserver::observe(PortVdsl::class);
        return $this->syncModels($os->getDevice(), 'portsVdsl', $vdslPorts);
    }

    private function storeAdsl(PortAdsl $port, array $data, int $ifIndex, OS $os): void
    {
        $rrd_def = RrdDefinition::make()
            ->addDataset('AtucCurrSnrMgn', 'GAUGE', 0, 635)
            ->addDataset('AtucCurrAtn', 'GAUGE', 0, 635)
            ->addDataset('AtucCurrOutputPwr', 'GAUGE', 0, 635)
            ->addDataset('AtucCurrAttainableR', 'GAUGE', 0)
            ->addDataset('AtucChanCurrTxRate', 'GAUGE', 0)
            ->addDataset('AturCurrSnrMgn', 'GAUGE', 0, 635)
            ->addDataset('AturCurrAtn', 'GAUGE', 0, 635)
            ->addDataset('AturCurrOutputPwr', 'GAUGE', 0, 635)
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
            'AtucCurrSnrMgn' => $data['adslAtucCurrSnrMgn'] > 1280 ? null : $data['adslAtucCurrSnrMgn'],
            'AtucCurrAtn' => $data['adslAtucCurrAtn'],
            'AtucCurrOutputPwr' => $data['adslAtucCurrOutputPwr'],
            'AtucCurrAttainableRate' => $data['adslAtucCurrAttainableRate'],
            'AtucChanCurrTxRate' => $data['adslAtucChanCurrTxRate'],
            'AturCurrSnrMgn' => $data['adslAturCurrSnrMgn'] > 1280 ? null : $data['adslAturCurrSnrMgn'],
            'AturCurrAtn' => $data['adslAturCurrAtn'],
            'AturCurrOutputPwr' => $data['adslAturCurrOutputPwr'],
            'AturCurrAttainableRate' => $data['adslAturCurrAttainableRate'],
            'AturChanCurrTxRate' => $data['adslAturChanCurrTxRate'],
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

        data_update($os->getDeviceArray(), 'adsl', [
            'ifName' => $os->ifIndexToName($ifIndex),
            'rrd_name' => Rrd::portName($port->port_id, 'adsl'),
            'rrd_def' => $rrd_def,
        ], $fields);
    }

    private function storeVdsl(PortVdsl $port, array $data, int $ifIndex, OS $os): void
    {
        // Attainable
        data_update($os->getDeviceArray(), 'xdsl2LineStatusAttainableRate', [
            'ifName' => $os->ifIndexToName($ifIndex),
            'rrd_name' => Rrd::portName($port->port_id, 'xdsl2LineStatusAttainableRate'),
            'rrd_def' => RrdDefinition::make()
                ->addDataset('ds', 'GAUGE', 0)
                ->addDataset('us', 'GAUGE', 0)
        ], [
            'ds' => $data['xdsl2LineStatusAttainableRateDs'] ?? null,
            'us' => $data['xdsl2LineStatusAttainableRateUs'] ?? null,
        ]);

        // actual data rates
        data_update($os->getDeviceArray(), 'xdsl2LineStatusActRate', [
            'ifName' => $os->ifIndexToName($ifIndex),
            'rrd_name' => Rrd::portName($port->port_id, 'xdsl2ChStatusActDataRate'),
            'rrd_dev' => RrdDefinition::make()
                ->addDataset('xtuc', 'GAUGE', 0)
                ->addDataset('xtur', 'GAUGE', 0),
        ], [
            'xtuc' => $data['xdsl2ChStatusActDataRate']['xtuc'],
            'xtur' => $data['xdsl2ChStatusActDataRate']['xtur'],
        ]);

        // power levels
        data_update($os->getDeviceArray(), 'xdsl2LineStatusActAtp', [
            'ifName' => $os->ifIndexToName($ifIndex),
            'rrd_name' => Rrd::portName($port->port_id, 'xdsl2LineStatusActAtp'),
            'rrd_def' => RrdDefinition::make()
                ->addDataset('ds', 'GAUGE', 0)
                ->addDataset('us', 'GAUGE', 0),
        ], [
            'ds' => $data['xdsl2LineStatusActAtpDs'] ?? null,
            'us' => $data['xdsl2LineStatusActAtpUs'] ?? null,
        ]);
    }
}
