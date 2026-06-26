@extends('layouts.librenmsv1')

@section('title', __('validation.results.validate'))

@section('content')
    <div x-data="{
            groups: @js($groups),
            listItems: 10,
            validateGroup(group) {
                // reset state and run/re-run the given group
                group.loading = true;
                group.errorMessage = '';
                group.status = null;
                group.statusText = '';
                group.results = [];

                const urlTemplate = '{{ route('validate.results', ['group' => ':group']) }}';
                const url = urlTemplate.replace(':group', encodeURIComponent(group.group));
                fetch(url)
                    .then(response => {
                        if (! response.ok) {
                            group.errorMessage = '{{ trans('validation.results.backend_failed') }}';
                            response.text().then(console.log);
                            group.loading = false;
                            return;
                        }
                        response.json()
                            .then(data => {
                                const arr = Array.isArray(data) ? data : [data];
                                const match = arr.find(item => item.group === group.group) ?? arr[0];
                                if (match) {
                                    group.status = match.status;
                                    group.statusText = match.statusText;
                                    group.results = match.results ?? [];
                                } else {
                                    group.errorMessage = '{{ trans('validation.results.backend_failed') }}';
                                }
                            })
                            .catch(error => {
                                group.errorMessage = ((error instanceof SyntaxError || error instanceof TypeError) ? '{{ trans('validation.results.backend_failed') }}' : String(error));
                            })
                            .finally(() => { group.loading = false; });
                    })
                    .catch(error => { group.errorMessage = String(error); group.loading = false; });
            },
            init() {
                // augment groups with state
                this.groups = this.groups.map(g => ({
                    ...g,
                    loading: !!g.enabled,
                    errorMessage: '',
                    // If disabled, immediately mark as skipped (INFO)
                    status: g.enabled ? null : 3,
                    statusText: g.enabled ? '' : '{{ __('validation.results.skipped') }}',
                    results: []
                }));

                // auto-run enabled groups with a slight stagger to avoid blocking UI on load
                this.groups.forEach((g, i) => {
                    if (g.enabled) {
                        const delay = 150 + (i * 100); // start after 150ms, then stagger 100ms per group
                        setTimeout(() => this.validateGroup(g), delay);
                    }
                });
            }
        }">
        <div class="tw:mx-10">
            <template x-for="(group, index) in groups" :key="group.group">
                <div class="panel-group" style="margin-bottom: 5px">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title tw:flex tw:justify-between tw:items-center">
                                <a data-toggle="collapse" x-bind:data-target="'#body-' + group.group">
                                    <span x-text="group.name"></span>
                                </a>
                                <button type="button"
                                        class="btn btn-default btn-sm tw:inline-flex tw:items-center tw:gap-2 tw:whitespace-nowrap"
                                        title="{{ __('validation.results.run') }}"
                                        x-on:click.stop="validateGroup(group)"
                                        x-bind:disabled="group.loading"
                                >
                                    <i class="fa-solid" x-bind:class="group.loading ? 'fa-spinner fa-spin' : 'fa-play'"></i>
                                    <span x-bind:class="{
                                            'text-success': !group.loading && group.status === 2,
                                            'text-warning': !group.loading && group.status === 1,
                                            'text-danger': !group.loading && group.status === 0,
                                            'text-info': !group.loading && group.status === 3
                                        }" class="tw:font-bold">
                                        <span x-text="group.loading ? '{{ __('validation.results.validating') }}' : (group.statusText || '{{ __('validation.results.run') }}')"></span>
                                    </span>
                                </button>
                            </h4>
                        </div>
                        <div x-bind:id="'body-' + group.group" class="panel-collapse collapse" x-bind:class="{'in': group.loading || group.status !== 2}">
                            <div class="panel-body">
                                <div class="tw:grid tw:place-items-center" style="min-height: 80px" x-show="group.loading">
                                    <h4><i class="fa-solid fa-spinner fa-spin"></i> {{ __('validation.results.validating') }}</h4>
                                </div>
                                <div x-show="group.errorMessage && ! group.loading" class="panel panel-danger">
                                    <div class="panel-heading">
                                        <i class="fa-solid fa-exclamation-triangle"></i> {{ __('validation.results.fetch_failed') }}
                                    </div>
                                    <div class="panel-body" x-text="group.errorMessage"></div>
                                </div>
                                <template x-for="result in group.results" x-show="! group.loading && ! group.errorMessage">
                                    <div class="panel" x-bind:class="{'panel-info': result.status === 3, 'panel-success': result.status === 2, 'panel-warning': result.status === 1, 'panel-danger': result.status === 0}">
                                        <div class="panel-heading"
                                             x-text="result.statusText + ': ' + result.message"
                                        ></div>
                                        <div class="panel-body" x-show="result.fix.length || result.list.length || result.fixer">
                                            <div x-show="result.fixer" class="tw:mb-2" x-data="fixerData(result.fixer)">
                                                <button class="btn btn-success" x-on:click="runFixer" x-bind:disabled="running" x-show="! fixed">
                                                    <i class="fa-solid" x-bind:class="running ? 'fa-spinner fa-spin' : 'fa-wrench'"></i> {{ __('validation.results.autofix') }}
                                                </button>
                                                <div x-show="fixed">{{ __('validation.results.fixed') }}</div>
                                            </div>
                                            <div x-show="result.fix.length">
                                                {{ __('validation.results.fix') }}: <pre x-text='result.fix.join("\r\n")'>
                                                </pre>
                                            </div>
                                            <div x-show="result.list.length" class="tw:mt-5">
                                                <ul class='list-group' style='margin-bottom: -1px'>
                                                    <li class="list-group-item active" x-text="result.listDescription"></li>
                                                    <template x-for="shortList in result.list.slice(0, listItems)">
                                                        <li class="list-group-item" x-text="shortList"></li>
                                                    </template>
                                                </ul>
                                                <div x-data="{expanded: false}" x-show="result.list.length > listItems">
                                                    <button style="margin-top: 3px" type="button" class="btn btn-default" x-on:click="expanded = ! expanded" x-text="expanded ? '{{ __('validation.results.show_less')}}' : '{{ __('validation.results.show_all')}}'"></button>
                                                    <ul x-show="expanded" class='list-group'>
                                                        <template x-for="longList in result.list.slice(listItems)">
                                                            <li class='list-group-item' x-text="longList"></li>
                                                        </template>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function fixerData(name) {
            return {
                running: false,
                fixed: false,
                fixer: name,
                runFixer() {
                    event.target.disabled = true;
                    fetch('{{ route('validate.fix') }}', {
                        method: 'POST',
                        body: JSON.stringify({fixer: this.fixer}),
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            "X-CSRF-Token": document.querySelector('input[name=_token]').value
                        },
                    }).then(response => {
                        if (response.ok) {
                            this.fixed = true;
                        } else {
                            this.running = false;
                        }
                    }).catch(response => this.running = false);
                }
            }
        }
    </script>
@endpush
