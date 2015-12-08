<?php

if( defined('show_settings') || empty($widget_settings) ) {

    $common_output[] = '
    <form class="form-horizontal" onsubmit="widget_settings(this); return false;">
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
