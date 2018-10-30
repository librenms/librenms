<?php
/**
 * AuthSSO.php
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
 * @link       https://librenms.org
 * @copyright  2017 Adam Bishop
 * @author     Adam Bishop <adam@omega.org.uk>
 */

namespace LibreNMS\Tests;

use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Exceptions\AuthenticationException;

class AuthSSOTest extends DBTestCase
{
    private $last_user = null;
    private $original_auth_mech = null;
    private $server;

    public function setUp()
    {
        parent::setUp();
        global $config;

        $this->original_auth_mech = $config['auth_mechanism'];
        $config['auth_mechanism'] = 'sso';

        $this->server = $_SERVER;
    }

    // Set up an SSO config for tests
    public function basicConfig()
    {
        global $config;

        $config['sso']['mode'] = 'env';
        $config['sso']['create_users'] = true;
        $config['sso']['update_users'] = true;
        $config['sso']['trusted_proxies'] = array('127.0.0.1', '::1');
        $config['sso']['user_attr'] = 'REMOTE_USER';
        $config['sso']['realname_attr'] = 'displayName';
        $config['sso']['email_attr'] = 'mail';
        $config['sso']['descr_attr'] = null;
        $config['sso']['level_attr'] = null;
        $config['sso']['group_strategy'] = 'static';
        $config['sso']['group_attr'] = 'member';
        $config['sso']['group_filter'] = '/(.*)/i';
        $config['sso']['group_delimiter'] = ';';
        $config['sso']['group_level_map'] = null;
        $config['sso']['static_level'] = -1;
    }

    // Set up $_SERVER in env mode
    public function basicEnvironmentEnv()
    {
        global $config;
        unset($_SERVER);

        $config['sso']['mode'] = 'env';

        $_SERVER['REMOTE_ADDR'] = '::1';
        $_SERVER['REMOTE_USER'] = 'test';

        $_SERVER['mail'] = 'test@example.org';
        $_SERVER['displayName'] = bin2hex(openssl_random_pseudo_bytes(16));
    }


    // Set up $_SERVER in header mode
    public function basicEnvironmentHeader()
    {
        global $config;
        unset($_SERVER);

        $config['sso']['mode'] = 'header';

        $_SERVER['REMOTE_ADDR'] = '::1';
        $_SERVER['REMOTE_USER'] = bin2hex(openssl_random_pseudo_bytes(16));

        $_SERVER['HTTP_MAIL'] = 'test@example.org';
        $_SERVER['HTTP_DISPLAYNAME'] = 'Test User';
    }

    public function makeBreakUser()
    {
        $this->breakUser();

        $u = bin2hex(openssl_random_pseudo_bytes(16));
        $this->last_user = $u;
        $_SERVER['REMOTE_USER'] = $u;

        return $u;
    }

    public function breakUser()
    {
        $a = LegacyAuth::reset();

        if ($this->last_user !== null) {
            $r = $a->deleteUser($a->getUserid($this->last_user));
            $this->last_user = null;
            return $r;
        }

        return true;
    }

    // Excercise general auth flow
    public function testValidAuthNoCreateUpdate()
    {
        global $config;

        $this->basicConfig();
        $a = LegacyAuth::reset();

        $config['sso']['create_users'] = false;
        $config['sso']['update_users'] = false;

        // Create a random username and store it with the defaults
        $this->basicEnvironmentEnv();
        $user = $this->makeBreakUser();
        $this->assertTrue($a->authenticate($user, null));

        // Retrieve it and validate
        $dbuser = $a->getUser($a->getUserid($user));
        $this->assertFalse($a->authSSOGetAttr($config['sso']['realname_attr']) === $dbuser['realname']);
        $this->assertFalse($dbuser['level'] === "0");
        $this->assertFalse($a->authSSOGetAttr($config['sso']['email_attr']) === $dbuser['email']);
    }

