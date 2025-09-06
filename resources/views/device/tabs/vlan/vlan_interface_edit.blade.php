<style>
    .form-control-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        height: calc(1.5em + 0.5rem + 2px);
    }
    .form-label-sm {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    .card-header {
        padding: 0.5rem 1rem;
    }
    .card-body {
        padding: 1rem;
    }
</style>

<form id="vlan-config-form">

    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <strong>Configuring the Attribute of the Interface VLAN</strong>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">Port Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" name="port_name" value="{{ $port }}" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">PVID</label>
                <div class="col-sm-6">
                    <input type="number" class="form-control form-control-sm" name="pvid" value="{{ $pvid }}" min="1" max="4094">
                    <small class="text-muted">(1–4094)</small>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">Mode</label>
                <div class="col-sm-6">
                    <select class="form-select form-control-sm" name="mode">
                        <option {{ strtolower($mode) == 'access' ? 'selected' : '' }}>Access</option>
                        <option {{ strtolower($mode) == 'trunk' ? 'selected' : '' }}>Trunk</option>
                        <option {{ strtolower($mode) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">VLAN-allowed Range</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" name="vlan_allowed_range" value="{{ $vlanAllowed }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">VLAN-untagged Range</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" name="vlan_untagged_range" value="{{ $vlanUntagged }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <strong>VLAN-allowed configuration</strong>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">VLAN-allowed Range</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" name="vlan_allowed_range" value="{{ $vlanAllowed }}">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">Add the VLAN-allowed range</label>
                <div class="col-sm-6">
                    <textarea class="form-control form-control-sm" name="add_vlan_allowed_range" rows="1"></textarea>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">Remove the VLAN-allowed range</label>
                <div class="col-sm-6">
                    <textarea class="form-control form-control-sm" name="remove_vlan_allowed_range" rows="1"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <strong>VLAN-untagged configuration</strong>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">VLAN-untagged Range</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" name="vlan_untagged_range" value="{{ $vlanUntagged }}">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">Add the VLAN-untagged range</label>
                <div class="col-sm-6">
                    <textarea class="form-control form-control-sm" name="add_vlan_untagged_range" rows="1"></textarea>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-4 col-form-label form-label-sm">Remove the VLAN-untagged range</label>
                <div class="col-sm-6">
                    <textarea class="form-control form-control-sm" name="remove_vlan_untagged_range" rows="1"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mb-3">
        <button type="button" class="btn btn-success btn-sm" onclick="saveVlanInterface()">Save</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="goBackToTable()">Back</button>
    </div>
</form>
