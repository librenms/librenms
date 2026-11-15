@extends('layouts.librenmsv1')

@section('title', __('RIPE NCC API'))

@section('content')
<div class="container">
    <x-panel title="{{ __('RIPE NCC API Tools') }}">
        <form method="GET">
            <label class="radio-inline"><input type="radio" name="type" value="whois" @checked($type === 'whois')> {{ __('Whois') }}</label>
            <label class="radio-inline"><input type="radio" name="type" value="abuse-contact-finder" @checked($type === 'abuse-contact-finder')> {{ __('Abuse Contact Finder') }}</label>
            <div class="input-group tw:mt-4">
                <input type="text" name="query" class="form-control" value="{{ $query }}" placeholder="{{ __('IP, ASN etc.') }}" required>
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-primary">{{ __('Query') }}</button>
                </span>
            </div>
        </form>

        @foreach($errors->all() as $message)
            <div class="alert alert-danger tw:mt-4">{{ $message }}</div>
        @endforeach
        @if($error)
            <div class="alert alert-danger tw:mt-4">{{ $error }}</div>
        @endif

        @if($results !== null)
            <table class="table table-condensed table-hover tw:mt-4">
                @forelse($results as [$key, $value])
                    <tr>
                        <th class="tw:whitespace-nowrap">{{ $key }}</th>
                        <td>{{ $value }}</td>
                    </tr>
                @empty
                    <tr><td><em>{{ __('No results') }}</em></td></tr>
                @endforelse
            </table>
        @endif
    </x-panel>
</div>
@endsection
