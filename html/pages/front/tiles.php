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

foreach (dbFetchRows('SELECT * FROM `users_widgets` LEFT JOIN `widgets` ON `widgets`.`widget_id`=`users_widgets`.`widget_id` WHERE `user_id`=?',array($_SESSION['user_id'])) as $items) {
    $data[] = $items;
}

if (!is_array($data)) {
    $data[] = array('user_widget_id'=>'0','widget_id'=>1,'title'=>'Add a widget','widget'=>'placeholder','col'=>1,'row'=>1,'size_x'=>2,'size_y'=>2,'refresh'=>60);
}

$data = serialize(json_encode($data));
$dash_config = unserialize(stripslashes($data));

?>

<div class="btn-group" role="group">
    <a class="btn btn-default disabled" role="button">Widgets</a>
    <a class="btn btn-success" role="button" data-toggle="collapse" href="#add_widget" aria-expanded="false" aria-controls="add_widget"><i class="fa fa-plus fa-fw"></i></a>
    <a class="btn btn-danger" role="button" id="clear_widgets" name="clear_widgets"><i class="fa fa-trash fa-fw"></i></a>
</div>
<div class="collapse" id="add_widget">
  <div class="well">
<?php

foreach (dbFetchRows("SELECT * FROM `widgets` ORDER BY `widget_title`") as $widgets) {
    echo '<a class="btn btn-success" role="button" id="place_widget" name="place_widget" data-widget_id="'.$widgets['widget_id'] .'">'. $widgets['widget_title'] .'</a> ';
}

?>
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
            data: {type: "update-dashboard-config", data: s},
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
                handle: 'header',
                stop: function(e, ui, $widget) {
                    updatePos(gridster);
                },
            },
            resize: {
                enabled: true,
                stop: function(e, ui, $widget) {
                    updatePos(gridster);
                }
            },
            serialize_params: function($w, wgd) { 
                return { 
                    id: $($w).attr('id'), 
                    col: wgd.col, 
                    row: wgd.row, 
                    size_x: wgd.size_x, 
                    size_y: wgd.size_y 
                };
            }
        }).data('gridster');

        gridster.remove_all_widgets();
        $.each(serialization, function() {
            gridster.add_widget(
                '<li id="'+this.user_widget_id+'">'+
                '\<script\>var timeout'+this.user_widget_id+' = grab_data('+this.user_widget_id+','+this.refresh+',\''+this.widget+'\');\<\/script\>'+
                '<header class="widget_header">'+this.title+'<button style="color: #ffffff" type="button" class="close close-widget" data-widget-id="'+this.user_widget_id+'" aria-label="Close"><span aria-hidden="true">&times;</span></button></header>'+
                '<div class="widget_body" id="widget_body_'+this.user_widget_id+'">'+this.widget+'</div>'+
                '</li>',
                parseInt(this.size_x), parseInt(this.size_y), parseInt(this.col), parseInt(this.row)
            );
        });

        $(document).on('click','#clear_widgets', function() {
            var widget_id = $(this).data('widget-id');
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "update-dashboard-config", sub_type: 'remove-all'},
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
                data: {type: "update-dashboard-config", sub_type: 'add', widget_id: widget_id},
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        var widget_id = data.extra.widget_id;
                        var title = data.extra.title;
                        var widget = data.extra.widget;
                        var size_x = data.extra.size_x;
                        var size_y = data.extra.size_y;
                        gridster.add_widget(
                            '<li id="'+widget_id+'">'+
                            '\<script\>var timeout'+widget_id+' = grab_data('+widget_id+',60,\''+widget+'\');\<\/script\>'+
                            '<header class="widget_header">'+title+'<button type="button" class="close close-widget" data-widget-id="'+widget_id+'" aria-label="Close"><span aria-hidden="true">&times;</span></button></header>'+
                            '<div class="widget_body" id="widget_body_'+widget_id+'">'+widget+'</div>'+
                            '</li>',
                            parseInt(size_x), parseInt(size_y)
                        );
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
                data: {type: "update-dashboard-config", sub_type: 'remove', widget_id: widget_id},
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

   });

   function grab_data(id,refresh,data_type) {
       new_refresh = refresh * 1000;
       $.ajax({
           type: 'POST',
           url: 'ajax_dash.php',
           data: {type: data_type},
           dataType: "json",
           success: function (data) {
               if (data.status == 'ok') {
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
     
       setTimeout(function() {
           grab_data(id,refresh,data_type);
       },
       new_refresh);
   }
$('#new-widget').popover();

</script>
