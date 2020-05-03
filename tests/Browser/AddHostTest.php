<?php

namespace LibreNMS\Tests\Browser;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use LibreNMS\Tests\Browser\Pages\AddHostPage;
use LibreNMS\Tests\DuskTestCase;

/**
 * Class LoginTest
 * @package LibreNMS\Tests\Browser
 * @group browser
 */
class AddhostPageTest extends DuskTestCase
{
    use DatabaseMigrations;

    private $hostName = "testHost";

    private function createAdminUser()
    {
        $password = 'some_password';

        $user = factory(User::class)->create([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'level' => 10
        ]);
        return $user;
    }

    private static function setSwitch($browser, $name, $setOff)
    {
        $childIndex = $setOff ? 0 : 2;
        $browser->Script("jQuery('input[name=\'".$name."\']').parent().children()[".$childIndex."].click();");
    }

    public function testUserAccess()
    {
        $user = $this->createAdminUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find(1))
                  ->visit('/addhost')
                  ->assertPathIs('/addhost');
        });
    }

    public function testCLIsnmpV1()
    {
        $user = $this->CreateAdminUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find($user['user_id']))
                    ->visit(new AddHostPage())
                    ->assertPathIs('/addhost')
                    ->type('#hostname', $this->hostName)
                    ->select('#snmpver', 'v1')
                    ->type('#community', "community");
            AddhostPageTest::setSwitch($browser, "force_add", false);
            $browser->press('button[type*="submit"]');
        });

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals("community", $device->community, "Wrong snmp community");
        $this->assertEquals("v1", $device->snmpver, "Wrong snmp version");
    }

    public function testCLIsnmpV2()
    {
        $user = $this->createAdminUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find($user['user_id']))
                    ->visit(new AddHostPage())
                    ->assertPathIs('/addhost')
                    ->type('#hostname', $this->hostName)
                    ->select('#snmpver', 'v2c')
                    ->type('#community', "community");
            AddhostPageTest::setSwitch($browser, "force_add", false);
            $browser->press('button[type*="submit"]');
        });

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals("community", $device->community, "Wrong snmp community");
        $this->assertEquals("v2c", $device->snmpver, "Wrong snmp version");
    }

    public function testCLIsnmpV3UserAndPW()
    {
        $user = $this->createAdminUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find($user['user_id']))
                    ->visit(new AddHostPage())
                    ->assertPathIs('/addhost')
                    ->type('#hostname', $this->hostName)
                    ->select('#snmpver', 'v3')
                    ->select('#authlevel', 'authPriv')
                    ->type('#authname', 'SecName')
                    ->type('#authpass', 'AuthPW')
                    ->type('#cryptopass', 'PrivPW');
            AddhostPageTest::setSwitch($browser, "force_add", false);
            $browser->press('button[type*="submit"]');
        });

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(0, $device->snmp_disable, "snmp is disabled");
        $this->assertEquals("authPriv", $device->authlevel, "Wrong snmp v3 authlevel");
        $this->assertEquals("SecName", $device->authname, "Wrong snmp v3 security username");
        $this->assertEquals("AuthPW", $device->authpass, "Wrong snmp v3 authentication password");
        $this->assertEquals("PrivPW", $device->cryptopass, "Wrong snmp v3 crypto password");
        $this->assertEquals("v3", $device->snmpver, "Wrong snmp version");
    }

    public function testPortAssociationMode()
    {
        $user = $this->createAdminUser();

        $modes = array('ifIndex', 'ifName', 'ifDescr', 'ifAlias');
        foreach ($modes as $index => $mode) {
            $host = "hostName".$mode;
            $this->browse(function (Browser $browser) use ($user, $mode, $host) {
                $browser->loginAs(User::find($user['user_id']))
                        ->visit(new AddHostPage())
                        ->assertPathIs('/addhost')
                        ->type('#hostname', $host)
                        ->select('#snmpver', 'v1')
                        ->select('#port_assoc_mode', $mode);
                AddhostPageTest::setSwitch($browser, "force_add", false);
                $browser->press('button[type*="submit"]');
            });

            $device = Device::findByHostname($host);
            $this->assertNotNull($device);
            $this->assertEquals($index+1, $device->port_association_mode, "Wrong port association mode ".$mode);
        }
    }

    public function testSnmpTransport()
    {
        $user = $this->createAdminUser();

        $modes = array('udp', 'udp6', 'tcp', 'tcp6');
        foreach ($modes as $mode) {
            $host = "hostName".$mode;
            $this->browse(function (Browser $browser) use ($user, $mode, $host) {
                $browser->loginAs(User::find($user['user_id']))
                        ->visit(new AddHostPage())
                        ->assertPathIs('/addhost')
                        ->type('#hostname', $host)
                        ->select('#snmpver', 'v1')
                        ->select('#transport', $mode);
                AddhostPageTest::setSwitch($browser, "force_add", false);
                $browser->press('button[type*="submit"]');
            });

            $device = Device::findByHostname($host);
            $this->assertNotNull($device);

            $this->assertEquals($mode, $device->transport, "Wrong snmp transport (udp/tcp) ipv4/ipv6");
        }
    }

    public function testSnmpV3AuthProtocol()
    {
        $user = $this->createAdminUser();

        $modes = array('MD5', 'SHA');
        foreach ($modes as $mode) {
            $host = "hostName".$mode;
            $this->browse(function (Browser $browser) use ($user, $mode, $host) {
                $browser->loginAs(User::find($user['user_id']))
                        ->visit(new AddHostPage())
                        ->assertPathIs('/addhost')
                        ->type('#hostname', $host)
                        ->select('#snmpver', 'v3')
                        ->select('#authalgo', $mode);
                AddhostPageTest::setSwitch($browser, "force_add", false);
                $browser->press('button[type*="submit"]');
            });

            $device = Device::findByHostname($host);
            $this->assertNotNull($device);

            $this->assertEquals($mode, $device->authalgo, "Wrong snmp v3 password algoritme");
        }
    }

    public function testSnmpV3PrivacyProtocol()
    {
        $user = $this->createAdminUser();

        $modes = array('DES', 'AES');
        foreach ($modes as $mode) {
            $host = "hostName".$mode;
            $this->browse(function (Browser $browser) use ($user, $mode, $host) {
                $browser->loginAs(User::find($user['user_id']))
                        ->visit(new AddHostPage())
                        ->assertPathIs('/addhost')
                        ->type('#hostname', $host)
                        ->select('#snmpver', 'v3')
                        ->select('#cryptoalgo', $mode);
                AddhostPageTest::setSwitch($browser, "force_add", false);
                $browser->press('button[type*="submit"]');
            });

            $device = Device::findByHostname($host);
            $this->assertNotNull($device);

            $this->assertEquals($mode, $device->cryptoalgo, "Wrong snmp v3 crypt algoritme");
        }
    }

    public function testCLIping()
    {
        $user = $this->createAdminUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find($user['user_id']))
                    ->visit(new AddHostPage())
                    ->assertPathIs('/addhost')
                    ->type('#hostname', $this->hostName)
                    ->Script("jQuery('input[name=\'snmp\']').parent().children()[0].click();");
            $browser->type('sysName', 'System')
                    ->type('hardware', 'hardware')
                    ->type('os', 'Linux');
            AddhostPageTest::setSwitch($browser, "force_add", false);
            $browser->press('button[name*="Submit"]');
        });

        $device = Device::findByHostname($this->hostName);
        $this->assertNotNull($device);

        $this->assertEquals(1, $device->snmp_disable, "snmp is not disabled");
        $this->assertEquals("hardware", $device->hardware, "Wrong hardware name");
        $this->assertEquals("System", $device->sysName, "Wrong system name");
    }
}
