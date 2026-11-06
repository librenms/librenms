<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Authentication\ActiveDirectoryCommon;
use LibreNMS\Tests\TestCase;

class DummyAdAuthorizer
{
    use ActiveDirectoryCommon;

    public function testUserFilter(string $username): string
    {
        return $this->userFilter($username);
    }

    public function testGroupFilter(string $groupname): string
    {
        return $this->groupFilter($groupname);
    }

    protected function getConnection(): ?\LDAP\Connection
    {
        return null;
    }
}

class LdapFilterSanitizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('ldap_escape')) {
            $this->markTestSkipped('LDAP extension is not available.');
        }
    }

    public function testUserFilterEscapesWildcardsAndParentheses(): void
    {
        $dummy = new DummyAdAuthorizer();

        $wildcardFilter = $dummy->testUserFilter('*');
        $this->assertStringNotContainsString('(samaccountname=*)', $wildcardFilter);
        $this->assertStringContainsString('\2a', $wildcardFilter);

        $injectedFilter = $dummy->testUserFilter('admin)(|(uid=*');
        $this->assertStringNotContainsString('admin)(|(uid=*', $injectedFilter);
        $this->assertStringContainsString('\28', $injectedFilter);
        $this->assertStringContainsString('\29', $injectedFilter);
    }

    public function testGroupFilterEscapesSpecialChars(): void
    {
        $dummy = new DummyAdAuthorizer();

        $filter = $dummy->testGroupFilter('Domain Admins)(member=*');
        $this->assertStringNotContainsString('(samaccountname=Domain Admins)(member=*)', $filter);
    }
}
