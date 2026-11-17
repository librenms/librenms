@extends('layouts.librenmsv1')

@section('title', __('Oxidized'))

@section('content')
<div class="container-fluid">
    <x-panel title="{{ __('Oxidized') }}">
        @unless($reachable)
            <div class="alert alert-danger">{{ __('Oxidized is not reachable') }}</div>
        @endunless

        <x-tabs active="nodes">
            <x-tab :name="__('Node List')" value="nodes">
                <table id="oxidized-nodes" class="table table-hover table-condensed table-striped">
                    <thead>
                    <tr>
                        <th data-column-id="id" data-visible="false" data-identifier="true">{{ __('ID') }}</th>
                        <th data-column-id="device_id" data-visible="false">{{ __('Device ID') }}</th>
                        <th data-column-id="hostname" data-formatter="hostname" data-order="asc">{{ __('Hostname') }}</th>
                        <th data-column-id="sysname" data-visible="false">{{ __('SysName') }}</th>
                        <th data-column-id="last_status" data-formatter="status">{{ __('Last Status') }}</th>
                        <th data-column-id="last_update">{{ __('Last Update') }}</th>
                        <th data-column-id="model">{{ __('Model') }}</th>
                        <th data-column-id="group">{{ __('Group') }}</th>
                        <th data-column-id="actions" data-formatter="actions" data-sortable="false" data-searchable="false">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($nodes as $node)
                        <tr>
                            <td>{{ $loop->index }}</td>
                            <td>{{ $node['device']?->device_id }}</td>
                            <td>{{ $node['name'] }}</td>
                            <td>{{ $node['device']?->sysName }}</td>
                            <td>{{ $node['status'] }}</td>
                            <td>{{ $node['time'] }}</td>
                            <td>{{ $node['model'] }}</td>
                            <td>{{ $node['group'] }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </x-tab>

            @can('oxidized.search')
                <x-tab :name="__('Config Search')" value="search">
                    <div x-data="oxidizedSearch" class="tw:mt-4">
                        <form x-on:submit.prevent="search()" autocomplete="off">
                            <div class="input-group">
                                <input type="search" name="config-search" class="form-control" x-model="query"
                                       aria-label="{{ __('Search configurations') }}"
                                       autocomplete="off" data-1p-ignore data-lpignore="true" data-bwignore
                                       placeholder="{{ __('service password-encryption etc. (Regex)') }}">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary" x-bind:disabled="searching">{{ __('Search') }}</button>
                                </span>
                            </div>
                        </form>

                        <div class="tw:mt-6" x-show="results !== null" x-bind:aria-busy="searching" aria-live="polite" x-cloak>
                            <div x-show="results?.length" class="tw:flex tw:items-center tw:gap-2 tw:mb-3">
                                <h3 class="tw:m-0 tw:text-base tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100">{{ __('Matching configurations') }}</h3>
                                <span class="tw:rounded-full tw:bg-gray-100 tw:px-2.5 tw:py-0.5 tw:text-sm tw:font-medium tw:text-gray-600 tw:dark:bg-dark-gray-300 tw:dark:text-dark-white-200" x-text="results?.length"></span>
                            </div>

                            <ul x-show="results?.length" class="tw:m-0 tw:grid tw:list-none tw:grid-cols-1 tw:gap-x-8 tw:gap-y-2 tw:p-0 tw:sm:grid-cols-2 tw:lg:grid-cols-3">
                                <template x-for="(result, index) in results ?? []" :key="index">
                                    <li class="tw:min-w-0 tw:wrap-anywhere tw:py-1">
                                        <a x-show="result.url" x-bind:href="result.url" x-text="result.name" class="tw:font-semibold tw:hover:underline"></a>
                                        <span x-show="! result.url" x-text="result.name" class="tw:text-gray-700 tw:dark:text-dark-white-200"></span>
                                    </li>
                                </template>
                            </ul>

                            <div x-show="results?.length === 0" class="tw:rounded-lg tw:border tw:border-dashed tw:border-gray-300 tw:px-4 tw:py-8 tw:text-center tw:dark:border-dark-gray-200">
                                <i class="fa fa-search tw:mb-3 tw:text-2xl tw:text-gray-400 tw:dark:text-dark-white-400" aria-hidden="true"></i>
                                <p class="tw:mb-1 tw:font-semibold tw:text-gray-800 tw:dark:text-dark-white-100">{{ __('No matching configs found.') }}</p>
                                <p class="tw:m-0 tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('Try a different search term or a broader regular expression.') }}</p>
                            </div>
                        </div>
                    </div>
                </x-tab>
            @endcan
        </x-tabs>
    </x-panel>
</div>

<template id="oxidized-nodes-actions">
    @can('config-backup.refresh')
        <button type="button" class="btn btn-success btn-sm" id="oxidized-reload" title="{{ __('Update Oxidized\'s node list from LibreNMS data') }}">
            <i class="fa fa-refresh" aria-hidden="true"></i> {{ __('Reload node list') }}
        </button>
    @endcan
</template>
@endsection

@push('scripts')
<script>
    function oxidizedRequest(url, data) {
        return axios.post(url, data ?? {})
            .then((response) => response.data)
            .catch((error) => {
                toastr.error(error.response?.data?.message ?? @js(__('Request failed')));
                throw error;
            });
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('oxidizedSearch', () => ({
            query: '',
            results: null,
            searching: false,

            search() {
                this.searching = true;
                oxidizedRequest(@js(route('oxidized.search')), {search: this.query})
                    .then((response) => this.results = response.results)
                    .catch(() => {})
                    .finally(() => this.searching = false);
            },
        }));
    });

    function escapeOxidizedHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html().replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    const deviceUrl = @js(route('device', ['device' => '__id__']));
    const configUrl = @js(route('device', ['device' => '__id__', 'tab' => 'config']));
    const refreshUrl = @js(route('device.config.refresh', ['device' => '__id__']));

    $("#oxidized-nodes").bootgrid({
        rowCount: [50, 100, 250, -1],
        converters: {
            // rows are parsed from the table as text, escape them again when rendering
            string: {
                from: (value) => value,
                to: escapeOxidizedHtml,
            }
        },
        formatters: {
            hostname: function (column, row) {
                return row.device_id
                    ? '<a href="' + deviceUrl.replace('__id__', encodeURIComponent(row.device_id)) + '">' + escapeOxidizedHtml(row.hostname) + '</a>'
                    : escapeOxidizedHtml(row.hostname);
            },
            actions: function (column, row) {
                if (! row.device_id) {
                    return '';
                }
                const id = encodeURIComponent(row.device_id);
                return @can('config-backup.refresh') '<button type="button" class="btn btn-default btn-sm oxidized-refresh" data-url="' + refreshUrl.replace('__id__', id) + '" title="' + @js(__('Refetch config')) + '"><i class="fa fa-refresh"></i></button> ' + @endcan
                    '<a href="' + configUrl.replace('__id__', id) + '" title="' + @js(__('View config')) + '"><i class="fa fa-align-justify fa-lg icon-theme"></i></a>';
            },
            status: function (column, row) {
                const color = row.last_status === 'success' ? 'success' : 'danger';
                return '<i class="fa fa-square text-' + color + '" title="' + escapeOxidizedHtml(row.last_status) + '"></i>';
            }
        }
    }).on('click', '.oxidized-refresh', function () {
        oxidizedRequest(this.dataset.url).then((response) => toastr.success(response.message)).catch(() => {});
    });

    $('#oxidized-nodes-header .actionBar').prepend(document.getElementById('oxidized-nodes-actions').content.cloneNode(true));
    $('#oxidized-reload').on('click', function () {
        oxidizedRequest(@js(route('oxidized.reload'))).then((response) => toastr.success(response.message)).catch(() => {});
    });
</script>
@endpush
