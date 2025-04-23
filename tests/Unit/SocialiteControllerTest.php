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

use LibreNMS\Tests\TestCase;
use Laravel\Socialite\AbstractUser;
use App\Http\Controllers\Auth\SocialiteController;
use App\Models\User;

class SocialiteControllerTest extends TestCase
{
    /**
     * Helper to test setRolesFromClaim().
     *
     * @param array $rawAttributes The simulated raw user data from getRaw().
     * @param array $expectedRoles The roles expected to be passed to syncRoles().
     * @return bool The return value from setRolesFromClaim().
     */
    private function runSetRolesFromClaimTest(array $rawAttributes, array $expectedRoles): bool
    {
        // Set the simulated configuration values.
        \LibreNMS\Config::set('auth.socialite.scopes', ['groups']);
        \LibreNMS\Config::set('auth.socialite.claims', [
            'Example-Admin-Group' => ['roles' => ['admin']],
            'Example-ReadOnly-Group' => ['roles' => ['global-read']],
        ]);

        // Create a stub for the Socialite user so that getRaw() returns our provided raw attributes.
        $socialiteUserStub = $this->createMock(AbstractUser::class);
        $socialiteUserStub->method('getRaw')->willReturn($rawAttributes);

        // Instantiate the controller and inject the Socialite user stub into its private property.
        $controller = new SocialiteController();
        $reflectionController = new \ReflectionClass($controller);
        $socialiteUserProp = $reflectionController->getProperty('socialite_user');
        $socialiteUserProp->setAccessible(true);
        $socialiteUserProp->setValue($controller, $socialiteUserStub);

        // Create a mock for the User model.
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['syncRoles'])
            ->getMock();

        // Set expectation: syncRoles should be called exactly once with the expected roles.
        $userMock->expects($this->once())
            ->method('syncRoles')
            ->with($expectedRoles);

        // Get access to the private method setRolesFromClaim() via reflection.
        $method = $reflectionController->getMethod('setRolesFromClaim');
        $method->setAccessible(true);

        // Call the method (the provider name "okta" is arbitrary here) and return its result.
        return $method->invokeArgs($controller, ['okta', $userMock]);
    }

    public function testSetRolesFromClaimOktaAdmin(): void
    {
        // Test with a 'groups' value that should result in a role of ['admin'].
        $rawAttributes = [
            'sub'                => '00REDACTED',
            'name'               => 'Citizen, John',
            'locale'             => 'en_US',
            'email'              => 'John.Citizen@example.com',
            'preferred_username' => 'johnc@sub.example.com',
            'given_name'         => 'John',
            'family_name'        => 'Citizen',
            'zoneinfo'           => 'America/Los_Angeles',
            'updated_at'         => 1715015601,
            'email_verified'     => 1,
            'groups'             => ['Example-Admin-Group'],
        ];

        $result = $this->runSetRolesFromClaimTest($rawAttributes, ['admin']);
        $this->assertTrue($result);
    }

    public function testSetRolesFromClaimOktaGlobalRead(): void
    {
        // Test with a 'groups' value that should result in a role of ['global-read'].
        $rawAttributes = [
            'sub'                => '00REDACTED',
            'name'               => 'Citizen, John',
            'locale'             => 'en_US',
            'email'              => 'John.Citizen@example.com',
            'preferred_username' => 'johnc@sub.example.com',
            'given_name'         => 'John',
            'family_name'        => 'Citizen',
            'zoneinfo'           => 'America/Los_Angeles',
            'updated_at'         => 1715015601,
            'email_verified'     => 1,
            'groups'             => ['Example-ReadOnly-Group'],
        ];

        $result = $this->runSetRolesFromClaimTest($rawAttributes, ['global-read']);
        $this->assertTrue($result);
    }
}
