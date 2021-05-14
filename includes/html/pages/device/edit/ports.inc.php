</form>
<h3> Port Settings </h3>
<span id="message"></span>

<form id='ignoreport' name='ignoreport' method='post' action='' role='form' class='form-inline'>
    <?php echo csrf_field() ?>
    <input type='hidden' name='ignoreport' value='yes'>
    <input type='hidden' name='type' value='update-ports'>
    <input type='hidden' name='device' value='<?php echo $device['device_id']; ?>'>
    <div class='table-responsive'>
    <table id='edit-ports' class='table table-striped'>
        <thead>
            <tr>
                <th data-column-id='ifIndex'>Index</th>
                <th data-column-id='ifName'>Name</th>
                <th data-column-id='ifAdminStatus'>Admin</th>
                <th data-column-id='ifOperStatus'>Operational</th>
                <th data-column-id='disabled' data-sortable='false'>Disable polling</th>
                <th data-column-id='ignore' data-sortable='false'>Ignore alert tag</th>
                <th data-column-id='ifSpeed'>ifSpeed (bits/s)</th>
                <th data-column-id='portGroup' data-sortable='false' data-searchable='false'>Port Group</th>
                <th data-column-id='port_tune' data-sortable='false' data-searchable='false'>RRD Tune</th>
                <th data-column-id='ifAlias'>Description</th>
            </tr>
        </thead>
    </table>
    </div>
</form>
<script>

