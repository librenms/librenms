@extends('layouts.librenmsv1')

@section('title', __('Create Role'))

@section('content')
<div class="tw:container-fluid tw:py-4" x-data="{
    permissions: [],
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
        <div class="tw:flex tw:items-center tw:mb-6">
            <a href="{{ route('permissions.index') }}" class="tw:mr-4 tw:text-gray-600 tw:hover:text-gray-900 tw:dark:text-gray-400 tw:dark:hover:text-gray-200">
                <i class="fas fa-arrow-left tw:text-2xl"></i>
            </a>
            <h1 class="tw:text-3xl tw:font-bold">{{ __('Create New Role') }}</h1>
        </div>

        <div class="tw:bg-white tw:dark:bg-gray-800 tw:shadow-lg tw:rounded-xl tw:overflow-hidden tw:border tw:border-gray-100 tw:dark:border-gray-700">
            <form action="{{ route('permissions.store') }}" method="POST" class="tw:p-8">
                @csrf

                <div class="tw:mb-8">
                    <label for="name" class="tw:block tw:text-base tw:font-semibold tw:text-gray-700 tw:dark:text-gray-300 tw:mb-2 tw:italic">
                        {{ __('Role Name') }}
                    </label>
                    <input type="text" name="name" id="name" required
                           class="tw:w-full tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:focus:ring-2 tw:focus:ring-blue-500 tw:focus:border-blue-500 tw:bg-white tw:dark:bg-gray-700 tw:text-gray-900 tw:dark:text-white tw:transition tw:duration-200 tw:text-base"
                           placeholder="{{ __('e.g., network-engineer') }}"
                           value="{{ old('name') }}">
                    @error('name')
                        <p class="tw:text-red-500 tw:text-sm tw:mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw:mb-8">
                    <div class="tw:flex tw:items-center tw:justify-between tw:mb-4 tw:border-b tw:pb-2 tw:border-gray-100 tw:dark:border-gray-700">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <h2 class="tw:text-2xl tw:font-bold tw:text-gray-800 tw:dark:text-gray-200">{{ __('Permissions') }}</h2>
                            <div class="tw:relative">
                                <span class="tw:absolute tw:inset-y-0 tw:left-0 tw:pl-3 tw:flex tw:items-center tw:text-gray-400">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" x-model="search" placeholder="{{ __('Search permissions...') }}"
                                       class="tw:pl-10 tw:pr-4 tw:py-2 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded-lg tw:bg-white tw:dark:bg-gray-700 tw:text-gray-900 tw:dark:text-white tw:focus:ring-2 tw:focus:ring-blue-500 tw:text-base">
                            </div>
                        </div>
                        <div class="tw:space-x-4 tw:text-base">
                            <button type="button" @click="permissions = Array.from(document.querySelectorAll('input[name=\'permissions[]\']')).map(el => el.value)" class="tw:text-blue-600 tw:hover:text-blue-700 tw:dark:text-blue-400 tw:font-semibold">
                                <i class="fas fa-check-square tw:mr-1"></i>{{ __('Select All') }}
                            </button>
                            <button type="button" @click="permissions = []" class="tw:text-gray-500 tw:dark:text-gray-400 tw:hover:text-gray-600 tw:font-semibold">
                                <i class="fas fa-square tw:mr-1"></i>{{ __('Clear All') }}
                            </button>
                        </div>
                    </div>

                    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-8">
                        @foreach($groups as $group => $perms)
                        @php
                            $groupPerms = array_map(function($perm) use ($labels, $group) {
                                return [
                                    'name' => $perm,
                                    'label' => $labels[$group][explode('.', $perm)[1]]['label'] ?? $perm,
                                    'description' => $labels[$group][explode('.', $perm)[1]]['description'] ?? ''
                                ];
                            }, $perms);
                        @endphp
                        <div class="tw:bg-gray-50 tw:dark:bg-gray-700 tw:p-6 tw:rounded-xl tw:border tw:border-gray-200 tw:dark:border-gray-600"
                             x-show="groupHasMatch('{{ $group }}', {{ json_encode($groupPerms) }})">
                            <h3 class="tw:font-bold tw:text-gray-700 tw:dark:text-gray-300 tw:mb-4 tw:border-b tw:border-gray-200 tw:dark:border-gray-600 tw:pb-2 tw:uppercase tw:text-base tw:tracking-wider">
                                {{ ucfirst($group) }}
                            </h3>
                            <div class="tw:space-y-4">
                                @foreach($groupPerms as $p)
                                <div class="tw:flex tw:items-start" x-show="isPermMatch('{{ $p['label'] }}', '{{ $p['description'] }}', '{{ $group }}')">
                                    <div class="tw:flex tw:items-center tw:h-6">
                                        <input type="checkbox" name="permissions[]" value="{{ $p['name'] }}" id="perm-{{ $p['name'] }}"
                                               x-model="permissions"
                                               class="tw:h-6 tw:w-6 tw:text-blue-600 tw:focus:ring-blue-500 tw:border-gray-300 tw:dark:border-gray-600 tw:rounded tw:cursor-pointer tw:transition tw:duration-150">
                                    </div>
                                    <div class="tw:ml-3 tw:text-lg">
                                        <label for="perm-{{ $p['name'] }}" class="tw:font-semibold tw:text-gray-800 tw:dark:text-gray-100 tw:cursor-pointer">
                                            {{ $p['label'] }}
                                        </label>
                                        <p class="tw:text-gray-600 tw:dark:text-gray-400 tw:text-base">
                                            {{ $p['description'] }}
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="tw:flex tw:justify-end tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-gray-700">
                    <button type="submit" class="tw:bg-blue-600 tw:hover:bg-blue-700 tw:text-white tw:font-bold tw:py-3 tw:px-10 tw:rounded tw:shadow tw:hover:shadow-lg tw:transition tw:duration-200 tw:flex tw:items-center tw:text-lg">
                        <i class="fas fa-save tw:mr-2"></i>{{ __('Save Role') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
