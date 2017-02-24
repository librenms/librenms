/* Copyright (C) 2015 Daniel Preussker <f0o@librenms.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * SMSEagle API Transport
 * @author Barry O'Donovan <barry@lightnet.ie>
 * @copyright 2017 Barry O'Donovan, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

$params = array(
		'login' => $opts['user'],
		'pass' => $opts['token'],
		'to'       => implode(',',$opts['to']),
		'message'  => $obj['title'],
);
$url  = 'http://'.$opts['url'].'/index.php/http_api/send_sms?'.http_build_query($params);
$curl = curl_init($url);

set_curl_proxy($curl);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$ret  = curl_exec($curl);
if (substr($ret,0,2) == "OK") {
	return true;
} else {
    return false;
}
