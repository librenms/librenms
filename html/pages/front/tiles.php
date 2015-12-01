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

/*
 * Code for Gridster.sort_by_row_and_col_asc(serialization) call is from http://gridster.net/demos/grid-from-serialize.html
 */

$no_refresh = true;
if (dbFetchCell('SELECT dashboard_id FROM dashboards WHERE user_id=?',array($_SESSION['user_id'])) == 0) {
    $vars['dashboard'] = dbInsert(array('dashboard_name'=>'Default','user_id'=>$_SESSION['user_id']),'dashboards');
    if (dbFetchCell('select 1 from users_widgets where user_id = ? && dashboard_id = ?',array($_SESSION['user_id'],0)) == 1) {
        dbUpdate(array('dashboard_id'=>$vars['dashboard']),'users_widgets','user_id = ? && dashboard_id = ?',array($_SESSION['user_id'],0));
    }
}
if (!empty($vars['dashboard'])) {
    $orig = $vars['dashboard'];
    $vars['dashboard'] = dbFetchRow('select * from dashboards where user_id = ? && dashboard_id = ? order by dashboard_id limit 1',array($_SESSION['user_id'],$vars['dashboard']));
    if (empty($vars['dashboard'])) {
        $vars['dashboard'] = dbFetchRow('select dashboards.*,users.username from dashboards inner join users on dashboards.user_id = users.user_id where dashboards.dashboard_id = ? && dashboards.access > 0',array($orig));
    }
}
if (empty($vars['dashboard'])) {
    $vars['dashboard'] = dbFetchRow('select * from dashboards where user_id = ? order by dashboard_id limit 1',array($_SESSION['user_id']));
    if (isset($orig)) {
        $msg_box[] = array('type' => 'error', 'message' => 'Dashboard <code>#'.$orig.'</code> does not exist! Loaded <code>'.$vars['dashboard']['dashboard_name'].'</code> instead.','title' => 'Requested Dashboard Not Found!');
    }
}
$data = array();
foreach (dbFetchRows('SELECT user_widget_id,users_widgets.widget_id,title,widget,col,row,size_x,size_y,refresh FROM `users_widgets` LEFT JOIN `widgets` ON `widgets`.`widget_id`=`users_widgets`.`widget_id` WHERE `dashboard_id`=?',array($vars['dashboard']['dashboard_id'])) as $items) {
    $data[] = $items;
}
if (empty($data)) {
    $data[] = array('user_widget_id'=>'0','widget_id'=>1,'title'=>'Add a widget','widget'=>'placeholder','col'=>1,'row'=>1,'size_x'=>6,'size_y'=>2,'refresh'=>60);
}
$data        = serialize(json_encode($data));
$dash_config = unserialize(stripslashes($data));
$dashboards  = dbFetchRows("SELECT * FROM `dashboards` WHERE `user_id` = ? && `dashboard_id` != ? ORDER BY `dashboard_name`",array($_SESSION['user_id'],$vars['dashboard']['dashboard_id']));

