<?php

/*
 * ModuleModelObserver.php
 *
 * Displays +,-,U,. while running discovery and adding,deleting,updating, and doing nothing.
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Observers;

use App\Models\AccessPoint;
use App\Models\EntPhysical;
use App\Models\Ipv6Address;
use App\Models\IsisAdjacency;
use App\Models\Mempool;
use App\Models\OspfArea;
use App\Models\OspfInstance;
use App\Models\OspfNbr;
use App\Models\OspfPort;
use App\Models\Ospfv3Area;
use App\Models\Ospfv3Instance;
use App\Models\Ospfv3Nbr;
use App\Models\Ospfv3Port;
use App\Models\PortAdsl;
use App\Models\PortsNac;
use App\Models\PortStack;
use App\Models\PortStp;
use App\Models\PortVdsl;
use App\Models\PrinterSupply;
use App\Models\Sla;
use App\Models\Transceiver;
use App\Models\WirelessSensor;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model as Eloquent;

#[ObservedBy([EntPhysical::class])]
#[ObservedBy([Ipv6Address::class])]
#[ObservedBy([IsisAdjacency::class])]
#[ObservedBy([Mempool::class])]
#[ObservedBy([\App\Models\MplsTunnelCHop::class])]
#[ObservedBy([\App\Models\MplsTunnelArHop::class])]
#[ObservedBy([\App\Models\MplsSdpBind::class])]
#[ObservedBy([\App\Models\MplsSap::class])]
#[ObservedBy([\App\Models\MplsService::class])]
#[ObservedBy([\App\Models\MplsSdp::class])]
#[ObservedBy([\App\Models\MplsLspPath::class])]
#[ObservedBy([\App\Models\MplsLsp::class])]
#[ObservedBy([\App\Models\MplsTunnelCHop::class])]
#[ObservedBy([\App\Models\MplsTunnelArHop::class])]
#[ObservedBy([\App\Models\MplsSdpBind::class])]
#[ObservedBy([\App\Models\MplsSap::class])]
#[ObservedBy([\App\Models\MplsService::class])]
#[ObservedBy([\App\Models\MplsSdp::class])]
#[ObservedBy([\App\Models\MplsLspPath::class])]
#[ObservedBy([\App\Models\MplsLsp::class])]
#[ObservedBy([PortsNac::class])]
#[ObservedBy([OspfNbr::class])]
#[ObservedBy([OspfPort::class])]
#[ObservedBy([OspfArea::class])]
#[ObservedBy([OspfInstance::class])]
#[ObservedBy([Ospfv3Nbr::class])]
#[ObservedBy([Ospfv3Port::class])]
#[ObservedBy([Ospfv3Area::class])]
#[ObservedBy([Ospfv3Instance::class])]
#[ObservedBy([Ospfv3Nbr::class])]
#[ObservedBy([Ospfv3Port::class])]
#[ObservedBy([Ospfv3Area::class])]
#[ObservedBy([Ospfv3Instance::class])]
#[ObservedBy([PortStack::class])]
#[ObservedBy([PrinterSupply::class])]
#[ObservedBy([\App\Models\Qos::class])]
#[ObservedBy([Sla::class])]
#[ObservedBy([\App\Models\Storage::class])]
#[ObservedBy([PortStp::class])]
#[ObservedBy([\App\Models\Stp::class])]
#[ObservedBy([PortStp::class])]
#[ObservedBy([\App\Models\Stp::class])]
#[ObservedBy([Transceiver::class])]
#[ObservedBy([\App\Models\Vminfo::class])]
#[ObservedBy([\App\Models\Vminfo::class])]
#[ObservedBy([WirelessSensor::class])]
#[ObservedBy([PortVdsl::class])]
#[ObservedBy([PortAdsl::class])]
#[ObservedBy(['\App\Models\MplsLsp\TnmsneInfo'])]
#[ObservedBy([AccessPoint::class])]
class ModuleModelObserver
{
    /**
     * Install observers to output +, -, U for models being created, deleted, and updated
     *
     * @param  string|Eloquent  $model  The model name including namespace
     */
    public static function observe($model)
    {
        static $observed_models = []; // keep track of observed models so we don't duplicate output
        $class = ltrim($model, '\\');

        if (! in_array($class, $observed_models)) {
            $model::observe(new static());
            $observed_models[] = $class;
        }
    }

    /**
     * @param  Eloquent  $model
     */
    public function saving($model)
    {
        if (! $model->isDirty()) {
            echo '.';
        }
    }

    /**
     * @param  Eloquent  $model
     */
    public function updated($model): void
    {
        d_echo('Updated data:', 'U');
        d_echo($model->getDirty());
    }

    /**
     * @param  Eloquent  $model
     */
    public function restored($model): void
    {
        d_echo('Restored data:', 'R');
        d_echo($model->getDirty());
    }

    /**
     * @param  Eloquent  $model
     */
    public function created($model): void
    {
        echo '+';
    }

    /**
     * @param  Eloquent  $model
     */
    public function deleted($model): void
    {
        echo '-';
    }
}
