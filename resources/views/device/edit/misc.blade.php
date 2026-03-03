@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('device.edit.misc.update', $device->device_id) }}" method="POST" class="form-horizontal">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="override_icmp_disable" class="col-sm-4 control-label">Disable ICMP Test?</label>
                <div class="col-sm-8">
                    <input type="checkbox" name="override_icmp_disable" id="override_icmp_disable" data-size="small" {{ old('override_icmp_disable', $override_icmp_disable == 'true') ? 'checked' : '' }}>
                </div>
            </div>
            <div class="form-group">
                <label for="override_Oxidized_disable" class="col-sm-4 control-label">Exclude from Oxidized?</label>
                <div class="col-sm-8">
                    <input type="checkbox" name="override_Oxidized_disable" id="override_Oxidized_disable" data-size="small" {{ old('override_Oxidized_disable', $override_Oxidized_disable == 'true') ? 'checked' : '' }}>
                </div>
            </div>
            <div class="form-group @error('override_device_ssh_port') has-error @enderror">
                <label for="override_device_ssh_port" class="col-sm-4 control-label">Override default ssh port</label>
                <div class="col-sm-1">
                    <input type="text" name="override_device_ssh_port" id="override_device_ssh_port" class="form-control" value="{{ old('override_device_ssh_port', $override_device_ssh_port) }}">
                    @error('override_device_ssh_port')
                        <span class="help-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-group @error('override_device_telnet_port') has-error @enderror">
                <label for="override_device_telnet_port" class="col-sm-4 control-label">Override default telnet port</label>
                <div class="col-sm-1">
                    <input type="text" name="override_device_telnet_port" id="override_device_telnet_port" class="form-control" value="{{ old('override_device_telnet_port', $override_device_telnet_port) }}">
                    @error('override_device_telnet_port')
                        <span class="help-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-group @error('override_device_http_port') has-error @enderror">
                <label for="override_device_http_port" class="col-sm-4 control-label">Override default http port</label>
                <div class="col-sm-1">
                    <input type="text" name="override_device_http_port" id="override_device_http_port" class="form-control" value="{{ old('override_device_http_port', $override_device_http_port) }}">
                    @error('override_device_http_port')
                        <span class="help-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-group @error('override_Unixagent_port') has-error @enderror">
                <label for="override_Unixagent_port" class="col-sm-4 control-label">Unix agent port</label>
                <div class="col-sm-1">
                    <input type="text" name="override_Unixagent_port" id="override_Unixagent_port" class="form-control" value="{{ old('override_Unixagent_port', $override_Unixagent_port) }}">
                    @error('override_Unixagent_port')
                        <span class="help-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="override_rrdtool_tune" class="col-sm-4 control-label">Enable RRD Tune for all ports?</label>
                <div class="col-sm-8">
                    <input type="checkbox" name="override_rrdtool_tune" id="override_rrdtool_tune" data-size="small" {{ old('override_rrdtool_tune', $override_rrdtool_tune == 'true') ? 'checked' : '' }}>
                </div>
            </div>
            <div class="form-group">
                <label for="selected_ports" class="col-sm-4 control-label">Enable selected port polling?</label>
                <div class="col-sm-8">
                    <input type="checkbox" name="selected_ports" id="selected_ports" data-size="small" {{ old('selected_ports', $selected_ports == 'true') ? 'checked' : '' }}>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-8">
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </div>
        </form>
    </x-device.page>
@endsection

@push('scripts')
<script>
    $('[type="checkbox"]').bootstrapSwitch('offColor', 'danger');
</script>
@endpush
