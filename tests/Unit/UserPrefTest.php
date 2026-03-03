<?php

/**
 * UserPrefTest.php
 *
 * Tests for UserPref model
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
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Unit;

use App\Models\User;
use App\Models\UserPref;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

final class UserPrefTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testSetPref(): void
    {
        $user = User::factory()->create();
        $pref = UserPref::setPref($user, 'test_pref', 'test_value');

        $this->assertInstanceOf(UserPref::class, $pref);
        $this->assertEquals($user->user_id, $pref->user_id);
        $this->assertEquals('test_pref', $pref->pref);
        $this->assertEquals('test_value', $pref->value);
    }

    public function testGetPref(): void
    {
        $user = User::factory()->create();
        UserPref::setPref($user, 'test_pref', 'test_value');

        $value = UserPref::getPref($user, 'test_pref');

        $this->assertEquals('test_value', $value);
    }

    public function testGetPrefReturnsNullWhenNotSet(): void
    {
        $user = User::factory()->create();
        $value = UserPref::getPref($user, 'nonexistent_pref');

        $this->assertNull($value);
    }

    public function testGetPrefWithLoadedRelation(): void
    {
        $user = User::factory()->create();
        UserPref::setPref($user, 'test_pref', 'test_value');

        $user->load('preferences');
        $value = UserPref::getPref($user, 'test_pref');

        $this->assertEquals('test_value', $value);
    }

    public function testForgetPref(): void
    {
        $user = User::factory()->create();
        UserPref::setPref($user, 'test_pref', 'test_value');

        UserPref::forgetPref($user, 'test_pref');

        $this->assertNull(UserPref::getPref($user, 'test_pref'));
    }

    public function testSetPrefUpdatesExistingPref(): void
    {
        $user = User::factory()->create();
        UserPref::setPref($user, 'test_pref', 'original');

        UserPref::setPref($user, 'test_pref', 'updated');

        $this->assertEquals('updated', UserPref::getPref($user, 'test_pref'));
        $this->assertEquals(1, $user->preferences()->where('pref', 'test_pref')->count());
    }

    public function testArrayValueIsJsonEncoded(): void
    {
        $user = User::factory()->create();
        $arrayValue = ['key' => 'value', 'nested' => ['a', 'b']];

        UserPref::setPref($user, 'test_pref', $arrayValue);

        $this->assertEquals($arrayValue, UserPref::getPref($user, 'test_pref'));
    }

    public function testMultiplePrefsPerUser(): void
    {
        $user = User::factory()->create();
        UserPref::setPref($user, 'pref_one', 'value_one');
        UserPref::setPref($user, 'pref_two', 'value_two');

        $this->assertEquals('value_one', UserPref::getPref($user, 'pref_one'));
        $this->assertEquals('value_two', UserPref::getPref($user, 'pref_two'));
    }

    public function testUserRelationship(): void
    {
        $user = User::factory()->create();
        $pref = UserPref::setPref($user, 'test_pref', 'test_value');

        $this->assertEquals($user->user_id, $pref->user->user_id);
    }
}
