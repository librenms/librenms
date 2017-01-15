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

class DiscoveryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up and test an os
     * If $filename is not set, it will use the snmprec file matching $expected_os
     *
     * @param string $expected_os The os we should get back from getHostOS()
     * @param string $filename the name of the snmprec file to use
     */
    private function checkOS($expected_os, $filename = null)
    {
        $community = $filename ?: $expected_os;

        ob_start();
        $os = getHostOS($this->genDevice($community));
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($expected_os, $os, "Test file: $community.snmprec\n$output");
    }

    /**
     * Generate a fake $device array
     *
     * @param string $community The snmp community to set
     * @return array resulting device array
     */
    private function genDevice($community)
    {
        return array(
            'device_id' => 1,
            'hostname' => '127.0.0.1',
            'snmpver' => 'v2c',
            'port' => 11161,
            'timeout' => 3,
            'retries' => 0,
            'snmp_max_repeaters' => 10,
            'community' => $community,
            'os' => 'generic',
            'os_group' => '',
        );
    }

    public function test3com()
    {
        $this->checkOS('3com');
        $this->checkOS('3com', '3com1');
        $this->checkOS('3com', '3com2');
    }

    public function testAcano()
    {
        $this->checkOS('acano');
    }

    public function testAcs()
    {
        $this->checkOS('acs');
    }

    public function testAcsw()
    {
        $this->checkOS('acsw');
        $this->checkOS('acsw', 'acsw1');
        $this->checkOS('acsw', 'acsw2');
    }

    public function testAdtranAos()
    {
        $this->checkOS('adtran-aos');
        $this->checkOS('adtran-aos', 'adtran-aos1');
    }

    public function testAen()
    {
        $this->checkOS('aen');
    }

    public function testAerohive()
    {
        $this->checkOS('aerohive');
    }

    public function testAirport()
    {
        $this->checkOS('airport');
        $this->checkOS('airport', 'airport1');
        $this->checkOS('airport', 'airport2');
    }

    public function testAiros()
    {
        $this->checkOS('airos');
        $this->checkOS('airos', 'airos1');
        $this->checkOS('airos', 'airos2');
    }

    public function testAirosAf()
    {
        $this->checkOS('airos-af');
    }

    public function testAkcp()
    {
        $this->checkOS('akcp');
    }

    public function testAos()
    {
        $this->checkOS('aos');
        $this->checkOS('aos', 'aos1');
    }

    public function testAllied()
    {
        $this->checkOS('allied');
    }

    public function testApc()
    {
        $this->checkOS('apc');
        $this->checkOS('apc', 'apc-switched-rack');
        $this->checkOS('apc', 'apc-masterswitch');
        $this->checkOS('apc', 'apc-metered-rack');
        $this->checkOS('apc', 'apc-embedded-powernet');
    }

    public function testApic()
    {
        $this->checkOS('apic');
    }

    public function testAreca()
    {
        $this->checkOS('areca');
    }

    public function testAristaEos()
    {
        $this->checkOS('arista_eos');
    }

    public function testArubaos()
    {
        $this->checkOS('arubaos');
    }

    public function testAsa()
    {
        $this->checkOS('asa');
    }

    public function testAsusMerlin()
    {
        $this->checkOS('asuswrt-merlin');
    }

    public function testAvayaers()
    {
        $this->checkOS('avaya-ers');
        $this->checkOS('avaya-ers', 'avaya-ers1');
    }

    public function testAvayaipo()
    {
        $this->checkOS('avaya-ipo');
    }

    public function testAvayavsp()
    {
        $this->checkOS('avaya-vsp', 'avaya-vsp-4850gts');
        $this->checkOS('avaya-vsp', 'avaya-vsp-4850gts-pwr');
        $this->checkOS('avaya-vsp', 'avaya-vsp-8284xsq');
        $this->checkOS('avaya-vsp', 'avaya-vsp-4450gsx-pwr');
        $this->checkOS('avaya-vsp', 'avaya-vsp-8404');
        $this->checkOS('avaya-vsp', 'avaya-vsp-7254xsq');
        $this->checkOS('avaya-vsp', 'avaya-vsp-7254xtq');
    }

    public function testAvocent()
    {
        $this->checkOS('avocent');
        $this->checkOS('avocent', 'avocent-alterpath');
    }

    public function testAvtech()
    {
        $this->checkOS('avtech', 'avtech-tempager4e');
    }

    public function testAxiscam()
    {
        $this->checkOS('axiscam');
        $this->checkOS('axiscam', 'axiscam-nve');
    }

    public function testAxisdocserver()
    {
        $this->checkOS('axisdocserver');
    }

    public function testBarracudaloadbalancer()
    {
        $this->checkOS('barracudaloadbalancer');
        $this->checkOS('barracudaloadbalancer', 'barracudaloadbalancer-adc');
    }

    public function testBarracudaspamfirewall()
    {
        $this->checkOS('barracudaspamfirewall');
    }

    public function testBarracudangfirewall()
    {
        $this->checkOS('barracudangfirewall');
    }

    public function testBcm963()
    {
        $this->checkOS('bcm963');
    }

    public function testBdcom()
    {
        $this->checkOS('bdcom');
    }

    public function testBinos()
    {
        $this->checkOS('binos');
    }

    public function testBinox()
    {
        $this->checkOS('binox');
    }

    public function testBintecsmart()
    {
        $this->checkOS('bintec-smart');
    }

    public function testBnt()
    {
        $this->checkOS('bnt');
        $this->checkOS('bnt', 'bnt1');
    }

    public function testBrother()
    {
        $this->checkOS('brother');
    }

    public function testBuffalo()
    {
        $this->checkOS('buffalo');
    }

    public function testCalix()
    {
        $this->checkOS('calix');
        $this->checkOS('calix', 'calix1');
        $this->checkOS('calix', 'calix-e7-2');
    }

    public function testCambium()
    {
        $this->checkOS('cambium');
        $this->checkOS('cambium', 'cambium-ptp');
        $this->checkOS('cambium', 'cambium-ptp250');
        $this->checkOS('cambium', 'cambium-ptp50650');
    }

    public function testCanonprinter()
    {
        $this->checkOS('canonprinter', 'canonprinter-mf');
        $this->checkOS('canonprinter', 'canonprinter-ir-adv');
    }

    public function testCanopy()
    {
        $this->checkOS('canopy');
        $this->checkOS('canopy', 'canopy-cmm');
    }

    public function testCat1900()
    {
        $this->checkOS('cat1900');
    }

    public function testCatos()
    {
        $this->checkOS('catos');
    }

    public function testCeraos()
    {
        $this->checkOS('ceraos');
    }

    public function testCimc()
    {
        $this->checkOS('cimc');
    }

    public function testCips()
    {
        $this->checkOS('cips');
    }

    public function testCiscosb()
    {
        $this->checkOS('ciscosb');
        $this->checkOS('ciscosb', 'ciscosb1');
        $this->checkOS('ciscosb', 'ciscosb2');
        $this->checkOS('ciscosb', 'ciscosb3');
        $this->checkOS('ciscosb', 'ciscosb4');
        $this->checkOS('ciscosb', 'ciscosb5');
        $this->checkOS('ciscosb', 'ciscosb6');
    }

    public function testCiscosmblinux()
    {
        $this->checkOS('ciscosmblinux');
    }

    public function testCiscowap()
    {
        $this->checkOS('ciscowap');
        $this->checkOS('ciscowap', 'ciscowap-wap321');
    }

    public function testCiscowlc()
    {
        $this->checkOS('ciscowlc');
    }

    public function testCmts()
    {
        $this->checkOS('cmts');
    }

    public function testCometsystemp85xx()
    {
        $this->checkOS('cometsystem-p85xx');
    }

    public function testComware()
    {
        $this->checkOS('comware');
        $this->checkOS('comware', 'comware1');
        $this->checkOS('comware', 'comware-hp-c1234');
    }

    public function testCucm()
    {
        $this->checkOS('cucm');
    }

    public function testCumulus()
    {
        $this->checkOS('cumulus');
    }

    public function testDasanNos()
    {
        $this->checkOS('dasan-nos');
    }

    public function testDatacom()
    {
        $this->checkOS('datacom');
    }

    public function testDatadomain()
    {
        $this->checkOS('datadomain');
    }

    public function testDcnSoftware()
    {
        $this->checkOS('dcn-software');
    }

    public function testDdnos()
    {
        $this->checkOS('ddnos');
    }

    public function testDeliberant()
    {
        $this->checkOS('deliberant');
    }

    public function testDellrcs()
    {
        $this->checkOS('dell-rcs');
    }

    public function testDelllaser()
    {
        $this->checkOS('dell-laser');
        $this->checkOS('dell-laser', 'dell-laser-color');
        $this->checkOS('dell-laser', 'dell-laser-mfp');
    }

    public function testDellups()
    {
        $this->checkOS('dell-ups');
    }

    public function testDeltaups()
    {
        $this->checkOS('deltaups');
    }

    public function testDevelopprinter()
    {
        $this->checkOS('developprinter');
    }

    public function testDlinkap()
    {
        $this->checkOS('dlinkap');
        $this->checkOS('dlinkap', 'dlinkap1');
        $this->checkOS('dlinkap', 'dlinkap2');
    }

    public function testDlink()
    {
        $this->checkOS('dlink', 'dlink-des');
        $this->checkOS('dlink', 'dlink-des1');
        $this->checkOS('dlink', 'dlink-des2');
        $this->checkOS('dlink', 'dlink-dgs');
    }

    public function testDnos()
    {
        $this->checkOS('dnos');
        $this->checkOS('dnos', 'dnos1');
        $this->checkOS('dnos', 'dnos2');
        $this->checkOS('dnos', 'dnos3');
        $this->checkOS('dnos', 'dnos4');
        $this->checkOS('dnos', 'dnos5');
        $this->checkOS('dnos', 'dnos6');
        $this->checkOS('dnos', 'dnos7');
        $this->checkOS('dnos', 'dnos8');
        $this->checkOS('dnos', 'dnos9');
    }

    public function testDrac()
    {
        $this->checkOS('drac');
        $this->checkOS('drac', 'drac1');
        $this->checkOS('drac', 'drac2');
    }

    public function testDsm()
    {
        $this->checkOS('dsm');
        $this->checkOS('dsm', 'dsm-ds214');
        $this->checkOS('dsm', 'dsm-ds916');
    }

    public function testEatonpdu()
    {
        $this->checkOS('eatonpdu');
    }

    public function testEatonups()
    {
        $this->checkOS('eatonups', 'eaton-5p');
        $this->checkOS('eatonups', 'eaton-5px');
        $this->checkOS('eatonups', 'eaton-powerxpert');
    }

    public function testEdgecos()
    {
        $this->checkOS('edgecos', 'edgecos-es3528m');
        $this->checkOS('edgecos', 'edgecos-ecs4120-28f');
        $this->checkOS('edgecos', 'edgecos-es3528mv2');
        $this->checkOS('edgecos', 'edgecos-ecs4510-28f');
        $this->checkOS('edgecos', 'edgecos-ecs4510-52t');
        $this->checkOS('edgecos', 'edgecos-ecs4210-28t');
        $this->checkOS('edgecos', 'edgecos-ecs3510-52t');
    }

    public function testEdgeos()
    {
        $this->checkOS('edgeos');
        $this->checkOS('edgeos', 'edgeos-erl');
        $this->checkOS('edgeos', 'edgeos-er');
    }

    public function testEdgeswitch()
    {
        $this->checkOS('edgeswitch');
        $this->checkOS('edgeswitch', 'edgeswitch-ep-s16');
        $this->checkOS('edgeswitch', 'edgeswitch-es-24-250w');
        $this->checkOS('edgeswitch', 'unifiswitch');
    }

    public function testEndian()
    {
        $this->checkOS('endian');
    }

    public function testEngenius()
    {
        $this->checkOS('engenius');
        $this->checkOS('engenius', 'engenius1');
        $this->checkOS('engenius', 'engenius2');
    }

    public function testEnterasys()
    {
        $this->checkOS('enterasys');
        $this->checkOS('enterasys', 'enterasys1');
    }

    public function testEpson()
    {
        $this->checkOS('epson');
    }

    public function testEquallogic()
    {
        $this->checkOS('equallogic');
    }

    public function testExtremeware()
    {
        $this->checkOS('extremeware');
    }

    public function testF5()
    {
        $this->checkOS('f5');
    }

    public function testFabos()
    {
        $this->checkOS('fabos');
        $this->checkOS('fabos', 'fabos1');
        $this->checkOS('fabos', 'fabos2');
    }

    public function testFiberhome()
    {

        $this->checkOS('fiberhome', 'fiberhome-an5516-01');
        $this->checkOS('fiberhome', 'fiberhome-an5516-06');
    }

    public function testFireware()
    {
        $this->checkOS('fireware', 'fireware-m400');
        $this->checkOS('fireware', 'fireware-xtm26w');
    }

    public function testFlareos()
    {
        $this->checkOS('flareos');
    }

    public function testFortigate()
    {
        $this->checkOS('fortigate');
        $this->checkOS('fortigate', 'fortigate1');
    }

    public function testFortios()
    {
        $this->checkOS('fortios');
    }

    public function testFortiswitch()
    {
        $this->checkOS('fortiswitch');
    }

    public function testFoundryos()
    {
        $this->checkOS('foundryos');
    }

    public function testFreebsd()
    {
        $this->checkOS('freebsd');
    }

    public function testFtos()
    {
        $this->checkOS('ftos');
    }

    public function testFujitsupyos()
    {
        $this->checkOS('fujitsupyos');
        $this->checkOS('fujitsupyos', 'fujitsupyos-10gbe');
    }

    public function testFujitsueternusos()
    {
        $this->checkOS('fujitsueternusos');
    }

    public function testFxos()
    {
        $this->checkOS('fxos');
    }

    public function testGaia()
    {
        $this->checkOS('gaia');
        $this->checkOS('gaia', 'gaia1');
    }

    public function testGamatronicups()
    {
        $this->checkOS('gamatronicups');
    }

    public function testGenerexUps()
    {
        $this->checkOS('generex-ups');
        $this->checkOS('generex-ups', 'generex-ups1');
        $this->checkOS('generex-ups', 'generex-ups2');
    }

    public function testHikvision()
    {
        $this->checkOS('hikvision');
        $this->checkOS('hikvision', 'hikvision1');
    }

    public function testHp3par()
    {
        $this->checkOS('informos');
    }

    public function testHpblmos()
    {
        $this->checkOS('hpblmos');
    }

    public function testHpeMsl()
    {
        $this->checkOS('hpe-msl');
    }

    public function testHpmsm()
    {
        $this->checkOS('hpmsm');
    }

    public function testHpvc()
    {
        $this->checkOS('hpvc');
    }

    public function testHuaweiups()
    {
        $this->checkOS('huaweiups');
    }

    public function testHwgposeidon()
    {
        $this->checkOS('hwg-poseidon');
    }

    public function testHwgste2()
    {
        $this->checkOS('hwg-ste2');
    }

    public function testHwgste()
    {
        $this->checkOS('hwg-ste');
    }

    public function testHytera()
    {
        $this->checkOS('hytera');
    }

    public function testIbmamm()
    {
        $this->checkOS('ibm-amm');
    }

    public function testIbmimm()
    {
        $this->checkOS('ibm-imm');
    }

    public function testIbmnos()
    {
        $this->checkOS('ibmnos');
        $this->checkOS('ibmnos', 'ibmnos1');
        $this->checkOS('ibmnos', 'ibmnos-flex');
    }

    public function testIbmtl()
    {
        $this->checkOS('ibmtl');
    }

    public function testIes()
    {
        $this->checkOS('ies');
    }

    public function testInfinity()
    {
        $this->checkOS('infinity');
    }

    public function testIos()
    {
        $this->checkOS('ios');
        $this->checkOS('ios', 'ios1');
        $this->checkOS('ios', 'ios2');
        $this->checkOS('ios', 'ios-c3825');
    }

    public function testIosxe()
    {
        $this->checkOS('iosxe');
        $this->checkOS('iosxe', 'iosxe-asr1000');
    }

    public function testIosxr()
    {
        $this->checkOS('iosxr');
    }

    public function testIpoman()
    {
        $this->checkOS('ipoman');
    }

    public function testIronware()
    {
        $this->checkOS('ironware');
    }

    public function testIse()
    {
        $this->checkOS('ise');
        $this->checkOS('ise', 'ise1');
    }

    public function testJetdirect()
    {
        $this->checkOS('jetdirect');
        $this->checkOS('jetdirect', 'jetdirect1');
        $this->checkOS('jetdirect', 'jetdirect2');
    }

    public function testJetstream()
    {
        $this->checkOS('jetstream');
    }

    public function testJuniperex2500os()
    {
        $this->checkOS('juniperex2500os');
    }

    public function testJunose()
    {
        $this->checkOS('junose');
    }

    public function testJunos()
    {
        $this->checkOS('junos');
        $this->checkOS('junos', 'junos1');
    }

    public function testJwos()
    {
        $this->checkOS('jwos');
    }

    public function testKonica()
    {
        $this->checkOS('konica');
    }

    public function testKyocera()
    {
        $this->checkOS('kyocera');
    }

    public function testLanier()
    {
        $this->checkOS('lanier');
    }

    public function testLantronixslc()
    {
        $this->checkOS('lantronix-slc');
    }

    public function testLcos()
    {
        $this->checkOS('lcos');
        $this->checkOS('lcos', 'lcos1');
    }

    public function testLenovoemc()
    {
        $this->checkOS('lenovoemc');
    }

    public function testLexmarkprinter()
    {
        $this->checkOS('lexmarkprinter');
    }

    public function testLiebert()
    {
        $this->checkOS('liebert');
    }

    public function testLigoos()
    {
        $this->checkOS('ligoos');
    }

    public function testLinux()
    {
        $this->checkOS('linux');
    }

    public function testMacosx()
    {
        $this->checkOS('macosx');
        $this->checkOS('macosx', 'macosx-sierra');
    }

    public function testMaipu()
    {
        $this->checkOS('mypoweros');
    }

    public function testMellanox()
    {
        $this->checkOS('mellanox');
    }

    public function testMerakimr()
    {
        $this->checkOS('merakimr');
    }

    public function testMerakims()
    {
        $this->checkOS('merakims');
    }

    public function testMerakimx()
    {
        $this->checkOS('merakimx');
    }

    public function testMgepdu()
    {
        $this->checkOS('mgepdu');
    }

    public function testMgeups()
    {
        $this->checkOS('mgeups', 'mgeups-pulsar');
        $this->checkOS('mgeups', 'mgeups-galaxy');
        $this->checkOS('mgeups', 'mgeups-evolution');
        $this->checkOS('mgeups', 'mgeups-proxy');
        $this->checkOS('mgeups', 'mgeups-comet');
    }

    public function testMicrosemitime()
    {
        $this->checkOS('microsemitime');
    }

    public function testMimosa()
    {
        $this->checkOS('mimosa');
    }

    public function testMinkelsrms()
    {
        $this->checkOS('minkelsrms');
    }

    public function testMirth()
    {
        $this->checkOS('mirth');
    }

    public function testMonowall()
    {
        $this->checkOS('monowall');
    }

    public function testMoxaNport()
    {
        $this->checkOS('moxa-nport');
    }

    public function testMrvld()
    {
        $this->checkOS('mrvld');
    }

    public function testNetapp()
    {
        $this->checkOS('netapp');
    }

    public function testNetbsd()
    {
        $this->checkOS('netbsd');
    }

    public function testNetbotz()
    {
        $this->checkOS('netbotz', 'netbotz-2014');
        $this->checkOS('netbotz', 'netbotz-2016');
    }

    public function testNetgear()
    {
        $this->checkOS('netgear');
        $this->checkOS('netgear', 'netgear1');
    }

    public function testNetmanplus()
    {
        $this->checkOS('netmanplus');
        $this->checkOS('netmanplus', 'netmanplus1');
    }

    public function testNetonix()
    {
        $this->checkOS('netonix', 'netonix-wispswitch');
    }

    public function testNetopia()
    {
        $this->checkOS('netopia');
    }

    public function testNetscaler()
    {
        $this->checkOS('netscaler');
    }

    public function testNetvision()
    {
        $this->checkOS('netvision');
    }

    public function testNetware()
    {
        $this->checkOS('netware');
    }

    public function testNimbleos()
    {
        $this->checkOS('nimbleos');
    }

    public function testNios()
    {
        $this->checkOS('nios');
        $this->checkOS('nios', 'nios-ipam');
    }

    public function testNitro()
    {
        $this->checkOS('nitro');
        $this->checkOS('nitro', 'nitro1');
        $this->checkOS('nitro', 'nitro2');
        $this->checkOS('nitro', 'nitro3');
    }

    public function testNos()
    {
        $this->checkOS('nos');
        $this->checkOS('nos', 'nos1');
        $this->checkOS('nos', 'nos2');
        $this->checkOS('nos', 'nos3');
    }

    public function testNrg()
    {
        $this->checkOS('nrg');
    }

    public function testNxos()
    {
        $this->checkOS('nxos');
    }

    public function testOkilan()
    {
        $this->checkOS('okilan');
    }

    public function testOpensolaris()
    {
        $this->checkOS('opensolaris');
    }

    public function testOnefs()
    {
        $this->checkOS('onefs');
    }

    public function testOns()
    {
        $this->checkOS('ons');
    }

    public function testOpenbsd()
    {
        $this->checkOS('openbsd');
        $this->checkOS('openbsd', 'openbsd1');
    }

    public function testOracleilom()
    {
        $this->checkOS('oracle-ilom');
    }

    public function testPacketshaper()
    {
        $this->checkOS('packetshaper');
    }

    public function testPanos()
    {
        $this->checkOS('panos');
    }

    public function testPapouchtme()
    {
        $this->checkOS('papouch-tme');
        $this->checkOS('papouch-tme', 'papouch-tme1');
    }

    public function testPbn()
    {
        $this->checkOS('pbn');
    }

    public function testPbncpe()
    {
        $this->checkOS('pbn-cp');
    }

    public function testPcoweb()
    {
        $this->checkOS('pcoweb');
    }

    public function testPerle()
    {
        $this->checkOS('perle');
    }

    public function testPfsense()
    {
        $this->checkOS('pfsense');
    }

    public function testPix()
    {
        $this->checkOS('pixos');
    }

    public function testPktj()
    {
        $this->checkOS('pktj');
    }

    public function testPlanetos()
    {
        $this->checkOS('planetos');
    }

    public function testPoweralert()
    {
        $this->checkOS('poweralert');
        $this->checkOS('poweralert', 'poweralert1');
    }

    public function testPowerconnect()
    {
        $this->checkOS('powerconnect');
    }

    public function testPowervault()
    {
        $this->checkOS('powervault');
    }

    public function testPowerwalker()
    {
        $this->checkOS('powerwalker');
    }

    public function testPowerware()
    {
        $this->checkOS('powerware');
    }

    public function testPrestige()
    {
        $this->checkOS('prestige');
    }

    public function testPrimeinfrastructure()
    {
        $this->checkOS('primeinfrastructure');
    }

    public function testProcera()
    {
        $this->checkOS('procera');
    }

    public function testProcurve()
    {
        $this->checkOS('procurve');
        $this->checkOS('procurve', 'procurve-1800-8g');
        $this->checkOS('procurve', 'procurve-1820');
        $this->checkOS('procurve', 'procurve-ecos-100');
        $this->checkOS('procurve', 'procurve-2530');
        $this->checkOS('procurve', 'procurve-5402r');
    }

    public function testProxim()
    {
        $this->checkOS('proxim');
    }

    public function testPulse()
    {
        $this->checkOS('pulse');
        $this->checkOS('pulse', 'pulse-mag2600');
        $this->checkOS('pulse', 'pulse-sa2500');
        $this->checkOS('pulse', 'pulse-sa6500');
        $this->checkOS('pulse', 'pulse-vaspe');
        $this->checkOS('pulse', 'pulse-sa');
    }

    public function testQnap()
    {
        $this->checkOS('qnap');
    }

    public function testQuanta()
    {
        $this->checkOS('quanta');
        $this->checkOS('quanta', 'quanta1');
        $this->checkOS('quanta', 'quanta2');
        $this->checkOS('quanta', 'quanta3');
        $this->checkOS('quanta', 'quanta-lb9');
    }

    public function testRadlan()
    {
        $this->checkOS('radlan');
    }

    public function testRaisecom()
    {
        $this->checkOS('raisecom');
    }

    public function testRaritan()
    {
        $this->checkOS('raritan');
        $this->checkOS('raritan', 'raritan-px2');
    }

    public function testRedback()
    {
        $this->checkOS('redback');
    }

    public function testRicoh()
    {
        $this->checkOS('ricoh');
        $this->checkOS('ricoh', 'ricoh-aficio');
    }

    public function testRiverbed()
    {
        $this->checkOS('riverbed');
    }

    public function testRouteros()
    {
        $this->checkOS('routeros');
        $this->checkOS('routeros', 'routeros1');
    }

    public function testRuckuswireless()
    {
        $this->checkOS('ruckuswireless');
    }

    public function testSaf()
    {
        $this->checkOS('saf');
    }

    public function testSamsungprinter()
    {
        $this->checkOS('samsungprinter', 'samsungprinter-clx');
        $this->checkOS('samsungprinter', 'samsungprinter-scx');
        $this->checkOS('samsungprinter', 'samsungprinter-c');
        $this->checkOS('samsungprinter', 'samsungprinter-s');
        $this->checkOS('samsungprinter', 'samsungprinter-ml');
    }

    public function testSanos()
    {
        $this->checkOS('sanos');
    }

    public function testScreenos()
    {
        $this->checkOS('screenos');
        $this->checkOS('screenos', 'screenos1');
    }

    public function testSentry3()
    {
        $this->checkOS('sentry3', 'sentry3-switched');
        $this->checkOS('sentry3', 'sentry3-smart');
    }

    public function testSentry4()
    {
        $this->checkOS('sentry4', 'sentry4-switched');
        $this->checkOS('sentry4', 'sentry4-smart');
    }

    public function testServeriron()
    {
        $this->checkOS('serveriron');
    }

    public function testSgos()
    {
        $this->checkOS('sgos');
    }

    public function testSharp()
    {
        $this->checkOS('sharp', 'sharp-mx2614n');
        $this->checkOS('sharp', 'sharp-mxc301w');
        $this->checkOS('sharp', 'sharp-mx3140n');
    }

    public function testSiklu()
    {
        $this->checkOS('siklu');
    }

    public function testSinetica()
    {
        $this->checkOS('sinetica');
    }

    public function testMegatec()
    {
        $this->checkOS('netagent2');
    }

    public function testSmartax()
    {
        $this->checkOS('smartax');
    }

    public function testSolaris()
    {
        $this->checkOS('solaris');
        $this->checkOS('solaris', 'solaris1');
    }

    public function testSonicwall()
    {
        $this->checkOS('sonicwall');
    }

    public function testSonusgsx()
    {
        $this->checkOS('sonus-gsx');
    }

    public function testSonussbc()
    {
        $this->checkOS('sonus-sbc');
        $this->checkOS('sonus-sbc', 'sonus-sbc1');
    }

    public function testSophos()
    {
        $this->checkOS('sophos');
        $this->checkOS('sophos', 'sophos1');
    }

    public function testSpeedtouch()
    {
        $this->checkOS('speedtouch');
        $this->checkOS('speedtouch', 'speedtouch-tg585');
        $this->checkOS('speedtouch', 'speedtouch-st5000');
    }

    public function testSub10()
    {
        $this->checkOS('sub10');
    }

    public function testSupermicroswitch()
    {
        $this->checkOS('supermicro-switch');
        $this->checkOS('supermicro-switch', 'supermicro-switch-sse');
        $this->checkOS('supermicro-switch', 'supermicro-switch-sbm');
    }

    public function testSwos()
    {
        $this->checkOS('swos', 'swos-rb250gs');
        $this->checkOS('swos', 'swos-rb260gs');
        $this->checkOS('swos', 'swos-rb260gsp');
    }

    public function testSymbol()
    {
        $this->checkOS('symbol');
    }

    public function testTimos()
    {
        $this->checkOS('timos');
        $this->checkOS('timos', 'timos1');
        $this->checkOS('timos', 'timos2');
        $this->checkOS('timos', 'timos3');
        $this->checkOS('timos', 'timos4');
        $this->checkOS('timos', 'timos5');
        $this->checkOS('timos', 'timos6');
        $this->checkOS('timos', 'timos7');
        $this->checkOS('timos', 'timos8');
    }

    public function testTomato()
    {
        $this->checkOS('tomato');
    }

    public function testTpconductor()
    {
        $this->checkOS('tpconductor');
    }

    public function testTplink()
    {
        $this->checkOS('tplink');
    }

    public function testTranzeo()
    {
        $this->checkOS('tranzeo');
    }

    public function testUnifi()
    {
        $this->checkOS('unifi');
    }

    public function testVccodec()
    {
        $this->checkOS('vccodec');
    }

    public function testVcs()
    {
        $this->checkOS('vcs');
    }

    public function testViprinux()
    {
        $this->checkOS('viprinux');
    }

    public function testVmware()
    {
        $this->checkOS('vmware', 'vmware-esx');
        $this->checkOS('vmware', 'vmware-vcsa');
    }

    public function testVoswall()
    {
        $this->checkOS('voswall');
    }

    public function testVrp()
    {
        $this->checkOS('vrp');
        $this->checkOS('vrp', 'vrp1');
        $this->checkOS('vrp', 'vrp2');
        $this->checkOS('vrp', 'vrp3');
    }

    public function testVyatta()
    {
        $this->checkOS('vyatta');
    }

    public function testVyos()
    {
        $this->checkOS('vyos');
        $this->checkOS('vyos', 'vyos1');
        $this->checkOS('vyos', 'vyos-vyatta');
    }

    public function testWaas()
    {
        $this->checkOS('waas');
    }

    public function testWebpower()
    {
        $this->checkOS('webpower');
    }

    public function testWindows()
    {
        $this->checkOS('windows');
        $this->checkOS('windows', 'windows1');
    }

    public function testWxgoos()
    {
        $this->checkOS('wxgoos');
        $this->checkOS('wxgoos', 'wxgoos1');
    }

    public function testXerox()
    {
        $this->checkOS('xerox', 'xerox-phaser');
        $this->checkOS('xerox', 'xerox-workcentre');
        $this->checkOS('xerox', 'xerox-docuprint');
    }

    public function testXirrus()
    {
        $this->checkOS('xirrus_aos');
    }

    public function testXos()
    {
        $this->checkOS('xos');
    }

    public function testZxr10()
    {
        $this->checkOS('zxr10');
    }

    public function testZynos()
    {
        $this->checkOS('zynos', 'zynos-es');
        $this->checkOS('zynos', 'zynos-gs');
        $this->checkOS('zynos', 'zynos-mes3528');
    }

    public function testZywall()
    {
        $this->checkOS('zywall');
        $this->checkOS('zywall', 'zywall1');
    }

    public function testZyxelnwa()
    {
        $this->checkOS('zyxelnwa');
    }
}