    // Excercise general auth flow with creation enabled
    public function testValidAuthCreateOnly()
    {
        global $config;

        $this->basicConfig();
        $a = LegacyAuth::reset();

        $config['sso']['create_users'] = true;
        $config['sso']['update_users'] = false;

        // Create a random username and store it with the defaults
        $this->basicEnvironmentEnv();
        $user = $this->makeBreakUser();
        $this->assertTrue($a->authenticate($user, null));

        // Retrieve it and validate
        $dbuser = $a->getUser($a->getUserid($user));
        $this->assertTrue($a->authSSOGetAttr($config['sso']['realname_attr']) === $dbuser['realname']);
        $this->assertTrue($dbuser['level'] == -1);
        $this->assertTrue($a->authSSOGetAttr($config['sso']['email_attr']) === $dbuser['email']);

        // Change a few things and reauth
        $_SERVER['mail'] = 'test@example.net';
        $_SERVER['displayName'] = 'Testier User';
        $config['sso']['static_level'] = 10;
        $this->assertTrue($a->authenticate($user, null));

        // Retrieve it and validate the update was not persisted
        $dbuser = $a->getUser($a->getUserid($user));
        $this->assertFalse($a->authSSOGetAttr($config['sso']['realname_attr']) === $dbuser['realname']);
        $this->assertFalse($dbuser['level'] === "10");
        $this->assertFalse($a->authSSOGetAttr($config['sso']['email_attr']) === $dbuser['email']);
    }

    // Excercise general auth flow with updates enabled
    public function testValidAuthUpdate()
    {
        global $config;

        $this->basicConfig();
        $a = LegacyAuth::reset();

        // Create a random username and store it with the defaults
        $this->basicEnvironmentEnv();
        $user = $this->makeBreakUser();
        $this->assertTrue($a->authenticate($user, null));

        // Change a few things and reauth
        $_SERVER['mail'] = 'test@example.net';
        $_SERVER['displayName'] = 'Testier User';
        $config['sso']['static_level'] = 10;
        $this->assertTrue($a->authenticate($user, null));

        // Retrieve it and validate the update persisted
        $dbuser = $a->getUser($a->getUserid($user));
        $this->assertTrue($a->authSSOGetAttr($config['sso']['realname_attr']) === $dbuser['realname']);
        $this->assertTrue($dbuser['level'] == 10);
        $this->assertTrue($a->authSSOGetAttr($config['sso']['email_attr']) === $dbuser['email']);
    }

    // Check some invalid authentication modes
    public function testBadAuth()
    {
        global $config;

        $this->basicConfig();
        $a = LegacyAuth::reset();

        $this->basicEnvironmentEnv();
        unset($_SERVER);

        $this->setExpectedException('LibreNMS\Exceptions\AuthenticationException');
        $a->authenticate(null, null);

        $this->basicEnvironmentHeader();
        unset($_SERVER);

        $this->setExpectedException('LibreNMS\Exceptions\AuthenticationException');
        $a->authenticate(null, null);
    }

    // Test some missing attributes
    public function testNoAttribute()
    {
        global $config;

        $this->basicConfig();
        $a = LegacyAuth::reset();

        $this->basicEnvironmentEnv();
        unset($_SERVER['displayName']);
        unset($_SERVER['mail']);

        $this->assertTrue($a->authenticate($this->makeBreakUser(), null));

        $this->basicEnvironmentHeader();
        unset($_SERVER['HTTP_DISPLAYNAME']);
        unset($_SERVER['HTTP_MAIL']);

        $this->assertTrue($a->authenticate($this->makeBreakUser(), null));
    }

    // Document the modules current behaviour, so that changes trigger test failures
    public function testCapabilityFunctions()
    {
        $a = LegacyAuth::reset();

        $this->assertTrue($a->canUpdatePasswords() === 0);
        $this->assertTrue($a->changePassword(null, null) === 0);
        $this->assertTrue($a->canManageUsers() === 1);
        $this->assertTrue($a->canUpdateUsers() === 1);
        $this->assertTrue($a->authIsExternal() === 1);
    }

    /* Everything from here comprises of targeted tests to excercise single methods */

