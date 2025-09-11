@extends('layouts.librenmsv1')

@section('title', __('tools.oui.lookup'))

@section('content')
    <div class="container">
        <x-panel title="{{ __('tools.oui.title') }}" class="tw:w-fit tw:mx-auto">
            @config('mac_oui.enabled')
                @if($db_populated)
                    <div class="tw:flex tw:flex-col tw:md:flex-row">
                    <form class="tw:mx-4 tw:sm:mx-10">
                        <div>{{ __('tools.oui.prompt') }}</div>
                        <textarea name="query" class="tw:border-2 tw:w-auto! form-control" cols="30" rows="5">{{ $query }}</textarea>
                        <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                    </form>
                        <div class="tw:flex tw:flex-col tw:border-2 tw:rounded-lg tw:inset-shadow-sm/10 tw:p-3 tw:mt-4 tw:mx-4 tw:sm:mx-10 tw:border-gray-300">
                            <table class="table-condensed table-hover tw:mb-0! tw:h-auto tw:min-h-4 tw:md:min-w-md">
                                @foreach($results as $result)
                                    <tr>
                                        <td style="width: 0.01%; white-space: nowrap;">{{ $result['mac'] }}</td>
                                        <td>{{ $result['vendor'] }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                @else
                    {{ __('tools.oui.no_db', ['command' => 'lnms maintenance:fetch-ouis']) }}
                @endif
            @else
                {{ __('tools.oui.not_enabled', ['setting' => 'mac_oui.enabled']) }}
            @endconfig
        </x-panel>
    </div>
@endsection
