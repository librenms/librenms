/* Copyright (C) 2015 Aldemir Akpinar <aldemir.akpinar@gmail.com>
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
 * Jira API Transport
 * @author  Aldemir Akpinar <aldemir.akpinar@gmail.com>
 * @copyright 2017 Aldemir Akpinar, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

// Don't create tickets for resolutions
if($obj['severity'] == 'recovery' && $obj['msg'] != 'This is a test alert') {
    return True;
}

$device = device_by_id_cache($obj['device_id']); // for event logging

$username = $opts['username'];
$password = $opts['password'];
$prjkey = $opts['prjkey'];
$issuetype = $opts['issuetype'];
$details = "Librenms alert for: ".$obj['hostname'];
$description = $obj['msg'];
$url  = $opts['url'].'/rest/api/latest/issue';
$curl = curl_init();

$data = array("project" => array("key" => $prjkey),
            "summary" => $details,
            "description" => $description,
            "issuetype" => array("name" => $issuetype));
$postdata = array("fields" => $data);
$datastring = json_encode($postdata);

set_curl_proxy($curl);

$headers = array('Accept: application/json', 'Content-Type: application/json');

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_VERBOSE, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);

$ret  = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if( $code == 200 ) {
    $jiraout = json_decode($ret, true);
    d_echo("Created jira issue ".$jiraout['key']." for ".$device);
    return true;
} else {
    d_echo("Jira connection error: ".serialize($ret));
    return false;
}