    public function testGetExternalUserName()
    {
        global $config;

        $this->basicConfig();
        $a = LegacyAuth::reset();

        $this->basicEnvironmentEnv();
        $this->assertInternalType('string', $a->getExternalUsername());

        // Missing
        unset($_SERVER['REMOTE_USER']);
        $this->assertNull($a->getExternalUsername());
        $this->basicEnvironmentEnv();

        // Missing pointer to attribute
        unset($config['sso']['user_attr']);
        $this->assertNull($a->getExternalUsername());
        $this->basicEnvironmentEnv();

        // Non-existant attribute
        $config['sso']['user_attr'] = 'foobar';
        $this->assertNull($a->getExternalUsername());
        $this->basicEnvironmentEnv();

        // null pointer to attribute
        $config['sso']['user_attr'] = null;
        $this->assertNull($a->getExternalUsername());
        $this->basicEnvironmentEnv();

        // null attribute
        $config['sso']['user_attr'] = 'REMOTE_USER';
        $_SERVER['REMOTE_USER'] = null;
        $this->assertNull($a->getExternalUsername());
    }

    public function testGetAttr()
    {
        global $config;
        $a = LegacyAuth::reset();

        $_SERVER['HTTP_VALID_ATTR'] = 'string';
        $_SERVER['alsoVALID-ATTR'] = 'otherstring';

        $config['sso']['mode'] = 'env';
        $this->assertNull($a->authSSOGetAttr('foobar'));
        $this->assertNull($a->authSSOGetAttr(null));
        $this->assertNull($a->authSSOGetAttr(1));
        $this->assertInternalType('string', $a->authSSOGetAttr('alsoVALID-ATTR'));
        $this->assertInternalType('string', $a->authSSOGetAttr('HTTP_VALID_ATTR'));

        $config['sso']['mode'] = 'header';
        $this->assertNull($a->authSSOGetAttr('foobar'));
        $this->assertNull($a->authSSOGetAttr(null));
        $this->assertNull($a->authSSOGetAttr(1));
        $this->assertNull($a->authSSOGetAttr('alsoVALID-ATTR'));
        $this->assertInternalType('string', $a->authSSOGetAttr('VALID-ATTR'));
    }

    public function testTrustedProxies()
    {
        global $config;
        $a = LegacyAuth::reset();

        $config['sso']['trusted_proxies'] = array('127.0.0.1', '::1', '2001:630:50::/48', '8.8.8.0/25');

        // v4 valid CIDR
        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
        $this->assertTrue($a->authSSOProxyTrusted());

        // v4 valid single
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->assertTrue($a->authSSOProxyTrusted());

        // v4 invalid CIDR
        $_SERVER['REMOTE_ADDR'] = '9.8.8.8';
        $this->assertFalse($a->authSSOProxyTrusted());

        // v6 valid CIDR
        $_SERVER['REMOTE_ADDR'] = '2001:630:50:baad:beef:feed:face:cafe';
        $this->assertTrue($a->authSSOProxyTrusted());

        // v6 valid single
        $_SERVER['REMOTE_ADDR'] = '::1';
        $this->assertTrue($a->authSSOProxyTrusted());

        // v6 invalid CIDR
        $_SERVER['REMOTE_ADDR'] = '2600::';
        $this->assertFalse($a->authSSOProxyTrusted());

        // Not an IP
        $_SERVER['REMOTE_ADDR'] = 16;
        $this->assertFalse($a->authSSOProxyTrusted());

        //null
        $_SERVER['REMOTE_ADDR'] = null;
        $this->assertFalse($a->authSSOProxyTrusted());

        // Invalid String
        $_SERVER['REMOTE_ADDR'] = 'Not an IP address at all, but maybe PHP will end up type juggling somehow';
        $this->assertFalse($a->authSSOProxyTrusted());

        // Not a list
        $config['sso']['trusted_proxies'] = '8.8.8.0/25';
        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
        $this->assertFalse($a->authSSOProxyTrusted());

        // Unset
        unset($_SERVER['REMOTE_ADDR']);
        $this->assertFalse($a->authSSOProxyTrusted());
    }

