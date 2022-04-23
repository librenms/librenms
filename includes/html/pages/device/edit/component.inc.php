<span id="message"><small><div class="alert alert-danger">n.b For the first time, please click any button twice.</div></small></span>

<form id='components' class='form-inline' method='POST'>
    <?php echo csrf_field() ?>
    <table id='table' class='table table-condensed table-responsive table-striped'>
        <thead>
            <tr>
                <th data-column-id='id'>ID</th>
                <th data-column-id='type'>Type</th>
                <th data-column-id='label'>Label</th>
                <th data-column-id='status'>Status</th>
                <th data-column-id='disable' data-sortable='false'>Disable</th>
                <th data-column-id='ignore' data-sortable='false'>Ignore alert tag</th>
            </tr>
        </thead>
    </table>
    <input type='hidden' name='component' value='yes'>
    <input type='hidden' name='type' value='component'>
    <input type='hidden' name='device' value='<?php echo $device['device_id']; ?>'>
</form>
<script>
    // Waiting for the document to be ready.
    $(document).ready(function() {

        $('form#components').on("submit", function (event) {

            $('#disable-toggle').on("click", function (event) {
                // invert selection on all disable buttons
                event.preventDefault();
                $('input[name^="dis_"]').trigger('click');
            });

            $('#disable-select').on("click", function (event) {
                // select all disable buttons
                event.preventDefault();
                $('.disable-check').prop('checked', true);
            });
            $('#ignore-toggle').on("click", function (event) {
                // invert selection on all ignore buttons
                event.preventDefault();
                $('input[name^="ign_"]').trigger('click');
            });
            $('#ignore-select').on("click", function (event) {
                // select all ignore buttons
                event.preventDefault();
                $('.ignore-check').prop('checked', true);
            });
            $('#warning-select').on("click", function (event) {
                // select ignore button for all components that are in a warning state.
                event.preventDefault();
                $('[name^="status_"]').each(function () {
                    var name = $(this).attr('name');
                    var text = $(this).text();
                    if (name && text == 'Warning') {
                        // get the component number from the object name
                        var id = name.split('_')[1];
                        // find its corresponding checkbox and toggle it
                        $('input[name="ign_' + id + '"]').trigger('click');
                    }
                });
            });
            $('#critical-select').on("click", function (event) {
                // select ignore button for all components that are in a critical state.
                event.preventDefault();
                $('[name^="status_"]').each(function () {
                    var name = $(this).attr('name');
                    var text = $(this).text();
                    if (name && text == 'Critical') {
                        // get the component number from the object name
                        var id = name.split('_')[1];
                        // find its corresponding checkbox and toggle it
                        $('input[name="ign_' + id + '"]').trigger('click');
                    }
                });
            });
            $('#form-reset').on("click", function (event) {
                // reset objects in the form to the value when the page was loaded
                event.preventDefault();
                $('#components')[0].reset();
            });
            $('#save-form').on("click", function (event) {
                event.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "ajax_form.php",
                    data: $('form#components').serialize(),
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

    var grid = $("#table").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id: 'component',
                device_id: "<?php echo $device['device_id']; ?>"
            };
        },
        url: "ajax_table.php"
    });
</script>