//$("[name='override_config']").bootstrapSwitch('offColor','danger');
    $(document).on('blur', "[name='if-alias']", function (){
        var $this = $(this);
        var descr = $this.val();
        var device_id = $this.data('device_id');
        var port_id = $this.data('port_id');
        var ifName = $this.data('ifname');
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "update-ifalias", descr: descr, ifName: ifName, port_id: port_id, device_id: device_id},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $this.closest('.form-group').addClass('has-success');
                    $this.next().addClass('fa-check');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-success');
                        $this.next().removeClass('fa-check');
                    }, 2000);
                } else if (data.status == 'na') {

                } else {
                    $(this).closest('.form-group').addClass('has-error');
                    $this.next().addClass('fa-times');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-error');
                        $this.next().removeClass('fa-times');
                    }, 2000);
                }
            },
            error: function () {
                $(this).closest('.form-group').addClass('has-error');
                $this.next().addClass('fa-times');
                setTimeout(function(){
                   $this.closest('.form-group').removeClass('has-error');
                   $this.next().removeClass('fa-times');
                }, 2000);
            }
        });
    });
    $(document).on('blur keyup', "[name='if-speed']", function (e){
        if (e.type === 'keyup' && e.keyCode !== 13) return;
        var $this = $(this);
        var speed = $this.val().replace(/[^0-9]/gi, '');
        var device_id = $this.data('device_id');
        var port_id = $this.data('port_id');
        var ifName = $this.data('ifname');
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "update-ifspeed", speed: speed, ifName: ifName, port_id: port_id, device_id: device_id},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $this.closest('.form-group').addClass('has-success');
                    $this.next().addClass('fa-check');
                    $this.val(speed);
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-success');
                        $this.next().removeClass('fa-check');
                    }, 2000);
                } else if (data.status == 'na') {

                } else {
                    $(this).closest('.form-group').addClass('has-error');
                    $this.next().addClass('fa-times');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-error');
                        $this.next().removeClass('fa-times');
                    }, 2000);
                }
            },
            error: function () {
                $(this).closest('.form-group').addClass('has-error');
                $this.next().addClass('fa-times');
                setTimeout(function(){
                   $this.closest('.form-group').removeClass('has-error');
                   $this.next().removeClass('fa-times');
                }, 2000);
            }
        });
    });
    $(document).ready(function() {
        $('#disable-toggle').on("click", function (event) {
            // invert selection on all disable buttons
            event.preventDefault();
            $('input[name^="disabled_"]').trigger('click');
        });
        $('#ignore-toggle').on("click", function (event) {
            // invert selection on all ignore buttons
            event.preventDefault();
            $('input[name^="ignore_"]').trigger('click');
        });
        $('#disable-select').on("click", function (event) {
            // select all disable buttons
            event.preventDefault();
            $('.disable-check').bootstrapSwitch('state', true);
        });
        $('#ignore-select').on("click", function (event) {
            // select all ignore buttons
            event.preventDefault();
            $('.ignore-check').bootstrapSwitch('state', true);
        });
        $('#down-select').on("click", function (event) {
            // select ignore buttons for all ports which are down
            event.preventDefault();
            $('[id^="operstatus_"]').each(function () {
                var name = $(this).attr('id');
                var text = $(this).text();
                if (name && text === 'down') {
                    // get the interface number from the object name
                    var port_id = name.split('_')[1];
                    // find its corresponding checkbox and enable it
                    $('input[name="ignore_' + port_id + '"]').bootstrapSwitch('state', true);
                }
            });
        });
        $('#alerted-toggle').on("click", function (event) {
            // toggle ignore buttons for all ports which are in class red
            event.preventDefault();
            $('.red').each(function () {
                var name = $(this).attr('id');
                if (name) {
                    // get the interface number from the object name
                    var port_id = name.split('_')[1];
                    // find its corresponding checkbox and enable it
                    $('input[name="ignore_' + port_id + '"]').bootstrapSwitch('state', true);
                }
            });
        });
        $('#form-reset').on("click", function (event) {
            // reset objects in the form to their previous values
            event.preventDefault();
            $('#ignoreport')[0].reset();
        });
        $('#save-form').on("click", function (event) {
            // reset objects in the form to their previous values
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "ajax_form.php",
                data: $('form#ignoreport').serialize(),
                dataType: "json",
                success: function(data){
                    if (data.status == 'ok') {
                        $("#message").html('<div class="alert alert-info">' + data.message + '</div>')
                    } else {
                        $("#message").html('<div class="alert alert-danger">' + data.message + '</div>');
                    }
                },
                error: function(){
                    $("#message").html('<div class="alert alert-danger">Error creating config item</div>');
                }
            });
        });

        $('form#ignoreport').on("submit", function (event) {
            event.preventDefault();
        });
    });

    var grid = $("#edit-ports").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        templates: {
            header: '<div id="{{ctx.id}}" class="{{css.header}}"><div class="row">\
                        <div class="col-sm-8 actionBar header_actions">\
                            <span class="pull-left">\
                                <span class="action_group">Disable polling\
                                <button type="submit" value="Toggle" class="btn btn-default btn-sm" id="disable-toggle" title="Toggle polling for all ports">Toggle</button>\
                                <button type="submit" value="Select" class="btn btn-default btn-sm" id="disable-select" title="Disable polling on all ports">Disable All</button>\
                                </span>\
                                <span class="action_group">Ignore alerts\
                                <button type="submit" value="Alerted" class="btn btn-default btn-sm" id="alerted-toggle" title="Toggle alerting on all currently-alerted ports">Alerted</button>\
                                <button type="submit" value="Down" class="btn btn-default btn-sm" id="down-select" title="Disable alerting on all currently-down ports">Down</button>\
                                <button type="submit" value="Toggle" class="btn btn-default btn-sm" id="ignore-toggle" title="Toggle alert tag for all ports">Toggle</button>\
                                <button type="submit" value="Select" class="btn btn-default btn-sm" id="ignore-select" title="Disable alert tag on all ports">Ignore All</button></span>\
                                </span>\
                                <span class="action_group">\
                                <button id="save-form" type="submit" value="Save" class="btn btn-success btn-sm" title="Save current port disable/ignore settings">Save Toggles</button>\
                                <button type="submit" value="Reset" class="btn btn-danger btn-sm" id="form-reset" title="Reset form to previously-saved settings">Reset</button>\
                                </span>\
                            </span>\
                        </div>\
                        <div class="col-sm-4 actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div>\
                    </div></div>'
        },
        post: function ()
        {
            return {
                device_id: "<?php echo $device['device_id']; ?>"
            };
        },
        url: "<?php echo url('/ajax/table/edit-ports/'); ?>"
    }).on("loaded.rs.jquery.bootgrid", function() {
        $("[type='checkbox']").bootstrapSwitch();
        $("[name='override_config']").bootstrapSwitch('offColor','danger');
        $('input[name="override_config"]').on('switchChange.bootstrapSwitch',  function(event, state) {
            override_config(event,state,$(this));
        });

        init_select2('.port_group_select', 'port-group', {}, null, 'No Group');
        $('.port_group_select').on('change', function (e) {
            var $target = $(e.target)
            $.ajax({
                type: "PUT",
                url: "<?php echo url('port'); ?>/" + $target.data('port_id'),
                data: {"groups": $target.val()},
                success: function(data) {
                    toastr.success(data.message)
                },
                error: function(data) {
                    toastr.error(data.responseJSON.message)
                }
            });
        });
    });
</script>
<style>
    .header_actions {
        text-align: left !important;
    }
    .action_group {
        margin-right: 20px;
        white-space: nowrap;
    }
</style>
