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

$no_refresh = true;

foreach (dbFetchRows('SELECT * FROM `dashboard_items` WHERE `user_id`=?',array($_SESSION['user_id'])) as $items) {
    $data[] = $items;
}

if (!is_array($data)) {
    $data[] = array('dashboard_item_id'=>'0','widget_id'=>1,'title'=>'Add a widget','content'=>'placeholder','col'=>1,'row'=>1,'size_x'=>2,'size_y'=>2,'refresh'=>60);
}

$data = serialize(json_encode($data));
$dash_config = unserialize(stripslashes($data));

$new_tile = '
<select id="tile_id" name="tile_id" class="form-control">
    <option value="" disabled selected></option>
';

foreach (dbFetchRows("SELECT * FROM `tiles` ORDER BY `tile_title`") as $tiles) {
    $new_tile .= '<option value="'. $tiles['tile_id'] .'">'. $tiles['tile_title'] .'</option>';
}

$new_title .= '
</select>
';

?>

&nbsp;<button type='button' id='new-tile' class='btn btn-success btn-sm' name='new-dashboard-tile' data-html='true' data-container='body' data-toggle='popover' data-placement='right' data-content='<a href="#">
<?php
    echo $new_tile;
?>
</a>'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> Add tile</button>
<script>
$('#new-tile').popover();

</script>

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
            url: '/ajax_form.php',
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
            autogrow_cols: true,
            min_cols: 1,
            min_rows: 2,
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
                '<li id="'+this.dashboard_item_id+'">'+
                '\<script\>var timeout'+this.dashboard_item_id+' = grab_data('+this.dashboard_item_id+','+this.refresh+',\''+this.content+'\');\<\/script\>'+
                '<header class="tile_header">'+this.title+'<button style="color: #ffffff" type="button" class="close close-tile" data-tile-id="'+this.dashboard_item_id+'" aria-label="Close"><span aria-hidden="true">&times;</span></button></header>'+
                '<div class="tile_body" id="tile_body_'+this.dashboard_item_id+'">'+this.content+'</div>'+
                '</li>',
                this.size_x, this.size_y, this.col, this.row
            );
        });

        $(document).on( "click", ".close-tile", function() {
            var tile_id = $(this).data('tile-id');
            $.ajax({
                type: 'POST',
                url: '/ajax_form.php',
                data: {type: "update-dashboard-config", sub_type: 'remove', tile_id: tile_id},
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        gridster.remove_widget($('#'+tile_id));
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

        $(document).on('change','#tile_id',function(){
            var tile_id = $(this).val();
            $.ajax({
                type: 'POST',
                url: '/ajax_form.php',
                data: {type: "update-dashboard-config", sub_type: 'add', tile_id: tile_id},
                dataType: "json",
                success: function (data) {
                   if (data.status == 'ok') {
                       var item_id = data.extra.item_id;
                       var title = data.extra.title;
                       var content = data.extra.content;
                       var size_x = data.extra.size_x;
                       var size_y = data.extra.size_y;
                       gridster.add_widget(
                           '<li id="'+item_id+'">'+
                           '\<script\>var timeout'+item_id+' = grab_data('+item_id+',60,\''+content+'\');\<\/script\>'+
                           '<header class="tile_header">'+title+'<button type="button" class="close" data-tile-id="'+item_id+'" aria-label="Close"><span aria-hidden="true">&times;</span></button></header>'+
                           '<div class="tile_body" id="tile_body_'+item_id+'">'+content+'</div>'+
                           '</li>',
                           size_x, size_y
                       );
                       $('#new-tile').popover('hide')
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
           url: '/ajax_dash.php',
           data: {type: data_type},
           dataType: "json",
           success: function (data) {
               if (data.status == 'ok') {
                   $("#tile_body_"+id).html(data.html);
               }
               else {
                   $("#tile_body_"+id).html('<div class="alert alert-info">' + data.message + '</div>');
               }
           },
           error: function () {
               $("#tile_body_"+id).html('<div class="alert alert-info">Problem with backend</div>');
           }
       });
     
       setTimeout(function() {
           grab_data(id,refresh,data_type);
       },
       new_refresh);
   }
</script>
