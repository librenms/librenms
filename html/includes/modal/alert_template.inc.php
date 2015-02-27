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

if(is_admin() === false) {
    die('ERROR: You need to be admin');
}

?>

<div class="modal fade bs-example-modal-lg" id="alert-template" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="Create">Alert Rules</h4>
            </div>
            <div class="modal-body">
                <form method="post" role="form" id="rules" class="form alert-template-form">
                <div class="row">
                    <div class="col-md-12">
                        <span id="response"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="template" class="control-label">Template:</label><br />
                            <div class="alert alert-danger" role="alert">You can enter text for your template directly below if you're feeling brave enough :)</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="designer" class="control-label">Designer:</label><br />
                            <div class="alert alert-warning" role="alert">The designer below will help you create a template - be warned, it's beta :)</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <textarea class="form-control" id="template" name="template" rows="15"></textarea><br /><br />
                            <strong><em>Give your template a name: </em></strong><br />
                            <input type="text" class="form-control input-sm" id="name" name="name"><br />
                            <span id="error"></span><br />
                            <button type="button" class="btn btn-primary btn-sm" name="create-template" id="create-template">Create template</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <span><strong>Controls:</strong><br />
<?php
    $controls = array('if','endif','else','foreach', 'endforeach');
    foreach ($controls as $control) {
        echo '              <button type="button" class="btn btn-primary btn-sm" data-target="#control-add" id="control-add" name="control-add" data-type="control" data-value="'.$control.'">'.$control.'</button>';
    }
?>
                            </span><br /><br />
                            <span><strong>Placeholders:</strong><br />
<?php
    $placeholders = array('hostname','title','elapsed','id','uid','faults','state','severity','rule','timestamp','contacts','key','value','new line');
    foreach ($placeholders as $placeholder) {
        echo '              <button type="button" class="btn btn-success btn-sm" data-target="#placeholder-add" id="placeholder-add" name="placeholder-add" data-type="placeholder" data-value="'.$placeholder.'">'.$placeholder.'</button>';
    }
?>
                            </span><br /><br />
                            <span><strong>Operator:</strong><br />
<?php
    $operators = array('==','!=','>=','>','<=','<','&&','||','blank');
    foreach ($operators as $operator) {
        echo '              <button type="button" class="btn btn-warning btn-sm" data-target="#operator-add" id="operator-add" name="operator-add" data-type="operator" data-value="'.$operator.'">'.$operator.'</button>';
    }
?>
<br /><br />
                            <small><em>Free text - press enter to add</em></small><br />
                            <input type="text" class="form-control" id="value" name="value" autocomplete="off"><br /><br />
                            <input type="text" class="form-control" id="line" name="line"><br /><br />
                            <input type="hidden" name="template_id" id="template_id">
                            <button type="button" class="btn btn-primary" id="add_line" name="add_line">Add line</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
<script>

$('#alert-template').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var template_id = button.data('template_id');
    var action = button.data('template_action');
    $('#line').val('');
    $('#value').val('');
    if(action == 'edit') {
        $('#template_id').val(template_id);
        $.ajax({
            type: "POST",
            url: "/ajax_form.php",
            data: { type: "parse-alert-template", template_id: template_id },
            dataType: "json",
            success: function(output) {
                $('#template').append(output['template']);
                $('#name').val(output['name']);
            }
        });
    }
});

$('#create-template').click('', function(e) {
    e.preventDefault();
    var template = $("#template").val();
    var template_id = $("#template_id").val();
    var name = $("#name").val();
    $.ajax({
        type: "POST",
        url: "/ajax_form.php",
        data: { type: "alert-templates", template: template , name: name, template_id: template_id},
        dataType: "html",
        success: function(msg){
            if(msg.indexOf("ERROR:") <= -1) {
                $("#message").html('<div class="alert alert-info">'+msg+'</div>');
                $("#alert-template").modal('hide');
                setTimeout(function() {
                    location.reload(1);
                }, 1000);
            } else {
                $("#error").html('<div class="alert alert-info">'+msg+'</div>');
            }
        },
        error: function(){
            $("#error").html('<div class="alert alert-info">An error occurred updating this alert template.</div>');
        }
    });
});

$('#add_line').click('', function(e) {
    e.preventDefault();
    var line = $('#line').val();
    $('#template').append(line + '\r\n');
    $('#line').val('');
});

$('button[name="control-add"],button[name="placeholder-add"],button[name="operator-add"]').click('', function(e) {
    e.preventDefault();
    var type = $(this).data("type");
    var value = $(this).data("value");
    var line = $('#line').val();
    var new_line = '';
    if(type == 'control') {
        $('button[name="control-add"]').prop('disabled',true);
        if(value == 'if') {
            new_line = '{if ';
        } else if(value == 'endif') {
            new_line = '{/if}';
            $('button[name="control-add"]').prop('disabled',false);
        } else if(value == 'else') {
            new_line = ' {else} ';
        } else if(value == 'foreach') {
            new_line = '{foreach ';
        } else if(value == 'endforeach') {
            new_line = '{/foreach} ';
            $('button[name="control-add"]').prop('disabled',false);
        }
    } else if(type == 'placeholder') {
        if($('button[name="control-add"]').prop('disabled') === true) {
            $('button[name="placeholder-add"]').prop('disabled',true);
        }
        if(value == 'new line') {
            new_line = '\\r\\n ';
        } else {
            new_line = '%'+value+' ';
        }
        if(value == 'key' || value == 'value' || value == 'new line') {
            $('button[name="placeholder-add"]').prop('disabled',false);
        }
    } else if(type == 'operator') {
        if(value == 'blank') {
            $('button[name="control-add"]').prop('disabled',false);
            $('button[name="placeholder-add"]').prop('disabled',false);
            new_line = '}';
        } else {
            $('button[name="operator-add"]').prop('disabled',true);
            new_line = value+' ';
        }
    }
    $('#line').val(line + new_line);
    $('#valuee').focus();
});

$('#value').keypress(function (e) {
    if(e.which == 13) {
        updateLine($('#value').val());
        $('#value').val('');
    }
});

function updateLine(value) {
    var line = $('#line').val();
    //$('#value').prop('disabled',true);
    if($('button[name="placeholder-add"]').prop('disabled') === true) {
        value = '"'+value+'" } ';
        //$('#value').prop('disabled',false);
    } else {
        value = value + ' ';
    }
    $('#line').val(line + value);
    $('button[name="control-add"]').prop('disabled',false);
    $('button[name="placeholder-add"]').prop('disabled',false);
    $('button[name="operator-add"]').prop('disabled',false);
}

</script>
