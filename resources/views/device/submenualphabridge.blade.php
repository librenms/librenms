@extends('device.index')

@section('tab')
@php
//   dd($data);  
@endphp

<div class="col-4 col-md-4 col-lg-4"></div>
    <div class="col-12 col-md-4 col-lg-4">
        
        <div style="padding:10px;border:1px solid #ccc">
            <div class="logoicon">
                <img src="{{ url($device->logo()) }}" title="{{ $device->logo() }}" class="device-icon-header pull-left" style="max-height: 100px">
                <h3 class="text-center mb-4">{{ $data['hostname'] }} </h3>
                <h3 class="text-center mb-4">Config</h3>
            </div>

            <div class="card p-4 shadow-lg">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
                 <form action="{{ route('vlan.configure') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        {{-- <label class="form-label">Host</label> --}}
                        <input type="hidden" id="host" name="host" class="form-control" value="{{ $data['hostname'] }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">VLAN ID:</label>
                        <input type="text" class="form-control" name="vlan_id"  value="{{ old('vlan_id', 555) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Interface:</label>
                        <select name="interface" class="form-control" id="interface">
                            <option value="">Select Port</option>
                            @foreach ($data['ports'] as $port)
                            <option value="{{$port->ifDescr}}">{{$port->ifDescr}}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mode:</label>
                        <select name="mode" class="form-control" id="mode">
                            <option value="">Select Mode</option>
                            <option value="access">Access</option>
                            <option value="trunk">Trunk</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">PvID:</label>
                        <input type="text" class="form-control" id="pvid" name="pvid">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mt-3" style="margin-top: 5px;">Run Command</button>
                </form>

            </div>
            
        </div>
    </div>
<div class="col-4 col-md-4 col-lg-4"></div>
<script>
    $(document).ready(function() {
        $('#mode').parent().hide();
        $('#pvid').parent().hide();

        $('#interface').change(function() {
            if ($(this).val()) {
                $('#mode').parent().show(); // Show mode if interface is selected
            } else {
                $('#mode').parent().hide();
                $('#pvid').parent().hide();
                $('#pvid').val(''); // Clear PVID when mode is hidden
            }
        });

        $('#mode').change(function() {
            let mode = $(this).val();
            $('#pvid').parent().show(); // Always show PVID when mode is selected
            $('#pvid').val(''); // Clear previous input

            if (mode === "access") {
                $('#pvid').attr('pattern', '^[0-9]+$'); // Allow only single VLAN ID
                $('#pvid').attr('placeholder', 'Enter a single VLAN ID'); // Tooltip for validation
                $('#pvid').on('input', function() {
                    $(this).val($(this).val().replace(/,/g, '')); // Remove commas in real-time
                });
            } else if (mode === "trunk") {
                $('#pvid').attr('pattern', '^[0-9]+(,[0-9]+)*$'); // Allow comma-separated VLANs
                $('#pvid').attr('placeholder', 'Enter Single or multiple VLAN IDs separated by commas'); // Tooltip for validation
                $('#pvid').off('input'); // Remove real-time restriction
            } else {
                $('#pvid').parent().hide();
                $('#pvid').val('');
            }
        });
    });
</script>


@endsection
