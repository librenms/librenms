@extends('layouts.librenmsv1')

@section('title', __('bulk-snmp.title'))

@section('content')
<div class="container-fluid"
     x-data="bulkSnmpForm({
        groupId: {{ $group->id }},
        deviceCount: {{ $deviceCount }},
        testUrl: '{{ route('device-group.bulk-snmp.test', $group) }}',
        applyUrl: '{{ route('device-group.bulk-snmp.apply', $group) }}',
        csrfToken: '{{ csrf_token() }}',
     })">

    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            <div class="page-header">
                <h2>
                    <i class="fa fa-key"></i> {{ __('bulk-snmp.title') }}
                    <small>{{ $group->name }} &mdash;
                        {{ trans_choice('bulk-snmp.device_count', $deviceCount, ['count' => $deviceCount]) }}
                    </small>
                </h2>
            </div>

            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> {{ __('bulk-snmp.description') }}
            </div>

            <form @submit.prevent="apply">
                @csrf

                {{-- SNMP Version --}}
                <div class="panel panel-default">
                    <div class="panel-heading">{{ __('bulk-snmp.sections.snmp_version') }}</div>
                    <div class="panel-body">
                        <div class="btn-group" role="group">
                            <template x-for="v in ['v2c','v3']" :key="v">
                                <button type="button" class="btn"
                                        @click="form.snmpver = v"
                                        :class="form.snmpver === v ? 'btn-primary' : 'btn-default'"
                                        x-text="v"></button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- v2c fields --}}
                <template x-if="form.snmpver === 'v2c'">
                    <div class="panel panel-default">
                        <div class="panel-heading">{{ __('bulk-snmp.sections.credentials') }}</div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="community">{{ __('bulk-snmp.fields.community') }}</label>
                                <input type="text" id="community" class="form-control"
                                       x-model="form.community" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                </template>

                {{-- v3 fields --}}
                <template x-if="form.snmpver === 'v3'">
                    <div class="panel panel-default">
                        <div class="panel-heading">{{ __('bulk-snmp.sections.credentials') }}</div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="authlevel">{{ __('bulk-snmp.fields.authlevel') }}</label>
                                        <select id="authlevel" class="form-control" x-model="form.authlevel">
                                            @foreach ($securityLevels as $lvl)
                                                <option value="{{ $lvl }}">{{ $lvl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="authname">{{ __('bulk-snmp.fields.authname') }}</label>
                                        <input type="text" id="authname" class="form-control"
                                               x-model="form.authname" autocomplete="off" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="authalgo">{{ __('bulk-snmp.fields.authalgo') }}</label>
                                        <select id="authalgo" class="form-control" x-model="form.authalgo">
                                            @foreach ($authAlgos as $algo)
                                                <option value="{{ $algo }}">{{ $algo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="authpass">{{ __('bulk-snmp.fields.authpass') }}</label>
                                        <input type="password" id="authpass" class="form-control"
                                               x-model="form.authpass" autocomplete="new-password" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cryptoalgo">{{ __('bulk-snmp.fields.cryptoalgo') }}</label>
                                        <select id="cryptoalgo" class="form-control" x-model="form.cryptoalgo">
                                            @foreach ($privAlgos as $algo)
                                                <option value="{{ $algo }}">{{ $algo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cryptopass">{{ __('bulk-snmp.fields.cryptopass') }}</label>
                                        <input type="password" id="cryptopass" class="form-control"
                                               x-model="form.cryptopass" autocomplete="new-password" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Options --}}
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" x-model="form.skip_down" />
                                {{ __('bulk-snmp.fields.skip_down') }}
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Error banner --}}
                <template x-if="errorMessage">
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span x-text="errorMessage"></span>
                    </div>
                </template>

                {{-- Test results --}}
                <template x-if="testResults">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <span x-text="testResults.passed"></span>
                            / <span x-text="testResults.total"></span>
                            {{ __('bulk-snmp.feedback.devices_reachable') }}
                        </div>
                        <div class="panel-body" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-condensed table-striped">
                                <tbody>
                                    <template x-for="r in testResults.results" :key="r.device_id">
                                        <tr>
                                            <td style="width: 24px;">
                                                <span x-show="r.success" class="text-success">
                                                    <i class="fa fa-check"></i>
                                                </span>
                                                <span x-show="!r.success" class="text-danger">
                                                    <i class="fa fa-times"></i>
                                                </span>
                                            </td>
                                            <td x-text="r.hostname"></td>
                                            <td class="text-muted" x-text="r.message"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                {{-- Apply results --}}
                <template x-if="applyResults">
                    <div class="panel"
                         :class="applyResults.failed_count > 0 ? 'panel-warning' : 'panel-success'">
                        <div class="panel-heading">
                            <span x-text="applyResults.success_count"></span>
                            / <span x-text="applyResults.total"></span>
                            {{ __('bulk-snmp.feedback.devices_updated') }}
                        </div>
                        <template x-if="applyResults.failed_count > 0">
                            <div class="panel-body">
                                <table class="table table-condensed">
                                    <tbody>
                                        <template x-for="f in applyResults.failed" :key="f.device_id">
                                            <tr class="text-danger">
                                                <td><i class="fa fa-times"></i></td>
                                                <td x-text="f.hostname"></td>
                                                <td x-text="f.message"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Buttons --}}
                <div class="form-group">
                    <a href="{{ route('device-groups.edit', $group->id) }}" class="btn btn-default">
                        {{ __('bulk-snmp.buttons.cancel') }}
                    </a>
                    <button type="button" class="btn btn-default" @click="runTest" :disabled="loading">
                        <i class="fa fa-flask"></i> {{ __('bulk-snmp.buttons.test') }}
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <i class="fa fa-key"></i>
                        {{ __('bulk-snmp.buttons.apply', ['count' => $deviceCount]) }}
                    </button>
                    <span x-show="loading" class="text-muted">
                        <i class="fa fa-spinner fa-spin"></i> {{ __('bulk-snmp.feedback.working') }}
                    </span>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function bulkSnmpForm(config) {
    return {
        loading: false,
        testResults: null,
        applyResults: null,
        errorMessage: null,
        form: {
            snmpver: 'v3',
            community: '',
            authlevel: 'authPriv',
            authname: '',
            authpass: '',
            authalgo: 'SHA',
            cryptopass: '',
            cryptoalgo: 'AES',
            skip_down: false,
        },

        /**
         * Perform a request and return parsed JSON, or throw a readable error.
         */
        async request(url) {
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.form),
            });

            let data = null;
            try {
                data = await resp.json();
            } catch (e) {
                data = null;
            }

            if (!resp.ok) {
                // 403 = not authorized, 422 = validation, 500 = server error
                if (resp.status === 403) {
                    throw new Error(
                        '{{ __('bulk-snmp.denied.message') }}'
                    );
                }
                if (resp.status === 422 && data && data.errors) {
                    const first = Object.values(data.errors)[0];
                    throw new Error(Array.isArray(first) ? first[0] : first);
                }
                throw new Error(
                    (data && data.message) ? data.message : ('HTTP ' + resp.status)
                );
            }
            return data;
        },

        async runTest() {
            this.loading = true;
            this.testResults = null;
            this.errorMessage = null;
            try {
                this.testResults = await this.request(config.testUrl);
            } catch (e) {
                this.errorMessage = e.message;
            } finally {
                this.loading = false;
            }
        },

        async apply() {
            if (!confirm('{{ __('bulk-snmp.feedback.confirm_apply', ['count' => $deviceCount]) }}')) {
                return;
            }
            this.loading = true;
            this.applyResults = null;
            this.errorMessage = null;
            try {
                this.applyResults = await this.request(config.applyUrl);
            } catch (e) {
                this.errorMessage = e.message;
            } finally {
                this.loading = false;
            }
        },
    }
}
</script>
@endsection
