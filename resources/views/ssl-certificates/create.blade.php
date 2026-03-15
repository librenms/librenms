@extends('layouts.librenmsv1')

@section('title', __('Add SSL Certificate'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <x-panel title="{{ __('Add SSL Certificate') }}" id="add-ssl-certificate-panel">
                <form action="{{ route('ssl-certificates.store') }}" method="POST" class="form-horizontal">
                    @csrf
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="host" class="col-sm-2 control-label">{{ __('Host') }}</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="host" name="host" value="{{ old('host') }}" placeholder="hostname.example.com or 192.0.2.1" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="port" class="col-sm-2 control-label">{{ __('Port') }}</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" id="port" name="port" value="{{ old('port', 443) }}" min="1" max="65535">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="device_id" class="col-sm-2 control-label">{{ __('Device') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control" id="device_id" name="device_id">
                                <option value="">{{ __('— None —') }}</option>
                                @foreach ($devices as $device)
                                    <option value="{{ $device->device_id }}" {{ old('device_id') == $device->device_id ? 'selected' : '' }}>{{ $device->hostname }}</option>
                                @endforeach
                            </select>
                            <p class="help-block">{{ __('Optionally link this certificate to a device.') }}</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-6">
                            <button type="submit" class="btn btn-primary">{{ __('Add Certificate') }}</button>
                            <a href="{{ route('ssl-certificates.index') }}" class="btn btn-default">{{ __('Cancel') }}</a>
                        </div>
                    </div>
                </form>
            </x-panel>
        </div>
    </div>
</div>
@endsection
