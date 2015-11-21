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
 * Clickatell REST-API Transport
 * @author f0o <f0o@librenms.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

$data = array("api_id" => $opts['api_id'], "user" => $opts['user'], "password" => $opts['password'], "to" => implode(',',$opts['to']), "text" => $obj['title']);
if (!empty($opts['from'])) {
    $data['from'] = $opts['from'];
}
$url  = 'https://api.clickatell.com/http/sendmsg?'.http_build_query($data);
$curl = curl_init($url);

curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$ret  = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if( $code > 200 ) {
    if( $debug ) {
        var_dump($ret);
    }
    return false;
}
return true;
