<div style="margin: 10px;">
  <h3>ObserverNMS</h3>

    <div style="float: right; padding: 5px;">
    <?php print_optionbar_start(NULL); ?>
    <h3>License</h3>
    <pre>ObserverNMS Network Management and Monitoring System
Copyright (C) 2010 Adam Armstrong
 
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

  <?php echo('<h4>v'. $config['version']);

    #if (file_exists('.svn/entries'))
    #{
    #  $svn = File('.svn/entries');
    #  echo '-SVN r' . trim($svn[3]);
    #  unset($svn);
    #}

    echo('</h4>');
    ?>

  <i>ObserverNMS is an autodiscovering PHP/MySQL based network monitoring system.</i>

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

