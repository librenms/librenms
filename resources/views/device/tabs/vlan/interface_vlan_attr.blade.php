@include('device.tabs.datatablepath.datatablespath') {{-- Ensure this includes required assets --}}

<div class="container mt-4 mb-4">
    <h4 class="text-center"><strong>Interface VLAN Attributes</strong></h4>
    <div id="vlan-interface-table-section">
        @if (count($interfaces))
            <div class="table-responsive">
                <table id="vlanInterfaceTable" class="table table-bordered table-striped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Port</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>VLAN</th>
                            <th>Duplex</th>
                            <th>Speed</th>
                            <th>Type</th>
                            <th>Operate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($interfaces as $intf)
    <tr>
        <td>{{ $intf['Port'] }}</td>
        <td>{{ $intf['Description'] }}</td>
        <td>{{ $intf['Status'] }}</td>
        <td>{{ $intf['VLAN'] }}</td>
        <td>{{ $intf['Duplex'] }}</td>
        <td>{{ $intf['Speed'] }}</td>
        <td>{{ $intf['Type'] }}</td>
        <td>
           <button class="btn btn-sm btn-primary"
    onclick="editVlanInterface('{{ $intf['Port'] }}', '{{ $intf['VLAN'] }}', '{{ $intf['Mode'] ?? 'access' }}')">
    Details
</button>
        </td>
    </tr>
@endforeach

                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning text-center">No interface VLAN data found.</div>
        @endif
    </div>
</div>
<div id="edit-vlan-interface-form" class="mt-4"></div>
<script>
    $(document).ready(function() {
        $('#vlanInterfaceTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 20, 50],
            order: [],
            responsive: true
        });
    });

 function editVlanInterface(port = '', pvid = '', mode = '') {
    document.getElementById("vlan-interface-table-section").style.display = "none";
    
    const url = `/vlan/interface/edit?port=${encodeURIComponent(port)}&pvid=${encodeURIComponent(pvid)}&mode=${encodeURIComponent(mode)}`;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById("edit-vlan-interface-form").innerHTML = html;
        })
        .catch(error => {
            console.error("Error loading VLAN edit form:", error);
        });
}




//edit part
function goBackToTable() {
        document.getElementById("edit-vlan-interface-form").innerHTML = '';
        document.getElementById("vlan-interface-table-section").style.display = "block";
    }





    function saveVlanInterface() {
    console.log("ok");
    const data = $("#vlan-config-form").serialize() + '&_token={{ csrf_token() }}';


    console.log(data);
    $.ajax({
        url: "{{ route('vlan.interface.save') }}",
        method: "POST",
        data: data,
        success: function (response) {
            alert("Configuration applied!\n" + response.output);
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert("Failed to apply configuration.");
        }
    });
}
</script>
