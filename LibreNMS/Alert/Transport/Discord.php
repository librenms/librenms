<?php
/**
 * Discord.php
 *
 * LibreNMS Discord API Tranport
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
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 * @contributer f0o, sdef2
 * Thanks to F0o <f0o@devilcode.org> for creating the Slack transport which is the majority of this code.
 * Thanks to sdef2 for figuring out the differences needed to make Discord work.
 */
namespace LibreNMS\Alert\Transport;

use LibreNMS\Interfaces\Alert\Transport;

class Discord implements Transport
{
    public function deliverAlert($obj, $opts)
    {
        foreach ($opts as $tmp_api) {
            $host          = $tmp_api['url'];
            $curl          = curl_init();
            $discord_msg   = strip_tags($obj['msg']);
            $color         = ($obj['state'] == 0 ? '#00FF00' : '#FF0000');
            $data          = array(
                        'username'=>$tmp_api['username'],
                        'content' => "". $obj['title'] ."\n" . $discord_msg
                );
            $alert_message = json_encode($data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            set_curl_proxy($curl);
            curl_setopt($curl, CURLOPT_URL, $host);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message);

            $ret  = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($code != 204) {
                var_dump("API '$host' returned Error"); //FIXME: propper debuging
                var_dump("Params: " . $alert_message); //FIXME: propper debuging
                var_dump("Return: " . $ret); //FIXME: propper debuging
                return 'HTTP Status code ' . $code;
            }
        }
        return true;
    }
}
