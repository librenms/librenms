<div class="card">
    <div class="card-header">
        <h5>Edit VLAN </h5>
    </div>
    <div class="card-body">


        <div class="d-flex justify-content-center mb-4">
            <div class="text-center" style="width: 200px; margin:auto;">
                <div class="form-group mb-2">
                    <label for="vlanId"><strong>VLAN ID:</strong></label>
                    <input type="text" class="form-control form-control-sm text-center" id="vlanId" name="vlanId"
                        value="{{ $vlanId }}" readonly>
                </div>

                <div class="form-group">
                    <label for="vlanName"><strong>VLAN Name:</strong></label>
                    <input type="text" class="form-control form-control-sm text-center" id="vlanName"
                        name="vlanName" value="{{ $vlanName }}">
                </div>
            </div>
        </div>





        @if (count($interfaces))
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Port</th>
                            <th>Default Vlan</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>VLAN</th>
                            <th>Duplex</th>
                            <th>Speed</th>
                            <th>Type</th>
                            <th>Extra Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($interfaces as $intf)
                            @if ($loop->first)
                                @continue
                            @endif
                            <tr>
                                <td>{{ $intf['Port'] }}</td>
                                <td class="d-flex align-items-center">
                                    <input type="number" class="form-control form-control-sm me-2"
                                        style="width: 80px;">
                                    <span class="text-muted">&lt;1–4094&gt;</span>
                                </td>

                                <td>{{ $intf['Description'] }}</td>
                                <td>{{ $intf['Status'] }}</td>
                                <td>{{ $intf['VLAN'] }}</td>
                                <td>{{ $intf['Duplex'] }}</td>
                                <td>{{ $intf['Speed'] }}</td>
                                <td>{{ $intf['Type'] }}</td>
                                <td>{{ $intf['Continuation'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning">No data found.</div>
        @endif
        <div class="d-flex justify-content-center gap-2 mt-3">
            <button class="btn btn-success" onclick="applyChanges()">Apply</button>
            <button class="btn btn-warning" onclick="resetForm()">Reset</button>
            <button class="btn btn-info" onclick="cancelEdit()">Back to VLAN Table</button>
        </div>

    </div>
</div>

<script>
    function cancelEdit() {
        document.getElementById("vlan-table-section").style.display = "block";
        document.getElementById("edit-vlan-form").innerHTML = "";
    }

    function applyChanges() {
        const vlanId = document.getElementById("vlanId").value;
        const vlanName = document.getElementById("vlanName").value;

        // TODO: Implement AJAX or form submit here
        alert(`Apply clicked\nVLAN ID: ${vlanId}\nVLAN Name: ${vlanName}`);
    }

    function resetForm() {
        document.getElementById("vlanName").value = "{{ $vlanName }}";
    }
</script>
