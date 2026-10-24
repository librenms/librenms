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

        <form id="routing-contexts" action="{{ route('device.edit.routing.contexts', $device->device_id) }}" method="POST">
            @csrf
            @method('PUT')
            <x-panel :title="__('VRFs')">
                <p class="text-muted">{{ __('Routing modules (BGP, OSPF, IP addresses, ARP, IPv6 neighbors) poll each VRF through its SNMP context.') }}</p>
                <div class="table-responsive">
                    <table id="vrf-contexts" class="table table-hover table-condensed">
                        <thead>
                        <tr>
                            <th class="tw:w-[80px]">{{ __('Poll') }}</th>
                            <th>{{ __('VRF') }}</th>
                            <th>{{ __('RD') }}</th>
                            <th>{{ __('SNMP Context') }}</th>
                            <th>{{ __('Source') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($vrfs as $vrf)
                            <tr>
                                <td>
                                    @if ($vrf->context_name !== null)
                                        <x-toggle name="context_enabled[{{ $vrf->vrf_id }}]" :checked="$vrf->context_enabled" :aria-label="__('Poll VRF :vrf', ['vrf' => $vrf->vrf_name])" />
                                    @endif
                                </td>
                                <td>{{ $vrf->vrf_name }}</td>
                                <td>{{ $vrf->mplsVpnVrfRouteDistinguisher }}</td>
                                <td>{{ $vrf->context_name ?? '-' }}</td>
                                <td>{{ $vrf->context_name === null ? '' : (in_array($vrf->context_name, $snmp_contexts, true) ? __('Manual') : __('Device')) }}</td>
                                <td>
                                    @if ($vrf->context_name === null)
                                        <span class="text-muted">{{ __('No context') }}</span>
                                    @elseif (! $vrf->context_enabled)
                                        <span class="label label-default">{{ __('Disabled') }}</span>
                                    @elseif ($vrf->context_verified)
                                        <span class="label label-success">{{ __('Answering') }}</span>
                                    @else
                                        <span class="label label-warning" title="{{ __('The context did not answer at the last VRF discovery') }}">{{ __('Not answering') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            @if (empty($pending_contexts))
                                <tr>
                                    <td colspan="6" class="text-center"><em>{{ __('No VRFs found for this device.') }}</em></td>
                                </tr>
                            @endif
                        @endforelse
                        @foreach ($pending_contexts as $context => $mapped_context)
                            <tr>
                                <td></td>
                                <td>{{ $context }}</td>
                                <td></td>
                                <td>{{ $context }}</td>
                                <td>{{ __('Manual') }}</td>
                                <td>
                                    @if ($mapped_context === null)
                                        <span class="label label-info">{{ __('Waiting for discovery') }}</span>
                                    @else
                                        <span class="label label-default" title="{{ __('The device maps this VRF to context :context', ['context' => $mapped_context]) }}">{{ __('Ignored, the device maps this VRF') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="form-group">
                    <label for="snmp_contexts">{{ __('Manual VRF contexts') }}</label>
                    <select id="snmp_contexts" name="snmp_contexts[]" class="form-control" multiple>
                        @foreach (old('snmp_contexts', $snmp_contexts) as $context)
                            <option value="{{ $context }}" selected>{{ $context }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{ __('Save') }}</button>
            </x-panel>
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
