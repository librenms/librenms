@include('device.tabs.datatablepath.datatablespath')

<style>
    td input[type="checkbox"] {
        display: block;
        margin: auto;
</style>

<div class="container mt-4 mb-2">
    <h4><b>VLAN Configuration Table</b></h4>
    <div id="vlan-table-section">
        <div class="mb-3">
            <button class="btn btn-success btn-sm" onclick="addVlan()">Add</button>
            <button class="btn btn-danger btn-sm" onclick="deleteSelectedVlans()">Delete Selected</button>
        </div>


        @if (count($vlanRows))
            <div class="table-responsive">
                <table id="vlanTable" class="table table-bordered table-hover table-striped">

                    <thead class="thead-dark">
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" onclick="toggleAllCheckboxes(this)">
                            </th>
                            <th>VLAN ID</th>
                            <th>Status</th>
                            <th>Name</th>
                            <th>Operate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vlanRows as $row)
                            <tr>
                                <td>
                                    <input type="checkbox" class="vlan-checkbox" value="{{ $row['vlan_id'] }}">
                                </td>
                                <td>{{ $row['vlan_id'] }}</td>
                                <td>{{ $row['status'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary"
                                        onclick="editVlan('{{ $row['vlan_id'] }}', '{{ addslashes($row['name']) }}')">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning">No VLAN data found.</div>
        @endif
    </div>
</div>

<div id="edit-vlan-form" class="mt-4"></div>

<script>
    $(document).ready(function() {
        $('#vlanTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 20, 50],
            order: [],
            responsive: true
        });
    });

    function addVlan() {
        document.getElementById("vlan-table-section").style.display = "none";
        fetch(`/vlan/add`)
            .then(response => response.text())
            .then(html => {
                document.getElementById("edit-vlan-form").innerHTML = html;
            })
            .catch(error => {
                console.error("Error loading VLAN add form:", error);
            });
    }

    function editVlan(vlanId, vlanName) {
        document.getElementById("vlan-table-section").style.display = "none";
        fetch(`/vlan/edit/${vlanId}?name=${encodeURIComponent(vlanName)}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById("edit-vlan-form").innerHTML = html;
            })
            .catch(error => {
                console.error("Error loading VLAN edit form:", error);
            });
    }

    function cancelEdit() {
        document.getElementById("vlan-table-section").style.display = "block";
        document.getElementById("edit-vlan-form").innerHTML = "";
    }

    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('.vlan-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }

    function deleteSelectedVlans() {
        const checkedBoxes = document.querySelectorAll('.vlan-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert("Please select at least one VLAN to delete.");
            return;
        }

        if (!confirm("Are you sure you want to delete the selected VLAN(s)?")) {
            return;
        }

        const vlanIds = Array.from(checkedBoxes).map(cb => cb.value);

        fetch("{{ route('vlan.deleteBatch') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    vlan_ids: vlanIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || "Selected VLANs deleted.");
                    location.reload(); // or manually remove rows from table if you want
                } else {
                    alert(data.message || "Failed to delete VLANs.");
                }
            })
            .catch(error => {
                console.error("Error deleting VLANs:", error);
                alert("Something went wrong.");
            });
    }



    //for add vlan
    function cancelEdit() {
        document.getElementById("vlan-table-section").style.display = "block";
        document.getElementById("edit-vlan-form").innerHTML = "";
    }

    function resetInputs() {
        document.getElementById("vlanId").value = '';
        document.getElementById("vlanName").value = '';
        document.querySelectorAll('.vlan-input').forEach(el => el.value = '');
    }


    function submitVlanDataok() {

        const vlanId = document.getElementById('vlanId').value;
        const vlanName = document.getElementById('vlanName').value;

        const portVlans = {};
        document.querySelectorAll('.vlan-input').forEach(input => {
            const port = input.dataset.port;
            const value = input.value;
            if (value) {
                portVlans[port] = value;
            }
        });

        fetch("{{ route('vlan.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    vlanId: vlanId,
                    vlanName: vlanName,
                    port_vlan: portVlans,
                }),
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'VLAN configuration applied.');
            })
            .catch(err => {
                console.error(err);
                alert('Something went wrong.');
            });
    }
</script>
