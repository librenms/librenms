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

namespace LibreNMS\Tests\Feature\Transports;

use LibreNMS\Alert\Transport\Mail;
use LibreNMS\Config;
use LibreNMS\Tests\LaravelTestCase;
use Mockery\Mock;

class MailTransportTest extends LaravelTestCase
{
    public function testEmpty()
    {
        $this->assertEquals(
            "<strong>You must provide at least one recipient email address.</strong><br />\n",
            (new Mail())->deliverAlert(null, $this->buildTransports())
        );
    }

    public function testGlobalSend()
    {

        /** @var Mock $mail */
        $mail = $this->mock(\LibreNMS\Alert\Transport\Mail::class);
        $mail->makePartial()->shouldReceive('sendMail')->once()->with(['test@test.com' => ''], 'Title', 'Message', false);

//        Config::set('email_from', 'test@test.com');
        Config::set('alert.default_mail', 'test@test.com');
        Config::set('alert.default_only', true);
        $res = $mail->deliverAlert($this->buildObj(), $this->buildTransports());
//        dd($res);
    }


    private function buildObj($device_id = null, $faults = [], $title = 'Title', $msg = 'Message')
    {
        return [
            'device_id' => $device_id,
            'title' => $title,
            'msg' => $msg,
            'faults' => $faults,
        ];
    }

    private function buildTransports()
    {
        return ['transports' => collect()];
    }
}
