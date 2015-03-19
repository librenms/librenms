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

echo('<ul class="nav nav-tabs">');

$poll_tabs = array(array("name" => 'Pollers', 'icon' => 'clock_link'),
             array("name" => 'Groups', 'icon' => 'clock_add'));

foreach ($poll_tabs as $tab) {
    echo('
            <li>
                <a href="/pollers/tab='. lcfirst($tab["name"]) .'">
                   <img src="images/16/'. $tab["icon"] .'.png" align="absmiddle" border="0"> ' . $tab["name"] . '
                </a>
            </li>');

}

echo ('</ul>');

if (isset($vars['tab'])) {
    require_once "pages/pollers/".mres($vars['tab']).".inc.php";
}
