<?php
/*
 * Fortinet.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Shared;

class Fortinet extends \LibreNMS\OS
{
    protected function getHardwareName()
    {
        $rewrite_fortinet_hardware = [
            '.1.3.6.1.4.1.12356.102.1.1000' => 'FortiAnalyzer 100',
            '.1.3.6.1.4.1.12356.102.1.10002' => 'FortiAnalyzer 1000B',
            '.1.3.6.1.4.1.12356.102.1.1001' => 'FortiAnalyzer 100A',
            '.1.3.6.1.4.1.12356.102.1.1002' => 'FortiAnalyzer 100B',
            '.1.3.6.1.4.1.12356.102.1.20000' => 'FortiAnalyzer 2000',
            '.1.3.6.1.4.1.12356.102.1.20001' => 'FortiAnalyzer 2000A',
            '.1.3.6.1.4.1.12356.102.1.4000' => 'FortiAnalyzer 400',
            '.1.3.6.1.4.1.12356.102.1.40000' => 'FortiAnalyzer 4000',
            '.1.3.6.1.4.1.12356.102.1.40001' => 'FortiAnalyzer 4000A',
            '.1.3.6.1.4.1.12356.102.1.4002' => 'FortiAnalyzer 400B',
            '.1.3.6.1.4.1.12356.102.1.8000' => 'FortiAnalyzer 800',
            '.1.3.6.1.4.1.12356.102.1.8002' => 'FortiAnalyzer 800B',
            '.1.3.6.1.4.1.12356.101.1.1000' => 'FortiGate 100',
            '.1.3.6.1.4.1.12356.101.1.10000' => 'FortiGate 1000',
            '.1.3.6.1.4.1.12356.101.1.10001' => 'FortiGate 1000A',
            '.1.3.6.1.4.1.12356.101.1.10002' => 'FortiGate 1000AFA2',
            '.1.3.6.1.4.1.12356.101.1.10003' => 'FortiGate 1000ALENC',
            '.1.3.6.1.4.1.12356.101.1.1001' => 'FortiGate 100A',
            '.1.3.6.1.4.1.12356.101.1.1002' => 'FortiGate 110C',
            '.1.3.6.1.4.1.12356.101.1.1003' => 'FortiGate 111C',
            '.1.3.6.1.4.1.12356.101.1.2000' => 'FortiGate 200',
            '.1.3.6.1.4.1.12356.101.1.20000' => 'FortiGate 2000',
            '.1.3.6.1.4.1.12356.101.1.2001' => 'FortiGate 200A',
            '.1.3.6.1.4.1.12356.101.1.2002' => 'FortiGate 224B',
            '.1.3.6.1.4.1.12356.101.1.2003' => 'FortiGate 200A',
            '.1.3.6.1.4.1.12356.101.1.3000' => 'FortiGate 300',
            '.1.3.6.1.4.1.12356.101.1.30000' => 'FortiGate 3000',
            '.1.3.6.1.4.1.12356.101.1.3001' => 'FortiGate 300A',
            '.1.3.6.1.4.1.12356.101.1.30160' => 'FortiGate 3016B',
            '.1.3.6.1.4.1.12356.101.1.302' => 'FortiGate 30B',
            '.1.3.6.1.4.1.12356.101.1.3002' => 'FortiGate 310B',
            '.1.3.6.1.4.1.12356.101.1.36000' => 'FortiGate 3600',
            '.1.3.6.1.4.1.12356.101.1.36003' => 'FortiGate 3600A',
            '.1.3.6.1.4.1.12356.101.1.38100' => 'FortiGate 3810A',
            '.1.3.6.1.4.1.12356.101.1.4000' => 'FortiGate 400',
            '.1.3.6.1.4.1.12356.101.1.40000' => 'FortiGate 4000',
            '.1.3.6.1.4.1.12356.101.1.4001' => 'FortiGate 400A',
            '.1.3.6.1.4.1.12356.101.1.5000' => 'FortiGate 500',
            '.1.3.6.1.4.1.12356.101.1.50000' => 'FortiGate 5000',
            '.1.3.6.1.4.1.12356.101.1.50010' => 'FortiGate 5001',
            '.1.3.6.1.4.1.12356.101.1.50011' => 'FortiGate 5001A',
            '.1.3.6.1.4.1.12356.101.1.50012' => 'FortiGate 5001FA2',
            '.1.3.6.1.4.1.12356.101.1.50021' => 'FortiGate 5002A',
            '.1.3.6.1.4.1.12356.101.1.50001' => 'FortiGate 5002FB2',
            '.1.3.6.1.4.1.12356.101.1.50040' => 'FortiGate 5004',
            '.1.3.6.1.4.1.12356.101.1.50050' => 'FortiGate 5005',
            '.1.3.6.1.4.1.12356.101.1.50051' => 'FortiGate 5005FA2',
            '.1.3.6.1.4.1.12356.101.1.5001' => 'FortiGate 500A',
            '.1.3.6.1.4.1.12356.101.1.500' => 'FortiGate 50A',
            '.1.3.6.1.4.1.12356.101.1.501' => 'FortiGate 50AM',
            '.1.3.6.1.4.1.12356.101.1.502' => 'FortiGate 50B',
            '.1.3.6.1.4.1.12356.101.1.504' => 'FortiGate 51B',
            '.1.3.6.1.4.1.12356.101.1.600' => 'FortiGate 60',
            '.1.3.6.1.4.1.12356.101.1.6201' => 'FortiGate 600D',
            '.1.3.6.1.4.1.12356.101.1.602' => 'FortiGate 60ADSL',
            '.1.3.6.1.4.1.12356.101.1.603' => 'FortiGate 60B',
            '.1.3.6.1.4.1.12356.101.1.601' => 'FortiGate 60M',
            '.1.3.6.1.4.1.12356.101.1.6200' => 'FortiGate 620B',
            '.1.3.6.1.4.1.12356.101.1.8000' => 'FortiGate 800',
            '.1.3.6.1.4.1.12356.101.1.8001' => 'FortiGate 800F',
            '.1.3.6.1.4.1.12356.101.1.800' => 'FortiGate 80C',
            '.1.3.6.1.4.1.12356.1688' => 'FortiMail 2000A',
            '.1.3.6.1.4.1.12356.103.1.1000' => 'FortiManager 100',
            '.1.3.6.1.4.1.12356.103.1.1001' => 'FortiManager VM',
            '.1.3.6.1.4.1.12356.103.1.1003' => 'FortiManager 100C',
            '.1.3.6.1.4.1.12356.103.1.2004' => 'FortiManager 200D',
            '.1.3.6.1.4.1.12356.103.1.2005' => 'FortiManager 200E',
            '.1.3.6.1.4.1.12356.103.1.3004' => 'FortiManager 300D',
            '.1.3.6.1.4.1.12356.103.1.3005' => 'FortiManager 300E',
            '.1.3.6.1.4.1.12356.103.1.4000' => 'FortiManager 400',
            '.1.3.6.1.4.1.12356.103.1.4001' => 'FortiManager 400A',
            '.1.3.6.1.4.1.12356.103.1.4002' => 'FortiManager 400B',
            '.1.3.6.1.4.1.12356.103.1.4003' => 'FortiManager 400C',
            '.1.3.6.1.4.1.12356.103.1.4005' => 'FortiManager 400E',
            '.1.3.6.1.4.1.12356.103.1.10003' => 'FortiManager 1000C',
            '.1.3.6.1.4.1.12356.103.1.10004' => 'FortiManager 1000D',
            '.1.3.6.1.4.1.12356.103.1.20005' => 'FortiManager 2000E',
            '.1.3.6.1.4.1.12356.103.1.20000' => 'FortiManager 2000XL',
            '.1.3.6.1.4.1.12356.103.1.30000' => 'FortiManager 3000',
            '.1.3.6.1.4.1.12356.103.1.30002' => 'FortiManager 3000B',
            '.1.3.6.1.4.1.12356.103.1.30003' => 'FortiManager 3000C',
            '.1.3.6.1.4.1.12356.103.1.30006' => 'FortiManager 3000F',
            '.1.3.6.1.4.1.12356.103.1.39005' => 'FortiManager 3900E',
            '.1.3.6.1.4.1.12356.103.1.40004' => 'FortiManager 4000D',
            '.1.3.6.1.4.1.12356.103.1.40005' => 'FortiManager 4000E',
            '.1.3.6.1.4.1.12356.103.1.50011' => 'FortiManager 5001A',
            '.1.3.6.1.4.1.12356.106.1.50030' => 'FortiSwitch 5003A',
            '.1.3.6.1.4.1.12356.101.1.510' => 'FortiWiFi 50B',
            '.1.3.6.1.4.1.12356.101.1.610' => 'FortiWiFi 60',
            '.1.3.6.1.4.1.12356.101.1.611' => 'FortiWiFi 60A',
            '.1.3.6.1.4.1.12356.101.1.612' => 'FortiWiFi 60AM',
            '.1.3.6.1.4.1.12356.101.1.613' => 'FortiWiFi 60B',
        ];

        return $rewrite_fortinet_hardware[$this->getDevice()->sysObjectID] ?? null;
    }
}
