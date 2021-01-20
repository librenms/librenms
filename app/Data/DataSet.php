<?php
/*
 * DataSet.php
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

namespace App\Data;

use LibreNMS\Enum\DataRateType;
use LibreNMS\Enum\DataType;

class DataSet
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var int
     */
    public $rate;
    /**
     * @var int
     */
    private $type;
    /**
     * @var int
     */
    private $min;
    /**
     * @var int
     */
    private $max;
    private $oldDsFile;
    private $oldDsName;
    private $value;

    public function __construct(string $name, $rate = DataRateType::NONE, $type = DataType::INT, $min = null, $max = null)
    {
        $this->name = $name;
        $this->rate = $rate;
        $this->type = $type;
        $this->min = $min;
        $this->max = $max;
    }

    public function setValue($value)
    {
        $this->value = $this->type === DataType::INT ? (int) $value : (float) $value;
    }

    /**
     * Set this to allow data to be migrated from an old RRD file
     *
     * @param  string  $file
     * @param  string  $ds
     */
    public function migrateFrom(string $file, string $ds)
    {
        $this->oldDsFile = $file;
        $this->oldDsName = $ds;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRateType()
    {
        return $this->rate;
    }

    public function getOldRrdFile()
    {
        return $this->oldDsFile;
    }

    public function getOldDsName()
    {
        return $this->oldDsName;
    }

    public function getMin()
    {
        return $this->min;
    }

    public function getMax()
    {
        return $this->max;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getType()
    {
        return $this->type;
    }
}
