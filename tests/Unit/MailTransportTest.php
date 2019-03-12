<?php
/**
 * MailTransportTest.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use App\Models\User;
use LibreNMS\Alert\Transport\Mail;
use LibreNMS\Config;
use LibreNMS\Tests\TestCase;

class MailTransportTest extends TestCase
{
    public function testUsersForFaults()
    {
        $admin = factory(User::class)->state('admin')->create();
        $read = factory(User::class)->state('read')->create();
        $normal = factory(User::class, 3)->create();

        $devices = factory(Device::class, 5)->create();


        $mail = new Mail(null);

        Config::set('alert.admins', true);
        Config::set('alert.globals', false);
        Config::set('alert.users', false);

        $faults = collect($devices);
        $contacts = $mail->getUsersForFaults();
    }

    public function testsSendsToAdmin()
    {
        $mock = \Mockery::mock('\LibreNMS\Alert\Transport\Mail[getPermissions,getUsersForFaults,sendMail]');
    }
}
