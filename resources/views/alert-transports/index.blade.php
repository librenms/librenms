@extends('layouts.librenmsv1')

@section('title', __('Alert Transports'))

@section('content')
<div class="container-fluid" x-data="alertTransports(@js($templates))">
    <x-tabs :active="$tab" x-on:tab-change="selectTab($event.detail)">
        <x-tab :name="__('Transports') . ' (' . $transports->count() . ')'" value="transports">
            <x-panel>
                <x-slot:heading class="tw:flex tw:items-center tw:justify-between">
                    <h3 class="panel-title">
                        {{ __('Alert Transports') }}
                        <a href="https://docs.librenms.org/Alerting/Transports/" target="_blank" rel="noopener" title="{{ __('Docs') }}"><i class="fa fa-book" aria-hidden="true"></i></a>
                    </h3>
                    @can('create', \App\Models\AlertTransport::class)
                        <button type="button" class="btn btn-primary btn-sm" x-on:click="editTransport(null)">{{ __('Create alert transport') }}</button>
                    @endcan
                </x-slot:heading>
                <x-slot:table>
                    <div class="table-responsive">
                        <table class="table table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th>{{ __('Transport Name') }}</th>
                                    <th>{{ __('Transport Type') }}</th>
                                    <th>{{ __('Default') }}</th>
                                    <th>{{ __('Details') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($transports as $transport)
                                @php($instance = $transport->instance())
                                <tr id="transport-{{ $transport->transport_id }}">
                                    <td>{{ $transport->transport_name }}</td>
                                    <td>{{ $instance->name() }}</td>
                                    <td>@if($transport->is_default)<span class="label label-primary">{{ __('Default') }}</span>@endif</td>
                                    <td class="tw:wrap-anywhere"><i>{!! nl2br(e($instance->displayDetails())) !!}</i></td>
                                    <td>
                                        <div class="btn-group btn-group-sm tw:inline-flex" role="group">
                                            @can('update', $transport)
                                                <button type="button" class="btn btn-primary btn-sm" x-on:click="editTransport({{ $transport->transport_id }})" title="{{ __('Edit transport') }}"><i class="fa fa-lg fa-pencil" aria-hidden="true"></i></button>
                                            @endcan
                                            @can('delete', $transport)
                                                <button type="button" class="btn btn-danger btn-sm" x-on:click="confirmDelete(route('alert-transports.destroy', {transport: {{ $transport->transport_id }}}), @js($transport->transport_name))" title="{{ __('Delete transport') }}"><i class="fa fa-lg fa-trash" aria-hidden="true"></i></button>
                                            @endcan
                                            @can('update', $transport)
                                                <button type="button" class="btn btn-warning btn-sm" x-on:click="test({{ $transport->transport_id }}, @js($transport->transport_name))" title="{{ __('Test transport') }}"><i class="fa fa-lg fa-check" aria-hidden="true"></i></button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="tw:text-center tw:italic">{{ __('No alert transports configured') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-slot:table>
            </x-panel>
        </x-tab>
        <x-tab :name="__('Groups') . ' (' . $groups->count() . ')'" value="groups">
            <x-panel>
                <x-slot:heading class="tw:flex tw:items-center tw:justify-between">
                    <h3 class="panel-title">{{ __('Transport Groups') }}</h3>
                    @can('create', \App\Models\AlertTransportGroup::class)
                        <button type="button" class="btn btn-primary btn-sm" x-on:click="editGroup(null)">{{ __('Create transport group') }}</button>
                    @endcan
                </x-slot:heading>
                <x-slot:table>
                    <div class="table-responsive">
                        <table class="table table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th>{{ __('Transport Group') }}</th>
                                    <th>{{ __('Members') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($groups as $group)
                                <tr id="transport-group-{{ $group->transport_group_id }}">
                                    <td>{{ $group->transport_group_name }}</td>
                                    <td>
                                        @foreach($group->transports as $member)
                                            <i>{{ $member->instance()->name() }}: {{ $member->transport_name }}</i><br>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm tw:inline-flex" role="group">
                                            @can('update', $group)
                                                <button type="button" class="btn btn-primary btn-sm" x-on:click="editGroup({{ $group->transport_group_id }})" title="{{ __('Edit transport group') }}"><i class="fa fa-lg fa-pencil" aria-hidden="true"></i></button>
                                            @endcan
                                            @can('delete', $group)
                                                <button type="button" class="btn btn-danger btn-sm" x-on:click="confirmDelete(route('alert-transport-groups.destroy', {alert_transport_group: {{ $group->transport_group_id }}}), @js($group->transport_group_name))" title="{{ __('Delete transport group') }}"><i class="fa fa-lg fa-trash" aria-hidden="true"></i></button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="tw:text-center tw:italic">{{ __('No transport groups configured') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-slot:table>
            </x-panel>
        </x-tab>
    </x-tabs>

    <x-modal show="transportOpen" maxWidth="2xl">
        <x-slot:heading>
            <h4 class="tw:m-0 tw:text-base tw:font-semibold" x-text="transport.id ? @js(__('Edit alert transport')) : @js(__('Create alert transport'))"></h4>
        </x-slot:heading>

        <form id="transport-form" class="form-horizontal" x-on:submit.prevent="saveTransport()">
            <div class="form-group">
                <label for="transport-name" class="col-sm-3 control-label">{{ __('Transport name') }}</label>
                <div class="col-sm-9">
                    <input type="text" id="transport-name" class="form-control" maxlength="200" x-model="transport.name" required>
                </div>
            </div>
            <div class="form-group">
                <label for="transport-type" class="col-sm-3 control-label">{{ __('Transport type') }}</label>
                <div class="col-sm-9">
                    <select id="transport-type" class="form-control" x-model="transport.type" x-on:change="transport.config = defaults(transport.type)">
                        @foreach($types as $type => $name)
                            <option value="{{ $type }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <x-toggle model="transport.is_default" size="sm" :label="__('Default alert transport')" />
                </div>
            </div>

            @foreach($templates as $type => $fields)
                <template x-if="transport.type === @js($type)">
                    <div>
                        @foreach($fields as $field)
                            @php($id = 'transport-' . $type . '-' . $field['name'])
                            <div class="form-group" title="{{ $field['descr'] ?? '' }}">
                                <label for="{{ $id }}" class="col-sm-3 control-label">{{ $field['title'] }}</label>
                                <div class="col-sm-9">
                                    @switch($field['type'])
                                        @case('checkbox')
                                            <div class="tw:pt-2">
                                                <x-toggle :id="$id" :model="'transport.config[' . Js::from($field['name']) . ']'" size="sm" />
                                            </div>
                                            @break
                                        @case('select')
                                            <select id="{{ $id }}" class="form-control" x-model="transport.config[@js($field['name'])]">
                                                @foreach($field['options'] as $text => $value)
                                                    <option value="{{ $value }}">{{ $text }}</option>
                                                @endforeach
                                            </select>
                                            @break
                                        @case('textarea')
                                            <textarea id="{{ $id }}" class="form-control" placeholder="{{ $field['descr'] ?? '' }}" x-model="transport.config[@js($field['name'])]"></textarea>
                                            @break
                                        @default
                                            <input type="{{ $field['type'] === 'password' ? 'password' : 'text' }}" id="{{ $id }}" class="form-control"
                                                   x-model="transport.config[@js($field['name'])]" @required(! empty($field['required']))>
                                    @endswitch
                                </div>
                            </div>
                        @endforeach
                    </div>
                </template>
            @endforeach
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="transportOpen = false">{{ __('Cancel') }}</button>
            <button type="submit" form="transport-form" class="btn btn-success" x-bind:disabled="saving">{{ __('Save Transport') }}</button>
        </x-slot:footer>
    </x-modal>

    <x-modal show="groupOpen" maxWidth="xl">
        <x-slot:heading>
            <h4 class="tw:m-0 tw:text-base tw:font-semibold" x-text="group.id ? @js(__('Edit transport group')) : @js(__('Create transport group'))"></h4>
        </x-slot:heading>

        <form id="transport-group-form" x-on:submit.prevent="saveGroup()">
            <div class="form-group">
                <label for="transport-group-name">{{ __('Group name') }}</label>
                <input type="text" id="transport-group-name" class="form-control" maxlength="255" x-model="group.name" required>
            </div>
            <div class="form-group">
                <label for="transport-group-members">{{ __('Group members') }}</label>
                <select id="transport-group-members" x-ref="members" class="form-control" multiple style="width: 100%"></select>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="groupOpen = false">{{ __('Cancel') }}</button>
            <button type="submit" form="transport-group-form" class="btn btn-success" x-bind:disabled="saving">{{ __('Save Transport Group') }}</button>
        </x-slot:footer>
    </x-modal>

    <x-modal show="deleteUrl" :title="__('Confirm Delete')" maxWidth="md">
        <p x-text="@js(__('Delete :name?')).replace(':name', deleteName)"></p>
        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="deleteUrl = null">{{ __('Cancel') }}</button>
            <button type="button" class="btn btn-danger" x-on:click="destroy()" x-bind:disabled="saving">{{ __('Delete') }}</button>
        </x-slot:footer>
    </x-modal>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('alertTransports', (templates) => ({
            transportOpen: false,
            groupOpen: false,
            saving: false,
            deleteUrl: null,
            deleteName: '',
            transport: {config: {}},
            group: {},

            defaults(type) {
                return Object.fromEntries((templates[type] ?? []).map((field) => [
                    field.name,
                    field.type === 'checkbox' ? [true, 'on'].includes(field.default) : (field.default ?? ''),
                ]));
            },

            editTransport(id) {
                if (! id) {
                    this.transport = {id: null, name: '', type: 'mail', is_default: false, config: this.defaults('mail')};
                    this.transportOpen = true;
                    return;
                }

                this.request('GET', route('alert-transports.show', {transport: id})).then((transport) => {
                    const config = this.defaults(transport.type);
                    (templates[transport.type] ?? []).forEach((field) => {
                        if (field.name in transport.config) {
                            const value = transport.config[field.name];
                            config[field.name] = field.type === 'checkbox' ? [true, 'on'].includes(value) : (value ?? '');
                        }
                    });
                    this.transport = {...transport, config};
                    this.transportOpen = true;
                }).catch(() => {});
            },

            saveTransport() {
                const request = this.transport.id
                    ? this.request('PUT', route('alert-transports.update', {transport: this.transport.id}), this.transport)
                    : this.request('POST', route('alert-transports.store'), this.transport);

                request.then((response) => this.done(response)).catch(() => {});
            },

            test(id, name) {
                this.request('POST', route('alert.transports.test', {transport: id})).then((response) => {
                    if (response.status === 'ok') {
                        toastr.success(@js(__('Test to :name ok')).replace(':name', name));
                    } else {
                        toastr.error($('<div>').text(response.message).html(), @js(__('Test to :name failed')).replace(':name', name));
                    }
                }).catch(() => {});
            },

            editGroup(id) {
                const group = id
                    ? this.request('GET', route('alert-transport-groups.show', {alert_transport_group: id}))
                    : Promise.resolve({id: null, name: '', members: []});

                group.then((group) => {
                    this.group = group;
                    this.groupOpen = true;
                    this.$nextTick(() => {
                        const $members = $(this.$refs.members);
                        if (! $members.hasClass('select2-hidden-accessible')) {
                            init_select2($members, 'alert-transport', {}, null, @js(__('Select transports')), {dropdownParent: $members.closest('form'), width: '100%'});
                        }
                        $members.empty();
                        group.members.forEach((member) => $members.append(new Option(member.text, member.id, true, true)));
                        $members.trigger('change');
                    });
                }).catch(() => {});
            },

            saveGroup() {
                const data = {name: this.group.name, members: ($(this.$refs.members).val() ?? []).map(Number)};
                const request = this.group.id
                    ? this.request('PUT', route('alert-transport-groups.update', {alert_transport_group: this.group.id}), data)
                    : this.request('POST', route('alert-transport-groups.store'), data);

                request.then((response) => this.done(response)).catch(() => {});
            },

            confirmDelete(url, name) {
                this.deleteUrl = url;
                this.deleteName = name;
            },

            selectTab(tab) {
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                history.replaceState(null, '', url);
            },

            destroy() {
                this.request('DELETE', this.deleteUrl).then((response) => this.done(response)).catch(() => {});
            },

            done(response) {
                this.transportOpen = false;
                this.groupOpen = false;
                this.deleteUrl = null;
                toastr.success(response.message);
                window.location.reload();
            },

            request(method, url, data) {
                this.saving = true;

                return axios({method, url, data})
                    .then((response) => response.data)
                    .catch((error) => {
                        toastr.error(error.response?.data?.message ?? error.message);
                        throw error;
                    })
                    .finally(() => this.saving = false);
            },
        }));
    });
</script>
@endpush
