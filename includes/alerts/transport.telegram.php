/**
* transport-telegram.inc.php
*
* LibreNMS Telegram alerting transport
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
* @copyright  2017 Neil Lathwood
* @author     Neil Lathwood <neil@lathwood.co.uk>
*/

foreach ($opts as $chat_id => $data) {
    $curl = curl_init();
    set_curl_proxy($curl);
    $text = urlencode($obj['msg']);
print_r($data);
    curl_setopt($curl, CURLOPT_URL, ("https://api.telegram.org/bot{$data['token']}/sendMessage?chat_id={$data['chat_id']}&text=$text") );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if( $code != 200 ) {
        var_dump("API '$host' returned Error"); //FIXME: propper debuging
        var_dump("Params: ".$api); //FIXME: propper debuging
        var_dump("Return: ".$ret); //FIXME: propper debuging
        return 'HTTP Status code '.$code;
    }
}
return true;
