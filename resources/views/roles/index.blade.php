@extends('layouts.librenmsv1')

@section('title', __('permissions.rbac.title'))

@section('content')
    <div class="container">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle tw:mr-2"></i>
            <strong>{{ __('permissions.rbac.beta_warning_title') }}:</strong>
            {{ __('permissions.rbac.beta_warning_message') }}
        </div>
        <x-panel>
            <x-slot name="title">
                <i class="fas fa-shield-halved fa-fw fa-lg" aria-hidden="true"></i> {{ __('permissions.rbac.title') }}
            </x-slot>

            <div class="tw:flex tw:justify-between tw:items-center tw:mb-4">
                <div class="tw:text-sm tw:text-slate-500 tw:dark:text-dark-white-400">
                    {{ $roles->count() }} {{ Str::plural('role', $roles->count()) }} configured
                </div>
                <div class="tw:flex tw:items-center tw:gap-2">
                    @can('viewAny', \App\Models\User::class)
                        <a href="{{ route('users.index') }}"
                           class="btn btn-default">
                            <i class="fas fa-users tw:mr-1"></i>
                            {{ __('permissions.rbac.manage_users') }}
                        </a>
                    @endcan
                    <a href="{{ route('roles.create') }}"
                       class="btn btn-primary">
                        <i class="fas fa-plus tw:mr-1"></i>
                        {{ __('permissions.rbac.add_role') }}
                    </a>
                </div>
            </div>

            @if($roles->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th class="tw:w-64">{{ __('Role') }}</th>
                                <th>{{ __('Permissions') }}</th>
                                <th class="tw:w-32 tw:text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td class="tw:align-middle">
                                        <div class="tw:font-bold tw:text-slate-800 tw:dark:text-dark-white-100">
                                            {{ $role->name }}
                                        </div>
                                        <div class="tw:text-xs tw:text-slate-400 tw:dark:text-dark-white-400">
                                            {{ $role->permissions->count() }} {{ Str::plural('permission', $role->permissions->count()) }}
                                        </div>
                                    </td>
                                    <td class="tw:align-middle">
                                        <div class="tw:flex tw:flex-wrap tw:gap-1">
                                            @forelse($role->permissions as $permission)
                                                <span class="label label-info">
                                                    {{ $permission->name }}
                                                </span>
                                            @empty
                                                @if($role->name == 'admin')
                                                    <span class="label label-success">
                                                        {{ __('permissions.rbac.all_permissions') }}
                                                    </span>
                                                @elseif($role->name == 'global-read')
                                                    <span class="label label-primary">
                                                        {{ __('permissions.rbac.read_permissions') }}
                                                    </span>
                                                @else
                                                    <span class="tw:text-slate-400 tw:dark:text-dark-white-400 tw:italic">
                                                        {{ __('permissions.rbac.no_permissions') }}
                                                    </span>
                                                @endif
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="tw:text-center tw:align-middle">
                                        @if(in_array($role->name, $protected))
                                            <i class="fas fa-lock tw:text-slate-300 tw:dark:text-dark-gray-100" title="Protected role"></i>
                                        @else
                                            <div class="tw:flex tw:justify-center tw:gap-1">
                                                <a href="{{ route('roles.edit', $role->id) }}"
                                                   title="{{ __('permissions.rbac.edit_role') }}"
                                                   class="btn btn-xs btn-warning">
                                                    <i class="fas fa-pencil"></i>
                                                </a>
                                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                      onsubmit="return confirm('{{ __('permissions.rbac.confirm_delete') }}');"
                                                      class="tw:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            title="{{ __('permissions.rbac.delete_role') }}"
                                                            class="btn btn-xs btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="tw:flex tw:flex-col tw:items-center tw:justify-center tw:py-12 tw:text-center">
                    <div class="tw:w-16 tw:h-16 tw:rounded-2xl tw:bg-slate-100 tw:dark:bg-dark-gray-400 tw:flex tw:items-center tw:justify-center tw:mb-4 tw:border tw:border-slate-200 tw:dark:border-dark-gray-200">
                        <i class="fas fa-shield-halved tw:text-slate-300 tw:dark:text-dark-gray-100 tw:text-2xl"></i>
                    </div>
                    <h3 class="tw:text-slate-700 tw:dark:text-dark-white-200 tw:font-semibold tw:text-lg tw:mb-1">No roles yet</h3>
                    <p class="tw:text-slate-400 tw:dark:text-dark-white-400 tw:text-sm tw:mb-6">Create your first role to start managing access control.</p>
                    <a href="{{ route('roles.create') }}"
                       class="btn btn-primary">
                        <i class="fas fa-plus tw:mr-1"></i> {{ __('permissions.rbac.add_role') }}
                    </a>
                </div>
            @endif
        </x-panel>
    </div>
@endsection
