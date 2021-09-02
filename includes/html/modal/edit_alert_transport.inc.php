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

use LibreNMS\Config;

if (Auth::user()->hasGlobalAdmin()) {
    ?>
<!--Modal for adding or updating an alert transport -->
    <div class="modal fade" id="edit-alert-transport" tabindex="-1" role="dialog"
         aria-labelledby="Edit-transport" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Edit-transport">Alert Transport :: <a target="_blank" href="https://docs.librenms.org/Alerting/">Docs <i class="fa fa-book fa-1x"></i></a> </h5>
                </div>
                <div class="modal-body">
                    <form method="post" role="form" id="transports" class="form-horizontal transports-form">
                        <?php echo csrf_field() ?>
                        <input type="hidden" name="transport_id" id="transport_id" value="">
                        <input type="hidden" name="type" id="type" value="alert-transports">
                        <div class='form-group' title="The description of this alert transport.">
                            <label for='name' class='col-sm-3 col-md-2 control-label'>Transport name: </label>
                            <div class='col-sm-9 col-md-10'>
                                <input type='text' id='name' name='name' class='form-control validation' maxlength='200' required>
                            </div>
                        </div>
                        <div class="form-group" title="The type of transport.">
                            <label for='transport-choice' class='col-sm-3 col-md-2 control-label'>Transport type: </label>
                            <div class="col-sm-3">
                                <select name='transport-choice' id='transport-choice' class='form-control'>
    <?php

// Create list of transport
    $transport_dir = Config::get('install_dir') . '/LibreNMS/Alert/Transport';
    $transports_list = [];
    foreach (scandir($transport_dir) as $transport) {
        $transport = strstr($transport, '.', true);
        if (empty($transport)) {
            continue;
        }
        $transports_list[] = $transport;
    }
    foreach ($transports_list as $transport) {
        echo '<option value="' . strtolower($transport) . '-form">' . $transport . '</option>';
    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" title="The transport is default.">
                            <label for="default" class="col-sm-3 col-md-2 control-label">Default Alert: </label>
                            <div class="col-sm-2">
                                <input type="checkbox" name="is_default" id="is_default">
                            </div>
                        </div>
                    </form>
    <?php

    $switches = []; // store names of bootstrap switches
    foreach ($transports_list as $transport) {
        $class = 'LibreNMS\\Alert\\Transport\\' . $transport;

        if (! method_exists($class, 'configTemplate')) {
            // Skip since support has not been added
            continue;
        }

        echo '<form method="post" role="form" id="' . strtolower($transport) . '-form" class="form-horizontal transport">';
        echo csrf_field();
        echo '<input type="hidden" name="transport-type" id="transport-type" value="' . strtolower($transport) . '">';

        $tmp = call_user_func($class . '::configTemplate');

        foreach ($tmp['config'] as $item) {
            if ($item['type'] !== 'hidden') {
                echo '<div class="form-group" title="' . $item['descr'] . '">';
                echo '<label for="' . $item['name'] . '" class="col-sm-3 col-md-2 control-label">' . $item['title'] . ': </label>';
                if ($item['type'] == 'text' || $item['type'] == 'password') {
                    echo '<div class="col-sm-9 col-md-10">';
                    echo '<input type="' . $item['type'] . '" id="' . $item['name'] . '" name="' . $item['name'] . '" class="form-control" ';
                    if ($item['required']) {
                        echo 'required>';
                    } else {
                        echo '>';
                    }
                    echo '</div>';
                } elseif ($item['type'] == 'checkbox') {
                    echo '<div class="col-sm-2">';
                    echo '<input type="checkbox" name="' . $item['name'] . '" id="' . $item['name'] . '">';
                    echo '</div>';
                    $switches[$item['name']] = $item['default'];
                } elseif ($item['type'] == 'select') {
                    echo '<div class="col-sm-3">';
                    echo '<select name="' . $item['name'] . '" id="' . $item['name'] . '" class="form-control">';
                    foreach ($item['options'] as $descr => $opt) {
                        echo '<option value="' . $opt . '">' . $descr . '</option>';
                    }
                    echo '</select>';
                    echo '</div>';
                } elseif ($item['type'] === 'textarea') {
                    echo '<div class="col-sm-9 col-md-10">';
                    echo '<textarea name="' . $item['name'] . '" id="' . $item['name'] . '" class="form-control" placeholder="' . $item['descr'] . '">';
                    echo '</textarea>';
                    echo '</div>';
                } elseif ($item['type'] === 'oauth') {
                    $class = isset($item['class']) ? $item['class'] : 'btn-success';
                    $callback = urlencode(url()->current() . '/?oauthtransport=' . $transport);
                    $url = $item['url'] . $callback;

                    echo '<a class="btn btn-oauth ' . $class . '"';
                    echo '" href="' . $url . '" data-base-url="' . $url . '">';
                    if (isset($item['icon'])) {
                        echo '<img src="' . asset('images/transports/' . $item['icon']) . '"  width="24" height="24"> ';
                    }
                    echo $item['descr'];
                    echo '</a>';
                }
                echo '</div>';
            }
        }
        echo '<div class="form-group">';
        echo '<div class="col-sm-12 text-center">';
        echo '<button type="button" class="btn btn-success btn-save" name="save-transport">';
        echo 'Save Transport';
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '</form>';
    } ?>
                </div>
            </div>
        </div>
    </div>
<!-- Modal end for adding or updating an alert tramsport-->

<!--Modal for deleting an alert transport -->
    <div class="modal fade" id="delete-alert-transport" tabindex="-1" role="dialog"
         aria-labelledby="Delete" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Delete">Confirm Transport Delete</h5>
                </div>
                <div class="modal-body">
                    <p>If you would like to remove this alert transport then please click Delete.</p>
                </div>
                <div class="modal-footer">
                    <form role="form" class="remove_transport_form">
                        <?php echo csrf_field() ?>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger danger" id="remove-alert-transport" data-target="remove-alert-transport">Delete</button>
                        <input type="hidden" name="transport_id" id="delete_transport_id" value="">
                        <input type="hidden" name="confirm" id="confirm" value="yes">
                    </form>
                </div>
            </div>
        </div>
    </div>
<!--Modal end for deleting an alert transport -->

    <script>
        // Scripts related to editing/updating alert transports

        // Display different form on selection
        $("#transport-choice").on("change", function (){
            $(".transport").hide();
            $("#" + $(this).val()).show().find("input:text").val("");

        });

        $("#edit-alert-transport").on("show.bs.modal", function(e) {
            // Get transport id of clicked element
            var transport_id = $(e.relatedTarget).data("transport_id");
            $("#transport_id").val(transport_id);
            if(transport_id > 0) {
                $.ajax({
                    type: "POST",
                    url: "ajax_form.php",
                    data: { type: "show-alert-transport", transport_id: transport_id },
                    success: function (data) {
                        loadTransport(data);
                    },
                    error: function () {
                        toastr.error("Failed to process alert transport");
                    }
                });

            } else {
            // Resetting to default
                $("#name").val("");
                $("#transport-choice").val("mail-form");
                $(".transport").hide();
                $("#" + $("#transport-choice").val()).show().find("input:text").val("");
                $("#is_default").bootstrapSwitch('state', false);

                // Turn on all switches in form
                var switches = <?php echo json_encode($switches); ?>;
                $.each(switches, function(name, state) {
                    $("input[name="+name+"]").bootstrapSwitch('state', state);
                });
            }
        });

        function loadTransport(transport) {
            var form_id = transport.type+"-form";
            var transport_form = $("#" + form_id);

            $("#name").val(transport.name);
            $("#transport-choice").val(form_id);
            $("#is_default").bootstrapSwitch('state', transport.is_default);
            $(".transport").hide();
            transport_form.show().find("input:text").val("");

            // Populate the field values
            transport.details.forEach(function(config) {
                var $field = transport_form.find("#" + config.name);
                if ($field.prop('type') == 'checkbox') {
                    $field.bootstrapSwitch('state', config.value);
                } else {
                    $field.val(config.value);
                }
            });
        }

        $(".btn-oauth").on("click", function (e) {
            this.href = $(this).data('base-url') + '%26id=' + $("#transport_id").val();
        });

        // Save alert transport
        $(".btn-save").on("click", function (e) {
            e.preventDefault();

            //Combine form data (general and transport specific)
            data = $("form.transports-form").serializeArray();
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
                                $("#edit-alert-transports").modal("hide");
                                window.location.reload();
                            }, 500);
                        } else {
                            toastr.error(data.message);
                        }
                    },
                    error: function () {
                        toastr.error("Failed to process alert transport");
                    }
                });
            }
        });

        // Scripts related to deleting an alert transport

        // Populate transport id value
        $("#delete-alert-transport").on("show.bs.modal", function(event) {
            transport_id = $(event.relatedTarget).data("transport_id");
            $("#delete_transport_id").val(transport_id);
        });

        // Delete the alert transport
        $("#remove-alert-transport").on("click", function(event) {
            event.preventDefault();
            var transport_id = $("#delete_transport_id").val();
            $.ajax({
                type: "POST",
                url: "ajax_form.php",
                data: { type: "delete-alert-transport", transport_id: transport_id },
                dataType: "json",
                success: function(data) {
                    if (data.status == 'ok') {
                        toastr.success(data.message);
                        $("#alert-transport-" + transport_id).remove();
                        $("#delete-alert-transport").modal("hide");
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
