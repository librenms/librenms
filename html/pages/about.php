<div style="margin: 10px;">
  <h3>ObserverNMS <?php echo($config['version']);?></h3>

    <div style="float: right; padding: 5px;">
    <?php print_optionbar_start(NULL); ?>
    <h3>License</h3>
    <pre>ObserverNMS Network Management and Monitoring System
Copyright (C) 2006-2010 Adam Armstrong
 
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
     </div>

    <?php

    $observer_version = $config['version'];
    if (file_exists('.svn/entries'))
    {
      $svn = File('.svn/entries');
      $observer_version .='-SVN r' . trim($svn[3]);
      unset($svn);
    }


$apache_version = str_replace("Apache/", "", $_SERVER['SERVER_SOFTWARE']);

$php_version = phpversion();

$t=mysql_query("select version() as ve");
echo mysql_error();
$r=mysql_fetch_object($t);
$mysql_version = $r->ve;

$netsnmp_version = shell_exec($config['snmpget'] . " --version");


echo("

<table width=250 cellpadding=5 cellspacing=0 bgcolor=#e5e5e5 border=2 bordercolor=#ffffff>
<tr><td>ObserverNMS</td><td>$observer_version</td></tr>
<tr><td>Apache</td><td>$apache_version</td></tr>
<tr><td>PHP</td><td>$php_version</td></tr>
<tr><td>MySQL</td><td>$mysql_version</td></tr>
</table>

");



    ?>

  <h5>ObserverNMS is an autodiscovering PHP/MySQL based network monitoring system.</h5>

  <p><a href="http://www.observernms.org">Website</a> | 
     <a href="http://www.observernms.org/wiki/">Support Wiki</a> | 
     <a href="http://www.observernms.org/forum/">Forum</a> | 
     <a href="http://www.observernms.org/bugs/">Bugtracker</a> | 
     <a href="http://www.observernms.org/pmwiki.php/Main/MailingLists">Mailing List</a> | 
     <a href="http://http://twitter.com/observernms">Twitter</a></p>


  <h4>The Team</h4>

    <img src="images/icons/flags/gb.png"> <strong>Adam Armstrong</strong> Project Founder<br />
    <img src="images/icons/flags/be.png"> <strong>Geert Hauwaerts</strong> Developer<br />
    <img src="images/icons/flags/be.png"> <strong>Tom Laermans</strong> Developer<br />
  </ul>

  <h4>Acknowledgements</h4>

    <b>Stu Nicholls</b> Dropdown menu CSS code. <br />
    <b>Mark James</b> Silk Iconset. <br />
    <b>Erik Bosrup</b> Overlib Library. <br />
    <b>Jonathan De Graeve</b> SNMP code improvements. <br />
    <b>Xiaochi Jin</b> Logo design. <br />
    <b>Bruno Pramont</b> Collectd code. <br />


</div>

