<?php
/*
 * LibreNMS front page graphs
 *
 * Copyright (c) 2013 Gear Consulting Pty Ltd <http://libertysys.com.au/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

require_once("includes/front/common.inc.php");

echo('<div class="right-2-col-fixed">');
echo('<div class="boxes">');

echo('<div class=box>');
require_once("includes/front/top_ports.inc.php");
echo('</div>');

/*
echo('<div class=box>');
echo('<h5>Something</h5>');
echo('<p>Next 1</p>');
echo('</div>');

echo('<div class=box>');
echo('<h5>Something else</h5>');
echo('<p>Next 2</p>');
echo('</div>');
*/

echo('</div>');
echo('</div>');

?>
