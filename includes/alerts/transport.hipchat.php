/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>, Tyler Christiansen <code@tylerc.me>
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

/*
 * API Transport
 * @author Tyler Christiansen <code@tylerc.me>
 * @copyright 2014 Daniel Preussker, Tyler Christiansen, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

foreach($opts as $option) {
  $url = $option['url'];
  foreach($obj as $key=>$value) {
    $api = str_replace("%".$key, $method == "get" ? urlencode($value) : $value, $api);
  }
  $curl = curl_init();
  $data = array(
    "message" => $obj["msg"],
    "room_id" => $option["room_id"],
    "from" => $option["from"],
    "color" => $option["color"],
    "notify" => $option["notify"],
    "message_format" => $option["message_format"]
  );
  // Sane default of making the message color green if the message indicates
  // that the alert recovered.
  if(strpos($data["message"], "recovered")) { $data["color"] = "green"; }
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
  $ret = curl_exec($curl);

  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  if($code != 200) {
    var_dump("API '$url' returnd Error");
    var_dump("Params: " . $message);
    var_dump("Return: " . $ret);
    return false;
  }
}
return true;
