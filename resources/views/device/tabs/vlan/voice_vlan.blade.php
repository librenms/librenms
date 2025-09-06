@include('device.tabs.datatablepath.datatablespath')
<div class="container">
    <h4 class="text-center"><strong>Voice VLAN MAC Addresses</strong></h4>
    <div id="voice-vlan-table-section">
        <div class="mb-3">
            <button class="btn btn-success btn-sm" onclick="addVoiceVlan()">Add</button>
            <button class="btn btn-danger btn-sm" onclick="deleteSelectedVoiceVlans()">Delete Selected</button>
        </div>
        @if (count($voiceVlans))
            <div class="table-responsive">
                <table id="voiceVlanTable" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr class="info">
                            <th>
                                <input type="checkbox" id="select-all" onclick="toggleAllCheckboxes(this)">
                            </th>
                            <th>Index</th>
                            <th>MAC Address</th>
                            <th>MAC Mask</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($voiceVlans as $index => $item)
                            <tr>
                                <td>
                                    <input type="checkbox" class="voice-vlan-checkbox"
               value='@json(["mac" => $item["mac"], "mask" => $item["mask"]])'>
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['mac'] }}</td>
                                <td>{{ $item['mask'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning text-center">No voice VLAN MAC address found.</div>
        @endif
    </div>
</div>
<div id="edit-voice-vlan-form" class="mt-4"></div>

<script>
    $(document).ready(function() {
        $('#voiceVlanTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 20],
            order: [],
            searching: true
        });
    });

    //add voice vlan
    function addVoiceVlan() {
        document.getElementById("voice-vlan-table-section").style.display = "none";
        fetch(`/voice/vlan/add`)
            .then(response => response.text())
            .then(html => {
                document.getElementById("edit-voice-vlan-form").innerHTML = html;
            })
            .catch(error => {
                console.error("Error loading Voice VLAN add form:", error);
            });
    }

    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('.voice-vlan-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }

    function deleteSelectedVoiceVlans() {
    const checkedBoxes = document.querySelectorAll('.voice-vlan-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert("Please select at least one MAC address to delete.");
        return;
    }

    if (!confirm("Are you sure you want to delete the selected MAC address(es)?")) {
        return;
    }

    const selectedItems = Array.from(checkedBoxes).map(cb => JSON.parse(cb.value));

    fetch("{{ route('voice.vlan.delete') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ items: selectedItems })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || "Deletion complete.");
        location.reload();
    })
    .catch(error => {
        console.error("Error deleting MAC addresses:", error);
        alert("Something went wrong while deleting.");
    });
}




    function BackVlanVoice() {
        document.getElementById("add-voice-vlan-form").style.display = "none"; // hide add form
        document.getElementById("voice-vlan-table-section").style.display = "block"; // show table
        document.getElementById("edit-voice-vlan-form").innerHTML = ""; // clear form
    }



    //add vlan Part 
    function submitVoiceVlanData() {
        const macAddress = document.getElementById('macAdd').value.trim();
        const macMask = document.getElementById('maskAdd').value.trim();
        const resultMsg = document.getElementById('resultMsg');

        // Simple format check (can be enhanced)
        const macRegex = /^([0-9a-fA-F]{4}\.){2}[0-9a-fA-F]{4}$/;
        if (!macRegex.test(macAddress) || !macRegex.test(macMask)) {
            resultMsg.className = 'alert alert-danger';
            resultMsg.innerText = 'Invalid MAC or Mask format.';
            resultMsg.style.display = 'block';
            return;
        }

        // Show processing
        resultMsg.className = 'alert alert-info';
        resultMsg.innerText = 'Processing...';
        resultMsg.style.display = 'block';

        fetch("{{ route('voice.vlan.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    macAdd: macAddress,
                    maskAdd: macMask
                }),
            })
            .then(res => res.json())
            .then(data => {
                resultMsg.className = data.success ? 'alert alert-success' : 'alert alert-warning';
                resultMsg.innerText = data.message || 'Unexpected response.';
            })
            .catch(err => {
                console.error(err);
                resultMsg.className = 'alert alert-danger';
                resultMsg.innerText = 'Something went wrong while saving.';
            });
    }
</script>
