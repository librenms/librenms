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

    <div class="modal fade" id="edit-alert-contact" tabindex="-1" role="dialog"
         aria-labelledby="Edit-contact" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Edit-contact">Alert Contacts :: <a href="https://docs.librenms.org/Alerting/">Docs <i class="fa fa-book fa-1x"></i></a> </h5>
                </div>
                <div class="modal-body">
                    <form method="post" role="form" id="contacts" class="form-horizontal contacts-form">
                        <input type="hidden" name="contact_id" id="contact_id" value="">
                        <input type="hidden" name="type" id="type" value="alert-contacts">
                        <div class='form-group' title="The description of this alert contact.">
                            <label for='name' class='col-sm-3 col-md-2 control-label'>Contact name: </label>
                            <div class='col-sm-9 col-md-10'>
                                <input type='text' id='name' name='name' class='form-control validation' maxlength='200' required>
                            </div>
                        </div>
                        <div class="form-group" title="The type of transport for this contact.">
                            <label for='transport-choice' class='col-sm-3 col-md-2 control-label'>Transport type: </label>
                            <div class="col-sm-3">
                                <select name='transport-choice' id='transport-choice' class='form-control'>
                                    <option value="email-form" selected>Email</option>
                                    <option value="ciscospark-form">Cisco Spark</option>
                                    <!--Insert more transport type options here has support is added -->
                                </select>
                            </div>
                        </div>
                    </form>
                    <form method="post" role="form" id="email-form" class="form-horizontal transport">
                        <input type="hidden" name="transport-type" id="transport-type" value="email">
                        <div class="form-group" title="The configuration for this transport">
                            <label for="transport-type" class="col-sm-3 col-md-2 control-label">Transport config: </label>
                            <div class="col-sm-3">
                                <select name="transport-config" id="transport-config" class="form-control">
                                    <option selected value="default">Default</option>
                                    <!--Can only have default or other option -->
                                </select>
                            </div>
                        </div>
                        <div class="form-group" title="Email for contact">
                            <label for="email" class="col-sm-3 col-md-2 control-label">Email: </label>
                            <div class="col-sm-9 col-md-10">
                                <input type="text" id="email" name="email" class="form-control" required>
                            </div>
                        </div>
                    </form>
                    <form method="post" role="form" id="ciscospark-form" class="form-horizontal transport" style="display:none">
                        <input type="hidden" name="transport-type" id="transport-type" value="ciscospark">
                        <div class="form-group" title="The configuration for this transport">
                            <label for="transport-type" class="col-sm-3 col-md-2 control-label">Transport config: </label>
                            <div class="col-sm-3">    
                                <select name="transport-config" id="transport-config" class="form-control">
                                    <option selected value="none">None</option>
                                    <!--Can only have none option -->
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="api-token" class="col-sm-3 col-md-2 control-label">API Token: </label>
                            <div class="col-sm-9 col-md-10">
                                <input type="text" id="api-token" name="api-token" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="room-id" class="col-sm-3 col-md-2 control-label">RoomID: </label>
                            <div class="col-sm-9 col-md-10">
                                <input type="text" id="room-id" name="room-id" class="form-control" required>
                            </div>
                        </div>
                    </form>
                    <div class="col-sm-12 text-center">
                        <button type="button" class="btn btn-success" id="btn-save" name="save-contact">
                        Save Rule
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        
        // Display different form on selection 
        $("#transport-choice").change(function (){
            $(".transport").hide();
            $("#" + $(this).val()).show();
        
        });

        // Save alert contact
        $("#btn-save").on("click", function (e) {
            e.preventDefault();

            //Combine form data (general and contact specific)
            data = $("form.contacts-form").serializeArray();
            data = data.concat($("#" + $("#transport-choice").val()).serializeArray());
            
            if (data !== null) {
                //post data to ajax form
                $.ajax({
                    type: "POST",
                    url: "ajax_form.php",
                    data: data,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'ok') {
                            toastr.success(data.message);
                            setTimeout(function (){
                                $("#edit-alert-contacts").modal("hide");
                                window.location.reload();
                            }, 500);
                        } else {
                            toastr.error(data.message);
                        }
                    },
                    error: function () {
                        toastr.error("Failed to process alert contact");
                    }
                });
            }
        });

    </script>

    <?php
}
