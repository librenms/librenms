<?php

/**
 * SocialiteControllerTest.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Peter Childs
 * @author     Peter Childs <pjchilds@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\Http\Controllers\Auth\SocialiteController;
use App\Models\User;
use Laravel\Socialite\AbstractUser;
use LibreNMS\Tests\TestCase;

class SocialiteControllerTest extends TestCase
{
    /**
     * Helper to test setRolesFromClaim().
     *
     * @param  string  $provider  The Socialite provider name (e.g. 'okta' or 'saml2')
     * @param  array  $rawAttributes  The simulated raw user data from getRaw().
     * @param  array  $expectedRoles  The roles expected to be passed to syncRoles() - the expected returns
     * @param  array  $claimMap  A map of claim-values to roles (config for auth.socialite.claims).
     * @param  array  $scopes  Optional scopes config; defaults to ['groups'].
     * @return bool The return value from setRolesFromClaim().
     */
    private function runSetRolesFromClaimTest(
        string $provider,
        array $rawAttributes,
        array $expectedRoles,
        array $claimMap,
        array $scopes = ['groups']
    ): bool {
        // Inject scopes & claims.
        \LibreNMS\Config::set('auth.socialite.scopes', $scopes);
        \LibreNMS\Config::set('auth.socialite.claims', $claimMap);
        \LibreNMS\Config::set('auth.socialite.debug', false);

        // Stub the Socialite user.
        $socialiteUserStub = $this->createMock(AbstractUser::class);
        $socialiteUserStub
            ->method('getRaw')
            ->willReturn($rawAttributes);

        // Make the SocialiteController private bits accessable via reflection.
        $controller = new SocialiteController();
        $reflectionClass = new \ReflectionClass($controller);
        $prop = $reflectionClass->getProperty('socialite_user');
        $prop->setAccessible(true);
        $prop->setValue($controller, $socialiteUserStub);

        // Stub the User model and assert syncRoles().
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['syncRoles'])
            ->getMock();

        // we expect syncRoles is called once with our expected roles.
        $userMock->expects($this->once())
            ->method('syncRoles')
            ->with($expectedRoles);

        // Invoke the private method with the chosen provider.
        $method = $reflectionClass->getMethod('setRolesFromClaim');
        $method->setAccessible(true);

        return $method->invokeArgs($controller, [$provider, $userMock]);
    }

    public function testSetRolesFromClaimOktaAdmin(): void
    {
        // Test with a 'groups' value that should result in a role of ['admin'].
        $rawAttributes = [
            'sub' => '00REDACTED',
            'name' => 'Citizen, John',
            'locale' => 'en_US',
            'email' => 'John.Citizen@example.com',
            'preferred_username' => 'johnc@sub.example.com',
            'given_name' => 'John',
            'family_name' => 'Citizen',
            'zoneinfo' => 'America/Los_Angeles',
            'updated_at' => 1715015601,
            'email_verified' => 1,
            'groups' => ['Example-Admin-Group'],
        ];

        $result = $this->runSetRolesFromClaimTest(
            'okta', $rawAttributes, ['admin'],
            [
                'Example-Admin-Group' => ['roles' => ['admin']],
                'Example-ReadOnly-Group' => ['roles' => ['global-read']],
            ]
        );
        $this->assertTrue($result);
    }

    public function testSetRolesFromClaimOktaGlobalRead(): void
    {
        // Test with a 'groups' value that should result in a role of ['global-read'].
        $rawAttributes = [
            'sub' => '00REDACTED',
            'name' => 'Citizen, John',
            'locale' => 'en_US',
            'email' => 'John.Citizen@example.com',
            'preferred_username' => 'johnc@sub.example.com',
            'given_name' => 'John',
            'family_name' => 'Citizen',
            'zoneinfo' => 'America/Los_Angeles',
            'updated_at' => 1715015601,
            'email_verified' => 1,
            'groups' => ['Example-ReadOnly-Group'],
        ];

        $result = $this->runSetRolesFromClaimTest(
            'okta', $rawAttributes, ['global-read'],
            [
                'Example-Admin-Group' => ['roles' => ['admin']],
                'Example-ReadOnly-Group' => ['roles' => ['global-read']],
            ]);
        $this->assertTrue($result);
    }

    public function testSetRolesFromClaimSaml2Admin(): void
    {
        // we don't import LightSaml\Model\Assertion\Attribute for testing
        $attr = new class
        {
            public function getName(): string
            {
                return 'http://schemas.microsoft.com/ws/2008/06/identity/claims/groups';
            }

            public function getAllAttributeValues(): array
            {
                return ['G_librenms_admins'];
            }
        };

        $result = $this->runSetRolesFromClaimTest(
            'saml2',
            [$attr],
            ['admin'],
            [
                'G_librenms_admins' => ['roles' => ['admin']],
                'G_librenms_users' => ['roles' => ['global-read']],
            ]
        );
        $this->assertTrue($result);
    }

    public function testSetRolesFromClaimSaml2GlobalRead(): void
    {
        // we don't import LightSaml\Model\Assertion\Attribute for testing
        $attr = new class
        {
            public function getName(): string
            {
                return 'http://schemas.microsoft.com/ws/2008/06/identity/claims/groups';
            }

            public function getAllAttributeValues(): array
            {
                return ['G_librenms_users'];
            }
        };

        $result = $this->runSetRolesFromClaimTest(
            'saml2',
            [$attr],
            ['global-read'],
            [
                'G_librenms_admins' => ['roles' => ['admin']],
                'G_librenms_users' => ['roles' => ['global-read']],
            ]
        );
        $this->assertTrue($result);
    }
}
