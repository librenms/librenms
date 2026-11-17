@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="customoid" />

        <div x-data="customOids({{ $device->device_id }})">
            <x-panel>
                @if($canCreate)
                    <x-slot:heading>
                        <button type="button" class="btn btn-primary btn-sm" x-on:click="create()"><i class="fa fa-plus"></i> {{ __('Add New OID') }}</button>
                    </x-slot:heading>
                @endif
                <x-slot:table>
                    <table class="table table-hover table-condensed">
                        <thead>
                        <tr>
                            <th rowspan="2">{{ __('Name') }}</th>
                            <th rowspan="2">{{ __('OID') }}</th>
                            <th rowspan="2">{{ __('Value') }}</th>
                            <th rowspan="2">{{ __('Unit') }}</th>
                            <th colspan="2">{{ __('Alert Threshold') }}</th>
                            <th colspan="2">{{ __('Warning Threshold') }}</th>
                            <th rowspan="2">{{ __('Alerts') }}</th>
                            <th rowspan="2">{{ __('Passed') }}</th>
                            <th rowspan="2"></th>
                        </tr>
                        <tr>
                            <th><small>{{ __('High') }}</small></th>
                            <th><small>{{ __('Low') }}</small></th>
                            <th><small>{{ __('High') }}</small></th>
                            <th><small>{{ __('Low') }}</small></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($customoids as $oid)
                            <tr>
                                <td>{{ $oid->customoid_descr }}</td>
                                <td>{{ $oid->customoid_oid }}</td>
                                <td>{{ $oid->customoid_current }}</td>
                                <td>{{ $oid->customoid_unit }}</td>
                                <td>{{ $oid->customoid_limit }}</td>
                                <td>{{ $oid->customoid_limit_low }}</td>
                                <td>{{ $oid->customoid_limit_warn }}</td>
                                <td>{{ $oid->customoid_limit_low_warn }}</td>
                                <td><input type="checkbox" @checked($oid->customoid_alert) disabled></td>
                                <td><input type="checkbox" @checked($oid->customoid_passed) disabled></td>
                                <td class="tw:whitespace-nowrap">
                                    @can('update', $oid)
                                        <button type="button" class="btn btn-primary btn-sm" x-on:click="edit({{ $oid->customoid_id }})" title="{{ __('Edit') }}"><i class="fa fa-pencil"></i></button>
                                    @endcan
                                    @can('delete', $oid)
                                        <button type="button" class="btn btn-danger btn-sm" x-on:click="deleteId = {{ $oid->customoid_id }}" title="{{ __('Delete') }}"><i class="fa fa-trash"></i></button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11"><em>{{ __('No custom OIDs') }}</em></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </x-slot:table>
            </x-panel>

            <x-modal show="formOpen" maxWidth="2xl">
                <x-slot:heading>
                    <h4 class="tw:m-0 tw:text-base tw:font-semibold">
                        {{ __('Custom OID') }}
                        <a target="_blank" href="https://docs.librenms.org/Extensions/Custom-OIDs/">{{ __('Docs') }} <i class="fa fa-book"></i></a>
                    </h4>
                </x-slot:heading>

                <form class="form-horizontal" id="customoid-form" x-on:submit.prevent="save()">
                    <div class="form-group">
                        <label for="customoid-name" class="col-sm-3 control-label">{{ __('Name') }}</label>
                        <div class="col-sm-9"><input type="text" id="customoid-name" class="form-control" maxlength="200" required x-model="form.name"></div>
                    </div>
                    <div class="form-group">
                        <label for="customoid-oid" class="col-sm-3 control-label">{{ __('OID') }}</label>
                        <div class="col-sm-9"><input type="text" id="customoid-oid" class="form-control" maxlength="255" required x-model="form.oid" x-on:input="form.passed = false"></div>
                    </div>
                    <div class="form-group">
                        <label for="customoid-datatype" class="col-sm-3 control-label">{{ __('Data Type') }}</label>
                        <div class="col-sm-9">
                            <select id="customoid-datatype" class="form-control" x-model="form.datatype" x-bind:disabled="id">
                                <option value="GAUGE">GAUGE</option>
                                <option value="COUNTER">COUNTER</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="customoid-unit" class="col-sm-3 control-label">{{ __('Unit') }}</label>
                        <div class="col-sm-9"><input type="text" id="customoid-unit" class="form-control" maxlength="10" x-model="form.unit"></div>
                    </div>
                    <div class="form-group">
                        <label for="customoid-divisor" class="col-sm-3 control-label">{{ __('Divisor') }}</label>
                        <div class="col-sm-3"><input type="number" step="any" id="customoid-divisor" class="form-control" x-model="form.divisor"></div>
                        <label for="customoid-multiplier" class="col-sm-3 control-label">{{ __('Multiplier') }}</label>
                        <div class="col-sm-3"><input type="number" step="any" id="customoid-multiplier" class="form-control" x-model="form.multiplier"></div>
                    </div>
                    <div class="form-group">
                        <label for="customoid-user-func" class="col-sm-3 control-label">{{ __('User Function') }}</label>
                        <div class="col-sm-9">
                            <select id="customoid-user-func" class="form-control" x-model="form.user_func">
                                <option value=""></option>
                                <option value="celsius_to_fahrenheit">C to F</option>
                                <option value="fahrenheit_to_celsius">F to C</option>
                                <option value="kelvin_to_celsius">K to C</option>
                                <option value="uw_to_dbm">uW to dBm</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="customoid-limit" class="col-sm-3 control-label">{{ __('Alert High') }}</label>
                        <div class="col-sm-3"><input type="number" step="any" id="customoid-limit" class="form-control" x-model="form.limit"></div>
                        <label for="customoid-limit-low" class="col-sm-3 control-label">{{ __('Alert Low') }}</label>
                        <div class="col-sm-3"><input type="number" step="any" id="customoid-limit-low" class="form-control" x-model="form.limit_low"></div>
                    </div>
                    <div class="form-group">
                        <label for="customoid-limit-warn" class="col-sm-3 control-label">{{ __('Warning High') }}</label>
                        <div class="col-sm-3"><input type="number" step="any" id="customoid-limit-warn" class="form-control" x-model="form.limit_warn"></div>
                        <label for="customoid-limit-low-warn" class="col-sm-3 control-label">{{ __('Warning Low') }}</label>
                        <div class="col-sm-3"><input type="number" step="any" id="customoid-limit-low-warn" class="form-control" x-model="form.limit_low_warn"></div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <div class="checkbox"><label><input type="checkbox" x-model="form.alerts"> {{ __('Alerts Enabled') }}</label></div>
                            <div class="checkbox"><label><input type="checkbox" x-model="form.passed" disabled> {{ __('Passed Check') }}</label></div>
                            <p class="help-block">{{ __('The OID will not be polled until a test completes successfully.') }}</p>
                        </div>
                    </div>
                </form>

                <x-slot:footer>
                    <button type="button" class="btn btn-default" x-on:click="formOpen = false">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-info" x-on:click="test()" x-bind:disabled="saving">{{ __('Test OID') }}</button>
                    <button type="button" class="btn btn-warning" x-on:click="form.passed = true; save()" x-bind:disabled="saving">{{ __('Save (Skip test)') }}</button>
                    <button type="submit" form="customoid-form" class="btn btn-primary" x-bind:disabled="saving">{{ __('Save OID') }}</button>
                </x-slot:footer>
            </x-modal>

            <x-modal show="deleteId" :title="__('Confirm Delete')" maxWidth="md">
                <p>{{ __('Please confirm that you would like to delete this custom OID.') }}</p>
                <x-slot:footer>
                    <button type="button" class="btn btn-default" x-on:click="deleteId = null">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger" x-on:click="destroy()" x-bind:disabled="saving">{{ __('Delete') }}</button>
                </x-slot:footer>
            </x-modal>
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('customOids', (deviceId) => ({
                formOpen: false,
                deleteId: null,
                saving: false,
                id: null,
                form: {},

                create() {
                    this.id = null;
                    this.form = {datatype: 'GAUGE', user_func: '', alerts: false, passed: false};
                    this.formOpen = true;
                },

                edit(id) {
                    axios.get(route('customoid.show', {customoid: id})).then((response) => {
                        this.id = id;
                        this.form = {...response.data, user_func: response.data.user_func ?? '', passed: response.data.cpassed};
                        this.formOpen = true;
                    }).catch((error) => this.error(error));
                },

                data() {
                    return {...this.form, device_id: deviceId, alerts: this.form.alerts ? 'on' : 'off', passed: this.form.passed ? 'on' : 'off'};
                },

                test() {
                    this.request('post', route('customoid.test', this.id ? {customoid: this.id} : {}))
                        .then((ok) => this.form.passed = ok);
                },

                save() {
                    this.request(this.id ? 'put' : 'post', this.id ? route('customoid.update', {customoid: this.id}) : route('customoid.store'))
                        .then((ok) => ok && window.location.reload());
                },

                destroy() {
                    this.request('delete', route('customoid.destroy', {customoid: this.deleteId}))
                        .then((ok) => ok && window.location.reload());
                },

                // resolves whether it succeeded; the custom OID endpoints answer {status, message}, so a failure may still be a 200
                request(method, url) {
                    this.saving = true;

                    return axios({method, url, data: method === 'delete' ? undefined : this.data()})
                        .then((response) => {
                            const ok = response.data.status === 'ok';
                            (ok ? toastr.success : toastr.error)(response.data.message);

                            return ok;
                        })
                        .catch((error) => {
                            this.error(error);

                            return false;
                        })
                        .finally(() => this.saving = false);
                },

                error(error) {
                    toastr.error(error.response?.data?.message ?? @js(__('Failed to process OID')));
                },
            }));
        });
    </script>
@endpush
