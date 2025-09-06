<div class="container text-center" style="margin-top: 50px;">
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">

            <!-- VLAN Configured Display -->
            <div class="panel panel-default">
                <div class="panel-heading"><strong>VLAN Configured</strong></div>
                <div class="panel-body">
                     <p>
            @if (count($vlanList))
                {{ implode(', ', $vlanList) }}
            @else
                <span class="text-muted">No VLANs found.</span>
            @endif
        </p>
                </div>
            </div>

            <!-- VLAN Add/Delete Form -->
            <form id="vlanBatchForm">
                <div class="form-group">
                    <label for="vlanAdd">VLAN Add <small>&lt;2–4094&gt;</small></label>
                    <input type="text" class="form-control text-center" name="vlanAdd" id="vlanAdd" placeholder="Enter VLAN(s) to Add">
                </div>

                <div class="form-group">
                    <label for="vlanDelete">VLAN Delete <small>&lt;2–4094&gt;</small></label>
                    <input type="text" class="form-control text-center" name="vlanDelete" id="vlanDelete" placeholder="Enter VLAN(s) to Delete">
                </div>

                <!-- Buttons -->
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-success">Apply</button>
                    <button type="reset" class="btn btn-default">Reset</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Result Message -->
<div id="resultMsg" class="alert mt-3" style="display:none;"></div>

<script>
$('#vlanBatchForm').on('submit', function(e) {
    e.preventDefault();

    const form = $(this);
    const resultMsg = $('#resultMsg');
    
    resultMsg
        .removeClass()
        .addClass('alert alert-info')
        .text('Processing...')
        .show();

    $.ajax({
        url: '{{ route("vlan.batch.store") }}',
        method: 'POST',
        data: form.serialize(),
        success: function(response) {
            if (response.success) {
                resultMsg
                    .removeClass()
                    .addClass('alert alert-success')
                    .text(response.message);
                form[0].reset();
            } else {
                resultMsg
                    .removeClass()
                    .addClass('alert alert-warning')
                    .text(response.message || 'Validation failed.');
            }
        },
        error: function(xhr) {
            resultMsg
                .removeClass()
                .addClass('alert alert-danger')
                .text('Error running Ansible script.');
        }
    });
});
</script>
