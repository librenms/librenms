@extends('layouts.librenmsv1')

@section('title', __('tools.oui.lookup'))

@section('content')
    <div class="container">
        <x-panel title="{{ __('tools.oui.title') }}">
            @config('mac_oui.enabled')
                @if($db_populated)
                    <form>
                        <div>{{ __('tools.oui.prompt') }}</div>
                        <div>
                            <textarea name="query" class="tw:border-2" cols="30" rows="5">{{ $query }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                    </form>
                    <table class="table-condensed tw:mb-0! tw:mt-5">
                        @foreach($results as $result)
                            <tr>
                                <td style="width: 0.01%; white-space: nowrap;">{{ $result['mac'] }}</td>
                                <td>{{ $result['vendor'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    {{ __('tools.oui.no_db', ['command' => 'lnms maintenance:fetch-ouis']) }}
                @endif
            @else
                {{ __('tools.oui.not_enabled', ['setting' => 'mac_oui.enabled']) }}
            @endconfig
        </x-panel>
    </div>
@endsection