if (empty($vars['bare']) || $vars['bare'] == "no") {
?>
<div class="row">
  <div class="col-md-6">
    <div class="btn-group btn-lg">
      <button class="btn btn-default disabled" style="min-width:160px;"><span class="pull-left">Dashboards</span></button>
      <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="min-width:160px;"><span class="pull-left"><?php echo ($vars['dashboard']['user_id'] != $_SESSION['user_id'] ? $vars['dashboard']['username'].':' : ''); ?><?php echo $vars['dashboard']['dashboard_name']; ?></span>
          <span class="pull-right">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </span>
        </button>
        <ul class="dropdown-menu">
<?php
$nodash = 0;
if (sizeof($dashboards) > 0 || $vars['dashboard']['user_id'] != $_SESSION['user_id']) {
    foreach ($dashboards as $dash) {
        if ($dash['dashboard_id'] != $vars['dashboard']['dashboard_id']) {
            echo '          <li><a href="'.rtrim($config['base_url'],'/').'/overview/dashboard='.$dash['dashboard_id'].'">'.$dash['dashboard_name'].'</a></li>';
            $nodash = 1;
        }
    }
}
if ($nodash == 0) {
    echo  '          <li><a>No other Dashboards</a></li>';
}
$shared_dashboards = dbFetchRows("SELECT dashboards.*,users.username FROM `dashboards` INNER JOIN `users` ON users.user_id = dashboards.user_id WHERE dashboards.access > 0 && dashboards.user_id != ? && dashboards.dashboard_id != ?",array($_SESSION['user_id'],$vars['dashboard']['dashboard_id']));
if (!empty($shared_dashboards)) {
    echo '          <li role="separator" class="divider"></li>';
    echo '          <li class="dropdown-header">Shared Dashboards</li>';
    foreach ($shared_dashboards as $dash) {
        if ($dash['dashboard_id'] != $vars['dashboard']['dashboard_id']) {
            echo '          <li><a href="'.rtrim($config['base_url'],'/').'/overview/dashboard='.$dash['dashboard_id'].'">&nbsp;&nbsp;&nbsp;'.$dash['username'].':'.$dash['dashboard_name'].($dash['access'] == 1 ? ' (Read)' : '').'</a></li>';
        }
    }
}
?>
        </ul>
      </div>
      <button class="btn btn-default edit-dash-btn" href="#edit_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-placement="top" title="Edit Dashboard"><i class="fa fa-pencil-square-o fa-fw"></i></button>
      <button class="btn btn-danger" href="#del_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-placement="top" title="Remove Dashboard"><i class="fa fa-trash fa-fw"></i></button>
      <button class="btn btn-success" href="#add_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-placement="top" title="New Dashboard"><i class="fa fa-plus fa-fw"></i></button>
    </div>
  </div>
</div>
<div class="dash-collapse" id="add_dash">
  <div class="row" style="margin-top:5px;">
    <div class="col-md-6">
      <form class="form-inline" onsubmit="dashboard_add(this); return false;">
        <div class="col-sm-3 col-sx-6">
          <div class="input-group">
            <span class="input-group-btn">
              <a class="btn btn-default disabled" type="button" style="min-width:160px;"><span class="pull-left">New Dashboard</span></a>
            </span>
            <input class="form-control" type="text" placeholder="Name" name="dashboard_name" style="min-width:160px;">
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
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-btn">
                <a class="btn btn-default disabled" type="button" style="min-width:160px;"><span class="pull-left">Dashboard Name</span></a>
              </span>
              <input class="form-control" type="text" placeholder="Dashbord Name" name="dashboard_name" value="<?php echo $vars['dashboard']['dashboard_name']; ?>" style="width:160px;">
              <select class="form-control" name="access" style="width:160px;">
<?php
foreach (array('Private','Shared (Read)','Shared') as $k=>$v) {
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
    echo '              <li><a href="javascript:return false;" name="place_widget" data-widget_id="'.$widgets['widget_id'] .'">'. $widgets['widget_title'] .'</a></li>';
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
<?php } //End Vars['bare'] If ?>
<script src='https://www.google.com/jsapi'></script>
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
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "update-dashboard-config", data: s, dashboard_id: <?php echo $vars['dashboard']['dashboard_id']; ?>},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                }
                else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-info">An error occurred.</div>');
            }
        });
    }

    var gridster_state = 0;

    $(function(){
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
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "update-dashboard-config", sub_type: 'remove-all', dashboard_id: <?php echo $vars['dashboard']['dashboard_id']; ?>},
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        gridster.remove_all_widgets();
                    }
                    else {
                        $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                    }
                },
                error: function () {
                    $("#message").html('<div class="alert alert-info">An error occurred.</div>');
                }
            });
        });

        $('a[name="place_widget"]').on('click',  function(event, state) {
            var widget_id = $(this).data('widget_id');
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "update-dashboard-config", sub_type: 'add', widget_id: widget_id, dashboard_id: <?php echo $vars['dashboard']['dashboard_id']; ?>},
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        widget_dom(data.extra);
                        updatePos(gridster);
                    }
                    else {
                        $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                    }
                },
                error: function () {
                    $("#message").html('<div class="alert alert-info">An error occurred.</div>');
                }
            });
        });

        $(document).on( "click", ".close-widget", function() {
            var widget_id = $(this).data('widget-id');
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "update-dashboard-config", sub_type: 'remove', widget_id: widget_id, dashboard_id: <?php echo $vars['dashboard']['dashboard_id']; ?>},
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        gridster.remove_widget($('#'+widget_id));
                        updatePos(gridster);
                    }
                    else {
                        $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                    }
                },
                error: function () {
                    $("#message").html('<div class="alert alert-info">An error occurred.</div>');
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
            data: {type: 'delete-dashboard', dashboard_id: $(data).data('dashboard')},
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                    window.location.href="<?php echo rtrim($config['base_url'],'/'); ?>/overview";
                }
                else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            }
        });
    }

    function dashboard_edit(data) {
        datas = $(data).serializeArray();
        data = [];
        for( var field in datas ) {
            data[datas[field].name] = datas[field].value;
        }
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: 'edit-dashboard', dashboard_name: data['dashboard_name'], dashboard_id: <?php echo $vars['dashboard']['dashboard_id']; ?>, access: data['access']},
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                    window.location.href="<?php echo rtrim($config['base_url'],'/'); ?>/overview/dashboard=<?php echo $vars['dashboard']['dashboard_id']; ?>";
                }
                else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            }
        });
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
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                    window.location.href="<?php echo rtrim($config['base_url'],'/'); ?>/overview/dashboard="+data.dashboard_id;
                }
                else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            }
        });
    }

    function widget_dom(data) {
        dom = '<li id="'+data.user_widget_id+'" data-type="'+data.widget+'" data-settings="0">'+
              '<header class="widget_header"><span id="widget_title_'+data.user_widget_id+'">'+data.title+
              '</span>'+
              '<span class="fade-edit pull-right">'+
              '<a href="javascript:return false;" class="fa fa-pencil-square-o edit-widget" data-widget-id="'+data.user_widget_id+'" aria-label="Settings" data-toggle="tooltip" data-placement="top" title="Settings">&nbsp;</a>&nbsp;'+
              '<a href="javascript:return false;" class="text-danger fa fa-times close-widget" data-widget-id="'+data.user_widget_id+'" aria-label="Close" data-toggle="tooltip" data-placement="top" title="Remove">&nbsp;</a>&nbsp;'+
              '</span>'+
              '</header>'+
              '<div class="widget_body" id="widget_body_'+data.user_widget_id+'" style="height: 100%; width:100%;">'+data.widget+'</div>'+
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
        datas = $(data).serializeArray();
        for( var field in datas ) {
            widget_settings[datas[field].name] = datas[field].value;
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
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: 'widget-settings', id: widget_id, settings: widget_settings},
                dataType: "json",
                success: function (data) {
                    if( data.status == "ok" ) {
                        widget_reload(widget_id,widget_type);
                    }
                    else {
                        $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                    }
                }
            });
        }
    }

    function widget_reload(id,data_type) {
        if( $("#widget_body_"+id).parent().data('settings') == 1 ) {
            settings = 1;
        } else {
            settings = 0;
        }
        $.ajax({
            type: 'POST',
            url: 'ajax_dash.php',
            data: {type: data_type, id: id, dimensions: {x:$("#widget_body_"+id).innerWidth()-50, y:$("#widget_body_"+id).innerHeight()-50}, settings:settings},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $("#widget_title_"+id).html(data.title);
                    $("#widget_body_"+id).html(data.html);
                }
                else {
                    $("#widget_body_"+id).html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function () {
                $("#widget_body_"+id).html('<div class="alert alert-info">Problem with backend</div>');
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
</script>
