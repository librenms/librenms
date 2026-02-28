@extends('layouts.librenmsv1')

@section('title', __('permissions.rbac.edit_role'))

@section('content')
<div class="container tw:py-4" x-data="{
    permissions: {{ json_encode($role->permissions->pluck('name')) }},
    search: '',
    isPermMatch(permLabel, permDesc, groupName) {
        if (!this.search) return true;
        const s = this.search.toLowerCase();
        return permLabel.toLowerCase().includes(s) ||
               permDesc.toLowerCase().includes(s) ||
               groupName.toLowerCase().includes(s);
    },
    groupHasMatch(groupName, perms) {
        if (!this.search) return true;
        return perms.some(p => {
            const label = p.label.toLowerCase();
            const desc = p.description.toLowerCase();
            const s = this.search.toLowerCase();
            return label.includes(s) || desc.includes(s) || groupName.toLowerCase().includes(s);
        });
    }
}">
    <div class="tw:max-w-4xl tw:mx-auto">
        <div class="tw:flex tw:items-center tw:justify-between tw:mb-6">
            <div class="tw:flex tw:items-center">
                <a href="{{ route('roles.index') }}" class="tw:mr-4 tw:text-gray-600 tw:hover:text-gray-900 tw:dark:text-gray-400 tw:dark:hover:text-gray-200">
                    <i class="fas fa-xl fa-arrow-left"></i>
                </a>
                <h1 class="tw:text-3xl tw:font-bold">{{ __('permissions.rbac.edit_role') }}: {{ $role->name }}</h1>
            </div>
            <button type="submit" form="role-form" class="tw:bg-blue-600 tw:hover:bg-blue-700 tw:text-white tw:font-bold tw:py-2 tw:px-6 tw:rounded tw:shadow tw:hover:shadow-lg tw:transition tw:duration-200 tw:flex tw:items-center tw:text-lg">
                <i class="fas fa-save tw:mr-2"></i>{{ __('permissions.rbac.update_role') }}
            </button>
        </div>

        <div class="tw:bg-white tw:dark:bg-gray-800 tw:shadow-lg tw:rounded-xl tw:overflow-hidden tw:border tw:border-gray-100 tw:dark:border-gray-700">
            <form action="{{ route('roles.update', $role->id) }}" method="POST" id="role-form" class="tw:p-8">
                @csrf
                @method('PUT')

                @include('roles.form-fields')
            </form>
        </div>
    </div>
</div>
@endsection
