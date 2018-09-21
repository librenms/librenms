<form class="form" onsubmit="widget_settings(this); return false;">
    <div class="form-group">
        <div class="col-sm-4">
            <label for="title" class="control-label availability-map-widget-header">Widget title</label>
        </div>
        <div class="col-sm-6">
            <input type="text" class="form-control" name="title" placeholder="Custom title for widget" value="'.htmlspecialchars($widget_settings['title']).'">
        </div>
    </div>';

    if ($config['webui']['availability_map_compact'] === false) {
    $common_output[] = '
    <div class="form-group">
        <div class="col-sm-4">
            <label for="color_only_select" class="control-label availability-map-widget-header">Uniform Tiles</label>
        </div>
        <div class="col-sm-6">
            <select class="form-control" name="color_only_select">
                <option value="1"' . ($widget_settings['color_only_select'] == 1 ? ' selected' : '')  . ' >yes</option>
                <option value="0"' . ($widget_settings['color_only_select'] == 1 ? '' : ' selected')  . ' >no</option>
            </select>
        </div>
    </div>
    ';
    }

    if ($config['webui']['availability_map_compact'] == 1) {
    $common_output[] = '
    <div class="form-group">
        <div class="col-sm-4">
            <label for="tile_size" class="control-label availability-map-widget-header">Tile size</label>
        </div>
        <div class="col-sm-6">
            <input type="text" class="form-control" name="tile_size" value="'.$compact_tile.'">
        </div>
    </div>';
    }

    if ($show_disabled_ignored == 1) {
    $selected_yes = 'selected';
    $selected_no = '';
    } else {
    $selected_yes = '';
    $selected_no = 'selected';
    }

    $common_output[] = '
    <div class="form-group">
        <div class="col-sm-4">
            <label for="show_disabled_and_ignored" class="control-label availability-map-widget-header">Disabled/ignored</label>
        </div>
        <div class="col-sm-6">
            <select class="form-control" name="show_disabled_and_ignored">
                <option value="1" '.$selected_yes.'>yes</option>
                <option value="0" '.$selected_no.'>no</option>
            </select>
        </div>
    </div>';

    $common_output[] = '
    <div class ="form-group">
        <div class="col-sm-4">
            <label for="mode_select" class="control-lable availability-map-widget-header">Mode</label>
        </div>
        <div class="col-sm-6">
            <select name="mode_select" class="form-control">';

                if ($config['show_services'] == 0) {
                $common_output[] = '<option value="0" selected>only devices</option>';
                } else {
                foreach ($select_modes as $mode_select => $option) {
                if ($mode_select == $widget_settings["mode_select"]) {
                $selected = 'selected';
                } else {
                $selected = '';
                }
                $common_output[] = '<option value="' . $mode_select . '" ' . $selected . '>' . $option . '</option>';
                }
                }
                $common_output[] = '
            </select>
        </div>
    </div>';

    if ($config['webui']['availability_map_compact'] == 1) {
    $common_outputp[] = '
    <div class="form-group">
        <div class="col-sm-4">
            <label for="tile_size" class="control-label availability-map-widget-header">Tile width</label>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode >= 48 && event.charCode <= 57" name="tile_size" placeholder="Tile side in px" value="'.$compact_tile.'">
        </div>
    </div>
    ';
    }


    $common_output[] = '
    <br style="clear:both;">
    <div class="form-group">
        <div class="col-sm-2">
            <button type="submit" class="btn btn-default">Set</button>
        </div>
    </div>
</form>
