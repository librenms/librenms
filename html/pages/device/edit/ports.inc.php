</form>
<span id="message"><small><div class="alert alert-danger">n.b For the first time, please click any button twice.</div></small></span>

<form id='ignoreport' name='ignoreport' method='post' action='' role='form' class='form-inline'>
    <input type='hidden' name='ignoreport' value='yes'>
    <input type='hidden' name='type' value='update-ports'>
    <input type='hidden' name='device' value='<?php echo $device['device_id'];?>'>
    <div class='table-responsive'>
    <table id='edit-ports' class='table table-striped'>
        <thead>
            <tr>
                <th data-column-id='ifIndex'>Index</th>
                <th data-column-id='ifName'>Name</th>
                <th data-column-id='ifAdminStatus'>Admin</th>
                <th data-column-id='ifOperStatus'>Oper</th>
                <th data-column-id='disabled' data-sortable='false'>Disable</th>
                <th data-column-id='ignore' data-sortable='false'>Ignore</th>
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
                    $this.next().addClass('glyphicon-ok');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-success');
                        $this.next().removeClass('glyphicon-ok');
                    }, 2000);
                } else if (data.status == 'na') {

                } else {
                    $(this).closest('.form-group').addClass('has-error');
                    $this.next().addClass('glyphicon-remove');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-error');
                        $this.next().removeClass('glyphicon-remove');
                    }, 2000);
                }
            },
            error: function () {
                $(this).closest('.form-group').addClass('has-error');
                $this.next().addClass('glyphicon-remove');
                setTimeout(function(){
                   $this.closest('.form-group').removeClass('has-error');
                   $this.next().removeClass('glyphicon-remove');
                }, 2000);
            }
        });
    });
    $(document).ready(function() {
        $('form#ignoreport').submit(function (event) {
            $('#disable-toggle').click(function (event) {
                // invert selection on all disable buttons
                event.preventDefault();
                $('input[name^="disabled_"]').trigger('click');
            });
            $('#ignore-toggle').click(function (event) {
                // invert selection on all ignore buttons
                event.preventDefault();
                $('input[name^="ignore_"]').trigger('click');
            });
            $('#disable-select').click(function (event) {
                // select all disable buttons
                event.preventDefault();
                $('.disable-check').prop('checked', true);
            });
            $('#ignore-select').click(function (event) {
                // select all ignore buttons
                event.preventDefault();
                $('.ignore-check').prop('checked', true);
            });
            $('#down-select').click(function (event) {
                // select ignore buttons for all ports which are down
                event.preventDefault();
                $('[name^="operstatus_"]').each(function () {
                    var name = $(this).attr('name');
                    var text = $(this).text();
                    if (name && text == 'down') {
                        // get the interface number from the object name
                        var port_id = name.split('_')[1];
                        // find its corresponding checkbox and toggle it
                        $('input[name="ignore_' + port_id + '"]').trigger('click');
                    }
                });
            });
            $('#alerted-toggle').click(function (event) {
                // toggle ignore buttons for all ports which are in class red
                event.preventDefault();
                $('.red').each(function () {
                    var name = $(this).attr('name');
                    if (name) {
                        // get the interface number from the object name
                        var port_id = name.split('_')[1];
                        // find its corresponding checkbox and toggle it
                        $('input[name="ignore_' + port_id + '"]').trigger('click');
                    }
                });
            });
            $('#form-reset').click(function (event) {
                // reset objects in the form to their previous values
                event.preventDefault();
                $('#ignoreport')[0].reset();
            });
            $('#save-form').click(function (event) {
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
            event.preventDefault();
        });

    });

    var grid = $("#edit-ports").bootgrid({
        ajax: true,
        rowCount: [50,100,250,-1],
        post: function ()
        {
            return {
                id: 'edit-ports',
                device_id: "<?php echo $device['device_id']; ?>"
            };
        },
        url: "ajax_table.php"
    }).on("loaded.rs.jquery.bootgrid", function() {
        $("[name='override_config']").bootstrapSwitch('offColor','danger');
        $('input[name="override_config"]').on('switchChange.bootstrapSwitch',  function(event, state) {
            override_config(event,state,$(this));
        });
    });
</script>
