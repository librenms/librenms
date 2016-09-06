<?php
/**
 * DiscoveryTest.php
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

include 'tests/mocks/mock.snmp.inc.php';

class DiscoveryTest extends \PHPUnit_Framework_TestCase
{
    public function testAiros()
    {
        $this->checkOS('airos', 'Linux', '.1.3.6.1.4.1.10002.1');
        $this->checkOS('airos', 'Linux', '.1.3.6.1.4.1.41112.1.4');

        $mockSnmp = array(
            'dot11manufacturerName.5' => 'Ubiquiti',
        );
        $this->checkOS('airos', 'Linux', '', $mockSnmp);
    }

    /**
     * Set up variables and include os discovery
     *
     * @param string $expectedOS the OS to test for
     * @param string $sysDescr set the snmp sysDescr variable
     * @param string $sysObjectId set the snmp sysObjectId variable
     * @param array $snmpMock set arbitrary snmp variables with an associative array
     * @param array $device device array to send
     */
    private function checkOS($expectedOS, $sysDescr = '', $sysObjectId = '', $mockSnmp = array(), $device = array())
    {
        global $config;
        setSnmpMock($mockSnmp);
        $os = null;

        // cannot use getHostOS() because of functions.php includes
        $pattern = $config['install_dir'] . '/includes/discovery/os/*.inc.php';
        foreach (glob($pattern) as $file) {
            include $file;
            if (isset($os)) {
                break;
            }
        }

        $this->assertEquals($expectedOS, $os);
    }

    public function testAirosAf()
    {
        $mockSnmp = array(
            'fwVersion.1' => '1.0',
        );
        $this->checkOS('airos-af', 'Linux', '.1.3.6.1.4.1.10002.1', $mockSnmp);
    }

    public function testCiscosmblinux()
    {
        $this->checkOS('ciscosmblinux', 'Linux Cisco Small Business');
    }

    public function testCumulus()
    {
        $this->checkOS('cumulus', 'Linux', '.1.3.6.1.4.1.40310');
    }

    public function testDdnos()
    {
        $mockSnmp = array(
            'SFA-INFO::systemName.0' => 1,
        );
        $this->checkOS('ddnos', 'Linux', '', $mockSnmp);
    }

    public function testDsm()
    {
        $mockSnmp = array(
            'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0' => 'syno_hw_version',
        );
        $this->checkOS('dsm', 'Linux', '', $mockSnmp);
    }

    public function testEndian()
    {
        $this->checkOS('endian', 'Linux endian');
    }

    public function testLinux()
    {
        $this->checkOS('linux', 'Linux');
    }

    public function testNetbotz()
    {
        $this->checkOS('netbotz', 'Linux', '.1.3.6.1.4.1.5528.100.20.10.2014');
        $this->checkOS('netbotz', 'Linux', '.1.3.6.1.4.1.5528.100.20.10.2016');
    }

    public function testPcoweb()
    {
        $mockSnmp = array(
            'roomTemp.0' => 1,
        );
        $this->checkOS('pcoweb', 'Linux', '', $mockSnmp);
    }

    public function testPktj()
    {
        $mockSnmp = array(
            'GANDI-MIB::rxCounter.0' => 1,
        );
        $this->checkOS('pktj', 'Linux', '', $mockSnmp);
    }

    public function testProcera()
    {
        $this->checkOS('procera', 'Linux', '.1.3.6.1.4.1.15397.2');
    }

    public function testQnap()
    {
        $mockSnmp = array(
            'ENTITY-MIB::entPhysicalMfgName.1' => 'QNAP',
        );
        $this->checkOS('qnap', 'Linux', '', $mockSnmp);
    }

    public function testSophos()
    {
        $this->checkOS('sophos', 'Linux g56fa85e');
        $this->checkOS('sophos', 'Linux gc80f187');
        $this->checkOS('sophos', 'Linux g829be90');
        $this->checkOS('sophos', 'Linux g63c0044');
    }

    public function testUnifi()
    {
        $mockSnmp = array(
            'dot11manufacturerProductName.6' => 'UAP',
        );
        $this->checkOS('unifi', 'Linux', '.1.3.6.1.4.1.10002.1', $mockSnmp);

        $mockSnmp = array(
            'dot11manufacturerProductName.4' => 'UAP-PRO',
        );
        $this->checkOS('unifi', 'Linux', '.1.3.6.1.4.1.10002.1', $mockSnmp);

        $mockSnmp = array(
            'dot11manufacturerProductName.0' => 'UAP-AC2',
        );
        $this->checkOS('unifi', 'Linux', '.1.3.6.1.4.1.10002.1', $mockSnmp);
    }
}
