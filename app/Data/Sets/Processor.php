<?php
/*
 * Processor.php
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
use LibreNMS\Enum\DataRateType;
use LibreNMS\Enum\DataType;
use Rrd;

class Processor extends DataGroup
{
    public static function make(\App\Models\Processor $processor)
    {
        $dg = (new self('processor'))
            ->setTags([
                'device' => $processor->device_id,
                'index' => $processor->processor_index,
            ])
            ->setFields([
                'hostname' => $processor->device->hostname,
                'description' => $processor->processor_descr,
            ])
            ->addDataSet('usage', DataRateType::NONE, DataType::FLOAT, 0, 1);

        $dg->getDataSet('usage')->migrateFrom(Rrd::name($processor->device->hostname, ['processor', $processor->processor_type, $processor->processor_index]), 'usage');

        return $dg;
    }
}
