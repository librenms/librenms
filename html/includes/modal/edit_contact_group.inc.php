<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Vivia Nguyen-Tran <vivia@ualberta.ca>
 *
 * Heavily based off of new_alert_rule.inc.php
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Authentication\Auth;

if (Auth::user()->hasGlobalAdmin()) {
    ?>
<!--Modal for adding or updating a contact group -->
    <div class="modal fade" id="edit-contact-group" tabindex="-1" role="dialog"
         aria-labelledby="Edit-contact" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Edit-contact">Alert Contact Groups :: <a href="https://docs.librenms.org/Alerting/">Docs <i class="fa fa-book fa-1x"></i></a> </h5>
                </div>
                <div class="modal-body">
                    <form method="post" role="form" id="contact-group" class="form-horizontal contact-group-form">
                        <input type="hidden" name="group_id" id="group_id" value="">
                        <input type="hidden" name="type" id="type" value="contact-groups">
                        <div class='form-group' title="The description of this contact group.">
                            <label for='name' class='col-sm-3 col-md-2 control-label'>Contact Group: </label>
                            <div class='col-sm-9 col-md-10'>
                                <input type='text' id='group-name' name='name' class='form-control validation' maxlength='200' required>
                            </div>
                        </div>
                        <div class="form-group" title="The members for this contact group.">
                            <label for='transport-choice' class='col-sm-3 col-md-2 control-label'>Contact Members: </label>
                            <div class="col-sm-9 col-md-10">
                                <select name='members[]' id='members' class='form-control' multiple="multiple"></select>
                            </div>
                        </div>
                        <div class="col-sm-12 text-center">
                            <button type="button" class="btn btn-success" id="save-group" name="save-group">
                            Save Contact Group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<!-- Modal end for adding or updating an alert contact-->

<!-- Modal for deleting contact group -->
    <div class="modal fade" id="delete-contact-group" tabindex="-1" role=dialog"
        aria-labelledby="Delete" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Delete">Confirm Group Delete</h5>
                </div>
                <div class="modal-body">
                    <p>If you would like to remove this contact group then please click Delete.</p>
                </div>
                <div class="modal-footer">
                    <form role="form" class="remove_contract_group">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger danger" id="remove-contact-group" data-target="remove-contact-group">Delete</button>
                        <input type="hidden" name="group_id" id="delete_group_id" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>
<!-- Modal end for deleting contact group-->

    <script>
    $("#edit-contact-group").on("show.bs.modal", function (e) {
        // Get group-id of the clicked element
        var group_id = $(e.relatedTarget).data("group_id");
        $("#group_id").val(group_id);
        
        // Reset form
        $(this).find("input[type=text]").val("");
        var $members = $("#members");
        $members.empty();
        $members.val(null).trigger('change');
        
        if (group_id > 0) {
            $.ajax({
                type: "POST",
                url: "ajax_form.php",
                data: { type: "show-contact-group", group_id: group_id},
                success: function (group) {
                    $("#group-name").val(group.name);
                    $.each(group.members, function(index, value) {
                        var option = new Option(value.text, value.id, true, true);
                        $members.append(option).trigger("change");
                    });
                },
                error: function () {
                    toastr.error("Failed to process contact group");
                }
            });
        }
    });

    $("#save-group").on("click", function (e) {
        e.preventDefault();
        data = $("form.contact-group-form").serializeArray();
        console.log(data);
        if (data != null) {
            $.ajax({
                type: "POST",
                url: "ajax_form.php",
                data: data,
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        toastr.success(data.message);
                        setTimeout(function () {
                            $("edit-contact-group").modal("hide");
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function () {
                    toastr.error("Failed to proccess contact group");
                }
            });
        }
    });

    $("#members").select2({
        width: "100%",
        placeholder: "Contact Name",
        ajax: {
            url: 'ajax_list.php',
            delay: 250,
            data: function(params) {
                return {
                    type: "contacts",
                    search: params.term
                }
            }
        }
    });
    
    // Populate group id value
    $("#delete-contact-group").on("show.bs.modal", function (event) {
        group_id = $(event.relatedTarget).data("group_id");
        $("#delete_group_id").val(group_id);
    });

    // Delete the contact group
    $("#remove-contact-group").click('', function (event) {
        event.preventDefault();
        var group_id = $("#delete_group_id").val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "delete-contact-group", group_id: group_id},
            dataType: "json",
            success: function(data) {
                if (data.status == 'ok') {
                    toastr.success(data.message);
                    setTimeout(function () {
                        $("#delete-contact-group").modal("hide");
                        window.location.reload();
                    }, 500);
                } else {
                    $("#message").html("<div class='alert alert-info>"+data.message+"</div>");
                    $("#delete-contact-group").modal("hide");
                }
            },
            error: function() {
                $("#message").html("<div class='alert alert-info'>The alert contact could not be deleted.</div>");
                $("#delete-contact-group").modal("hide");
            } 
        });
    });
    
    </script>

    <?php
}
