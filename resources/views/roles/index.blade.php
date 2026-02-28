@extends('layouts.librenmsv1')

@section('title', __('permissions.rbac.title'))

@section('content')
<div class="container tw:py-4">
    <div class="tw:flex tw:justify-between tw:items-center tw:mb-6">
        <h1 class="tw:text-3xl tw:font-bold">{{ __('permissions.rbac.title') }}</h1>
        <div class="tw:flex tw:gap-4">
            @can('viewAny', \App\Models\User::class)
                <a href="{{ route('users.index') }}" class="tw:bg-gray-600 tw:hover:bg-gray-700 tw:text-white tw:font-bold tw:py-2 tw:px-6 tw:rounded tw:shadow tw:hover:shadow-lg tw:transition tw:duration-200 tw:text-lg">
                    <i class="fas fa-users tw:mr-2"></i>{{ __('permissions.rbac.manage_users') }}
                </a>
            @endcan
            <a href="{{ route('roles.create') }}" class="tw:bg-blue-600 tw:hover:bg-blue-700 tw:text-white tw:font-bold tw:py-2 tw:px-6 tw:rounded tw:shadow tw:hover:shadow-lg tw:transition tw:duration-200 tw:text-lg">
                <i class="fas fa-plus tw:mr-2"></i>{{ __('permissions.rbac.add_role') }}
            </a>
        </div>
    </div>

    <div class="tw:bg-white tw:dark:bg-gray-800 tw:shadow-md tw:rounded-lg tw:overflow-hidden">
        <table class="tw:min-w-full tw:leading-normal">
            <thead>
                <tr>
                    <th class="tw:px-5 tw:py-3 tw:border-b-2 tw:border-gray-200 tw:dark:border-gray-700 tw:bg-gray-100 tw:dark:bg-gray-700 tw:text-left tw:font-semibold tw:text-gray-600 tw:dark:text-gray-300 tw:tracking-wider">
                        {{ __('permissions.rbac.role_name') }}
                    </th>
                    <th class="tw:px-5 tw:py-3 tw:border-b-2 tw:border-gray-200 tw:dark:border-gray-700 tw:bg-gray-100 tw:dark:bg-gray-700 tw:text-left tw:font-semibold tw:text-gray-600 tw:dark:text-gray-300 tw:tracking-wider">
                        {{ __('permissions.rbac.permissions') }}
                    </th>
                    <th class="tw:px-5 tw:py-3 tw:border-b-2 tw:border-gray-200 tw:dark:border-gray-700 tw:bg-gray-100 tw:dark:bg-gray-700 tw:text-right tw:font-semibold tw:text-gray-600 tw:dark:text-gray-300 tw:tracking-wider">
                        {{ __('permissions.rbac.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="tw:text-xl">
                @foreach($roles as $role)
                <tr class="tw:hover:bg-gray-50 tw:dark:hover:bg-gray-700 tw:transition tw:duration-150">
                    <td class="tw:px-5 tw:py-5 tw:border-b tw:border-gray-200 tw:dark:border-gray-700">
                        <p class="tw:text-gray-900 tw:dark:text-gray-100 tw:whitespace-no-wrap tw:font-bold">
                            {{ $role->name }}
                        </p>
                    </td>
                    <td class="tw:px-5 tw:py-5 tw:border-b tw:border-gray-200 tw:dark:border-gray-700">
                        <div class="tw:flex tw:flex-wrap tw:gap-2">
                            @forelse($role->permissions as $permission)
                                <span class="tw:bg-gray-200 tw:dark:bg-gray-600 tw:text-gray-800 tw:dark:text-gray-200 tw:px-3 tw:py-1 tw:rounded tw:text-base tw:font-medium">
                                    {{ $permission->name }}
                                </span>
                            @empty
                                <span class="tw:text-gray-500 tw:italic tw:text-base">{{ __('permissions.rbac.no_permissions') }}</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="tw:px-5 tw:py-5 tw:border-b tw:border-gray-200 tw:dark:border-gray-700 tw:text-lg tw:text-right">
                        <div class="tw:flex tw:justify-end tw:items-center tw:space-x-4">
                            <a href="{{ route('roles.edit', $role->id) }}" class="tw:text-blue-600 tw:hover:text-blue-800 tw:dark:text-blue-400 tw:dark:hover:text-blue-300 tw:transition tw:duration-200" title="{{ __('permissions.rbac.edit_role') }}">
                                <i class="fas fa-xl fa-edit"></i>
                            </a>
                            @if($role->name !== 'Admin' && $role->name !== 'admin')
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('{{ __('permissions.rbac.confirm_delete') }}');" class="tw:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tw:text-red-600 tw:hover:text-red-800 tw:dark:text-red-400 tw:dark:hover:text-red-300 tw:transition tw:duration-200" title="{{ __('permissions.rbac.delete_role') }}">
                                        <i class="fas fa-xl fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
