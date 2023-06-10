@extends('layouts.librenmsv1')

@section('title', __('validation.results.validate'))

@section('content')
    <div x-data="{results: [], listItems: 10, errorMessage: ''}" x-init="fetch('{{ route('validate.results') }}')
                .then(response => {
                    if (response.ok) {
                        response.json()
                            .then(data => results = data)
                            .catch(error => errorMessage = (error instanceof SyntaxError ? '{{ trans('validation.results.backend_failed') }}' : error))
                    } else {
                        errorMessage = '{{ trans('validation.results.backend_failed') }}';
                        response.text().then(console.log);
                    }
                });">
        <div class="tw-grid tw-place-items-center" style="height: 80vh" x-show="! results.length">
            <h3 x-show="! errorMessage"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('validation.results.validating') }}</h3>
            <div x-show="errorMessage" class="panel panel-danger">
                <div class="panel-heading">
                    <i class="fa-solid fa-exclamation-triangle"></i> {{ __('validation.results.fetch_failed') }}
                </div>
                <div class="panel-body" x-text="errorMessage"></div>
            </div>
        </div>
        <div x-show="results.length" class="tw-mx-10">
            <template x-for="(group, index) in results">
                <div class="panel-group" style="margin-bottom: 5px">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" x-bind:data-target="'#body-' + group.group">
                                    <span x-text="group.name"></span>
                                    <span class="pull-right"
                                          x-bind:class="{'text-success': group.status === 2, 'text-warning': group.status === 1, 'text-danger': group.status === 0}"
                                          x-text="group.statusText"></span>
                                </a>
                            </h4>
                        </div>
                        <div x-bind:id="'body-' + group.group" class="panel-collapse collapse" x-bind:class="{'in': group.status !== 2}">
                            <div class="panel-body">
                                <template x-for="result in group.results">
                                    <div class="panel" x-bind:class="{'panel-info': result.status === 3, 'panel-success': result.status === 2, 'panel-warning': result.status === 1, 'panel-danger': result.status === 0}">
                                        <div class="panel-heading"
                                             x-text="result.statusText + ': ' + result.message"
                                        ></div>
                                        <div class="panel-body" x-show="result.fix.length || result.list.length || result.fixer">
                                            <div x-show="result.fixer" class="tw-mb-2" x-data="fixerData(result.fixer)">
                                                <button class="btn btn-success" x-on:click="runFixer" x-bind:disabled="running" x-show="! fixed">
                                                    <i class="fa-solid" x-bind:class="running ? 'fa-spinner fa-spin' : 'fa-wrench'"></i> {{ __('validation.results.autofix') }}
                                                </button>
                                                <div x-show="fixed">{{ __('validation.results.fixed') }}</div>
                                            </div>
                                            <div x-show="result.fix.length">
                                                {{ __('validation.results.fix') }}: <pre x-text='result.fix.join("\r\n")'>
                                                </pre>
                                            </div>
                                            <div x-show="result.list.length" class="tw-mt-5">
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
