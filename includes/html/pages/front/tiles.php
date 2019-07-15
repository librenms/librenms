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

use LibreNMS\Config;

/*
 * Code for Gridster.sort_by_row_and_col_asc(serialization) call is from http://gridster.net/demos/grid-from-serialize.html
 */

$no_refresh   = true;
$default_dash = get_user_pref('dashboard', 0);

require_once 'includes/html/modal/alert_notes.inc.php';
require_once 'includes/html/modal/alert_ack.inc.php';

// get all dashboards this user can access and put them into two lists user_dashboards and shared_dashboards
$dashboards = get_dashboards();
list($user_dashboards, $shared_dashboards) = array_reduce($dashboards, function ($ret, $dash) {
    if ($dash['user_id'] == Auth::id()) {
        $ret[0][] = $dash;
    } else {
        $ret[1][] = $dash;
    }
    return $ret;
}, array());

// if the default dashboard doesn't exist, set it to the global default or to 0
if (!isset($dashboards[$default_dash])) {
    $global_default = (int)Config::get('webui.default_dashboard_id');
    $default_dash = isset($dashboards[$global_default]) ? $global_default : 0;
}

// if there are no possible dashboards, add one
if ($default_dash == 0 && empty($user_dashboards)) {
    $new_dash = array(
        'dashboard_name'=>'Default',
        'user_id'=>Auth::id(),
    );

    $dashboard_id = dbInsert($new_dash, 'dashboards');
    $new_dash['dashboard_id'] = $dashboard_id;
    $new_dash['username'] = Auth::user()->username;
    $vars['dashboard'] = $new_dash;

    if (dbFetchCell('select 1 from users_widgets where user_id = ? && dashboard_id = ?', array(Auth::id(),0)) == 1) {
        dbUpdate(array('dashboard_id'=>$dashboard_id), 'users_widgets', 'user_id = ? && dashboard_id = ?', array(Auth::id(), 0));
    }
} else {
    // load a dashboard
    $orig = $vars['dashboard'];
    if (!empty($orig) && isset($dashboards[$orig])) {
        // specific dashboard
        $vars['dashboard'] = $dashboards[$orig];
    } else {
        // load a default dashboard
        $vars['dashboard'] = $default_dash == 0 ? current($user_dashboards) : $dashboards[$default_dash];

        // $dashboard was requested, but doesn't exist
        if (!empty($orig)) {
            Toastr::error('Dashboard <code>#' . $orig .
                '</code> does not exist! Loaded <code>' . htmlentities($vars['dashboard']['dashboard_name']) .
                '</code> instead.', 'Requested Dashboard Not Found!');
        }
    }
}

$data = dbFetchRows(
    'SELECT `user_widget_id`,`users_widgets`.`widget_id`,`title`,`widget`,`col`,`row`,`size_x`,`size_y`,`refresh` FROM `users_widgets`
    LEFT JOIN `widgets` ON `widgets`.`widget_id`=`users_widgets`.`widget_id` WHERE `dashboard_id`=?',
    array($vars['dashboard']['dashboard_id'])
);
if (empty($data)) {
    $data[] = array('user_widget_id'=>'0','widget_id'=>1,'title'=>'Add a widget','widget'=>'placeholder','col'=>1,'row'=>1,'size_x'=>6,'size_y'=>2,'refresh'=>60);
}

$data        = serialize(json_encode($data));
$dash_config = unserialize(stripslashes($data));

