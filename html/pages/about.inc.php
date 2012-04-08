<div style="margin: 10px;">
  <h3>Observium <?php

echo($config['version']);

#if (file_exists('.svn/entries'))
#{
#  $svn = File('.svn/entries');
#  echo('-SVN r' . trim($svn[3]));
#  unset($svn);
#}

  ?></h3>
  <div style="float: right; padding: 0px; width: 49%">
<?php print_optionbar_start(NULL); ?>
    <h3>License</h3>
    <pre>Observium Network Management and Monitoring System
Copyright (C) 2006-<?php echo date("Y"); ?> Adam Armstrong

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.</pre>
<?php print_optionbar_end(); ?>

    &nbsp;

<?php print_optionbar_start(NULL); ?>

    <h3>Statistics</h3>

<?php
$stat_devices = dbFetchCell("SELECT COUNT(device_id) FROM `devices`");
$stat_ports = dbFetchCell("SELECT COUNT(interface_id) FROM `ports`");
$stat_syslog = dbFetchCell("SELECT COUNT(*) FROM `syslog`");
$stat_events = dbFetchCell("SELECT COUNT(event_id) FROM `eventlog`");
$stat_apps = dbFetchCell("SELECT COUNT(app_id) FROM `applications`");
$stat_services = dbFetchCell("SELECT COUNT(service_id) FROM `services`");
$stat_storage = dbFetchCell("SELECT COUNT(storage_id) FROM `storage`");
$stat_diskio = dbFetchCell("SELECT COUNT(diskio_id) FROM `ucd_diskio`");
$stat_processors = dbFetchCell("SELECT COUNT(processor_id) FROM `processors`");
$stat_memory = dbFetchCell("SELECT COUNT(mempool_id) FROM `mempools`");
$stat_sensors = dbFetchCell("SELECT COUNT(sensor_id) FROM `sensors`");
$stat_toner = dbFetchCell("SELECT COUNT(toner_id) FROM `toner`");
$stat_hrdev = dbFetchCell("SELECT COUNT(hrDevice_id) FROM `hrDevice`");
$stat_entphys = dbFetchCell("SELECT COUNT(entPhysical_id) FROM `entPhysical`");

$stat_ipv4_addy = dbFetchCell("SELECT COUNT(ipv4_address_id) FROM `ipv4_addresses`");
$stat_ipv4_nets = dbFetchCell("SELECT COUNT(ipv4_network_id) FROM `ipv4_networks`");
$stat_ipv6_addy = dbFetchCell("SELECT COUNT(ipv6_address_id) FROM `ipv6_addresses`");
$stat_ipv6_nets = dbFetchCell("SELECT COUNT(ipv6_network_id) FROM `ipv6_networks`");

$stat_pw = dbFetchCell("SELECT COUNT(pseudowire_id) FROM `pseudowires`");
$stat_vrf = dbFetchCell("SELECT COUNT(vrf_id) FROM `vrfs`");
$stat_vlans = dbFetchCell("SELECT COUNT(vlan_id) FROM `vlans`");

