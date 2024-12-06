@extends('device.index')

@section('tab')
    <x-panel title="{{ __('Device Notes') }}">
        <form method="post" action="{{ route('device.notes.update', $device)}}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <textarea @cannot('update-notes', $device) disabled @endcannot class="form-control" rows="3" name="note">{{ $device->notes }}</textarea>
            </div>
            <button @cannot('update-notes', $device) disabled @endcannot type="submit" class="btn btn-default"><i class="fa fa-check"></i> Save</button>
        </form>
    </x-panel>
@endsection