if (empty($vars['bare']) || $vars['bare'] == "no") {
    ?>
<div class="row">
  <div class="col-md-6">
    <div class="btn-group btn-lg">
      <button class="btn btn-default disabled" style="min-width:160px;"><span class="pull-left">Dashboards</span></button>
      <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="min-width:160px;"><span class="pull-left"><?php echo ($vars['dashboard']['user_id'] != Auth::id() ? $vars['dashboard']['username'].':' : ''); ?><?php echo $vars['dashboard']['dashboard_name']; ?></span>
          <span class="pull-right">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </span>
        </button>
        <ul class="dropdown-menu">
    <?php

    $nodash = true;
    foreach ($user_dashboards as $dash) {
        if ($dash['dashboard_id'] != $vars['dashboard']['dashboard_id']) {
            echo '          <li><a href="' . rtrim(Config::get('base_url'), '/') . '/overview/dashboard=' . $dash['dashboard_id'] . '">' . $dash['dashboard_name'] . '</a></li>';
            $nodash = false;
        }
    }
    if ($nodash) {
        echo  '          <li><a>No other Dashboards</a></li>';
    }

    if (!empty($shared_dashboards)) {
        echo '          <li role="separator" class="divider"></li>';
        echo '          <li class="dropdown-header">Shared Dashboards</li>';
        foreach ($shared_dashboards as $dash) {
            if ($dash['dashboard_id'] != $vars['dashboard']['dashboard_id']) {
                echo '          <li><a href="' . rtrim(Config::get('base_url'), '/') . '/overview/dashboard=' . $dash['dashboard_id'] . '">&nbsp;&nbsp;&nbsp;' . $dash['username'] . ':' . $dash['dashboard_name'] . ($dash['access'] == 1 ? ' (Read)' : '') . '</a></li>';
            }
        }
    }
    ?>
        </ul>
      </div>
      <button class="btn btn-default edit-dash-btn" href="#edit_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-container="body" data-placement="top" title="Edit Dashboard"><i class="fa fa-pencil-square-o fa-fw"></i></button>
      <button class="btn btn-danger" href="#del_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-container="body" data-placement="top" title="Remove Dashboard"><i class="fa fa-trash fa-fw"></i></button>
      <button class="btn btn-success" href="#add_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-container="body" data-placement="top" title="New Dashboard"><i class="fa fa-plus fa-fw"></i></button>
    </div>
  </div>
</div>
<div class="dash-collapse" id="add_dash">
  <div class="row" style="margin-top:5px;">
    <div class="col-md-6">
      <form class="form-inline" onsubmit="dashboard_add(this); return false;" name="add_form" id="add_form">
        <?php echo csrf_field() ?>
        <div class="col-sm-3 col-sx-6">
          <div class="input-group">
            <span class="input-group-btn">
              <a class="btn btn-default disabled" type="button" style="min-width:160px;"><span class="pull-left">New Dashboard</span></a>
            </span>
            <input class="form-control" type="text" placeholder="Name" name="dashboard_name" id="dashboard_name" style="min-width:160px;">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit">Add</button>
            </span>
          </div>
        </div>
      </form>
    </div>
  </div>
  <hr>
</div>
<div class="dash-collapse" id="edit_dash">
<!-- Start Dashboard-Settings -->
  <div class="row" style="margin-top:5px;">
    <div class="col-md-12">
      <div class="col-md-12">
        <form class="form-inline" onsubmit="dashboard_edit(this); return false;">
            <?php echo csrf_field() ?>
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-btn">
                <a class="btn btn-default disabled" type="button" style="min-width:160px;"><span class="pull-left">Dashboard Name</span></a>
              </span>
              <input class="form-control" type="text" placeholder="Dashbord Name" name="dashboard_name" value="<?php echo $vars['dashboard']['dashboard_name']; ?>" style="width:160px;">
              <select class="form-control" name="access" style="width:160px;">
    <?php
    foreach (array('Private','Shared (Read)','Shared') as $k => $v) {
        echo '                <option value="'.$k.'"'.($vars['dashboard']['access'] == $k ? 'selected' : '').'>'.$v.'</option>';
    }
    ?>
              </select>
              <span class="input-group-btn pull-left">
                <button class="btn btn-primary" type="submit">Update</button>
              </span>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
<!-- End Dashboard-Settings -->

<!-- Start Widget-Select -->
  <div class="row" style="margin-top:5px;">
    <div class="col-md-12">
      <div class="col-md-12">
        <div class="btn-group" role="group">
          <a class="btn btn-default disabled" role="button" style="min-width:160px;"><span class="pull-left">Add Widgets</span></a>
          <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="min-width:160px;"><span class="pull-left">Select Widget</span>
              <span class="pull-right">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
              </span>
            </button>
            <ul class="dropdown-menu">
    <?php
    foreach (dbFetchRows("SELECT * FROM `widgets` ORDER BY `widget_title`") as $widgets) {
        echo '              <li><a href="#" onsubmit="return false;" class="place_widget" data-widget_id="'.$widgets['widget_id'] .'">'. $widgets['widget_title'] .'</a></li>';
    }
    ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
<!-- End Widget-Select -->
  <hr>
</div>
<div class="dash-collapse" id="del_dash">
  <div class="row" style="margin-top:5px;">
    <div class="col-md-6">
      <div class="col-md-6">
        <button class="btn btn-danger" type="button" id="clear_widgets" name="clear_widgets" style="min-width:160px;"><span class="pull-left">Remove</span><strong class="pull-right">Widgets</strong></button>
      </div>
    </div>
  </div>
  <div class="row" style="margin-top:5px;">
    <div class="col-md-6">
      <div class="col-md-6">
        <button class="btn btn-danger" type="button" onclick="dashboard_delete(this); return false;" data-dashboard="<?php echo $vars['dashboard']['dashboard_id']; ?>" style="min-width:160px;"><span class="pull-left">Delete</span><strong class="pull-right">Dashboard</strong></button>
      </div>
    </div>
  </div>
  <hr>
</div>
<?php } //End Vars['bare'] If
?>
<script src="js/jquery.gridster.min.js"></script>

