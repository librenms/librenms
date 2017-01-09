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
<h3> Oxidized - Config Search </h3>
<hr>
<form class="form-horizontal" action="" method="post">
    <br />
    <div class="input-group">
        <input type="text" class="form-control" id="input-parameter" placeholder="service password-encryption etc.">
        <span class="input-group-btn">
        <button type="submit" name="btn-search" id="btn-search" class="btn btn-primary">Search</button>
        </span>
    </div>
</form>
<br />
<div id="search-output" class="alert alert-success" style="display: none;"></div>
<br />
<script>
    $("[name='btn-search']").on('click', function(event) {
        event.preventDefault();
        var $this = $(this);
        var search_in_conf_textbox = $("#input-parameter").val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {
                type: "oxidized-search-config",
                search_in_conf_textbox: search_in_conf_textbox
            },
            dataType: "json",
            success: function(data) {
                $('#search-output').empty();
                $("#search-output").show();
                if (data.output)
                    $('#search-output').append('Config appears on the folllowing device(s):<br />');
                    $.each(data.output, function(row, value) {
                        $('#search-output').append(value['full_name'] + '<br />');
                    });
            },
            error: function() {
                toastr.error('Error');
            }
        });
    });
</script>
