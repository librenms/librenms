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

use LibreNMS\Authentication\Auth;

if (!Auth::user()->hasGlobalAdmin()) {
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
                <div class="row">
                    <div class="col-md-12">
                        <span id="response"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Template name: </label>
                                    <input type="text" class="form-control input-sm" id="name" name="name">
                                </div>
                                <div class="form-group">
                                    <label for="template">Template: </label>
                                    <textarea class="form-control" id="template" name="template" rows="15"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="title">Alert title: </label>
                                    <input type="text" class="form-control input-sm" id="title" name="title" placeholder="Alert Title">
                                </div>
                                <div class="form-group">
                                    <label for="title_rec">Recovery title: </label>
                                    <input type="text" class="form-control input-sm" id="title_rec" name="title_rec" placeholder="Recovery Title">
                                </div>
                                <div class="form-group">
                                    <label for="template_type">Template type:</label>
                                    <select size="1" name="template_type" id="template_type" class="form-control">
                                        <option value="blade">Blade</option>
                                        <option value="librenms">LibreNMS (deprecated)</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" name="create-template" id="create-template">Create template</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="panel panel-warning">
                                        <div class="panel-heading">
                                            Templating Docs <a href="https://laravel.com/docs/5.4/blade"><i class="fa fa-book fa-1x"></i></a>
                                        </div>
                                        <div class="panel-body">
                                            {{ placeholder }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

$('#alert-template').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var template_id = $('#template_id').val();
    var default_template = $('#default_template').val();

    if(template_id != null && template_id != '') {
        if(default_template == "1") {
            $('#create-template').after('<span class="pull-right"><button class="btn btn-primary btn-sm" id="reset-default">Reset to Default</button></span>');
            $('#name').prop("disabled",true);
        }
        $('#create-template').text('Update template');
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "parse-alert-template", template_id: template_id },
            dataType: "json",
            success: function(output) {
                $('#template').val(output['template']);
                $('#name').val(output['name']);
                $('#title').val(output['title']);
                $('#title_rec').val(output['title_rec']);
                $('#template_type').val(output['type']);
            }
        });
    }
});

$('#alert-template').on('hide.bs.modal', function(event) {
    $('#template_id').val('');
    $('#template').val('');
    $('#line').val('');
    $('#value').val('');
    $('#name').val('');
    $('#template_type').val('');
    $('#create-template').text('Create template');
    $('#default-template').val('0');
    $('#reset-default').remove();
    $('#name').prop("disabled",false);
    $('#error').val('');
});

$('#create-template').click('', function(e) {
    e.preventDefault();
    var template = $("#template").val();
    var template_id = $("#template_id").val();
    var name = $("#name").val();
    var title = $("#title").val();
    var title_rec = $("#title_rec").val();
    var template_type = $("#template_type").val();
    alertTemplateAjaxOps(template, name, template_id, title, title_rec, template_type);
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

$('div').on('click', 'button#reset-default', function(e) {
    e.preventDefault();
    var template_id = $("#template_id").val();
    var template = '%title\r\nSeverity: %severity\r\n{if %state == 0}Time elapsed: %elapsed\r\n{/if}Timestamp: %timestamp\r\nUnique-ID: %uid\r\nRule: {if %name}%name{else}%rule{/if}\r\n{if %faults}Faults:\r\n{foreach %faults}  #%key: %value.string\r\n{/foreach}{/if}Alert sent to: {foreach %contacts}%value <%key> {/foreach}';
    var name = 'Default Alert Template';
    alertTemplateAjaxOps(template, name, template_id, '', '');
});

function alertTemplateAjaxOps(template, name, template_id, title, title_rec, template_type)
{
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: { type: "alert-templates", template: template, name: name, template_id: template_id, title: title, title_rec: title_rec, template_type: template_type},
        dataType: "json",
        success: function(output){
            if(output.status == 'ok') {
                toastr.success(output.message);
                $("#alert-template").modal('hide');

                if(template_id != null && template_id != '') {
                    $('#templatetable tbody tr').each(function (i, row) {
                        if ($(row).children().eq(0).text() == template_id) {
                            $(row).children().eq(1).text(name);
                            // We found our match so stop looping through the table
                            return false;
                        }
                    });
                } else {
                    var newrow = [{id: output.newid, templatename:name}];
                    $('#templatetable').bootgrid("append", newrow);
                }
            } else {
                $("#error").html('<div class="alert alert-danger">'+msg+'</div>');
            }
        },
        error: function(){
            $("#error").html('<div class="alert alert-danger">An error occurred updating this alert template.</div>');
        }
    });

}

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
