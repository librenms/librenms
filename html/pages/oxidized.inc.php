<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
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
                                <th data-column-id="hostname" data-order="desc">Hostname</th>
                                <th data-column-id="last_status">Last Status</th>
                                <th data-column-id="last_update">Last Update</th>
                                <th data-column-id="model">Model</th>
                                <th data-column-id="group">Group</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php get_oxidized_nodes_list();?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="search">
                    <form class="form-horizontal" action="" method="post">
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
                if (data.output)
                    $('#search-output').append('Config appears on the folllowing device(s):<br />');
                $.each(data.output, function (row, value) {
                    $('#search-output').append(value['full_name'] + '<br />');
                });
            },
            error: function () {
                toastr.error('Error');
            }
        });
    });
</script>