    public function testLevelCaulculationFromAttr()
    {
        global $config;
        $a = LegacyAuth::reset();

        $config['sso']['mode'] = 'env';
        $config['sso']['group_strategy'] = 'attribute';

        //Integer
        $config['sso']['level_attr'] = 'level';
        $_SERVER['level'] = 9;
        $this->assertTrue($a->authSSOCalculateLevel() === 9);

        //String
        $config['sso']['level_attr'] = 'level';
        $_SERVER['level'] = "9";
        $this->assertTrue($a->authSSOCalculateLevel() === 9);

        //Invalid String
        $config['sso']['level_attr'] = 'level';
        $_SERVER['level'] = 'foobar';
        $this->setExpectedException('LibreNMS\Exceptions\AuthenticationException');
        $a->authSSOCalculateLevel();

        //null
        $config['sso']['level_attr'] = 'level';
        $_SERVER['level'] = null;
        $this->setExpectedException('LibreNMS\Exceptions\AuthenticationException');
        $a->authSSOCalculateLevel();

        //Unset pointer
        unset($config['sso']['level_attr']);
        $_SERVER['level'] = "9";
        $this->setExpectedException('LibreNMS\Exceptions\AuthenticationException');
        $a->authSSOCalculateLevel();

        //Unset attr
        $config['sso']['level_attr'] = 'level';
        unset($_SERVER['level']);
        $this->setExpectedException('LibreNMS\Exceptions\AuthenticationException');
        $a->authSSOCalculateLevel();
    }

    public function testGroupParsing()
    {
        global $config;

        $this->basicConfig();
        $a = LegacyAuth::reset();

        $this->basicEnvironmentEnv();

        $config['sso']['group_strategy'] = 'map';
        $config['sso']['group_delimiter'] = ';';
        $config['sso']['group_attr'] = 'member';
        $config['sso']['group_level_map'] = array('librenms-admins' => 10, 'librenms-readers' => 1, 'librenms-billingcontacts' => 5);
        $_SERVER['member'] = "librenms-admins;librenms-readers;librenms-billingcontacts;unrelatedgroup;confluence-admins";

        // Valid options
        $this->assertTrue($a->authSSOParseGroups() === 10);

        // No match
        $_SERVER['member'] = "confluence-admins";
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Delimiter only
        $_SERVER['member'] = ";;;;";
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Empty
        $_SERVER['member'] = "";
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Null
        $_SERVER['member'] = null;
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Unset
        unset($_SERVER['member']);
        $this->assertTrue($a->authSSOParseGroups() === 0);

        $_SERVER['member'] = "librenms-admins;librenms-readers;librenms-billingcontacts;unrelatedgroup;confluence-admins";

        // Empty
        $config['sso']['group_level_map'] = array();
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Not associative
        $config['sso']['group_level_map'] = array('foo', 'bar', 'librenms-admins');
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Null
        $config['sso']['group_level_map'] = null;
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Unset
        unset($config['sso']['group_level_map']);
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // No delimiter
        unset($config['sso']['group_delimiter']);
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Test group filtering by regex
        $config['sso']['group_filter'] = "/confluence-(.*)/i";
        $config['sso']['group_delimiter'] = ';';
        $config['sso']['group_level_map'] = array('librenms-admins' => 10, 'librenms-readers' => 1, 'librenms-billingcontacts' => 5, 'confluence-admins' => 7);
        $this->assertTrue($a->authSSOParseGroups() === 7);

        // Test group filtering by empty regex
        $config['sso']['group_filter'] = "";
        $this->assertTrue($a->authSSOParseGroups() === 10);

        // Test group filtering by null regex
        $config['sso']['group_filter'] = null;
        $this->assertTrue($a->authSSOParseGroups() === 10);
    }

    public function tearDown()
    {
        parent::tearDown();
        global $config;

        $config['auth_mechanism'] = $this->original_auth_mech;
        unset($config['sso']);
        $this->breakUser();

        $_SERVER = $this->server;
    }
}
