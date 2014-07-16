<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($_SESSION['userlevel'] == '10')
{

?>

<div class="row">
  <div class="col-sm-9">
    <a name="intro"></a>
    <h3>Introduction</h3>
    <p>The API is designed to enable you to interact with your <?php echo $config['project_name'];?> installtion from other systems, monitoring systems, apps or websites using any programming language that can make a web request and both send and receive json data. This documentation will provide you the methods, accepted parameters and responses from the API.</p>
    <a name="tokens"></a>
    <h3>Token authentication</h3>
    <p>Authentication against the API is done by tokens which are assigned to a user account. You can view and create tokens using the <a href="api-access">API access</a> link within the System > API menu.</p><br />
    <p>To send the token to the API you need to do this by using the X-Auth-Token within the header. As example if your API token was 91c60e737e342c205be5bba8e2954d27 then you would send the following X-Auth-Token: 91c60e737e342c205be5bba8e2954d27. As an example using curl within PHP you would do something like this:</p>
    <code>curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Auth-Token: 91c60e737e342c205be5bba8e2954d27'));</code><br />
    <a name="responses"></a>
    <h3>API Responses</h3>
    <p>The following responses are standard across each request type except for where an image would be returned directly, the response will be output in json format:</p>
    <strong>status</strong><br />
    <ul class="list-unstyled">
      <li>ok = The request was successful</li>
      <li>error = The request failed</li>
    </ul>
    <strong>message</strong><br />
    <p>This will contain the reason for the success or failure of the request.</p><br />
    <p>As an example to get you started quickly, here we use the API to request to list all devices:</p>
    <pre>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" "https://librenms.example.com/api/v1/list/devices"
{ 
  "status": "ok", 
  "devices": [  
  {
   "device_id": "4",
   "hostname": "example.co.uk",
   "sysName": "example.co.uk",
   "community": "public",
   "authlevel": null,
   "authname": null,
   "authpass": null,
   "authalgo": null,
   "cryptopass": null,
   "cryptoalgo": null,
   "snmpver": "v1",
   "port": "161",
   "transport": "udp",
   "timeout": null,
   "retries": null,
   "bgpLocalAs": null,
   "sysObjectID": null,
   "sysDescr": "Linux example.co.uk 2.6.18-238.19.1.el5 #1 SMP Fri Jul 15 07:31:24 EDT 2011 x86_64",
   "sysContact": "root@localhost",
   "version": "2.6.18-238.19.1.el5",
   "hardware": "Generic x86 64-bit",
   "features": null,
   "location": "Unknown",
   "os": "linux",
   "status": "1",
   "ignore": "0",
   "disabled": "0",
   "uptime": "90741309",
   "agent_uptime": "0",
   "last_polled": "2014-07-16 17:40:04",
   "last_polled_timetaken": "2.16",
   "last_discovered_timetaken": "3.15",
   "last_discovered": "2014-07-16 12:33:44",
   "purpose": "",
   "type": "server",
   "serial": null,
   "icon": null
  },
  {
   "device_id": "24",
   "hostname": "localhost",
   "sysName": "testing",
   "community": "public",
   "authlevel": "noAuthNoPriv",
   "authname": "",
   "authpass": "",
   "authalgo": "MD5",
   "cryptopass": "",
   "cryptoalgo": "AES",
   "snmpver": "v1",
   "port": "161",
   "transport": "udp",
   "timeout": null,
   "retries": null,
   "bgpLocalAs": null,
   "sysObjectID": null,
   "sysDescr": "Linux testing 2.6.32-431.el6.x86_64 #1 SMP Fri Nov 22 03:15:09 UTC 2013 x86_64",
   "sysContact": "root@localhost",
   "version": "2.6.32-431.el6.x86_64",
   "hardware": "Generic x86 64-bit",
   "features": null,
   "location": "Unknown",
   "os": "linux",
   "status": "1",
   "ignore": "0",
   "disabled": "0",
   "uptime": "19355163",
   "agent_uptime": "0",
   "last_polled": "2014-07-16 17:40:04",
   "last_polled_timetaken": "1.74",
   "last_discovered_timetaken": "1.28",
   "last_discovered": "2014-07-16 12:33:25",
   "purpose": null,
   "type": "server",
   "serial": null,
   "icon": null
  }
 ]
}
    </pre>
    <a name="request-types"></a>
    <h3>API request types</h3>
    <p>The following request types are currently used:</p>
    <ul class="list-unstyled">
      <li><strong>GET</strong> - This is used for the retrieval of information or images.</li>
      <li><strong>POST</strong> - This is used for adding new items such as devices.</li>
      <li><strong>PUT</strong> - This is used to update existing items.</li>
      <li><strong>DELETE</strong> - This is used to remove items.</li>
    </ul>
    <a name="requests"></a>
    <h3>Available requests</h3>
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-condensed">
        <tr>
          <th></th>
          <th>Version</th>
          <th>Path (required)</th>
          <th>Path (additional)</th>
          <th>Variables / JSON Data</th>
          <th>Returns</th>
        </tr>
        <tr class="success">
          <td colspan="6"><a name="port_graphs"></a><strong>Port Graphs</strong></td>
        </tr>
        <tr>
          <td>/api</td>
          <td>/v1</td>
          <td>/get/graph/port/id/$id</td>
          <td>/$type/$width/$height/$from/$to</td>
          <td>
            <ul class="list-unstyled">
              <li>$id = the ID of the port</li>
              <li>$type = the type of graph for the port (port_bits,port_upkts)</li>
              <li>$width = the width of the graph to be returned</li>
              <li>$height = the height of the graph to be returned</li>
              <li>$from = the from date/time of the graph (unix timestamp)</li>
              <li>$to = the to date/time of the graph (unix timestamp)</li>
            </ul>
          </td>
          <td>
            PNG Image
          </td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/port/id/1" > /tmp/graph.png</code></td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/port/id/1/port_bits/1024/768/1405457456/1405543856" > /tmp/graph.png</code></td>
        </tr>
        <tr>
          <td>/api</td>
          <td>/v1</td>
          <td>/get/graph/port/device/$device_id/$ifName</td>
          <td>/$type/$width/$height/$from/$to</td>
          <td>
            <ul class="list-unstyled">
              <li>$device_id = the ID of the device</li>
              <li>$ifName = the ifName of an interface</li>
              <li>$type = the type of graph for the port (port_bits,port_upkts)</li>
              <li>$width = the width of the graph to be returned</li>
              <li>$height = the height of the graph to be returned</li>
              <li>$from = the from date/time of the graph (unix timestamp)</li>
              <li>$to = the to date/time of the graph (unix timestamp)</li>
            </ul>
          </td>
          <td>
            PNG Image
          </td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/port/device/1/eth0" > /tmp/graph.png</code></td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/port/device/1/eth0/port_bits/1024/768/1405457456/1405543856" > /tmp/graph.png</code></td>
        </tr>
        <tr>
          <td>/api</td>
          <td>/v1</td>
          <td>/get/graph/port/hostname/$hostname/$ifName</td>
          <td>/$type/$width/$height/$from/$to</td>
          <td>
            <ul class="list-unstyled">
              <li>$hostname = the hostname of the device</li>
              <li>$ifName = the ifName of an interface</li>
              <li>$type = the type of graph for the port (port_bits,port_upkts)</li>
              <li>$width = the width of the graph to be returned</li>
              <li>$height = the height of the graph to be returned</li>
              <li>$from = the from date/time of the graph (unix timestamp)</li>
              <li>$to = the to date/time of the graph (unix timestamp)</li>
            </ul>
          </td>
          <td>
            PNG Image
          </td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/port/hostname/localhost/eth0" > /tmp/graph.png</code></td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/port/hostname/localhost/eth0/port_bits/1024/768/1405457456/1405543856" > /tmp/graph.png</code></td>
        </tr>
        <tr class="success">
          <td colspan="6"><a name="general_graphs"></a><strong>General Graphs</strong></td>
        </tr>
        <tr>
          <td>/api</td>
          <td>/v1</td>
          <td>/get/graph/general/device/$device_id/$type</td>
          <td>/$width/$height/$from/$to</td>
          <td>
            <ul class="list-unstyled">
              <li>$device_id = the ID of the device</li>
              <li>$type = the type of graph for the device (device_processor,device_storage)</li>
              <li>$width = the width of the graph to be returned</li>
              <li>$height = the height of the graph to be returned</li>
              <li>$from = the from date/time of the graph (unix timestamp)</li>
              <li>$to = the to date/time of the graph (unix timestamp)</li>
            </ul>
          </td>
          <td>
            PNG Image
          </td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/general/device/1/device_processor" > /tmp/graph.png</code></td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/general/device/1/device_processor/1024/768/1405457456/1405543856" > /tmp/graph.png</code></td>
        </tr>
        <tr>
          <td>/api</td>
          <td>/v1</td>
          <td>/get/graph/general/hostname/$hostname/$type</td>
          <td>/$width/$height/$from/$to</td>
          <td>
            <ul class="list-unstyled">
              <li>$hostname = the hostname of the device</li>
              <li>$type = the type of graph for the device (device_processor,device_storage)</li>
              <li>$width = the width of the graph to be returned</li>
              <li>$height = the height of the graph to be returned</li>
              <li>$from = the from date/time of the graph (unix timestamp)</li>
              <li>$to = the to date/time of the graph (unix timestamp)</li>
            </ul>
          </td>
          <td>
            PNG Image
          </td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/general/hostname/localhost/device_processor" > /tmp/graph.png</code></td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/graph/general/hostname/localhost/device_processor/1024/768/1405457456/1405543856" > /tmp/graph.png</code></td>
        </tr>
        <tr class="success">
          <td colspan="6"><a name="port_stats"></a><strong>Port Stats</strong></td>
        </tr>
        <tr>
          <td>/api</td>
          <td>/v1</td>
          <td>/get/stats/port/id/$port_id</td>
          <td></td>
          <td>
            <ul class="list-unstyled">
              <li>$port_id = the ID of the port</li>
            </ul>
          </td>
          <td>
            JSON
          </td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/stats/port/id/1"</code></td>
        </tr>
        <tr>
          <td>/api</td>
          <td>/v1</td>
          <td>/get/stats/port/device/$device_id/$ifName</td>
          <td></td>
          <td>
            <ul class="list-unstyled">
              <li>$device_id = the ID of the device</li>
              <li>$ifName = the ifName of the port</li>
            </ul>
          </td>
          <td>
            JSON
          </td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/get/stats/port/device/1/eth0"</code></td>
        </tr>
        <tr class="success">
          <td colspan="6"><a name="list"></a><strong>List</strong></td>
        </tr>
        <tr>
          <td>/api</td>
          <td>/v1</td>
          <td>/list/devices</td>
          <td>/$order/$type</td>
          <td>
            <ul class="list-unstyled">
              <li>$order = the name of the column to order by</li>
              <li>$type = this is the device status (all, ignored, up, down, disabled)</li>
            </ul>
          </td>
          <td>
            JSON
          </td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/list/devices"</code></td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/list/devices/hostname/all"</code></td>
        </tr>
        <tr class="success">
          <td colspan="6"><a name="add"></a><strong>Add</strong></td>
        </tr>
        <tr>
          <td>/api</td>
          <td>/v1</td>
          <td>/add/device</td>
          <td></td>
          <td>
            <ul class="list-unstyled">
              <li>hostname = the hostname to be added</li>
              <li>version = the version of snmp to use</li>
              <li>community = the community to use</li>
              <li>port = the port to use</li>
              <li>transport = the transport to use</li>
              <li>authlevel = the auth level to use for v3</li>
              <li>authname = the auth name to use for v3</li>
              <li>authpass = the auth pass to use for v3</li>
              <li>authalog = the auth algorythm to use for v3</li>
              <li>cryptopass = the crypto pass to use for v3</li>
              <li>cryptoalgo = the crytpo algo to use for v3</li>
            </ul>
          </td>
          <td>
            JSON
          </td>
        </tr>
        <tr>
          <td colspan="6"><code>curl -X POST -d '{"hostname":"localhost.localdomain","version":"v1","community":"public"}' \<br />-H "Content-Type: application/json" -H "X-Auth-Token: 91c60e737e342c205be5bba8e2954d27" \<br/> "https://librenms.example.com/api/v1/add/device"</code></td>
        </tr>
      </table>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="bs-docs-sidebar hidden-print hidden-xs hidden-sm affix" id="sidebar" role="complementary">
      <ul class="nav bs-docs-sidenav">
        <li><a href="api-docs/#intro">Introduction</a></li>
        <li><a href="api-docs/#tokens">Token authentication</a></li>
        <li><a href="api-docs/#responses">API responses</a></li>
        <li><a href="api-docs/#request-types">API request types</a></li>
        <li>
          <a href="api-docs/#requests">Available requests</a>
          <ul class="nav">
            <li><a href="api-docs/#port_graphs">Port Graphs</a></li>
            <li><a href="api-docs/#general_graphs">General Graphs</a></li>
            <li><a href="api-docs/#port_stats">Port Stats</a></li>
            <li><a href="api-docs/#list">List</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</div>
<script>
$('#sidebar').affix({
  offset: {
    top: $('header').height()
  }
}); 
</script>
<?php

} else {
  include("includes/error-no-perm.inc.php");
}

?>
