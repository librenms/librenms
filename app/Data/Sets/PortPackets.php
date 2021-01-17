<?php
/*
 * PortBits.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Data\Sets;

use App\Data\DataGroup;
use App\Facades\Rrd;
use App\Models\Port;
use LibreNMS\Enum\DataRateType;
use LibreNMS\Enum\DataType;

class PortPackets extends DataGroup
{
    public static function make(Port $port): DataGroup
    {
        $dg = (new self('port_packets'))
            ->setTags([
                'device' => $port->device_id,
                'port' => $port->port_id,
            ])
            ->setFields([
                'hostname' => $port->device->hostname,
                'ifName' => $port->ifName,
                'ifAlias' => $port->ifAlias,
                'ifIndex' => $port->ifIndex,
                'port_descr_type' => $port->port_descr_type,
            ])
            ->addDataSet('ifInOctets', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifOutOctets', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifInErrors', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifOutErrors', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifInUcastPkts', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifOutUcastPkts', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifInNUcastPkts', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifOutNUcastPkts', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifInDiscards', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifOutDiscards', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifInUnknownProtos', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifInBroadcastPkts', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifOutBroadcastPkts', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifInMulticastPkts', DataRateType::COUNTER, DataType::INT, 0)
            ->addDataSet('ifOutMulticastPkts', DataRateType::COUNTER, DataType::INT, 0);

        // enable migration
        $old_file = Rrd::name($port->device->hostname, Rrd::portName($port->port_id));
        $dg->getDataSet('ifInOctets')->migrateFrom($old_file, 'INOCTETS');
        $dg->getDataSet('ifOutOctets')->migrateFrom($old_file, 'OUTOCTETS');
        $dg->getDataSet('ifInErrors')->migrateFrom($old_file, 'INERRORS');
        $dg->getDataSet('ifOutErrors')->migrateFrom($old_file, 'OUTERRORS');
        $dg->getDataSet('ifInUcastPkts')->migrateFrom($old_file, 'INUCASTPKTS');
        $dg->getDataSet('ifOutUcastPkts')->migrateFrom($old_file, 'OUTUCASTPKTS');
        $dg->getDataSet('ifInNUcastPkts')->migrateFrom($old_file, 'INNUCASTPKTS');
        $dg->getDataSet('ifOutNUcastPkts')->migrateFrom($old_file, 'OUTNUCASTPKTS');
        $dg->getDataSet('ifInDiscards')->migrateFrom($old_file, 'INDISCARDS');
        $dg->getDataSet('ifOutDiscards')->migrateFrom($old_file, 'OUTDISCARDS');
        $dg->getDataSet('ifInUnknownProtos')->migrateFrom($old_file, 'INUNKNOWNPROTOS');
        $dg->getDataSet('ifInBroadcastPkts')->migrateFrom($old_file, 'INBROADCASTPKTS');
        $dg->getDataSet('ifOutBroadcastPkts')->migrateFrom($old_file, 'OUTBROADCASTPKTS');
        $dg->getDataSet('ifInMulticastPkts')->migrateFrom($old_file, 'INMULTICASTPKTS');
        $dg->getDataSet('ifOutMulticastPkts')->migrateFrom($old_file, 'OUTMULTICASTPKTS');

        return $dg;
    }
}
