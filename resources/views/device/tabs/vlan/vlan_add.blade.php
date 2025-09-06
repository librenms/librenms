<div class="card">
    <div class="card-body">

        <!-- VLAN ID and Name -->
        <div class="d-flex justify-content-center mb-4">
            <div class="text-center" style="width: 200px; margin:auto;">
                <div class="form-group mb-2">
                    <label for="vlanId"><strong>VLAN ID:</strong></label>
                    <input type="text" class="form-control form-control-sm text-center" id="vlanId">
                </div>
                <div class="form-group">
                    <label for="vlanName"><strong>VLAN Name:</strong></label>
                    <input type="text" class="form-control form-control-sm text-center" id="vlanName">
                </div>
            </div>
        </div>

        @if (count($interfaces))
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Port</th>
                            <th>New VLAN</th>
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
                                    <input type="number"
                                           class="form-control form-control-sm me-2 vlan-input"
                                           data-port="{{ $intf['Port'] }}"
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
            <button class="btn btn-success" onclick="submitVlanDataok()">Apply</button>
            <button class="btn btn-warning" onclick="resetInputs()">Reset</button>
            <button class="btn btn-info" onclick="cancelEdit()">Back to VLAN Table</button>
        </div>
    </div>
</div>



