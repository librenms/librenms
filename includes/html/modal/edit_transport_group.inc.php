<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Vivia Nguyen-Tran <vivia@ualberta.ca>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (Auth::user()->hasGlobalAdmin()) {
    ?>
<!--Modal for adding or updating a transport group -->
    <div class="modal fade" id="edit-transport-group" tabindex="-1" role="dialog"
         aria-labelledby="Edit-transport" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Edit-transport">Alert Transport Groups :: <a target="_blank" href="https://docs.librenms.org/Alerting/">Docs <i class="fa fa-book fa-1x"></i></a> </h5>
                </div>
                <div class="modal-body">
                    <form method="post" role="form" id="transport-group" class="form-horizontal transport-group-form">
                        <?php echo csrf_field() ?>
                        <input type="hidden" name="group_id" id="group_id" value="">
                        <input type="hidden" name="type" id="type" value="transport-groups">
                        <div class='form-group' title="The description of this transport group.">
                            <label for='name' class='col-sm-3 col-md-2 control-label'>Group Name: </label>
                            <div class='col-sm-9 col-md-10'>
                                <input type='text' id='group-name' name='name' class='form-control validation' maxlength='200' required>
                            </div>
                        </div>
                        <div class="form-group" title="The members for this transport group.">
                            <label for='transport-choice' class='col-sm-3 col-md-2 control-label'>Group Members: </label>
                            <div class="col-sm-9 col-md-10">
                                <select name='members[]' id='members' class='form-control' multiple="multiple"></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12 text-center">
                                <button type="button" class="btn btn-success" id="save-group" name="save-group">
                                Save Transport Group
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<!-- Modal end for adding or updating an alert transport-->

<!-- Modal for deleting transport group -->
    <div class="modal fade" id="delete-transport-group" tabindex="-1" role=dialog"
        aria-labelledby="Delete" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Delete">Confirm Group Delete</h5>
                </div>
                <div class="modal-body">
                    <p>If you would like to remove this transport group then please click Delete.</p>
                </div>
                <div class="modal-footer">
                    <form role="form" class="remove_contract_group">
                        <?php echo csrf_field() ?>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger danger" id="remove-transport-group" data-target="remove-transport-group">Delete</button>
                        <input type="hidden" name="group_id" id="delete_group_id" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>
<!-- Modal end for deleting transport group-->

    <script>
    $("#edit-transport-group").on("show.bs.modal", function (e) {
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
                data: { type: "show-transport-group", group_id: group_id},
                success: function (group) {
                    $("#group-name").val(group.name);
                    $.each(group.members, function(index, value) {
                        var option = new Option(value.text, value.id, true, true);
                        $members.append(option).trigger("change");
                    });
                },
                error: function () {
                    toastr.error("Failed to process transport group");
                }
            });
        }
    });

    $("#save-group").on("click", function (e) {
        e.preventDefault();
        data = $("form.transport-group-form").serializeArray();
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
                            $("edit-transport-group").modal("hide");
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function () {
                    toastr.error("Failed to proccess transport group");
                }
            });
        }
    });

    $("#members").select2({
        width: "100%",
        placeholder: "Transport Name",
        ajax: {
            url: 'ajax_list.php',
            delay: 250,
            data: function(params) {
                return {
                    type: "transports",
                    search: params.term
                }
            }
        }
    });

    // Populate group id value
    $("#delete-transport-group").on("show.bs.modal", function (event) {
        group_id = $(event.relatedTarget).data("group_id");
        $("#delete_group_id").val(group_id);
    });

    // Delete the transport group
    $("#remove-transport-group").on("click", function (event) {
        event.preventDefault();
        var group_id = $("#delete_group_id").val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "delete-transport-group", group_id: group_id},
            dataType: "json",
            success: function(data) {
                if (data.status == 'ok') {
                    toastr.success(data.message);
                    $("#alert-transport-group-" + group_id).remove();
                    $("#delete-transport-group").modal("hide");
                } else {
                    toastr.error(data.message);
                }
            },
            error: function() {
                toastr.error("The alert transport could not be deleted.");
            }
        });
    });

    </script>

    <?php
}
