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
use Laravel\Socialite\AbstractUser;
use LibreNMS\Tests\TestCase;

final class SocialiteControllerTest extends TestCase
{
    /**
     * Helper to test getAuthorizedRoles().
     *
     * @param  string  $provider  The Socialite provider name
     * @param  array  $rawAttributes  The simulated raw user data from getRaw().
     * @param  array|false  $expectedRoles  The roles expected to be returned (or false for access denied)
     * @param  array  $claimMap  A map of claim-values to roles.
     * @param  array  $scopes  Optional scopes config; defaults to ['groups'].
     * @return array|false The return value from getAuthorizedRoles().
     */
    private function runGetAuthorizedRolesTest(
        string $provider,
        array $rawAttributes,
        array|false $expectedRoles,
        array $claimMap,
        array $scopes = ['groups']
    ) {
        // Inject scopes & claims.
        \App\Facades\LibrenmsConfig::set('auth.socialite.scopes', $scopes);
        \App\Facades\LibrenmsConfig::set('auth.socialite.claims', $claimMap);
        \App\Facades\LibrenmsConfig::set('auth.socialite.debug', false);
        \App\Facades\LibrenmsConfig::set('auth.socialite.default_role', 'none');

        // Dynamically mock the correct User class based on the provider
        if ($provider === 'saml2') {
            // SAML2 uses the base AbstractUser (No tokens)
            $socialiteUserStub = $this->createMock(\Laravel\Socialite\AbstractUser::class);
        } else {
            // OAuth2/OIDC providers (Okta, Azure, etc.) use Two\User (Has tokens)
            $socialiteUserStub = $this->createMock(\Laravel\Socialite\Two\User::class);
            $socialiteUserStub->accessTokenResponseBody = [];
        }

        $socialiteUserStub
            ->method('getRaw')
            ->willReturn($rawAttributes);
            
        // Mock the accessTokenResponseBody property used for JWT logic
        $socialiteUserStub->accessTokenResponseBody = [];

        // Make the SocialiteController private bits accessible via reflection.
        $controller = new SocialiteController();
        $reflectionClass = new \ReflectionClass($controller);

        $prop = $reflectionClass->getProperty('socialite_user');
        $prop->setAccessible(true);
        $prop->setValue($controller, $socialiteUserStub);

        // Invoke the private method with the chosen provider.
        $method = $reflectionClass->getMethod('getAuthorizedRoles');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $provider);

        // Assert the returned roles match expectations
        $this->assertEquals($expectedRoles, $result);

        return $result;
    }

    public function testGetAuthorizedRolesOktaAdmin(): void
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

        $this->runGetAuthorizedRolesTest(
            'okta', $rawAttributes, ['admin'],
            [
                'Example-Admin-Group' => ['roles' => ['admin']],
                'Example-ReadOnly-Group' => ['roles' => ['global-read']],
            ]
        );
    }

    public function testGetAuthorizedRolesOktaGlobalRead(): void
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

        $this->runGetAuthorizedRolesTest(
            'okta', $rawAttributes, ['global-read'],
            [
                'Example-Admin-Group' => ['roles' => ['admin']],
                'Example-ReadOnly-Group' => ['roles' => ['global-read']],
            ]);
    }

    public function testGetAuthorizedRolesAccessDenied(): void
    {
        // Test scenario where no matching claims exist and default_role is 'none'
        $rawAttributes = [
            'email' => 'stranger@example.com',
            'groups' => ['Unknown-Group'],
        ];

        $this->runGetAuthorizedRolesTest(
            'okta', $rawAttributes, false, // Should return false (Access Denied)
            [
                'G_librenms_admins' => ['roles' => ['admin']],
                'G_librenms_users' => ['roles' => ['global-read']],
            ]
        );
    }

    public function testGetAuthorizedRolesSaml2Admin(): void
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

        $this->runGetAuthorizedRolesTest(
            'saml2',
            [$attr],
            ['admin'],
            [
                'G_librenms_admins' => ['roles' => ['admin']],
                'G_librenms_users' => ['roles' => ['global-read']],
            ]
        );
    }

    public function testGetAuthorizedRolesSaml2GlobalRead(): void
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

        $this->runGetAuthorizedRolesTest(
            'saml2',
            [$attr],
            ['global-read'],
            [
                'G_librenms_admins' => ['roles' => ['admin']],
                'G_librenms_users' => ['roles' => ['global-read']],
            ]
        );
    }
}
