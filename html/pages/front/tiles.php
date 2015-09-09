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
if (empty($vars['dashboard'])) {
    $vars['dashboard'] = dbFetchRow('select dashboard_id,dashboard_name from dashboards where user_id = ? order by dashboard_id limit 1',array($_SESSION['user_id']));
} else {
    $vars['dashboard'] = dbFetchRow('select dashboard_id,dashboard_name from dashboards where user_id = ? && dashboard_id = ? order by dashboard_id limit 1',array($_SESSION['user_id'],$vars['dashboard']));
}
$data = array();
foreach (dbFetchRows('SELECT user_widget_id,users_widgets.widget_id,title,widget,col,row,size_x,size_y,refresh FROM `users_widgets` LEFT JOIN `widgets` ON `widgets`.`widget_id`=`users_widgets`.`widget_id` WHERE `user_id`=? AND `dashboard_id`=?',array($_SESSION['user_id'],$vars['dashboard']['dashboard_id'])) as $items) {
    $data[] = $items;
}
if (empty($data)) {
    $data[] = array('user_widget_id'=>'0','widget_id'=>1,'title'=>'Add a widget','widget'=>'placeholder','col'=>1,'row'=>1,'size_x'=>2,'size_y'=>2,'refresh'=>60);
}
$data        = serialize(json_encode($data));
$dash_config = unserialize(stripslashes($data));
$dashboards  = dbFetchRows("SELECT * FROM `dashboards` WHERE `user_id` = ?",array($_SESSION['user_id']));
?>

<div class="btn-group" role="group">
  <a class="btn btn-default disabled" role="button">Dashboards</a>
<?php
foreach ($dashboards as $dash) {
    echo '  <a class="btn btn-'.($dash[dashboard_id] == $vars['dashboard']['dashboard_id'] ? 'info' : 'default').'" role="button" href="'.$config['base_url'].'/overview/dashboard='.$dash['dashboard_id'].'">'.$dash['dashboard_name'].'</a>';
    if ($dash[dashboard_id] == $vars['dashboard']['dashboard_id']) {
        echo '<a class="btn btn-info" role="button" data-toggle="collapse" href="#edit_dash" aria-expanded="false" aria-controls="edit_dash"><i class="fa fa-wrench fa-fw"></i></a>';
    }
}
?>
  <a class="btn btn-success" role="button" data-toggle="collapse" href="#add_dash" aria-expanded="false" aria-controls="add_dash"><i class="fa fa-plus fa-fw"></i></a>
</div>

<div class="clear-fix">

<div class="collapse" id="add_dash">
  <div class="well">
    <div class="row">
      <form class="form-inline" onsubmit="dashboard_add(this); return false;">
        <div class="col-sm-3 col-sx-6">
          <div class="input-group">
            <span class="input-group-btn">
              <a class="btn btn-default disabled" type="button">New Dashboard</a>
            </span>
            <input class="form-control" type="text" placeholder="Name" name="dashboard_name">
            <span class="input-group-btn">
              <button class="btn btn-default" type="submit">Add</button>
            </span>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="collapse" id="edit_dash">
  <div id="add_widget">
    <div class="well">
      <div class="row">
        <form class="form-inline" onsubmit="dashboard_edit(this); return false;">
          <div class="col-md-4 col-sm-6 col-sx-12">
            <div class="input-group">
              <span class="input-group-btn">
                <a class="btn btn-default disabled" type="button" style="width:161px;">Dashboard Name</a>
                <button class="btn btn-danger" type="button" onclick="dashboard_delete(this); return false;" data-dashboard="<?php echo $vars['dashboard']['dashboard_id']; ?>"><i class="fa fa-trash fa-fw"></i></button>
              </span>
              <input class="form-control" type="text" placeholder="Dashbord Name" name="dashboard_name" value="<?php echo $vars['dashboard']['dashboard_name']; ?>">
              <span class="input-group-btn">
                <button class="btn btn-default" type="submit">Update</button>
              </span>
            </div>
          </div>
        </form>
      </div>
      <div style="margin-top:5px">
        <div class="btn-group" role="group">
          <a class="btn btn-default disabled" role="button" style="width:160px;">Available Widgets</a>
          <a class="btn btn-danger" role="button" id="clear_widgets" name="clear_widgets"><i class="fa fa-trash fa-fw"></i></a>
        </div>
<?php

foreach (dbFetchRows("SELECT * FROM `widgets` ORDER BY `widget_title`") as $widgets) {
    echo '<a class="btn btn-success" role="button" name="place_widget" data-widget_id="'.$widgets['widget_id'] .'">'. $widgets['widget_title'] .'</a> ';
}

?>
      </div>
    </div>
  </div>
</div>

<script src='https://www.google.com/jsapi'></script>
<script src="js/jquery.gridster.min.js"></script>

<span class="message" id="message"></span>

<div class="row">
    <div class="col-sm-12">
        <div class="gridster grid">
            <ul>
            </ul>
        </div>
     </div>
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

    $(function(){
        gridster = $(".gridster ul").gridster({
            widget_base_dimensions: [100, 100],
            widget_margins: [5, 5],
            avoid_overlapped_widgets: true,
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

        gridster.remove_all_widgets();
        $.each(serialization, function() {
            widget_dom(this);
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
            obj = $(this).parent().parent();
            if( obj.data('settings') == 1 ) {
                obj.data('settings','0');
            } else {
                obj.data('settings','1');
            }
            widget_reload(obj.attr('id'),obj.data('type'));
        });

   });

    function dashboard_delete(data) {
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: 'delete-dashboard', dashboard_id: $(data).data('dashboard')},
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    window.location.href="<?php echo $config['base_url']; ?>/overview";
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
            data: {type: 'edit-dashboard', dashboard_name: data['dashboard_name'], dashboard_id: <?php echo $vars['dashboard']['dashboard_id']; ?>},
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    window.location.href="<?php echo $config['base_url']; ?>/overview/dashboard=<?php echo $vars['dashboard']['dashboard_id']; ?>";
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
                    window.location.href="<?php echo $config['base_url']; ?>/overview/dashboard="+data.dashboard_id;
                }
            }
        });
    }

    function widget_dom(data) {
        dom = '<li id="'+data.user_widget_id+'" data-type="'+data.widget+'" data-settings="0">'+
              '<header class="widget_header"><span id="widget_title_'+data.user_widget_id+'">'+data.title+'</span>'+
              '<button style="color: #ffffff;" type="button" class="fa fa-times close close-widget" data-widget-id="'+data.user_widget_id+'" aria-label="Close">&nbsp;</button>'+
              '<button style="color: #ffffff;" type="button" class="fa fa-pencil-square-o close edit-widget" data-widget-id="'+data.user_widget_id+'" aria-label="Settings">&nbsp;</button>'+
              '</header>'+
              '<div class="widget_body" id="widget_body_'+data.user_widget_id+'" style="height: 100%; width:100%;">'+data.widget+'</div>'+
              '\<script\>var timeout'+data.user_widget_id+' = grab_data('+data.user_widget_id+','+data.refresh+',\''+data.widget+'\');\<\/script\>'+
              '</li>';
        if (data.hasOwnProperty('col') && data.hasOwnProperty('row')) {
            gridster.add_widget(dom, parseInt(data.size_x), parseInt(data.size_y), parseInt(data.col), parseInt(data.row));
        } else {
            gridster.add_widget(dom, parseInt(data.size_x), parseInt(data.size_y));
        }
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
