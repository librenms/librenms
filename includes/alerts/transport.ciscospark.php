/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$token = $opts['token'];
$roomId = $opts['roomid'];
$text = strip_tags($obj['msg']);
$data = array(
    'roomId' => $roomId,
    'text' => $text
);
$curl = curl_init();
set_curl_proxy($curl);
curl_setopt($curl, CURLOPT_URL, 'https://api.ciscospark.com/v1/messages');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Content-type' => 'application/json',
    'Expect:',
    'Authorization: Bearer '.$token
));
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
$ret = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($code != 200) {
    var_dump("Cisco Spark returned Error, retry later");
    return false;
}

return true;
