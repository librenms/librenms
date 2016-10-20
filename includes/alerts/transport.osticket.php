/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$url = $opts['url'];
$token = $opts['token'];
$protocol = array(
    'name' => 'LibreNMS',
    'email' => $_SERVER['SERVER_NAME'],
    'subject' => ($obj['name'] ? $obj['name'] . ' on ' . $obj['hostname'] : $obj['title']) ,
    'message' => strip_tags($obj['msg']) ,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'attachments' => array() ,
);
$curl = curl_init();
set_curl_proxy($curl);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Content-type' => 'application/json',
    'Expect:',
    'X-API-Key: ' . $token
));
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($protocol));
$ret = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($code != 201) {
    var_dump("osTicket returned Error, retry later");
    return false;
}

return true;
