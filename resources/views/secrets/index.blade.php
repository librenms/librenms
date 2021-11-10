@extends('layouts.librenmsv1')

@section('title', __('Secrets'))

@section('content')
    <div class="container">
        <x-panel>
            <x-slot name="title">
                <i class="fas fa-key fa-fw fa-lg" aria-hidden="true"></i> {{ __('Secrets') }}
            </x-slot>

            <div class="tw:flex tw:justify-between tw:items-center tw:mb-4">
                <div class="tw:text-sm tw:text-slate-500 tw:dark:text-dark-white-400">
                    {{ $secrets->count() }} {{ Str::plural('secret', $secrets->count()) }} configured
                </div>
                <div class="tw:flex tw:items-center tw:gap-2">
                    <a href="{{ route('secrets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus tw:mr-1"></i>
                        {{ __('Add Secret') }}
                    </a>
                </div>
            </div>

            @if($secrets->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Default') }}</th>
                                <th class="tw:text-center">{{ __('Devices') }}</th>
                                <th class="tw:w-32 tw:text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($secrets as $secret)
                                <tr>
                                    <td class="tw:align-middle">
                                        <div class="tw:font-bold tw:text-slate-800 tw:dark:text-dark-white-100">
                                            {{ $secret->description }}
                                        </div>
                                    </td>
                                    <td class="tw:align-middle">
                                        {{ Str::upper($secret->secret_type->value) }}
                                    </td>
                                    <td class="tw:align-middle">
                                        @if($secret->default)
                                            <span class="label label-success">{{ __('Yes') }}</span>
                                        @else
                                            <span class="label label-default">{{ __('No') }}</span>
                                        @endif
                                    </td>
                                    <td class="tw:text-center tw:align-middle">
                                        <a href="{{ route('devices', ['filter' => ['secrets.secret_id' => ['eq' => $secret->id]]]) }}" class="tw:font-semibold tw:text-blue-600 tw:dark:text-blue-400 tw:hover:underline">
                                            {{ $secret->devices_count }}
                                        </a>
                                    </td>
                                    <td class="tw:text-center tw:align-middle">
                                        <div class="tw:flex tw:justify-center tw:gap-1">
                                            <a href="{{ route('secrets.edit', $secret->id) }}"
                                               title="{{ __('Edit Secret') }}"
                                               class="btn btn-xs btn-warning">
                                                <i class="fas fa-pencil"></i>
                                            </a>
                                            <form action="{{ route('secrets.destroy', $secret->id) }}" method="POST"
                                                  onsubmit="return confirm('{{ __('Are you sure you want to delete this secret?') }}');"
                                                  class="tw:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        title="{{ __('Delete Secret') }}"
                                                        class="btn btn-xs btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="tw:flex tw:flex-col tw:items-center tw:justify-center tw:py-12 tw:text-center">
                    <div class="tw:w-16 tw:h-16 tw:rounded-2xl tw:bg-slate-100 tw:dark:bg-dark-gray-400 tw:flex tw:items-center tw:justify-center tw:mb-4 tw:border tw:border-slate-200 tw:dark:border-dark-gray-200">
                        <i class="fas fa-key tw:text-slate-300 tw:dark:text-dark-gray-100 tw:text-2xl"></i>
                    </div>
                    <h3 class="tw:text-slate-700 tw:dark:text-dark-white-200 tw:font-semibold tw:text-lg tw:mb-1">{{ __('No secrets yet') }}</h3>
                    <p class="tw:text-slate-400 tw:dark:text-dark-white-400 tw:text-sm tw:mb-6">{{ __('Create your first secret to start managing access.') }}</p>
                </div>
            @endif
        </x-panel>
    </div>
@endsection
