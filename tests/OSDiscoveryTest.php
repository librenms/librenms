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
    /**
     * Set up variables and include os discovery
     *
     * @param string $expectedOS the OS to test for
     * @param string $sysDescr set the snmp sysDescr variable
     * @param string $sysObjectId set the snmp sysObjectId variable
     * @param array $mockSnmp set arbitrary snmp variables with an associative array
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

    public function test3com()
    {
        $this->checkOS('3com', '3Com Switch 4500G 24-Port PWR Software Version 3Com OS V5.02.00s168p12');
        $this->checkOS('3com', '3Com SuperStack 3 Switch 4500 26-Port Software Version 3Com OS V3.02.00s56');
        $this->checkOS('3com', '3Com Baseline Switch 2916-SFP Plus');
    }

    public function testAcano()
    {
        $this->checkOS('acano', 'Acano', '.1.3.6.1.4.1.8072.3.2.10');
    }

    public function testAcs()
    {
        $this->checkOS('acs', 'Cisco Secure Access Control System ', '.1.3.6.1.4.1.9.1.1117');
    }

    public function testAcsw()
    {
        $this->checkOS('acsw', 'Cisco Application Control Software');
        $this->checkOS('acsw', 'Application Control Engine');
        $this->checkOS('acsw', 'Cisco ACE', '.1.3.6.1.4.1.9.1.1291');
    }

    public function testAdtranAos()
    {
        $this->checkOS('adtran-aos', 'NetVanta');
        $this->checkOS('adtran-aos', 'Something that we do not have', '.1.3.6.1.4.1.664');
    }

    public function testAen()
    {
        $this->checkOS('aen', 'AMN-');
    }

    public function testAerohive()
    {
        $this->checkOS('aerohive', 'HiveOS');
    }

    public function testAirport()
    {
        $this->checkOS('airport', 'Apple AirPort');
        $this->checkOS('airport', 'Apple Base Station');
        $this->checkOS('airport', 'Base Station V3.84');
    }

    public function testAiros()
    {
        $this->checkOS('airos', 'Linux', '.1.3.6.1.4.1.10002.1');
        $this->checkOS('airos', 'Linux', '.1.3.6.1.4.1.41112.1.4');

        $mockSnmp = array(
            'dot11manufacturerName.5' => 'Ubiquiti',
        );
        $this->checkOS('airos', 'Linux', '', $mockSnmp);
    }

    public function testAirosAf()
    {
        $mockSnmp = array(
            'fwVersion.1' => '1.0',
        );
        $this->checkOS('airos-af', 'Linux', '.1.3.6.1.4.1.10002.1', $mockSnmp);
    }

    public function testAkcp()
    {
        $this->checkOS('akcp', 'SensorProbe');
    }

    public function testAos()
    {
        $this->checkOS('aos', 'AOS-W', '.1.3.6.1.4.1.6486.801');
        $this->checkOS('aos', 'Alcatel-Lucent OS6850-U24X 6.4.3.520.R01 GA, April 08, 2010', '.1.3.6.1.4.1.6486.801');
    }

    public function testAllied()
    {
        $this->checkOS('allied', 'AT-GS950/24', '.1.3.6.1.4.1.207.1');
    }

    public function testApc()
    {
        $this->checkOS('apc', 'APC Web/SNMP Management Card (MB:v3.9.2 PF:v3.5.9 PN:apc_hw03_aos_359.bin AF1:v3.5.6 AN1:apc_hw03_nb200_356.bin MN:NBRK0200 HR:05 SN: FFFFFFFFFFFF MD:07/07/2012)', '.1.3.6.1.4.1.318.1.3.8.4');
        $this->checkOS('apc', 'APC Switched Rack PDU');
        $this->checkOS('apc', 'APC MasterSwitch PDU');
        $this->checkOS('apc', 'APC Metered Rack PDU');
    }

    public function testAreca()
    {
        $this->checkOS('areca', 'Raid Subsystem V');
    }

    public function testAristaEos()
    {
        $this->checkOS('arista_eos', 'Arista Networks EOS');
    }

    public function testArubaos()
    {
        $this->checkOS('arubaos', 'ArubaOS');
    }

    public function testAsa()
    {
        $this->checkOS('asa', 'Cisco Adaptive Security Appliance');
    }

    public function testAvayaers()
    {
        $this->checkOS('avaya-ers', 'Ethernet Routing Switch');
        $this->checkOS('avaya-ers', 'ERS-');
    }

    public function testAvayaipo()
    {
        $mockSnmp = array(
            'ENTITY-MIB::entPhysicalDescr.1' => 'Avaya IP Office',
        );
        $this->checkOS('avaya-ipo', 'Avaya IP Office', '', $mockSnmp);
    }

    public function testAvayavsp()
    {
        $this->checkOS('avaya-vsp', 'VSP-4850GTS', '.1.3.6.1.4.1.2272.202');
        $this->checkOS('avaya-vsp', 'VSP-4850GTS-PWR+', '.1.3.6.1.4.1.2272.203');
        $this->checkOS('avaya-vsp', 'VSP-8284XSQ', '.1.3.6.1.4.1.2272.205');
        $this->checkOS('avaya-vsp', 'VSP-4450GSX-PWR+', '.1.3.6.1.4.1.2272.206');
        $this->checkOS('avaya-vsp', 'VSP-8404', '.1.3.6.1.4.1.2272.208');
        $this->checkOS('avaya-vsp', 'VSP-7254XSQ', '.1.3.6.1.4.1.2272.209');
        $this->checkOS('avaya-vsp', 'VSP-7254XTQ', '.1.3.6.1.4.1.2272.210');
    }

    public function testAvocent()
    {
        $this->checkOS('avocent', 'Avocent');
        $this->checkOS('avocent', 'AlterPath');
    }

    public function testAvtech()
    {
        $this->checkOS('avtech', 'Something that we do not have', '.1.3.6.1.4.1.20916.1.');
    }

    public function testAxiscam()
    {
        $this->checkOS('axiscam', ' ; AXIS 221; Network Camera; 4.30; Nov 29 2005 11:18; 141; 1;');
        $this->checkOS('axiscam', ' ; AXIS M7011; Network Video Encoder; 5.75.1; Mar 04 2015 10:10; 1FC; 1;');
    }

    public function testAxisdocserver()
    {
        $this->checkOS('axisdocserver', 'AXIS 1234 Network Document Server');
    }

    public function testBarracudaloadbalancer()
    {
        $this->checkOS('barracudaloadbalancer', 'Barracuda Load Balancer');
        $this->checkOS('barracudaloadbalancer', 'Barracuda Load Balancer ADC');
    }

    public function testBarracudaspamfirewall()
    {
        $this->checkOS('barracudaspamfirewall', 'Barracuda Spam Firewall');
    }

    public function testBarracudangfirewall()
    {
        $this->checkOS('barracudangfirewall', 'Barracuda Firewall');
    }

    public function testBcm963()
    {
        $this->checkOS('bcm963', 'bcm963');
    }

    public function testBdcom()
    {
        $this->checkOS('bdcom', 'BDCOM(tm) S2524C Software, Version 2.1.0A Build 5721', '.1.3.6.1.4.1.3320.1');
    }

    public function testBinos()
    {
        $this->checkOS('binos', 'Something that we do not have', '.1.3.6.1.4.1.738.1.5.100');
    }

    public function testBinox()
    {
        $this->checkOS('binox', 'Something that we do not have', '.1.3.6.1.4.1.738.10.5.100');
    }

    public function testBintecsmart()
    {
        $this->checkOS('bintec-smart', 'Something that we do not have', '.1.3.6.1.4.1.272.4.201.82.78.79.48');
    }

    public function testBnt()
    {
        $this->checkOS('bnt', 'Blade Network Technologies');
        $this->checkOS('bnt', 'BNT ');
    }

    public function testBrother()
    {
        $this->checkOS('brother', 'Brother NC-8300h, Firmware Ver.1.14  (14.11.06),MID 8C5-F01,FID 2');
    }

    public function testBuffalo()
    {
        $this->checkOS('buffalo', 'BUFFALO TeraStation TS5400R Ver.3.00 (2015/11/20 18:27:09)');
    }

    public function testCalix()
    {
        $this->checkOS('calix', 'Something that we do not have', '.1.3.6.1.4.1.6321.1.2.2.5.3');
        $this->checkOS('calix', 'Something that we do not have', '.1.3.6.1.4.1.6066.1.44');
        $this->checkOS('calix', 'Something that we do not have', '.1.3.6.1.4.1.6321.1.2.3');
    }

    public function testCambium()
    {
        $this->checkOS('cambium', 'Cambium PTP 50650');
        $this->checkOS('cambium', 'Cambium PTP250');
        $this->checkOS('cambium', 'Cambium PTP');
        $this->checkOS('cambium', 'Something that we do not have', '.1.3.6.1.4.1.17713.21');
        $this->checkOS('cambium', 'Something that we do not have', 'enterprises.17713.21');
    }

    public function testCanonprinter()
    {
        $this->checkOS('canonprinter', 'Canon MF');
        $this->checkOS('canonprinter', 'Canon iR-ADV');
    }

    public function testCanopy()
    {
        $this->checkOS('canopy', 'CANOPY');
        $this->checkOS('canopy', 'CMM');
    }

    public function testCat1900()
    {
        $this->checkOS('cat1900', 'Cisco Systems Catalyst 1900');
    }

    public function testCatos()
    {
        $this->checkOS('catos', 'Cisco Catalyst Operating System Software');
    }

    public function testCimc()
    {
        $this->checkOS('cimc', 'Cisco Integrated Management Controller');
    }

    public function testCiscosb()
    {
        $this->checkOS('ciscosb', 'Something that we do not have', '.1.3.6.1.4.1.9.6.1.80');
        $this->checkOS('ciscosb', 'Something that we do not have', '.1.3.6.1.4.1.9.6.1.81');
        $this->checkOS('ciscosb', 'Something that we do not have', '.1.3.6.1.4.1.9.6.1.82');
        $this->checkOS('ciscosb', 'Something that we do not have', '.1.3.6.1.4.1.9.6.1.83');
        $this->checkOS('ciscosb', 'Something that we do not have', '.1.3.6.1.4.1.9.6.1.85');
        $this->checkOS('ciscosb', 'Something that we do not have', '.1.3.6.1.4.1.9.6.1.88');
        $this->checkOS('ciscosb', 'Something that we do not have', '.1.3.6.1.4.1.9.6.1.89');
    }

    public function testCiscosmblinux()
    {
        $this->checkOS('ciscosmblinux', 'Linux Cisco Small Business');
    }

    public function testCiscowap()
    {
        $this->checkOS('ciscowap', 'Cisco Small Business WAP');
    }

    public function testCiscowlc()
    {
        $this->checkOS('ciscowlc', 'Cisco Controller');
    }

    public function testCometsystemp85xx()
    {
        $mockSnmp = array(
            '.1.3.6.1.4.1.22626.1.5.2.1.3.0' => 1,
        );
        $this->checkOS('cometsystem-p85xx', ' Firmware Version 10-11-12 ', '', $mockSnmp);
    }

    public function testComware()
    {
        $this->checkOS('comware', 'Comware');
        $this->checkOS('comware', 'HP C1234 Switch Software Version');
        $this->checkOS('comware', 'Something that we do not have', '.1.3.6.1.4.1.25506.11.1');
    }

    public function testCucm()
    {
        $this->checkOS('cucm', 'Something that we do not have', '.1.3.6.1.4.1.9.1.1348');
    }

    public function testCumulus()
    {
        $this->checkOS('cumulus', 'Linux', '.1.3.6.1.4.1.40310');
    }

    public function testDatacom()
    {
        $this->checkOS('datacom', 'Something that we do not have', '.1.3.6.1.4.1.3709');
    }

    public function testDatadomain()
    {
        $this->checkOS('datadomain', 'Something that we do not have', '.1.3.6.1.4.1.19746.3.1');
    }

    public function testDdnos()
    {
        $mockSnmp = array(
            'SFA-INFO::systemName.0' => 1,
        );
        $this->checkOS('ddnos', 'Linux', '', $mockSnmp);
    }

    public function testDeliberant()
    {
        $this->checkOS('deliberant', 'Deliberant');
    }

    public function testDelllaser()
    {
        $this->checkOS('dell-laser', 'Dell Color Laser');
        $this->checkOS('dell-laser', 'Dell Laser Printer');
        $this->checkOS('dell-laser', 'Dell something MFP');
    }

    public function testDeltaups()
    {
        $this->checkOS('deltaups', 'Something that we do not have', '.1.3.6.1.4.1.2254.2.4');
    }

    public function testDevelopprinter()
    {
        $this->checkOS('developprinter', 'Something that we do not have', '.1.3.6.1.4.1.18334.1.2.1.2.1.50.2.2');
    }

    public function testDlinkap()
    {
        $this->checkOS('dlinkap', 'D-Link Something AP');
        $this->checkOS('dlinkap', 'D-Link DAP-');
        $this->checkOS('dlinkap', 'D-Link Access Point');
    }

    public function testDlink()
    {
        $this->checkOS('dlink', 'D-Link DES-');
        $this->checkOS('dlink', 'Dlink DES-');
        $this->checkOS('dlink', 'DES-');
        $this->checkOS('dlink', 'DGS-');
    }

    public function testDnos()
    {
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.6027.1.');
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.674.10895.3042');
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.674.10895.3044');
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.674.10895.3054');
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.674.10895.3055');
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.674.10895.3056');
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.674.10895.3046');
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.674.10895.3057');
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.674.10895.3058');
        $this->checkOS('dnos', 'Something that we do not have', '.1.3.6.1.4.1.674.10895.3060');
    }

    public function testDrac()
    {
        $this->checkOS('drac', 'Dell Out-of-band SNMP Agent for Remote Access Controller');
        $this->checkOS('drac', 'Something that we do not have', '.1.3.6.1.4.1.674.10892.2');
        $this->checkOS('drac', 'Something that we do not have', '.1.3.6.1.4.1.674.10892.5');
    }

    public function testDsm()
    {
        $mockSnmp = array(
            'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0' => 'syno_hw_version',
        );
        $this->checkOS('dsm', 'Linux', '', $mockSnmp);
    }

    public function testEatonpdu()
    {
        $this->checkOS('eatonpdu', 'Something that we do not have', '.1.3.6.1.4.1.534.6.6.7');
    }

    public function testEatonups()
    {
        $this->checkOS('eatonups', 'Eaton 5P 2200');
        $this->checkOS('eatonups', 'Eaton 5PX 2000');
    }

    public function testEdgeos()
    {
        $this->checkOS('edgeos', 'EdgeOS');
        $this->checkOS('edgeos', 'EdgeRouter Lite');
    }

    public function testEdgeswitch()
    {
        $this->checkOS('edgeswitch', 'Something that we do not have', '.1.3.6.1.4.1.4413');
    }

    public function testEndian()
    {
        $this->checkOS('endian', 'Linux endian');
    }

    public function testEngenius()
    {
        $mockSnmp = array(
            'SNMPv2-SMI::enterprises.14125.2.1.1.6.0' => 'something',
        );
        $this->checkOS('engenius', 'Something that we do not have', '.1.3.6.1.4.1.14125.100.1.3');
        $this->checkOS('engenius', 'Something that we do not have', '.1.3.6.1.4.1.14125.101.1.3');
        $this->checkOS('engenius', 'Wireless Access Point', '', $mockSnmp);
    }

    public function testEnterasys()
    {
        $this->checkOS('enterasys', 'Enterasys Networks');
        $this->checkOS('enterasys', 'Something that we do not have', '.1.3.6.1.4.1.5624.2.1');
    }

    public function testEpson()
    {
        $this->checkOS('epson', 'EPSON Built-in');
    }

    public function testEquallogic()
    {
        $this->checkOS('equallogic', 'Something that we do not have', '.1.3.6.1.4.1.12740.17.1');
    }

    public function testExtremeware()
    {
        $this->checkOS('extremeware', 'Something that we do not have', '.1.3.6.1.4.1.1916.2');
    }

    public function testF5()
    {
        $this->checkOS('f5', 'Linux', '.1.3.6.1.4.1.3375.2.1.3.4.1000');
    }

    public function testFabos()
    {
        $this->checkOS('fabos', 'Something that we do not have', '.1.3.6.1.4.1.1588.2.1.1.1');
        $this->checkOS('fabos', 'Something that we do not have', '.1.3.6.1.4.1.1588.2.1.1.43');
        $this->checkOS('fabos', 'Something that we do not have', '.1.3.6.1.4.1.1588.2.1.1.72');
    }

    public function testFiberhome()
    {
        // FIXME Should actually be OLT AN5516-01 but discovery is wrong
        $this->checkOS('fiberhome', 'AN5516-06');
        $this->checkOS('fiberhome', 'AN5516-01');
    }

    public function testFireware()
    {
        $this->checkOS('fireware', 'XTM Watchguard ');
        $this->checkOS('fireware', 'FBX Watchguard');
    }

    public function testFlareos()
    {
        $this->checkOS('flareos', 'Something that we do not have', '.1.3.6.1.4.1.1981.1.1');
    }

    public function testFortigate()
    {
        $this->checkOS('fortigate', 'Something that we do not have', '.1.3.6.1.4.1.12356.15');
        $this->checkOS('fortigate', 'Something that we do not have', '.1.3.6.1.4.1.12356.101.1');
    }

    public function testFortios()
    {
        $this->checkOS('fortios', 'Something that we do not have', '.1.3.6.1.4.1.12356.103');
    }

    public function testFoundryos()
    {
        $this->checkOS('foundryos', 'Foundry Networks');
    }

    public function testFreebsd()
    {
        $this->checkOS('freebsd', 'FreeBSD');
    }

    public function testFtos()
    {
        $this->checkOS('ftos', 'Force10 Operating System');
    }

    public function testFujitsupyos()
    {
        $this->checkOS('fujitsupyos', 'Fujitsu PY CB Eth Switch');
    }

    public function testFxos()
    {
        $this->checkOS('fxos', 'Cisco FX-OS');
    }

    public function testGaia()
    {
        $this->checkOS('gaia', 'Something that we do not have', '.1.3.6.1.4.1.2620.1.6.123.1.49');
    }

    public function testGamatronicups()
    {
        $mockSnmp = array(
            'GAMATRONIC-MIB::psUnitManufacture.0' => 'Gamatronic',
        );
        $this->checkOS('gamatronicups', '', '', $mockSnmp);
    }

    public function testHikvision()
    {
        $mockSnmp = array(
            '.1.3.6.1.4.1.39165.1.6.0' => 'Hikvision',
        );
        $this->checkOS('hikvision', 'Something that we do not have', '', $mockSnmp);
    }

    public function testHp3par()
    {
        $this->checkOS('informos', 'Something that we do not have', '.1.3.6.1.4.1.12925.1');
    }

    public function testHpblmos()
    {
        $this->checkOS('hpblmos', 'Something that we do not have', '.1.3.6.1.4.1.11.5.7.1.2');
    }

    public function testHpmsm()
    {
        $this->checkOS('hpmsm', 'Something that we do not have', '.1.3.6.1.4.1.8744.1');
    }

    public function testHpvc()
    {
        $this->checkOS('hpvc', 'Something that we do not have', '.1.3.6.1.4.1.11.5.7.5.1');
    }

    public function testHuaweiups()
    {
        $mockSnmp = array(
            'UPS-MIB::upsIdentManufacturer.0' => 'HUAWEI',
        );
        $this->checkOS('huaweiups', 'Linux GSE200M', '', $mockSnmp);
    }

    public function testHwgposeidon()
    {
        $this->checkOS('hwg-poseidon', 'Something that we do not have', '.1.3.6.1.4.1.21796.3.3');
    }

    public function testHwgste2()
    {
        $this->checkOS('hwg-ste2', 'Something that we do not have', '.1.3.6.1.4.1.21796.4.9');
    }

    public function testHwgste()
    {
        $this->checkOS('hwg-ste', 'Something that we do not have', '.1.3.6.1.4.1.21796.4.1');
    }

    public function testHytera()
    {
        $this->checkOS('hytera', 'Something that we do not have', '.1.3.6.1.4.1.26381');
    }

    public function testIbmamm()
    {
        $this->checkOS('ibm-amm', 'BladeCenter Advanced Management Module');
    }

    public function testIbmimm()
    {
        $this->checkOS('ibm-imm', 'Something that we do not have', '.1.3.6.1.4.1.2.3.51.3');
    }

    public function testIbmnos()
    {
        $this->checkOS('ibmnos', 'IBM Networking Operating System');
        $this->checkOS('ibmnos', 'IBM Flex System Fabric');
        $this->checkOS('ibmnos', 'IBM Networking OS');
    }

    public function testIbmtl()
    {
        $mockSnmp = array(
            'SML-MIB::product-Name.0' => 'IBM System Storage TS3500 Tape Library',
        );
        $this->checkOS('ibmtl', 'Something that we do not have', '', $mockSnmp);
    }

    public function testIes()
    {
        $this->checkOS('ies', 'IES-');
    }

    public function testInfinity()
    {
        $this->checkOS('infinity', 'NFT 2N');
    }

    public function testIos()
    {
        $this->checkOS('ios', 'Cisco Internetwork Operating System Software IOS (tm) s72033_rp Software (s72033_rp-PS-M), Version 12.2(18)SXD7, RELEASE SOFTWARE (fc1) Technical Support: http://www.cisco.com/techsupport Copyright (c) 1986-2005 by cisco Systems, Inc. Compiled Tue 13');
        $this->checkOS('ios', 'IOS (tm)');
        $this->checkOS('ios', 'Cisco IOS Software, 3800 Software (C3825-ADVIPSERVICESK9-M), Version 12.4(22)T5, RELEASE SOFTWARE (fc3) Technical Support: http://www.cisco.com/techsupport Copyright (c) 1986-2010 by Cisco Systems, Inc. Compiled Wed 28-Apr-10 11:30 by prod_rel_team');
        $this->checkOS('ios', 'Global Site Selector');
    }

    public function testIosxe()
    {
        $this->checkOS('iosxe', 'Cisco IOS Software, IOS-XE Software (PPC_LINUX_IOSD-ADVENTERPRISEK9-M), Version 15.1(3)S, RELEASE SOFTWARE (fc1) Technical Support: http://www.cisco.com/techsupport Copyright (c) 1986-2011 by Cisco Systems, Inc. Compiled Thu 21-Jul-11 21:59 by mcpre');
    }

    public function testIosxr()
    {
        $this->checkOS('iosxr', 'IOS XR');
    }

    public function testIpoman()
    {
        $this->checkOS('ipoman', 'Something that we do not have', '.1.3.6.1.4.1.2468.1.4.2.1');
    }

    public function testIronware()
    {
        $this->checkOS('ironware', 'IronWare');
    }

    public function testIse()
    {
        $this->checkOS('ise', 'Something that we do not have', '.1.3.6.1.4.1.9.1.2139');
        $this->checkOS('ise', 'Something that we do not have', '.1.3.6.1.4.1.9.1.1426');
    }

    public function testJetdirect()
    {
        $this->checkOS('jetdirect', 'JETDIRECT');
        $this->checkOS('jetdirect', 'HP ETHERNET MULTI-ENVIRONMENT');
        $this->checkOS('jetdirect', 'Something that we do not have', '.1.3.6.1.4.1.11.1');
    }

    public function testJuniperex2500os()
    {
        $this->checkOS('juniperex2500os', 'Something that we do not have', '.1.3.6.1.4.1.1411.102');
    }

    public function testJunose()
    {
        $this->checkOS('junose', 'Something that we do not have', '.1.3.6.1.4.1.4874');
    }

    public function testJunos()
    {
        $this->checkOS('junos', 'Something that we do not have', '.1.3.6.1.4.1.2636');
        $this->checkOS('junos', 'kernel JUNOS');
    }

    public function testJwos()
    {
        $this->checkOS('jwos', 'Something that we do not have', '.1.3.6.1.4.1.8239.1.2.9');
    }

    public function testKonica()
    {
        $this->checkOS('konica', 'KONICA MINOLTA ');
    }

    public function testKyocera()
    {
        $this->checkOS('kyocera', 'KYOCERA ');
    }

    public function testLanier()
    {
        $this->checkOS('lanier', 'LANIER ');
    }

    public function testLantronixslc()
    {
        $this->checkOS('lantronix-slc', 'Something that we do not have', '.1.3.6.1.4.1.244.1.1');
    }

    public function testLenovoemc()
    {
        $this->checkOS('lenovoemc', 'EMC SOHO-NAS Storage.');
    }

    public function testLexmarkprinter()
    {
        $this->checkOS('lexmarkprinter', 'Lexmark ');
    }

    public function testLiebert()
    {
        $this->checkOS('liebert', 'Something that we do not have', '.1.3.6.1.4.1.476.1.42');
    }

    public function testLigoos()
    {
        $this->checkOS('ligoos', 'LigoPTP');
    }

    public function testLinux()
    {
        $this->checkOS('linux', 'Linux');
    }

    public function testMacosx()
    {
        $this->checkOS('macosx', 'Darwin Kernel Version 15', '.1.3.6.1.4.1.9999999.3.2.16');
    }

    public function testMaipu()
    {
        $this->checkOS('mypoweros', 'Something that we do not have', '.1.3.6.1.4.1.5651.1.102.21');
    }

    public function testMellanox()
    {
        $this->checkOS('mellanox', 'Something that we do not have', '.1.3.6.1.4.1.33049.1.1.1.');
    }

    public function testMerakimr()
    {
        $this->checkOS('merakimr', 'Meraki MR');
    }

    public function testMerakims()
    {
        $this->checkOS('merakims', 'Meraki MS');
    }

    public function testMerakimx()
    {
        $this->checkOS('merakimx', 'Meraki MX');
    }

    public function testMgepdu()
    {
        $this->checkOS('mgepdu', 'MGE Switched PDU');
    }

    public function testMgeups()
    {
        $this->checkOS('mgeups', 'Pulsar M');
        $this->checkOS('mgeups', 'Galaxy ');
        $this->checkOS('mgeups', 'Evolution ');
        $this->checkOS('mgeups', 'MGE UPS SYSTEMS - Network Management Proxy');
    }

    public function testMicrosemitime()
    {
        $this->checkOS('microsemitime', 'Something that we do not have', '.1.3.6.1.4.1.39165.1.6');
    }

    public function testMinkelsrms()
    {
        $this->checkOS('minkelsrms', '8VD-X20');
    }

    public function testMonowall()
    {
        $this->checkOS('monowall', 'm0n0wall');
    }

    public function testMrvld()
    {
        $this->checkOS('mrvld', 'LambdaDriver');
    }

    public function testMultimatic()
    {
        $mockSnmp = array(
            'UPS-MIB::upsIdentManufacturer.0' => 'Multimatic',
        );
        $this->checkOS('multimatic', 'CS121 ', '', $mockSnmp);

        $mockSnmp = array(
            'UPS-MIB::upsIdentManufacturer.0' => 'S2S',
        );
        $this->checkOS('multimatic', 'CS121', '', $mockSnmp);
    }

    public function testNetapp()
    {
        $this->checkOS('netapp', 'NetApp');
    }

    public function testNetbsd()
    {
        $this->checkOS('netbsd', 'NetBSD');
    }

    public function testNetbotz()
    {
        $this->checkOS('netbotz', 'Linux', '.1.3.6.1.4.1.5528.100.20.10.2014');
        $this->checkOS('netbotz', 'Linux', '.1.3.6.1.4.1.5528.100.20.10.2016');
    }

    public function testNetgear()
    {
        $this->checkOS('netgear', 'ProSafe');
        $this->checkOS('netgear', 'Something that we do not have', '.1.3.6.1.4.1.4526');
    }

    public function testNetmanplus()
    {
        $this->checkOS('netmanplus', 'NetMan something plus');
        $this->checkOS('netmanplus', 'Something that we do not have', '.1.3.6.1.4.1.5491.6');
    }

    public function testNetonix()
    {
        $this->checkOS('netonix', 'Something that we do not have', '.1.3.6.1.4.1.46242');
    }

    public function testNetopia()
    {
        $this->checkOS('netopia', 'Netopia ');
    }

    public function testNetscaler()
    {
        $this->checkOS('netscaler', 'Something that we do not have', '.1.3.6.1.4.1.5951.1');
    }

    public function testNetvision()
    {
        $this->checkOS('netvision', 'Net Vision');
    }

    public function testNetware()
    {
        $this->checkOS('netware', 'Novell NetWare');
    }

    public function testNimbleos()
    {
        $this->checkOS('nimbleos', 'Nimble Storage');
    }

    public function testNios()
    {
        $this->checkOS('nios', 'Linux 3.14.25 #1 SMP Thu Jun 16 18:19:37 EDT 2016 x86_64', '.1.3.6.1.4.1.7779.1.1402');
        $this->checkOS('nios', 'IPAM', '.1.3.6.1.4.1.7779.1.1004');
    }

    public function testNitro()
    {
        $this->checkOS('nitro', 'Something that we do not have', '.1.3.6.1.4.1.23128.1000.1.1');
        $this->checkOS('nitro', 'Something that we do not have', '.1.3.6.1.4.1.23128.1000.3.1');
        $this->checkOS('nitro', 'Something that we do not have', '.1.3.6.1.4.1.23128.1000.7.1');
        $this->checkOS('nitro', 'Something that we do not have', '.1.3.6.1.4.1.23128.1000.11.1');
    }

    public function testNos()
    {
        $this->checkOS('nos', 'Brocade VDX');
        $this->checkOS('nos', 'BR-VDX');
        $this->checkOS('nos', 'VDX67');
    }

    public function testNrg()
    {
        $this->checkOS('nrg', 'NRG Network Printer');
    }

    public function testNxos()
    {
        $this->checkOS('nxos', 'Cisco NX-OS(tm) n3000, Software (n3000-uk9), Version 6.0(2)U1(1a), RELEASE SOFTWARE Copyright (c) 2002-2012 by Cisco Systems, Inc. Device Manager Version nms.sro not found, Compiled 7/1/2013 22:00:00');
    }

    public function testOkilan()
    {
        $this->checkOS('okilan', 'OKI OkiLAN');
    }

    public function testOpensolaris()
    {
        $this->checkOS('opensolaris', 'SunOS Something 5.11');
    }

    public function testOnefs()
    {
        $this->checkOS('onefs', 'Something that we do not have', '.1.3.6.1.4.1.12124.1');
    }

    public function testOns()
    {
        $this->checkOS('ons', 'Cisco ONS');
    }

    public function testOpenbsd()
    {
        $this->checkOS('openbsd', 'Something that we do not have', '.1.3.6.1.4.1.30155.23.1');
        $this->checkOS('openbsd', 'OpenBSD');
    }

    public function testOracleilom()
    {
        $this->checkOS('oracle-ilom', 'Something that we do not have', '.1.3.6.1.4.1.42.2.200.2.1.1');
    }

    public function testPacketshaper()
    {
        $this->checkOS('packetshaper', 'PacketShaper');
    }

    public function testPanos()
    {
        $this->checkOS('panos', 'Palo Alto Networks');
    }

    public function testPapouchtme()
    {
        $this->checkOS('papouch-tme', 'SNMP TME');
        $this->checkOS('papouch-tme', 'TME');
    }

    public function testPbn()
    {
        $this->checkOS('pbn', 'Something that we do not have', '.1.3.6.1.4.1.11606');
    }

    public function testPcoweb()
    {
        $mockSnmp = array(
            'roomTemp.0' => 1,
        );
        $this->checkOS('pcoweb', 'Linux', '', $mockSnmp);
    }

    public function testPerle()
    {
        $this->checkOS('perle', 'Perle MCR-MGT');
    }

    public function testPfsense()
    {
        $this->checkOS('pfsense', 'pfSense');
    }

    public function testPix()
    {
        $this->checkOS('pixos', 'Cisco PIX');
    }

    public function testPktj()
    {
        $mockSnmp = array(
            'GANDI-MIB::rxCounter.0' => 1,
        );
        $this->checkOS('pktj', 'Linux', '', $mockSnmp);
    }

    public function testPlanetos()
    {
        $this->checkOS('planetos', 'Something that we do not have', '.1.3.6.1.4.1.10456.1.1516');
    }

    public function testPoweralert()
    {
        $this->checkOS('poweralert', 'POWERALERT');
    }

    public function testPowervault()
    {
        $this->checkOS('powervault', 'Something that we do not have', '.1.3.6.1.4.1.674.10893.2.102');
    }

    public function testPowerwalker()
    {
        $this->checkOS('powerwalker', 'Network Management Card for UPS', '.1.3.6.1.4.1.935.10');
    }

    public function testPowerware()
    {
        $this->checkOS('powerware', 'Something that we do not have', '.1.3.6.1.4.1.534');
    }

    public function testPrestige()
    {
        $this->checkOS('prestige', 'Prestige 100');
    }

    public function testPrimeinfrastructure()
    {
        $this->checkOS('primeinfrastructure', 'Something that we do not have', '.1.3.6.1.4.1.9.1.2307');
    }

    public function testProcera()
    {
        $this->checkOS('procera', 'Linux', '.1.3.6.1.4.1.15397.2');
    }

    public function testProcurve()
    {
        $this->checkOS('procurve', 'ProCurve');
        $this->checkOS('procurve', 'HP 1820');
        $this->checkOS('procurve', 'eCos-100');
        $this->checkOS('procurve', 'HP 2530 ');
        $this->checkOS('procurve', 'HP 5402R ');
    }

    public function testProxim()
    {
        $this->checkOS('proxim', 'Something that we do not have', '.1.3.6.1.4.1.11898.2.4.9');
    }

    public function testPulse()
    {
        $this->checkOS('pulse', 'Pulse Connect Secure');
    }

    public function testQnap()
    {
        $mockSnmp = array(
            'ENTITY-MIB::entPhysicalMfgName.1' => 'QNAP',
        );
        $this->checkOS('qnap', 'Linux', '', $mockSnmp);
    }

    public function testQuanta()
    {
        $this->checkOS('quanta', 'vxworks', '.1.3.6.1.4.1.4413');
        $this->checkOS('quanta', 'vxworks', '.1.3.6.1.4.1.7244');
        $this->checkOS('quanta', 'Quanta', '.1.3.6.1.4.1.4413');
        $this->checkOS('quanta', 'Quanta', '.1.3.6.1.4.1.7244');
    }

    public function testRadlan()
    {
        $this->checkOS('radlan', 'AT-8000');
    }

    public function testRaisecom()
    {
        $this->checkOS('raisecom', 'Something that we do not have', '.1.3.6.1.4.1.8886');
    }

    public function testRaritan()
    {
        $this->checkOS('raritan', 'Raritan');
        $this->checkOS('raritan', 'PX2');
    }

    public function testRedback()
    {
        $this->checkOS('redback', 'Redback');
    }

    public function testRicoh()
    {
        $this->checkOS('ricoh', 'RICOH Aficio');
        $this->checkOS('ricoh', 'RICOH Network Printer');
    }

    public function testRiverbed()
    {
        $this->checkOS('riverbed', 'Something that we do not have', '1.3.6.1.4.1.17163.1.1');
    }

    public function testRouteros()
    {
        $mockSnmp = array(
            'SNMPv2-SMI::enterprises.14988.1.1.4.3.0' => 1,
        );
        $this->checkOS('routeros', 'router', '', $mockSnmp);
        $this->checkOS('routeros', 'RouterOS RB2011UiAS');
    }

    public function testRuckuswireless()
    {
        $this->checkOS('ruckuswireless', 'Something that we do not have', '.1.3.6.1.4.1.25053.3.1');
    }

    public function testSaf()
    {
        $this->checkOS('saf', 'Something that we do not have', '.1.3.6.1.4.1.7571.100.1.1.5');
    }

    public function testSamsungprinter()
    {
        $this->checkOS('samsungprinter', 'Samsung CLX');
        $this->checkOS('samsungprinter', 'Samsung SCX');
        $this->checkOS('samsungprinter', 'Samsung C');
        $this->checkOS('samsungprinter', 'Samsung S');
    }

    public function testSanos()
    {
        $this->checkOS('sanos', 'SAN-OS');
    }

    public function testScreenos()
    {
        $this->checkOS('screenos', 'Something that we do not have', '.1.3.6.1.4.1.674.3224.1');
        $this->checkOS('screenos', 'Something that we do not have', '.1.3.6.1.4.1.3224');
    }

    public function testSentry3()
    {
        $mockSnmp = array(
            'Sentry3-MIB::serverTech.4.1.1.1.3.0' => '0 7',
        );
        $this->checkOS('sentry3', 'Sentry Switched ', '', $mockSnmp);
        $this->checkOS('sentry3', 'Sentry Smart ', '', $mockSnmp);
    }

    public function testSentry4()
    {
        $mockSnmp = array(
            'Sentry3-MIB::serverTech.4.1.1.1.3.0' => '0 8',
        );
        $this->checkOS('sentry4', 'Sentry Switched ', '', $mockSnmp);
        $this->checkOS('sentry4', 'Sentry Smart ', '', $mockSnmp);
    }

    public function testServeriron()
    {
        $this->checkOS('serveriron', 'ServerIron');
    }

    public function testSharp()
    {
        $this->checkOS('sharp', 'SHARP MX-2614N');
        $this->checkOS('sharp', 'SHARP MX-C301W');
        $this->checkOS('sharp', 'SHARP MX-3140N');
    }

    public function testSiklu()
    {
        $mockSnmp = array(
            'ENTITY-MIB::entPhysicalMfgName.1' => 'Siklu',
        );
        $this->checkOS('siklu', 'Something that we do not have', '', $mockSnmp);
    }

    public function testSmartax()
    {
        $this->checkOS('smartax', 'Huawei Integrated Access Software');
    }

    public function testSolaris()
    {
        $this->checkOS('solaris', 'SunOS Something 5.10');
        $this->checkOS('solaris', 'Something that we do not have', '.1.3.6.1.4.1.42.2.1.1');
    }

    public function testSonicwall()
    {
        $this->checkOS('sonicwall', 'SonicWALL');
    }

    public function testSonusgsx()
    {
        $this->checkOS('sonus-gsx', 'Something that we do not have', '.1.3.6.1.4.1.2879.1.1.2');
    }

    public function testSonussbc()
    {
        $this->checkOS('sonus-sbc', 'Something that we do not have', '.1.3.6.1.4.1.2879.1.9.2');
        $this->checkOS('sonus-sbc', 'Something that we do not have', '.1.3.6.1.4.1.177.15.1.1.1');
    }

    public function testSophos()
    {
        $this->checkOS('sophos', 'Linux g56fa85e');
        $this->checkOS('sophos', 'Linux gc80f187');
        $this->checkOS('sophos', 'Linux g829be90');
        $this->checkOS('sophos', 'Linux g63c0044');
    }

    public function testSpeedtouch()
    {
        $this->checkOS('speedtouch', 'TG585v7');
        $this->checkOS('speedtouch', 'SpeedTouch ');
        $this->checkOS('speedtouch', 'ST5000');
    }

    public function testSub10()
    {
        $this->checkOS('sub10', 'Something that we do not have', '.1.3.6.1.4.1.39003');
    }

    public function testSupermicroswitch()
    {
        $this->checkOS('supermicro-switch', 'Supermicro Switch');
        $this->checkOS('supermicro-switch', 'SSE-');
        $this->checkOS('supermicro-switch', 'SBM-');
    }

    public function testSwos()
    {
        $mockSnmp = array(
            'SNMPv2-MIB::sysName.0' => 'MikroTik'
        );
        $this->checkOS('swos', 'RB250GS', '', $mockSnmp);
        $this->checkOS('swos', 'RB260GS', '', $mockSnmp);
        $this->checkOS('swos', 'RB260GSP', '', $mockSnmp);
    }

    public function testSymbol()
    {
        $this->checkOS('symbol', 'Something that we do not have', '.1.3.6.1.4.1.388');
    }

    public function testTimos()
    {
        $this->checkOS('timos', 'Alcatel-Lucent OS6850-U24X 6.4.3.520.R01 GA, April 08, 2010', '.1.3.6.1.4.1.6527.1.3');
        $this->checkOS('timos', 'Alcatel-Lucent OS6850-U24X 6.4.3.520.R01 GA, April 08, 2010', '.1.3.6.1.4.1.6527.6.2.1.2.2.');
        $this->checkOS('timos', 'Alcatel-Lucent OS6850-U24X 6.4.3.520.R01 GA, April 08, 2010', '.1.3.6.1.4.1.6527.1.6.1');
        $this->checkOS('timos', 'Alcatel-Lucent OS6850-U24X 6.4.3.520.R01 GA, April 08, 2010', '.1.3.6.1.4.1.6527.6.1.1.2.');
        $this->checkOS('timos', 'Alcatel-Lucent OS6850-U24X 6.4.3.520.R01 GA, April 08, 2010', '.1.3.6.1.4.1.6527.1.9.1');
        $this->checkOS('timos', 'Alcatel-Lucent OS6850-U24X 6.4.3.520.R01 GA, April 08, 2010', '.1.3.6.1.4.1.6527.1.15.');
    }

    public function testTpconductor()
    {
        $this->checkOS('tpconductor', 'Something that we do not have', '.1.3.6.1.4.1.5596.180.6.4.1');
    }

    public function testTplink()
    {
        $this->checkOS('tplink', 'Something that we do not have', '.1.3.6.1.4.1.11863.1.1');
    }

    public function testTranzeo()
    {
        $this->checkOS('tranzeo', 'Tranzeo');
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

    public function testVccodec()
    {
        $this->checkOS('vccodec', 'Something that we do not have', '.1.3.6.1.4.1.5596.150.6.4.1');
    }

    public function testVcs()
    {
        $this->checkOS('vcs', 'Something that we do not have', '.1.3.6.1.4.1.5596.130.6.4.1');
    }

    public function testViprinux()
    {
        $this->checkOS('viprinux', 'Viprinet VPN Router');
    }

    public function testVmware()
    {
        $this->checkOS('vmware', 'VMware ESX');
        $this->checkOS('vmware', 'VMware-vCenter-Server-Appliance');
    }

    public function testVoswall()
    {
        $this->checkOS('voswall', 'Voswall');
    }

    public function testVrp()
    {
        $this->checkOS('vrp', 'VRP (R) Software');
        $this->checkOS('vrp', 'VRP Software Version');
        $this->checkOS('vrp', 'Software Version VRP');
        $this->checkOS('vrp', 'Versatile Routing Platform Software');
    }

    public function testVyatta()
    {
        $this->checkOS('vyatta', 'Vyatta');
    }

    public function testVyos()
    {
        $this->checkOS('vyos', 'Vyatta VyOS');
        $this->checkOS('vyos', 'VyOS');
        $this->checkOS('vyos', 'vyos');
    }

    public function testWaas()
    {
        $this->checkOS('waas', 'Cisco Wide Area Application Services');
    }

    public function testWatchguard()
    {
        $this->checkOS('firebox', 'WatchGuard Fireware');
        $this->checkOS('firebox', 'Something that we do not have', '.1.3.6.1.4.1.3097.1.5');
    }

    public function testWebpower()
    {
        $this->checkOS('webpower', 'Something that we do not have', '.1.3.6.1.4.1.2468.1.2.1');
    }

    public function testWindows()
    {
        $this->checkOS('windows', 'Something that we do not have', '.1.3.6.1.4.1.311.1.1.3');
        $this->checkOS('windows', 'Hardware: Intel64 Family 6 Model 28 Stepping 10 AT/AT COMPATIBLE - Software: Windows Version 6.3 (Build 9600 Multiprocessor Free)');
    }

    public function testWxgoos()
    {
        $this->checkOS('wxgoos', 'NETOS 6.0', '.1.3.6.1.4.1.901.1');
        $this->checkOS('wxgoos', 'Something that we do not have', '.1.3.6.1.4.1.17373');
    }

    public function testXerox()
    {
        $this->checkOS('xerox', 'Xerox Phaser');
        $this->checkOS('xerox', 'Xerox WorkCentre');
        $this->checkOS('xerox', 'FUJI XEROX DocuPrint');
    }

    public function testXirrus()
    {
        $this->checkOS('xirrus_aos', 'Xirrus ArrayOS');
    }

    public function testXos()
    {
        $this->checkOS('xos', 'XOS');
    }

    public function testZxr10()
    {
        $this->checkOS('zxr10', 'ZTE Ethernet Switch  ZXR10 5250-52TM-H, Version: V2.05.11B23');
    }

    public function testZynos()
    {
        $this->checkOS('zynos', 'ES Something', '.1.3.6.1.4.1.890');
        $this->checkOS('zynos', 'GS Something', '.1.3.6.1.4.1.890');
    }

    public function testZywall()
    {
        $this->checkOS('zywall', 'ZyWALL 2X');
        $this->checkOS('zywall', 'ZyWALL 2X', '.1.3.6.1.4.1.890.1.15');
    }

    public function testZyxelnwa()
    {
        $this->checkOS('zyxelnwa', 'NWA-');
    }
}
