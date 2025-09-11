<div class="mr-4">
    <div class="card">
        <div class="card-header text-center">
            <h4>GVRP Config</h4>
        </div>
        <div class="card-body" style="margin-top:18px; padding:20px;">
            <form id="gvrp-config-form">
                <div class="mb-3">
                    <label for="gvrp_global" class="form-label">GVRP Global Configuration:</label>
                    <select id="gvrp_global" class="form-control" name="gvrp_global">
                        <option value="enable">Enable</option>
                        <option value="disable" selected>Disable</option>
                    </select>
                </div>

                <div class="mb-5" style="margin-top: 16px;">
                    <label for="dynamic_vlan" class="form-label">Set Dynamic VLAN to Take Effect Only on Registration Ports:</label>
                    <select id="dynamic_vlan" class="form-control" name="dynamic_vlan">
                        <option value="enable">Enable</option>
                        <option value="disable" selected>Disable</option>
                    </select>
                </div>

                <div class="d-flex justify-content-center mb-3" style="margin-top: 16px;">
                    <button type="submit" class="btn btn-primary me-2">Apply</button>
                    <button type="reset" class="btn btn-primary">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

$include_path = __DIR__ . '/ab_config/Api/process_'.$current_config.'.php';

// dd($include_path);
?>
<script>


const includePath = "<?php echo $include_path; ?>";
document.getElementById("gvrp-config-form").addEventListener("submit", function(event) {
    event.preventDefault();
    // alert("kk");
    const formData = {
        gvrp_global: document.getElementById("gvrp_global").value,
        dynamic_vlan: document.getElementById("dynamic_vlan").value
    };

    fetch(includePath, { 
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    }).then(response => response.json())
      .then(data => alert("Configuration Applied: " + JSON.stringify(data)))
      .catch(error => console.error("Error:", error));
});
</script>
