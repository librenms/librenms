<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$pagetitle[] = 'Oxidized';
?>
<div class="col-xs-12">
    <h2>Oxidized</h2>
    <div class="panel-heading">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#list" data-toggle="tab">Node List</a></li>
            <li><a href="#search" data-toggle="tab">Config Search</a></li>
            <li><a href="<?php echo \LibreNMS\Util\Url::generate(['page' => 'tools', 'tool' => 'oxidized-cfg-check']); ?>">Oxidized config validation</a></li>
        </ul>
    </div>
    <div class="panel with-nav-tabs panel-default">
        <div class="panel-body">
            <div class="tab-content">
                <div class="tab-pane fade in active" id="list">
                    <div class="table-responsive">
                        <table id="oxidized-nodes" class="table table-hover table-condensed table-striped">
                            <thead>
                            <tr>
                                <th data-column-id="id" data-visible="false">ID</th>
                                <th data-column-id="hostname" data-formatter="hostname" data-order="asc">Hostname</th>
                                <th data-column-id="sysname" data-visible=" <?php echo ! Config::get('force_ip_to_sysname') ? 'true' : 'false' ?>">SysName</th>
                                <th data-column-id="last_status" data-formatter="status">Last Status</th>
                                <th data-column-id="last_update">Last Update</th>
                                <th data-column-id="model">Model</th>
                                <th data-column-id="group">Group</th>
                                <th data-column-id="actions" data-formatter="actions"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php get_oxidized_nodes_list(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="search">
                    <form class="form-horizontal" action="" method="post">
                        <?php echo csrf_field() ?>
                        <br/>
                        <div class="input-group">
                            <input type="text" class="form-control" id="input-parameter"
                                   placeholder="service password-encryption etc.">
                            <span class="input-group-btn">
                                <button type="submit" name="btn-search" id="btn-search" class="btn btn-primary">Search</button>
                            </span>
                        </div>
                    </form>
                    <br/>
                    <div id="search-output" class="alert alert-success" style="display: none;"></div>
                    <br/>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var grid = $("#oxidized-nodes").bootgrid({
        templates: {
            header: '<div id="{{ctx.id}}" class="{{css.header}}"><div class="row">\
                        <div class="col-sm-8 actionBar">\
                            <span class="pull-left">\
                                <button type="submit" class="btn btn-success btn-sm" name="btn-reload-nodes" id="btn-reload-nodes"\
                                title="Update Oxidized\'s node list from LibreNMS data"><i class="fa fa-refresh"></i>\
                                Reload node list</button>\
                            </span>\
                        </div>\
                        <div class="col-sm-4 actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div>\
                    </div></div>'
            },
        rowCount: [50, 100, 250, -1],
        formatters: {
            "hostname": function(column, row) {
                if (row.id) {
                    return '<a href="<?= url('device') ?>/' + row.id + '">' + row.hostname + '</a>';
                } else {
                    return row.hostname;
                }
            },
            "actions": function(column, row) {
                if (row.id) {
                    return '<button class="btn btn-default btn-sm" name="btn-refresh-node-devId' + row.id +
                            '" id="btn-refresh-node-devId' + row.id + '" onclick="refresh_oxidized_node(\'' + row.hostname + '\');" title="Refetch config">' +
                            '<i class="fa fa-refresh"></i></button> ' +
                            '<a href="<?= url('device') ?>/' + row.id + '/tab=showconfig/" title="View config"><i class="fa fa-align-justify fa-lg icon-theme"></i></a>';
                }
            },
            "status": function(column, row) {
                var color = ((row.last_status == 'success') ? 'success' : 'danger');
                return '<i class="fa fa-square text-' + color + '" title="' + row.last_status + '"></i>';
            }
        }
    });

    $("[name='btn-search']").on('click', function (event) {
        event.preventDefault();
        var $this = $(this);
        var search_in_conf_textbox = $("#input-parameter").val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {
                type: "search-oxidized-config",
                search_in_conf_textbox: search_in_conf_textbox
            },
            dataType: "json",
            success: function (data) {
                $('#search-output').empty();
                $("#search-output").show();
                if (data.output) {
                    $('#search-output').append('<p>Config appears on the following device(s):</p>');
                    $.each(data.output, function (row, value) {
                        if (value['dev_id']) {
                            $('#search-output').append('<p><a href="<?= url('device') ?>/' + value['dev_id'] + '/tab=showconfig/">' + value['full_name'] + '</p>');
                        } else {
                            $('#search-output').append('<p>' + value['full_name'] + '</p>');
                        }
                        });
                }
            },
            error: function () {
                toastr.error('Error');
            }
        });
    });
    $("[name='btn-reload-nodes']").on('click', function (event) {
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "reload-oxidized-nodes-list" },
            dataType: "json",
            success: function (data) {
                if(data['status'] == 'ok') {
                    toastr.success(data['message']);
                } else {
                    toastr.error(data['message']);
                }
            },
            error:function(){
                toastr.error('An error occured while reloading the Oxidized nodes list');
            }
        });
    });
</script>
