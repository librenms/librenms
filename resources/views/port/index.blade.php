@extends('layouts.librenmsv1')

@section('title', __('Ports'))

@section('content')
    <div class="container-fluid">
        <x-panel>
            <x-slot name="heading">
                <div class="tw:flex tw:justify-between">
                    <div class="tw:min-h-8">
                        <x-option-bar :options="$nav" name="{{ __('Ports') }}" :selected="$view" linkClass="sync-filter-url" border="none" class="tw:inline-block tw:p-1"></x-option-bar>
                        <x-option-bar :options="$graphNav" name="{{ __('Graphs') }}" :selected="$view" linkClass="sync-filter-url" border="none" class="tw:inline-block tw:p-1"></x-option-bar>
                        <span id="group-graph-link" x-data="{ group: @js($group) }"  x-show="group" x-init="window.addEventListener('filter:apply', (e) => $data.group = e.detail.filters.group?.eq);">
                            | <a :href="'{{ url('iftype/group=:group') }}'.replace(':group', group)" title="{{ __('port.groups.graph') }}">{{ __('port.groups.combined') }}</a>
                        </span>
                    </div>
                    <div class="btn-group pull-right" role="group">
                        <div class="btn-group" role="group" x-data="portPurge()">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-v fa-lg fa-fw icon-theme"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="{{ $hideFilterLink }}"><i class="fa fa-regular @if($hideFilter) fa-square @else fa-square-check @endif fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ __('port.show_fitler') }}</a></li>
                                <li><a href="{{ $bareLink }}"><i class="fa fa-regular @if($bare) fa-square @else fa-square-check @endif fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ __('port.show_header') }}</a></li>
                                @can('delete', \App\Models\Port::class)
                                <li><a href="#" @click.prevent="purgeDeleted()">
                                    <i x-show="!loading" class="fa fa-trash fa-lg fa-fw icon-theme" aria-hidden="true"></i>
                                    <i x-show="loading" class="fa fa-refresh fa-spin fa-lg fa-fw icon-theme" aria-hidden="true"></i>
                                    <span x-text="loading ? '{{ __('port.processing') }}' : '{{ __('port.purge') }}'"></span>
                                </a></li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </div>
            </x-slot>
            @if($view === 'graph')
                @include('port.graphs')
            @else
                @include('port.list')
            @endif
        </x-panel>

    </div>
@endsection

@push('scripts')
    <script>
        function portPurge() {
            return {
                loading: false,
                async purgeDeleted() {
                    if (this.loading) return; // Prevent double-clicks
                    this.loading = true;

                    const url = '{{ route('ports.purge') }}';
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        const response = await fetch(url, {
                            method: 'DELETE',
                            body: JSON.stringify({ purge: 'all' }),
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            toastr.success(@js(__('port.purged_message')), @js(__('port.purged')));
                        } else {
                            toastr.error(@js(__('port.server_error')), @js(__('port.purge_failed')));
                        }
                    } catch (error) {
                        toastr.error(@js(__('port.network_error')), @js(__('port.purge_failed')));
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endpush
