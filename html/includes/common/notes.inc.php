<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2015 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if( defined('show_settings') || empty($widget_settings) ) {

    $common_output[] = '
    <form class="form-horizontal" onsubmit="widget_settings(this); return false;">
        <div class="form-group">
            <div class="col-sm-12">
                html is supported here. If you want just text then wrap in &lt;pre&gt;&lt;/pre&gt;
            </div>
        </div>
        <div class="form-group">
            <label for="'.$unique_id.'_notes" class="col-sm-1" control-label"></label>
            <div class="col-sm-11">
                <textarea name="notes" id="'.$unique_id.'_notes" rows="3" class="form-control">'.htmlspecialchars($widget_settings['notes']).'</textarea>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1">
                <button type="submit" class="btn btn-sm btn-primary">Set</button>
            </div>
        </div>
    </form>';
}
else {
    $common_output[] = stripslashes(nl2br($widget_settings['notes']));
}
