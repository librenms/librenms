<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$pagetitle[] = 'RIPE NCC - API Tools';
?>
<h3> RIPE NCC API Tools </h3>
<hr>
<form class="form-horizontal" action="" method="post">
    <div class="radio">
        <label><input type="radio" name="data_radio" value="abuse-contact-finder">Abuse Contact Finder</label>
    </div>
    <div class="radio">
        <label><input type="radio" name="data_radio" value="whois">Whois</label>
    </div>
    <br />
    <div class="input-group">
        <input type="text" class="form-control" id="input-parameter" placeholder="IP, ASN etc.">
        <span class="input-group-btn">
        <button type="submit" name="btn-query" id="btn-query" class="btn btn-primary">Query</button>
        </span>
    </div>
</form>
<br />
<div id="ripe-output" class="alert alert-success" style="display: none;"></div>
<br />
<script>
    $("[name='btn-query']").on('click', function(event) {
        event.preventDefault();
        var $this = $(this);
        var data_param = $('input[name=data_radio]:checked').val();
        var query_param = $("#input-parameter").val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {
                type: "query-ripenccapi",
                data_param: data_param,
                query_param: query_param
            },
            dataType: "json",
            success: function(data) {
                $('#ripe-output').empty();
                $("#ripe-output").show();
                if (data.output.data.records)
                    $.each(data.output.data.records[0], function(row, value) {
                        $('#ripe-output').append(value['key'] + ' = ' + value['value'] + '<br />');
                    });
                else if (data.output.data.anti_abuse_contacts.abuse_c)
                    $.each(data.output.data.anti_abuse_contacts.abuse_c, function(row, value) {
                        $('#ripe-output').append(value['description'] + ' = ' + value['email'] + '<br />');
                    });
            },
            error: function() {
                toastr.error('Error');
            }
        });
    });
</script>