<span class="message" id="message"></span>

        <div class="gridster grid">
            <ul>
            </ul>
        </div>

<script type="text/javascript">

    var gridster;

    var serialization = <?php echo $dash_config; ?>;

    serialization = Gridster.sort_by_row_and_col_asc(serialization);

    function updatePos(gridster) {
        var s = JSON.stringify(gridster.serialize());
        <?php
        if ($vars['dashboard']['dashboard_id'] > 0) {
            echo "var dashboard_id = " . $vars['dashboard']['dashboard_id'] . ";";
        } else {
            echo "var dashboard_id = 0;";
        }
        ?>
        if (dashboard_id > 0) {
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {
                    type: "update-dashboard-config",
                    data: s,
                    dashboard_id: dashboard_id
                },
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        toastr.success(data.message);
                    }
                    else {
                        toastr.error(data.message);
                    }
                },
                error: function (data) {
                    toastr.error(data.message);
                }
            });
        }
    }

    var gridster_state = 0;

    $(function(){
        <?php
        if ($vars['dashboard']['dashboard_id'] > 0) {
            echo "var dashboard_id = " . $vars['dashboard']['dashboard_id'] . ";";
        } else {
            echo "var dashboard_id = 0;";
        }
        ?>
        $('[data-toggle="tooltip"]').tooltip();
        dashboard_collapse();
        gridster = $(".gridster ul").gridster({
            widget_base_dimensions: ['auto', 100],
            autogenerate_stylesheet: true,
            widget_margins: [5, 5],
            avoid_overlapped_widgets: true,
            min_cols: 1,
            max_cols: 20,
            draggable: {
                handle: 'header, span',
                stop: function(e, ui, $widget) {
                    updatePos(gridster);
                },
            },
            resize: {
                enabled: true,
                stop: function(e, ui, widget) {
                    updatePos(gridster);
                    widget_reload(widget.attr('id'),widget.data('type'));
                }
            },
            serialize_params: function(w, wgd) {
                return {
                    id: $(w).attr('id'),
                    col: wgd.col,
                    row: wgd.row,
                    size_x: wgd.size_x,
                    size_y: wgd.size_y
                };
            }
        }).data('gridster');
        $('.gridster  ul').css({'width': $(window).width()});

        gridster.remove_all_widgets();
        gridster.disable();
        gridster.disable_resize();
        $.each(serialization, function() {
            widget_dom(this);
        });
        $(document).on('click','.edit-dash-btn', function() {
            if (gridster_state == 0) {
                gridster.enable();
                gridster.enable_resize();
                gridster_state = 1;
                $('.fade-edit').fadeIn();
            }
            else {
                gridster.disable();
                gridster.disable_resize();
                gridster_state = 0;
                $('.fade-edit').fadeOut();
            }
        });

        $(document).on('click','#clear_widgets', function() {
            var widget_id = $(this).data('widget-id');
            if (dashboard_id > 0) {
                $.ajax({
                    type: 'POST',
                    url: 'ajax_form.php',
                    data: {
                        type: "update-dashboard-config",
                        sub_type: 'remove-all',
                        dashboard_id: dashboard_id
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'ok') {
                            gridster.remove_all_widgets();
                            toastr.success(data.message);
                        }
                        else {
                            toastr.error(data.message);
                        }
                    },
                    error: function (data) {
                        toastr.error(data.message);
                    }
                });
            }
        });

        $('.place_widget').on('click',  function(event, state) {
            var widget_id = $(this).data('widget_id');
            event.preventDefault();
            if (dashboard_id > 0) {
                $.ajax({
                    type: 'POST',
                    url: 'ajax_form.php',
                    data: {
                        type: "update-dashboard-config",
                        sub_type: 'add',
                        widget_id: widget_id,
                        dashboard_id: dashboard_id
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'ok') {
                            widget_dom(data.extra);
                            updatePos(gridster);
                            toastr.success(data.message);
                        }
                        else {
                            toastr.error(data.message);
                        }
                    },
                    error: function (data) {
                        toastr.error(data.message);
                    }
                });
            }
        });

        $(document).on( "click", ".close-widget", function() {
            var widget_id = $(this).data('widget-id');
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {
                    type: "update-dashboard-config",
                    sub_type: 'remove',
                    widget_id: widget_id,
                    dashboard_id: dashboard_id
                },
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        gridster.remove_widget($('#'+widget_id));
                        updatePos(gridster);
                        toastr.success(data.message);
                    }
                    else {
                        toastr.error(data.message);
                    }
                },
                error: function (data) {
                    toastr.error(data.message);
                }
            });
        });

        $(document).on("click",".edit-widget",function() {
            obj = $(this).parent().parent().parent();
            if( obj.data('settings') == 1 ) {
                obj.data('settings','0');
            } else {
                obj.data('settings','1');
            }
            widget_reload(obj.attr('id'),obj.data('type'));
        });

   });

    function dashboard_collapse(target) {
        if (target !== undefined) {
            $('.dash-collapse:not('+target+')').each(function() {
                $(this).fadeOut(0);
            });
            $(target).fadeToggle(300);
            if (target != "#edit_dash") {
                gridster.disable();
                gridster.disable_resize();
                gridster_state = 0;
                $('.fade-edit').fadeOut();
            }
        } else {
            $('.dash-collapse').fadeOut(0);
        }
    }

    function dashboard_delete(data) {
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {
                type: 'delete-dashboard',
                dashboard_id: $(data).data('dashboard')
            },
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    toastr.success(data.message);
                    setTimeout(function (){
                        window.location.href = "<?php echo rtrim(Config::get('base_url'), '/'); ?>/overview";
                    }, 500);

                } else {
                    toastr.error(data.message);
                }
            },
            error: function (data) {
                toastr.error(data.message);
            }
        });
    }

    function dashboard_edit(data) {
        <?php
        if ($vars['dashboard']['dashboard_id'] > 0) {
            echo "var dashboard_id = " . $vars['dashboard']['dashboard_id'] . ";";
        } else {
            echo "var dashboard_id = 0;";
        }
        ?>
        datas = $(data).serializeArray();
        data = [];
        for( var field in datas ) {
            data[datas[field].name] = datas[field].value;
        }
        if (dashboard_id > 0) {
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {
                    type: 'edit-dashboard',
                    dashboard_name: data['dashboard_name'],
                    dashboard_id: dashboard_id,
                    access: data['access']
                },
                dataType: "json",
                success: function (data) {
                    if (data.status == "ok") {
                        toastr.success(data.message);
                        setTimeout(function (){
                            window.location.href = "<?php echo rtrim(Config::get('base_url'), '/'); ?>/overview/dashboard=" + dashboard_id;
                        }, 500);
                    }
                    else {
                        toastr.error(data.message);
                    }
                },
                error: function(data) {
                    toastr.error(data.message);
                }
            });
        }
    }

    function dashboard_add(data) {
        datas = $(data).serializeArray();
        data = [];
        for( var field in datas ) {
            data[datas[field].name] = datas[field].value;
        }
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: 'add-dashboard', dashboard_name: data['dashboard_name']},
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    toastr.success(data.message);
                    setTimeout(function (){
                        window.location.href = "<?php echo rtrim(Config::get('base_url'), '/'); ?>/overview/dashboard=" + data.dashboard_id;
                    }, 500);
                }
                else {
                    toastr.error(data.message);
                }
            },
            error: function(data) {
                toastr.error(data.message);
            }
        });
    }

    function widget_dom(data) {
        dom = '<li id="'+data.user_widget_id+'" data-type="'+data.widget+'" data-settings="0">'+
              '<header class="widget_header"><span id="widget_title_'+data.user_widget_id+'">'+data.title+
              '</span>'+
              '<span class="fade-edit pull-right">'+
                <?php
                if (($vars['dashboard']['access'] == 1 && Auth::id() === $vars['dashboard']['user_id']) ||
                        ($vars['dashboard']['access'] == 0 || $vars['dashboard']['access'] == 2)) {
                        echo "'<i class=\"fa fa-pencil-square-o edit-widget\" data-widget-id=\"'+data.user_widget_id+'\" aria-label=\"Settings\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Settings\">&nbsp;</i>&nbsp;'+";
                }
                ?>
              '<i class="text-danger fa fa-times close-widget" data-widget-id="'+data.user_widget_id+'" aria-label="Close" data-toggle="tooltip" data-placement="top" title="Remove">&nbsp;</i>&nbsp;'+
              '</span>'+
              '</header>'+
              '<div class="widget_body" id="widget_body_'+data.user_widget_id+'">'+data.widget+'</div>'+
              '\<script\>var timeout'+data.user_widget_id+' = grab_data('+data.user_widget_id+','+data.refresh+',\''+data.widget+'\');\<\/script\>'+
              '</li>';
        if (data.hasOwnProperty('col') && data.hasOwnProperty('row')) {
            gridster.add_widget(dom, parseInt(data.size_x), parseInt(data.size_y), parseInt(data.col), parseInt(data.row));
        } else {
            gridster.add_widget(dom, parseInt(data.size_x), parseInt(data.size_y));
        }
        if (gridster_state == 0) {
            $('.fade-edit').fadeOut(0);
        }
        $('[data-toggle="tooltip"]').tooltip();
    }

    function widget_settings(data) {
        var widget_settings = {};
        var widget_id = 0;
        var datas = $(data).serializeArray();
        for( var field in datas ) {
            var name = datas[field].name;
            if (name.endsWith('[]')) {
                name = name.slice(0, -2);
                if (widget_settings[name]) {
                    widget_settings[name].push(datas[field].value);
                } else {
                    widget_settings[name] = [datas[field].value];
                }
            } else {
                widget_settings[name] = datas[field].value;
            }
        }

        $('.gridster').find('div[id^=widget_body_]').each(function() {
            if(this.contains(data)) {
                widget_id = $(this).parent().attr('id');
                widget_type = $(this).parent().data('type');
                $(this).parent().data('settings','0');
            }
        });
        if( widget_id > 0 && widget_settings != {} ) {
            $.ajax({
                type: 'PUT',
                url: '<?php echo url('/ajax/form/widget-settings/'); ?>/' + widget_id,
                data: {settings: widget_settings},
                dataType: "json",
                success: function (data) {
                    if( data.status == "ok" ) {
                        widget_reload(widget_id,widget_type);
                        toastr.success(data.message);
                    }
                    else {
                        toastr.error(data.message);
                    }
                },
                error: function (data) {
                    toastr.error(data.message);
                }
            });
        }
    return false;
    }

    function widget_reload(id,data_type) {
        $("#widget_body_"+id+" .bootgrid-table").bootgrid("destroy");
        $("#widget_body_"+id+" *").off();
        var $widget_body = $("#widget_body_"+id);
        if ($widget_body.parent().data('settings') == 1 ) {
            settings = 1;
        } else {
            settings = 0;
        }
        $.ajax({
            type: 'POST',
            url: ajax_url + '/dash/' + data_type,
            data: {
                id: id,
                dimensions: {x:$widget_body.width(), y:$widget_body.height()},
                settings:settings
            },
            dataType: "json",
            success: function (data) {
                var $widget_body = $("#widget_body_"+id);
                $widget_body.empty();
                if (data.status === 'ok') {
                    $("#widget_title_"+id).html(data.title);
                    $widget_body.html(data.html).parent().data('settings', data.show_settings);
                } else {
                    $widget_body.html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function (data) {
                var $widget_body = $("#widget_body_"+id);
                $widget_body.empty();
                if (data.responseJSON.error) {
                    $widget_body.html('<div class="alert alert-info">' + data.responseJSON.error + '</div>');
                } else {
                    $widget_body.html('<div class="alert alert-info"><?php echo __('Problem with backend'); ?></div>');
                }
            }
        });
    }

    function grab_data(id,refresh,data_type) {
        if( $("#widget_body_"+id).parent().data('settings') == 0 ) {
            widget_reload(id,data_type);
        }
        new_refresh = refresh * 1000;
        setTimeout(function() {
            grab_data(id,refresh,data_type);
        },
        new_refresh);
    }
    $('#new-widget').popover();

    <?php
    if (empty($vars['dashboard']['dashboard_id']) && $default_dash == 0) {
        echo "\$('#dashboard_name').val('Default');\n";
        echo "dashboard_add(\$('#add_form'));\n";
    }
    ?>

</script>
