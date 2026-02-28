@extends('layouts.librenmsv1')

@section('title', __('Permissions'))

@section('content')
<div class="container tw:py-4">
    <div class="tw:flex tw:justify-between tw:items-center tw:mb-6">
        <h1 class="tw:text-3xl tw:font-bold">{{ __('Roles & Permissions') }}</h1>
        <a href="{{ route('permissions.create') }}" class="tw:bg-blue-600 tw:hover:bg-blue-700 tw:text-white tw:font-bold tw:py-2 tw:px-6 tw:rounded tw:shadow tw:hover:shadow-lg tw:transition tw:duration-200 tw:text-lg">
            <i class="fas fa-plus tw:mr-2"></i>{{ __('Add Role') }}
        </a>
    </div>

    <div class="tw:bg-white tw:dark:bg-gray-800 tw:shadow-md tw:rounded-lg tw:overflow-hidden">
        <table class="tw:min-w-full tw:leading-normal">
            <thead>
                <tr>
                    <th class="tw:px-5 tw:py-3 tw:border-b-2 tw:border-gray-200 tw:dark:border-gray-700 tw:bg-gray-100 tw:dark:bg-gray-700 tw:text-left tw:text-sm tw:font-semibold tw:text-gray-600 tw:dark:text-gray-300 tw:uppercase tw:tracking-wider">
                        {{ __('Role Name') }}
                    </th>
                    <th class="tw:px-5 tw:py-3 tw:border-b-2 tw:border-gray-200 tw:dark:border-gray-700 tw:bg-gray-100 tw:dark:bg-gray-700 tw:text-left tw:text-sm tw:font-semibold tw:text-gray-600 tw:dark:text-gray-300 tw:uppercase tw:tracking-wider">
                        {{ __('Permissions') }}
                    </th>
                    <th class="tw:px-5 tw:py-3 tw:border-b-2 tw:border-gray-200 tw:dark:border-gray-700 tw:bg-gray-100 tw:dark:bg-gray-700 tw:text-right tw:text-sm tw:font-semibold tw:text-gray-600 tw:dark:text-gray-300 tw:uppercase tw:tracking-wider">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                <tr class="tw:hover:bg-gray-50 tw:dark:hover:bg-gray-700 tw:transition tw:duration-150">
                    <td class="tw:px-5 tw:py-5 tw:border-b tw:border-gray-200 tw:dark:border-gray-700 tw:text-lg">
                        <p class="tw:text-gray-900 tw:dark:text-gray-100 tw:whitespace-no-wrap tw:font-bold">
                            {{ $role->name }}
                        </p>
                    </td>
                    <td class="tw:px-5 tw:py-5 tw:border-b tw:border-gray-200 tw:dark:border-gray-700 tw:text-lg">
                        <div class="tw:flex tw:flex-wrap tw:gap-2">
                            @forelse($role->permissions as $permission)
                                <span class="tw:bg-gray-200 tw:dark:bg-gray-600 tw:text-gray-800 tw:dark:text-gray-200 tw:px-3 tw:py-1 tw:rounded tw:text-base tw:font-medium">
                                    {{ $permission->name }}
                                </span>
                            @empty
                                <span class="tw:text-gray-500 tw:italic tw:text-base">{{ __('No permissions assigned') }}</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="tw:px-5 tw:py-5 tw:border-b tw:border-gray-200 tw:dark:border-gray-700 tw:text-lg tw:text-right">
                        <div class="tw:flex tw:justify-end tw:items-center tw:space-x-4">
                            <a href="{{ route('permissions.edit', $role->id) }}" class="tw:text-blue-600 tw:hover:text-blue-800 tw:dark:text-blue-400 tw:dark:hover:text-blue-300 tw:transition tw:duration-200" title="{{ __('Edit Role') }}">
                                <i class="fas fa-edit tw:text-xl"></i>
                            </a>
                            @if($role->name !== 'Admin' && $role->name !== 'admin')
                                <form action="{{ route('permissions.destroy', $role->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this role?') }}');" class="tw:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tw:text-red-600 tw:hover:text-red-800 tw:dark:text-red-400 tw:dark:hover:text-red-300 tw:transition tw:duration-200" title="{{ __('Delete Role') }}">
                                        <i class="fas fa-trash tw:text-xl"></i>
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
