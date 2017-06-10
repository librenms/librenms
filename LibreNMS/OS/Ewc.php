<?php
/**
 * Ewc.php
 *
 * Extreme Wireless Controller
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <n@laf.io>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCapacityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCcqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class Airos extends OS implements
    WirelessCapacityDiscovery,
    WirelessCcqDiscovery,
    WirelessClientsDiscovery,
    WirelessDistanceDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessQualityDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery
{

}
