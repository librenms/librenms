@extends('layouts.librenmsv1')

@section('title', __('Ports'))

@section('content')
    <div class="container-fluid">
        <x-panel>
            <x-slot name="heading">
                <div class="tw:flex tw:justify-between">
                    <div class="tw:min-h-8">
                        <x-option-bar :options="$nav" name="{{ __('Ports') }}" :selected="$view" linkClass="sync-url" border="none" class="tw:inline-block tw:p-1"></x-option-bar>
                        <x-option-bar :options="$graphNav" name="{{ __('Graphs') }}" :selected="$view" linkClass="sync-url" border="none" class="tw:inline-block tw:p-1"></x-option-bar>
                    </div>
                    <div class="btn-group pull-right" role="group">
                        <div class="btn-group" role="group" x-data="portPurge()">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-v fa-lg fa-fw icon-theme"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="{{ $hideFilterLink }}"><i class="fa fa-regular @if($hideFilter) fa-square @else fa-square-check @endif fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ __('Show Filter') }}</a></li>
                                <li><a href="{{ $bareLink }}"><i class="fa fa-regular @if($bare) fa-square @else fa-square-check @endif fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ __('Show Header') }}</a></li>
                                <li><a href="#" @click.prevent="purgeDeleted()">
                                    <i x-show="!loading" class="fa fa-trash fa-lg fa-fw icon-theme" aria-hidden="true"></i>
                                    <i x-show="loading" class="fa fa-refresh fa-spin fa-lg fa-fw icon-theme" aria-hidden="true"></i>
                                    <span x-text="loading ? '{{ __('Processing...') }}' : '{{ __('Purge all deleted') }}'"></span>
                                </a></li>
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
                            toastr.success('All deleted ports have been purged.', 'Purged');
                        } else {
                            toastr.error('The server encountered an error.', 'Purge Failed');
                        }
                    } catch (error) {
                        toastr.error('Could not connect to the server.', 'Network Error');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }

        // update all links with the sync-url class to the current filter
        $(window).on('filter:apply', function (event) {
            const serializedFilter = $.param({ filter: event.originalEvent.detail.formatted });

            $('a.sync-url').each(function () {
                const url = new URL($(this).attr('href'), window.location.origin);

                [...url.searchParams.keys()]
                    .filter(key => key.startsWith('filter'))
                    .forEach(key => url.searchParams.delete(key));

                const base = url.origin + url.pathname;
                const existing = url.searchParams.toString();
                $(this).attr('href', `${base}?${existing}${existing ? '&' : ''}${serializedFilter}`);
            });
        });
    </script>
@endpush
