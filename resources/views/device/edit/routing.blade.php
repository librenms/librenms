@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="routing" />

        <div x-data="deviceRouting(@js(['vrfs' => $vrfs, 'storeUrl' => route('device.edit.routing.vrf.store', $device)]))">
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
                            <th>{{ __('Status') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <template x-for="vrf in vrfs" :key="vrf.update_url">
                            <tr>
                                <td>
                                    <template x-if="vrf.context !== null">
                                        <x-toggle ::checked="vrf.enabled" ::aria-label="'{{ __('Poll VRF') }} ' + vrf.name" x-on:change="setEnabled(vrf, $event.target)" />
                                    </template>
                                </td>
                                <td>
                                    <span x-text="vrf.name"></span>
                                    <template x-if="vrf.context_only">
                                        <span class="tw:inline-flex tw:items-center tw:gap-1 tw:ml-2">
                                            <span class="label label-default" title="{{ __('The device does not list this VRF, it is only known through its SNMP context') }}">{{ __('Manual') }}</span>
                                            <button type="button"
                                                    class="btn btn-xs btn-default"
                                                    x-on:click="removeVrf(vrf)"
                                                    :disabled="isPending(vrf.destroy_url)"
                                                    :title="'{{ __('Remove VRF') }} ' + vrf.name"
                                                    :aria-label="'{{ __('Remove VRF') }} ' + vrf.name">
                                                <i class="fa fa-times" aria-hidden="true"></i>
                                            </button>
                                        </span>
                                    </template>
                                </td>
                                <td x-text="vrf.rd"></td>
                                <td>
                                    <template x-if="vrf.editable">
                                        <input type="text"
                                               class="form-control input-sm"
                                               maxlength="128"
                                               placeholder="{{ __('Not set') }}"
                                               :value="vrf.context ?? ''"
                                               :aria-label="'{{ __('SNMP context of VRF') }} ' + vrf.name"
                                               x-on:blur="setContext(vrf, $el)"
                                               x-on:keyup.enter="setContext(vrf, $el)">
                                    </template>
                                    <template x-if="! vrf.editable">
                                        <span x-text="vrf.context ?? '-'" :title="vrf.device_context ? @js(__('Mapped by the device')) : ''"></span>
                                    </template>
                                </td>
                                <td>
                                    <span :class="statuses[vrf.status].class" :title="statuses[vrf.status].title" x-text="statuses[vrf.status].text"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="vrfs.length === 0" x-cloak>
                            <td colspan="5" class="text-center"><em>{{ __('No VRFs found for this device.') }}</em></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <form class="form-inline" x-on:submit.prevent="addContext()">
                    <div class="form-group">
                        <label for="manual-context">{{ __('Add VRF by SNMP context') }}</label>
                        <input type="text" id="manual-context" class="form-control" x-model="newContext" maxlength="128" placeholder="{{ __('SNMP context') }}">
                    </div>
                    <button type="submit" class="btn btn-default" :disabled="newContext.trim() === '' || isPending('add')"><i class="fa fa-plus"></i> {{ __('Add') }}</button>
                </form>
            </x-panel>

            @if ($peers->isNotEmpty())
                <x-panel :title="__('BGP Peers')">
                    <x-slot:table>
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
                                                   class="form-control input-sm"
                                                   value="{{ $peer->bgpPeerDescr }}"
                                                   data-saved="{{ $peer->bgpPeerDescr }}"
                                                   x-on:blur="saveDescription($el, @js(route('device.edit.routing.peer.update', [$device, $peer])))"
                                                   x-on:keyup.enter="saveDescription($el, @js(route('device.edit.routing.peer.update', [$device, $peer])))"
                                                   :aria-label="@js(__('Description of peer :peer', ['peer' => $peer->bgpPeerIdentifier]))"
                                                   @disabled(! $can_update_peers)>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-slot:table>
                </x-panel>
            @endif
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('deviceRouting', (config) => ({
                vrfs: config.vrfs,
                newContext: '',
                pending: {},
                statuses: {
                    none: {text: @js(__('No context')), class: 'text-muted', title: ''},
                    disabled: {text: @js(__('Disabled')), class: 'label label-default', title: ''},
                    answering: {text: @js(__('Answering')), class: 'label label-success', title: ''},
                    waiting: {text: @js(__('Waiting for discovery')), class: 'label label-info', title: @js(__('The context is checked by the next VRF discovery'))},
                    failing: {text: @js(__('Not answering')), class: 'label label-warning', title: @js(__('The context did not answer at the last VRF discovery'))},
                },

                isPending(key) {
                    return this.pending[key] === true;
                },

                request(key, method, url, data = {}) {
                    this.pending[key] = true;

                    return fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(data),
                    }).then(async (response) => {
                        const body = await response.json().catch(() => ({}));
                        if (! response.ok) {
                            throw new Error(body.message ?? @js(__('Request failed')));
                        }

                        return body;
                    }).catch((error) => {
                        toastr.error(error.message);
                        throw error;
                    }).finally(() => delete this.pending[key]);
                },

                setEnabled(vrf, input) {
                    const enabled = input.checked;
                    if (this.isPending(vrf.update_url)) {
                        input.checked = ! enabled;
                        return;
                    }

                    this.request(vrf.update_url, 'PATCH', vrf.update_url, {context_enabled: enabled})
                        .then((updated) => Object.assign(vrf, updated))
                        .catch(() => input.checked = ! enabled);
                },

                addContext() {
                    const context = this.newContext.trim();
                    if (context === '' || this.isPending('add')) return;

                    this.request('add', 'POST', config.storeUrl, {context: context})
                        .then((response) => {
                            this.vrfs = response.vrfs;
                            this.newContext = '';
                            toastr.success(response.message);
                        })
                        .catch(() => {});
                },

                removeVrf(vrf) {
                    if (this.isPending(vrf.destroy_url)) return;

                    this.request(vrf.destroy_url, 'DELETE', vrf.destroy_url)
                        .then((response) => this.vrfs = response.vrfs)
                        .catch(() => {});
                },

                setContext(vrf, input) {
                    const context = input.value.trim();
                    if (context === (vrf.context ?? '') || this.isPending(vrf.update_url)) return;

                    this.request(vrf.update_url, 'PATCH', vrf.update_url, {context_name: context})
                        .then((updated) => Object.assign(vrf, updated))
                        .catch(() => input.value = vrf.context ?? '');
                },

                saveDescription(input, url) {
                    const descr = input.value;
                    if (descr === input.dataset.saved || this.isPending(url)) return;

                    this.request(url, 'POST', url, {descr: descr})
                        .then((response) => {
                            if (response.status !== 'ok') {
                                toastr.error(response.message);
                                return;
                            }

                            input.dataset.saved = descr;
                            toastr.success(response.message);
                        })
                        .catch(() => {});
                },
            }));
        });
    </script>
@endpush