echo("
    <table width=95% cellpadding=5 cellspacing=0>
      <tr>
        <td width=45%><img src='images/icons/device.png' class='optionicon'> <b>Devices</b></td><td align=right>$stat_devices</td>
        <td width=45%><img src='images/icons/port.png' class='optionicon'> <b>Ports</b></td><td align=right>$stat_ports</td>
      </tr>
      <tr>
        <td><img src='images/icons/ipv4.png'  class='optionicon'> <b>IPv4 Addresses<b></td><td align=right>$stat_ipv4_addy</td>
        <td><img src='images/icons/ipv4.png' class='optionicon'> <b>IPv4 Networks</b></td><td align=right>$stat_ipv4_nets</td>
      </tr>
      <tr>
        <td><img src='images/icons/ipv6.png'  class='optionicon'> <b>IPv6 Addresses<b></td><td align=right>$stat_ipv6_addy</td>
        <td><img src='images/icons/ipv6.png' class='optionicon'> <b>IPv6 Networks</b></td><td align=right>$stat_ipv6_nets</td>
       </tr>
     <tr>
        <td><img src='images/icons/services.png'  class='optionicon'> <b>Services<b></td><td align=right>$stat_services</td>
        <td><img src='images/icons/apps.png' class='optionicon'> <b>Applications</b></td><td align=right>$stat_apps</td>
      </tr>
      <tr>
        <td ><img src='images/icons/processor.png' class='optionicon'> <b>Processors</b></td><td align=right>$stat_processors</td>
        <td><img src='images/icons/memory.png' class='optionicon'> <b>Memory</b></td><td align=right>$stat_memory</td>
      </tr>
      <tr>
        <td><img src='images/icons/storage.png' class='optionicon'> <b>Storage</b></td><td align=right>$stat_storage</td>
        <td><img src='images/icons/diskio.png' class='optionicon'> <b>Disk I/O</b></td><td align=right>$stat_diskio</td>
      </tr>
      <tr>
        <td><img src='images/icons/inventory.png' class='optionicon'> <b>HR-MIB</b></td><td align=right>$stat_hrdev</td>
        <td><img src='images/icons/inventory.png' class='optionicon'> <b>Entity-MIB</b></td><td align=right>$stat_entphys</td>
      </tr>
      <tr>
        <td ><img src='images/icons/syslog.png' class='optionicon'> <b>Syslog Entries</b></td><td align=right>$stat_syslog</td>
        <td><img src='images/icons/eventlog.png' class='optionicon'> <b>Eventlog Entries</b></td><td align=right>$stat_events</td>
      </tr>
      <tr>
        <td ><img src='images/icons/sensors.png' class='optionicon'> <b>Sensors</b></td><td align=right>$stat_sensors</td>
        <td><img src='images/icons/toner.png' class='optionicon'> <b>Toner</b></td><td align=right>$stat_toner</td>
      </tr>
    </table>
");

print_optionbar_end(); ?>
  </div>

  <div style="float: left; padding: 0px; width: 49%">
<?php

$Observium_version = $config['version'];
#if (file_exists('.svn/entries'))
#{
#  $svn = File('.svn/entries');
#  $Observium_version .='-SVN r' . trim($svn[3]);
#  unset($svn);
#}

$apache_version = str_replace("Apache/", "", $_SERVER['SERVER_SOFTWARE']);

$php_version = phpversion();

$mysql_version = dbFetchCell("SELECT version()");

$netsnmp_version = shell_exec($config['snmpget'] . " --version");

print_optionbar_start(NULL);

echo("
    <h3>Versions</h3>
    <table width=100% cellpadding=3 cellspacing=0 border=0>
      <tr valign=top><td width=150><b>Observium</b></td><td>$Observium_version</td></tr>
      <tr valign=top><td><b>Apache</b></td><td>$apache_version</td></tr>
      <tr valign=top><td><b>PHP</b></td><td>$php_version</td></tr>
      <tr valign=top><td><b>MySQL</b></td><td>$mysql_version</td></tr>
    </table>
");

print_optionbar_end();

?>

    <h5>Observium is an autodiscovering PHP/MySQL based network monitoring system.</h5>

    <p>
      <a href="http://www.observium.org">Website</a> |
      <a href="http://www.observium.org/wiki/">Support Wiki</a> |
      <a href="http://www.observium.org/forum/">Forum</a> |
      <a href="http://www.observium.org/bugs/">Bugtracker</a> |
      <a href="http://www.observium.org/wiki/Mailing_Lists">Mailing List</a> |
<?php /*
      <a href="http://twitter.com/Observium">Twitter</a> |
*/ ?>
      <a href="http://www.facebook.com/pages/Observer/128354461353">Facebook</a>
    </p>

    <h3>Observium is a Free, Open project. Please donate to support continued development.</h3>

  <div style="margin-top:10px;">
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="hosted_button_id" value="W2ZJ3JRZR72Z6">
    <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal . The safer, easier way to pay online.">
    <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
    </form>
  </div>

    <h4>The Team</h4>

    <img src="images/icons/flags/gb.png"> <strong>Adam Armstrong</strong> Project Founder<br />
    <img src="images/icons/flags/be.png"> <strong>Geert Hauwaerts</strong> Developer<br />
    <img src="images/icons/flags/be.png"> <strong>Tom Laermans</strong> Developer<br />

    <h4>Acknowledgements</h4>

    <b>Stu Nicholls</b> Dropdown menu CSS code. <br />
    <b>Mark James</b> Silk Iconset. <br />
    <b>Erik Bosrup</b> Overlib Library. <br />
    <b>Jonathan De Graeve</b> SNMP code improvements. <br />
    <b>Xiaochi Jin</b> Logo design. <br />
    <b>Bruno Pramont</b> Collectd code. <br />
    <b>Dennis de Houx</b> Application monitors for PowerDNS, Shoutcast, NTPD (Client, Server). <br />

  </div>
</div>
