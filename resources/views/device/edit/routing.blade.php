@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="routing" />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="routing-contexts" action="{{ route('device.edit.routing.contexts', $device->device_id) }}" method="POST" class="form-horizontal">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="snmp_contexts" class="col-sm-2 control-label">{{ __('Routing SNMP Contexts') }}</label>
                <div class="col-sm-4">
                    <select id="snmp_contexts" name="snmp_contexts[]" class="form-control" multiple>
                        @foreach (old('snmp_contexts', $snmp_contexts) as $context)
                            <option value="{{ $context }}" selected>{{ $context }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{ __('Save') }}</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table id="routing" class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>{{ __('Peer address') }}</th>
                    <th>{{ __('Remote AS') }}</th>
                    <th>{{ __('Context') }}</th>
                    <th class="col-sm-4">{{ __('Description') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($peers as $peer)
                    <tr>
                        <td>{{ $peer->bgpPeerIdentifier }}</td>
                        <td>{{ $peer->bgpPeerRemoteAs }}</td>
                        <td>{{ $peer->context_name }}</td>
                        <td>
                            <input type="text"
                                   class="form-control input-sm routing-descr"
                                   data-update-url="{{ route('device.edit.routing.peer.update', [$device, $peer]) }}"
                                   value="{{ $peer->bgpPeerDescr }}"
                                   @disabled(! $can_update_peers)>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        init_select2('#snmp_contexts', 'snmp-contexts', {}, null, @json(__('Type context and press Enter')), {
            tags: true,
            multiple: true,
            ajax: null,
            createTag: function (params) {
                var term = $.trim(params.term);

                if (term === '') {
                    return null;
                }

                return {
                    id: term,
                    text: term
                };
            }
        });

        $('.routing-descr').on('focusin', function () {
            $(this).data('val', $(this).val());
        });

        $('.routing-descr').on('blur keyup', function (e) {
            if (e.type === 'keyup' && e.keyCode !== 13) return;
            var $this = $(this);
            var data = $this.val();
            if ($this.data('val') === data) return;

            $.ajax({
                type: 'POST',
                url: $this.data('update-url'),
                data: {
                    descr: data,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'ok') {
                        $this.data('val', data);
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message ?? 'Error updating routing information');
                }
            });
        });
    </script>
@endpush
