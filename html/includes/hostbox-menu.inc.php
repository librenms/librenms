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

echo('<td><div class="collapse navbar-collapse" id="navHeaderCollapse">
          <div class="btn-group">
              <button type="button" class="btn btn-sm btn-danger dropdown-toggle" data-toggle="dropdown" aria-expand="false">Action <span class="caret"></span></button>
              <ul class="dropdown-menu dropdown-menu-right">');
    if (device_permitted($device['device_id'])) {
        echo '<li><a href="'.generate_device_url($device).'"> <img src="images/16/server.png" border="0" align="absmiddle" /> View device</a></li>';
        echo '<li><a href="'.generate_device_url($device, array('tab' => 'alerts')).'"> <img src="images/16/bell.png" border="0" align="absmiddle" /> View alerts</a></li>';
        if ($_SESSION['userlevel'] >= "7") {
            echo '<li><a href="'.generate_device_url($device, array('tab' => 'edit')).'"> <img src="images/16/wrench.png" border="0" align="absmiddle" /> Edit device</a></li>';
        }
    }
echo('
              </ul>
          </div>
      </div></td>');

?>
