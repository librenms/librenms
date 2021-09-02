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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://librenms.org
 * @copyright  2017 Adam Bishop
 * @author     Adam Bishop <adam@omega.org.uk>
 */

namespace LibreNMS\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;

class AuthSSOTest extends DBTestCase
{
    use DatabaseTransactions;

    private $original_auth_mech = null;
    private $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->original_auth_mech = Config::get('auth_mechanism');
        Config::set('auth_mechanism', 'sso');

        $this->server = $_SERVER;
    }

    // Set up an SSO config for tests
    public function basicConfig()
    {
        Config::set('sso.mode', 'env');
        Config::set('sso.create_users', true);
        Config::set('sso.update_users', true);
        Config::set('sso.trusted_proxies', ['127.0.0.1', '::1']);
        Config::set('sso.user_attr', 'REMOTE_USER');
        Config::set('sso.realname_attr', 'displayName');
        Config::set('sso.email_attr', 'mail');
        Config::set('sso.descr_attr', null);
        Config::set('sso.level_attr', null);
        Config::set('sso.group_strategy', 'static');
        Config::set('sso.group_attr', 'member');
        Config::set('sso.group_filter', '/(.*)/i');
        Config::set('sso.group_delimiter', ';');
        Config::set('sso.group_level_map', null);
        Config::set('sso.static_level', -1);
    }

    // Set up $_SERVER in env mode
    public function basicEnvironmentEnv()
    {
        unset($_SERVER);

        Config::set('sso.mode', 'env');

        $_SERVER['REMOTE_ADDR'] = '::1';
        $_SERVER['REMOTE_USER'] = 'test';

        $_SERVER['mail'] = 'test@example.org';
        $_SERVER['displayName'] = Str::random();
    }

    // Set up $_SERVER in header mode
    public function basicEnvironmentHeader()
    {
        unset($_SERVER);

        Config::set('sso.mode', 'header');

        $_SERVER['REMOTE_ADDR'] = '::1';
        $_SERVER['REMOTE_USER'] = Str::random();

        $_SERVER['HTTP_MAIL'] = 'test@example.org';
        $_SERVER['HTTP_DISPLAYNAME'] = 'Test User';
    }

    public function makeUser()
    {
        $user = Str::random();
        $_SERVER['REMOTE_USER'] = $user;

        return $user;
    }

    // Excercise general auth flow
    public function testValidAuthNoCreateUpdate()
    {
        $this->basicConfig();
        $a = LegacyAuth::reset();

        Config::set('sso.create_users', false);
        Config::set('sso.update_users', false);

        // Create a random username and store it with the defaults
        $this->basicEnvironmentEnv();
        $user = $this->makeUser();
        $this->assertTrue($a->authenticate(['username' => $user]));

        // Retrieve it and validate
        $dbuser = $a->getUser($a->getUserid($user));
        $this->assertFalse($dbuser);
    }

    // Excercise general auth flow with creation enabled
    public function testValidAuthCreateOnly()
    {
        $this->basicConfig();
        /** @var \LibreNMS\Authentication\SSOAuthorizer */
        $a = LegacyAuth::reset();

        Config::set('sso.create_users', true);
        Config::set('sso.update_users', false);

        // Create a random username and store it with the defaults
        $this->basicEnvironmentEnv();
        $user = $this->makeUser();
        $this->assertTrue($a->authenticate(['username' => $user]));

        // Retrieve it and validate
        $dbuser = $a->getUser($a->getUserid($user));
        $this->assertTrue($a->authSSOGetAttr(Config::get('sso.realname_attr')) === $dbuser['realname']);
        $this->assertTrue($dbuser['level'] == -1);
        $this->assertTrue($a->authSSOGetAttr(Config::get('sso.email_attr')) === $dbuser['email']);

        // Change a few things and reauth
        $_SERVER['mail'] = 'test@example.net';
        $_SERVER['displayName'] = 'Testier User';
        Config::set('sso.static_level', 10);
        $this->assertTrue($a->authenticate(['username' => $user]));

        // Retrieve it and validate the update was not persisted
        $dbuser = $a->getUser($a->getUserid($user));
        $this->assertFalse($a->authSSOGetAttr(Config::get('sso.realname_attr')) === $dbuser['realname']);
        $this->assertFalse($dbuser['level'] === '10');
        $this->assertFalse($a->authSSOGetAttr(Config::get('sso.email_attr')) === $dbuser['email']);
    }

    // Excercise general auth flow with updates enabled
    public function testValidAuthUpdate()
    {
        $this->basicConfig();
        /** @var \LibreNMS\Authentication\SSOAuthorizer */
        $a = LegacyAuth::reset();

        // Create a random username and store it with the defaults
        $this->basicEnvironmentEnv();
        $user = $this->makeUser();
        $this->assertTrue($a->authenticate(['username' => $user]));

        // Change a few things and reauth
        $_SERVER['mail'] = 'test@example.net';
        $_SERVER['displayName'] = 'Testier User';
        Config::set('sso.static_level', 10);
        $this->assertTrue($a->authenticate(['username' => $user]));

        // Retrieve it and validate the update persisted
        $dbuser = $a->getUser($a->getUserid($user));
        $this->assertTrue($a->authSSOGetAttr(Config::get('sso.realname_attr')) === $dbuser['realname']);
        $this->assertTrue($dbuser['level'] == 10);
        $this->assertTrue($a->authSSOGetAttr(Config::get('sso.email_attr')) === $dbuser['email']);
    }

    // Check some invalid authentication modes
    public function testBadAuth()
    {
        $this->basicConfig();
        /** @var \LibreNMS\Authentication\SSOAuthorizer */
        $a = LegacyAuth::reset();

        $this->basicEnvironmentEnv();
        unset($_SERVER);

        $this->expectException('LibreNMS\Exceptions\AuthenticationException');
        $a->authenticate([]);

        $this->basicEnvironmentHeader();
        unset($_SERVER);

        $this->expectException('LibreNMS\Exceptions\AuthenticationException');
        $a->authenticate([]);
    }

    // Test some missing attributes
    public function testNoAttribute()
    {
        $this->basicConfig();
        /** @var \LibreNMS\Authentication\SSOAuthorizer */
        $a = LegacyAuth::reset();

        $this->basicEnvironmentEnv();
        unset($_SERVER['displayName']);
        unset($_SERVER['mail']);

        $this->assertTrue($a->authenticate(['username' => $this->makeUser()]));

        $this->basicEnvironmentHeader();
        unset($_SERVER['HTTP_DISPLAYNAME']);
        unset($_SERVER['HTTP_MAIL']);

        $this->assertTrue($a->authenticate(['username' => $this->makeUser()]));
    }

    // Document the modules current behaviour, so that changes trigger test failures
    public function testCapabilityFunctions()
    {
        $a = LegacyAuth::reset();

        $this->assertFalse($a->canUpdatePasswords());
        $this->assertFalse($a->changePassword(null, null));
        $this->assertTrue($a->canManageUsers());
        $this->assertTrue($a->canUpdateUsers());
        $this->assertTrue($a->authIsExternal());
    }

    /* Everything from here comprises of targeted tests to excercise single methods */

    public function testGetExternalUserName()
    {
        $this->basicConfig();
        /** @var \LibreNMS\Authentication\SSOAuthorizer */
        $a = LegacyAuth::reset();

        $this->basicEnvironmentEnv();
        $this->assertIsString($a->getExternalUsername());

        // Missing
        unset($_SERVER['REMOTE_USER']);
        $this->assertNull($a->getExternalUsername());
        $this->basicEnvironmentEnv();

        // Missing pointer to attribute
        Config::forget('sso.user_attr');
        $this->assertNull($a->getExternalUsername());
        $this->basicEnvironmentEnv();

        // Non-existant attribute
        Config::set('sso.user_attr', 'foobar');
        $this->assertNull($a->getExternalUsername());
        $this->basicEnvironmentEnv();

        // null pointer to attribute
        Config::set('sso.user_attr', null);
        $this->assertNull($a->getExternalUsername());
        $this->basicEnvironmentEnv();

        // null attribute
        Config::set('sso.user_attr', 'REMOTE_USER');
        $_SERVER['REMOTE_USER'] = null;
        $this->assertNull($a->getExternalUsername());
    }

    public function testGetAttr()
    {
        /** @var \LibreNMS\Authentication\SSOAuthorizer */
        $a = LegacyAuth::reset();

        $_SERVER['HTTP_VALID_ATTR'] = 'string';
        $_SERVER['alsoVALID-ATTR'] = 'otherstring';

        Config::set('sso.mode', 'env');
        $this->assertNull($a->authSSOGetAttr('foobar'));
        $this->assertNull($a->authSSOGetAttr(null));
        $this->assertNull($a->authSSOGetAttr(1));
        $this->assertIsString($a->authSSOGetAttr('alsoVALID-ATTR'));
        $this->assertIsString($a->authSSOGetAttr('HTTP_VALID_ATTR'));

        Config::set('sso.mode', 'header');
        $this->assertNull($a->authSSOGetAttr('foobar'));
        $this->assertNull($a->authSSOGetAttr(null));
        $this->assertNull($a->authSSOGetAttr(1));
        $this->assertNull($a->authSSOGetAttr('alsoVALID-ATTR'));
        $this->assertIsString($a->authSSOGetAttr('VALID-ATTR'));
    }

    public function testTrustedProxies()
    {
        /** @var \LibreNMS\Authentication\SSOAuthorizer */
        $a = LegacyAuth::reset();

        Config::set('sso.trusted_proxies', ['127.0.0.1', '::1', '2001:630:50::/48', '8.8.8.0/25']);

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
        Config::set('sso.trusted_proxies', '8.8.8.0/25');
        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
        $this->assertFalse($a->authSSOProxyTrusted());

        // Unset
        unset($_SERVER['REMOTE_ADDR']);
        $this->assertFalse($a->authSSOProxyTrusted());
    }

    public function testLevelCaulculationFromAttr()
    {
        /** @var \LibreNMS\Authentication\SSOAuthorizer */
        $a = LegacyAuth::reset();

        Config::set('sso.mode', 'env');
        Config::set('sso.group_strategy', 'attribute');

        //Integer
        Config::set('sso.level_attr', 'level');
        $_SERVER['level'] = 9;
        $this->assertTrue($a->authSSOCalculateLevel() === 9);

        //String
        Config::set('sso.level_attr', 'level');
        $_SERVER['level'] = '9';
        $this->assertTrue($a->authSSOCalculateLevel() === 9);

        //Invalid String
        Config::set('sso.level_attr', 'level');
        $_SERVER['level'] = 'foobar';
        $this->expectException('LibreNMS\Exceptions\AuthenticationException');
        $a->authSSOCalculateLevel();

        //null
        Config::set('sso.level_attr', 'level');
        $_SERVER['level'] = null;
        $this->expectException('LibreNMS\Exceptions\AuthenticationException');
        $a->authSSOCalculateLevel();

        //Unset pointer
        Config::forget('sso.level_attr');
        $_SERVER['level'] = '9';
        $this->expectException('LibreNMS\Exceptions\AuthenticationException');
        $a->authSSOCalculateLevel();

        //Unset attr
        Config::set('sso.level_attr', 'level');
        unset($_SERVER['level']);
        $this->expectException('LibreNMS\Exceptions\AuthenticationException');
        $a->authSSOCalculateLevel();
    }

    public function testGroupParsing()
    {
        $this->basicConfig();
        /** @var \LibreNMS\Authentication\SSOAuthorizer */
        $a = LegacyAuth::reset();

        $this->basicEnvironmentEnv();

        Config::set('sso.group_strategy', 'map');
        Config::set('sso.group_delimiter', ';');
        Config::set('sso.group_attr', 'member');
        Config::set('sso.group_level_map', ['librenms-admins' => 10, 'librenms-readers' => 1, 'librenms-billingcontacts' => 5]);
        $_SERVER['member'] = 'librenms-admins;librenms-readers;librenms-billingcontacts;unrelatedgroup;confluence-admins';

        // Valid options
        $this->assertTrue($a->authSSOParseGroups() === 10);

        // No match
        $_SERVER['member'] = 'confluence-admins';
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Delimiter only
        $_SERVER['member'] = ';;;;';
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Empty
        $_SERVER['member'] = '';
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Null
        $_SERVER['member'] = null;
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Unset
        unset($_SERVER['member']);
        $this->assertTrue($a->authSSOParseGroups() === 0);

        $_SERVER['member'] = 'librenms-admins;librenms-readers;librenms-billingcontacts;unrelatedgroup;confluence-admins';

        // Empty
        Config::set('sso.group_level_map', []);
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Not associative
        Config::set('sso.group_level_map', ['foo', 'bar', 'librenms-admins']);
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Null
        Config::set('sso.group_level_map', null);
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Unset
        Config::forget('sso.group_level_map');
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // No delimiter
        Config::forget('sso.group_delimiter');
        $this->assertTrue($a->authSSOParseGroups() === 0);

        // Test group filtering by regex
        Config::set('sso.group_filter', '/confluence-(.*)/i');
        Config::set('sso.group_delimiter', ';');
        Config::set('sso.group_level_map', ['librenms-admins' => 10, 'librenms-readers' => 1, 'librenms-billingcontacts' => 5, 'confluence-admins' => 7]);
        $this->assertTrue($a->authSSOParseGroups() === 7);

        // Test group filtering by empty regex
        Config::set('sso.group_filter', '');
        $this->assertTrue($a->authSSOParseGroups() === 10);

        // Test group filtering by null regex
        Config::set('sso.group_filter', null);
        $this->assertTrue($a->authSSOParseGroups() === 10);
    }

    protected function tearDown(): void
    {
        Config::set('auth_mechanism', $this->original_auth_mech);
        Config::forget('sso');
        $_SERVER = $this->server;
        parent::tearDown();
    }
}
