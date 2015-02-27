/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * API Transport
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

foreach( $opts as $method=>$apis ) {
	foreach( $apis as $tmp_api ) {
		list($host, $api) = explode("?",$tmp_api['url'],2);
		foreach( $obj as $k=>$v ) {
			$api = str_replace("%".$k,$method == "get" ? urlencode($v) : $v, $api);
		}
		$curl = curl_init();
                $data = array(
				'text' => $obj['msg'],
				'channel' => $tmp_api['channel'],
				'username' => $tmp_api['username'],
				'icon_url' => $tmp_api['icon_url'],
				'icon_emoji' => $tmp_api['icon_emoji'],
		);
                $alert_message = "payload=" . json_encode($data);
		curl_setopt($curl, CURLOPT_URL, ($method == "get" ? $host."?".$api : $host) );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POST,true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message );
		$ret = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if( $code != 200 ) {
			var_dump("API '$host' returnd Error"); //FIXME: propper debuging
			var_dump("Params: ".$api); //FIXME: propper debuging
			var_dump("Return: ".$ret); //FIXME: propper debuging
			return false;
		}
	}
}
return true;
