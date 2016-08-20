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

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

?>

<div class="modal fade" id="attach-alert-template" tabindex="-1" role="dialog" aria-labelledby="Attach" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Attach">Attach template to rules...</h5>
            </div>
            <div class="modal-body">
                <p>Please select the rules that you would like to assign this template to.</p>
                <form class="form-group">
                    <div class="form-group">
                        <label for="rules_list">Select rules</label>
                        <select multiple="multiple" class="form-control" id="rules_list" name="rules_list" size="10">
                            <option></option>
<?php

foreach (dbFetchRows("SELECT `id`,`rule`,`name` FROM `alert_rules`", array()) as $rule) {
    echo '<option value="'.$rule['id'].'">'.$rule['name'].'</option>';
}
?>
                        </select>
                    </div>
                </form>
                <span id="template_error"></span><br />
            </div>
            <div class="modal-footer">
                <form role="form" class="attach_rule_form">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger danger" id="alert-template-attach" data-target="alert-template-attach">Attach</button>
                    <input type="hidden" name="template_id" id="template_id" value="">
                    <input type="hidden" name="confirm" id="confirm" value="yes">
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$('#attach-alert-template').on('show.bs.modal', function(e) {
    template_id = $(e.relatedTarget).data('template_id');
    $("#template_id").val(template_id);
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: { type: "parse-template-rules", template_id: template_id },
        dataType: "json",
        success: function(output) {
            selected_items = [];
            $.each( output.rule_id, function( i, elem) {
                elem = parseInt(elem);
                selected_items.push(elem);
            });
            $('#rules_list').val(selected_items);
        }
    });
});

$('#attach-alert-template').on('hide.bs.modal', function(e) {
    $('#rules_list').val([]);
});

$('#alert-template-attach').click('', function(event) {
    event.preventDefault();
    var template_id = $("#template_id").val();
    var items = [];
    $('#rules_list :selected').each(function(i, selectedElement) {
        items.push($(selectedElement).val());
    });
    var rules = items.join(',');
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "attach-alert-template", template_id: template_id, rule_id: rules },
        dataType: "html",
        success: function(msg) {
            if(msg.indexOf("ERROR:") <= -1) {
                $("#message").html('<div class="alert alert-info">'+msg+'</div>');
                $("#attach-alert-template").modal('hide');
            } else {
                $('#template_error').html('<div class="alert alert-info">'+msg+'</div>');
            }
        },
        error: function() {
            $("#template_error").html('<div class="alert alert-info">The alert rules could not be attached to this template.</div>');
        }
    });
});
</script>
