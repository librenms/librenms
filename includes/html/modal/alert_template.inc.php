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

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

?>

<div class="modal fade bs-example-modal-lg" id="alert-template" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="Create">Alert Template :: <a target="_blank" href="https://docs.librenms.org/Alerting/Templates/"><i class="fa fa-book fa-1x"></i> Docs</a></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Template name: </label>
                            <input type="text" class="form-control input-sm" id="name" name="name">
                        </div>
                        <div class="form-group">
                            <label for="template">Template: </label>
                            <textarea class="form-control" id="template" name="template" style="font-family: Menlo, Monaco, Consolas, 'Courier New', monospace;" rows="15"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="rules_list">Attach template to rules: </label>
                            <select id="rules_list" name="rules_list[]" class="form-control" multiple="multiple"></select>
                        </div>
                        <div class="form-group">
                            <label for="title">Alert title: </label>
                            <input type="text" class="form-control input-sm" id="title" name="title" placeholder="Alert Title">
                        </div>
                        <div class="form-group">
                            <label for="title_rec">Recovery title: </label>
                            <input type="text" class="form-control input-sm" id="title_rec" name="title_rec" placeholder="Recovery Title">
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" name="create-template" id="create-template">Create template</button>
                        <!--//FIXME remove Deprecated template-->
                        <button type="button" class="btn btn-default btn-sm" name="convert-template" id="convert-template" title="Convert template to new syntax" style="display: none">Convert template</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
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
    }
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
            var selected_rules = [];
            $.each(output.rules, function(i, rule) {
                var ruleElem = $('<option>', {
                    value: rule.id,
                    text : rule.name
                }).attr('data-usedby', '');
                if (rule.selected) {
                    selected_rules.push(parseInt(rule.id));
                } else if (rule.used !== '') {
                    ruleElem.attr('data-usedby', rule.used).prop("disabled", true);
                }
                $('#rules_list').append(ruleElem);
            });
            $('#rules_list').select2({
                theme: "bootstrap",
                dropdownAutoWidth : true,
                width: "auto",
                allowClear: true,
                placeholder: "Nothing selected",
                templateResult: function(data) {
                    if (data.id && data.element.dataset.usedby !== '') {
                        return $(
                            '<span>' + data.text + ' <span class="label label-default">Used in template "' + data.element.dataset.usedby + '"</span></span>'
                        );
                    } else if (data.id && data.selected) {
                        return $(
                            '<span><i class="fa fa-check"></i> ' + data.text + '</span>'
                        );
                    }
                    return data.text;
                }
            }).val(selected_rules).trigger("change");
            //FIXME remove Deprecated template
            if(output['template'].indexOf("{/if}")>=0){
                toastr.info('The old template syntax is no longer supported. Please see https://docs.librenms.org/Alerting/Old_Templates/');
                $('#convert-template').show();
            }
        }
    });
});

$('#alert-template').on('hide.bs.modal', function(event) {
    $('#template_id').val('');
    $('#template').val('');
    $('#line').val('');
    $('#value').val('');
    $('#name').val('');
    $('#rules_list').find('option').remove().end().select2('destroy');
    $('#create-template').text('Create template');
    $('#default-template').val('0');
    $('#reset-default').remove();
    $('#name').prop("disabled",false);
    $('#error').val('');
    //FIXME remove Deprecated template
    $('#convert-template').hide();
});

$('#create-template').on("click", function(e) {
    e.preventDefault();

    var rules_items = $('#rules_list').select2('data');
    var template = $("#template").val();
    var template_id = $("#template_id").val();
    var name = $("#name").val();
    var title = $("#title").val();
    var title_rec = $("#title_rec").val();

    alertTemplateAjaxOps(template, name, template_id, title, title_rec, rules_items);
});

//FIXME remove Deprecated template
$('#convert-template').on("click", function(e) {
    e.preventDefault();
    var template = $("#template").val();
    var title    = $("#title").val();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: {type: "convert-template", template: template, title: title},
        dataType: "json",
        success: function(output) {
            if(output.status === 'ok') {
                toastr.success(output.message);
                $("#convert-template").hide();
                $("#template").val(output.template);
                $("#title").val(output.title);
            } else {
                toastr.error(output.message);
            }
        },
        error: function(){
            toastr.error('An error occurred updating this alert template.');
        }
    });
});

function alertTemplateAjaxOps(template, name, template_id, title, title_rec, rules) {
    var rule_ajax = [];
    var row_rules = [];
    for (var i=0; i < rules.length; i++) {
        rule_ajax.push(rules[i].id);
        row_rules.push({id: rules[i].id, name: rules[i].text});
    }

    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: { type: "alert-templates", template: template, name: name, template_id: template_id, title: title, title_rec: title_rec, rules: rule_ajax.join(',')},
        dataType: "json",
        success: function(output) {
            if(output.status == 'ok') {
                toastr.success(output.message);
                $("#alert-template").modal('hide');
                if(template_id != null && template_id != '') {
                    $('#templatetable tbody tr').each(function (i, row) {
                        if ($(row).children().eq(0).text() == template_id) {
                            $(row).children().eq(1).text(name);
                            return false;
                        }
                    });
                } else {
                    var newrow = [{id: output.newid, templatename: name, alert_rules: JSON.stringify(row_rules)}];
                    $('#templatetable').bootgrid("append", newrow);
                }
            } else {
                toastr.error(output.message);
            }
        },
        error: function(){
            toastr.error('An error occurred updating this alert template.');
        }
    });
}
</script>
