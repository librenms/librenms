<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

echo '<br>';
echo '<div class="panel panel-default">';
echo '<div class="panel-heading">';
echo '<strong>Alerts</strong>  &#187; ';

echo '<span class="pagemenu-selected"></span>';
echo generate_link('Active alerts', $vars, array('section' => 'alerts'));
echo '</span>';

echo ' | ';
   
echo '<span class="pagemenu-selected"></span>';
echo generate_link('Alerts history', $vars, array('section' => 'alert-log'));
echo '</span>';

echo '</div><br>';
echo '<div style="width:99%;margin:0 auto;">';

include 'includes/html/pages/alerts.inc.php';
include 'includes/html/pages/alert-log.inc.php';

switch ($vars['section']) {
    case 'alerts':
        //$vars['fromdevice'] = true;
        include 'includes/html/pages/alerts.inc.php';
        break;
    case 'alert-log':
        //$vars['fromdevice'] = true;
        include 'includes/html/pages/alert-log.inc.php';
        break;

    default:
        echo '</div>';
        echo report_this('Unknown section '.$vars['section']);
        break;
}

echo '</div>';
