{{-- Role Name --}}
<div class="tw:mb-8">
    <label for="name" class="tw:block tw:text-2xl tw:font-semibold tw:tracking-wider tw:text-slate-500 tw:dark:text-dark-white-400 tw:mb-2">
        {{ __('permissions.rbac.role_name') }}
    </label>
    <input type="text" name="name" id="name" required
           class="tw:w-full tw:px-4 tw:py-2.5 tw:rounded-lg tw:border tw:border-slate-200 tw:dark:border-dark-gray-100 tw:bg-white tw:dark:bg-dark-gray-500 tw:text-slate-800 tw:dark:text-dark-white-100 tw:placeholder-slate-400 tw:dark:placeholder-dark-white-400 tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-indigo-500 tw:dark:focus:ring-dark-gray-100 tw:focus:border-transparent tw:transition tw:duration-150 tw:text-lg"
           placeholder="{{ __('permissions.rbac.role_name_placeholder') }}"
           value="{{ old('name', $role->name ?? '') }}"
           @if(isset($role) && in_array(strtolower($role->name), ['admin', 'global-read'])) readonly @endif>
    @error('name')
    <p class="tw:text-red-500 tw:dark:text-red-400 tw:text-sm tw:mt-1.5">{{ $message }}</p>
    @enderror
</div>

{{-- Permissions --}}
<div>
    {{-- Section header --}}
    <div class="tw:flex tw:items-center tw:justify-between tw:mb-5 tw:pb-3 tw:border-b tw:border-slate-100 tw:dark:border-dark-gray-200">
        <div class="tw:flex tw:items-center tw:gap-4">
            <span class="tw:text-xl tw:font-bold tw:text-slate-800 tw:dark:text-dark-white-100">
                {{ __('permissions.rbac.permissions') }}
            </span>
            {{-- Search --}}
            <div class="tw:relative">
                <span class="tw:absolute tw:inset-y-0 tw:left-0 tw:pl-3 tw:flex tw:items-center tw:text-slate-400 tw:dark:text-dark-white-400 tw:pointer-events-none">
                    <i class="fas fa-search tw:text-xs"></i>
                </span>
                <input type="text" x-model="search"
                       placeholder="{{ __('permissions.rbac.search_permissions') }}"
                       class="tw:pl-8 tw:pr-4 tw:py-1.5 tw:border tw:border-slate-200 tw:dark:border-dark-gray-100 tw:rounded-lg tw:bg-white tw:dark:bg-dark-gray-500 tw:text-slate-800 tw:dark:text-dark-white-200 tw:placeholder-slate-400 tw:dark:placeholder-dark-white-400 tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-indigo-500 tw:dark:focus:ring-dark-gray-100 tw:focus:border-transparent tw:text-lg tw:transition tw:duration-150">
            </div>
        </div>
        <div class="tw:flex tw:items-center tw:gap-3 tw:text-sm">
            <button type="button"
                    @click="permissions = Array.from(document.querySelectorAll('input[name=\'permissions[]\']')).map(el => el.value)"
                    class="tw:inline-flex tw:items-center tw:text-lg tw:gap-1.5 tw:text-indigo-600 tw:dark:text-dark-white-300 tw:hover:text-indigo-800 tw:dark:hover:text-dark-white-100 tw:font-semibold tw:transition-colors tw:duration-150">
                <i class="fas fa-check-square"></i>{{ __('permissions.rbac.select_all') }}
            </button>
            <span class="tw:text-slate-300 tw:dark:text-dark-gray-100">|</span>
            <button type="button"
                    @click="permissions = []"
                    class="tw:inline-flex tw:items-center tw:text-lg tw:gap-1.5 tw:text-slate-500 tw:dark:text-dark-white-400 tw:hover:text-slate-700 tw:dark:hover:text-dark-white-200 tw:font-semibold tw:transition-colors tw:duration-150">
                <i class="fas fa-square"></i>{{ __('permissions.rbac.clear_all') }}
            </button>
        </div>
    </div>

    {{-- Permission groups grid --}}
    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4">
        @foreach($groups as $group => $perms)
            @php
                $groupPerms = array_map(function($perm) use ($labels, $group) {
                    $permName = explode('.', $perm)[1] ?? $perm;
                    return [
                        'name' => $perm,
                        'label' => $labels[$group][$permName]['label'] ?? ($labels[$group]['label'] ?? $perm),
                        'description' => $labels[$group][$permName]['description'] ?? ($labels[$group]['description'] ?? '')
                    ];
                }, $perms);
            @endphp
            <div class="tw:rounded-xl tw:border tw:border-slate-200 tw:dark:border-dark-gray-200 tw:bg-slate-50 tw:dark:bg-dark-gray-500 tw:overflow-hidden"
                 x-show="groupHasMatch('{{ $group }}', {{ json_encode($groupPerms) }})">

                {{-- Group header --}}
                <div class="tw:px-5 tw:py-3 tw:border-b tw:border-slate-200 tw:dark:border-dark-gray-200 tw:bg-white tw:dark:bg-dark-gray-300">
                <span class="tw:text-lg tw:font-bold tw:uppercase tw:tracking-widest tw:text-slate-500 tw:dark:text-dark-white-400">
                    {{ $labels[$group]['title'] ?? $group }}
                </span>
                </div>

                {{-- Permissions list --}}
                <div class="tw:px-5 tw:py-4 tw:space-y-3">
                    @foreach($groupPerms as $p)
                        <div class="tw:flex tw:items-start tw:gap-3"
                             x-show="isPermMatch('{{ $p['label'] }}', '{{ $p['description'] }}', '{{ $group }}')">
                            <div class="tw:flex tw:items-center tw:h-5">
                                <input type="checkbox" name="permissions[]" value="{{ $p['name'] }}" id="perm-{{ $p['name'] }}"
                                       x-model="permissions"
                                       class="tw:h-6 tw:w-6 tw:text-indigo-600 tw:focus:ring-indigo-500 tw:border-slate-300 tw:dark:border-dark-gray-100 tw:rounded tw:cursor-pointer tw:bg-white tw:dark:bg-dark-gray-300 tw:transition tw:duration-150">
                            </div>
                            <div class="tw:ms-2 tw:text-sm tw:select-none">
                                <label for="perm-{{ $p['name'] }}" class="tw:block tw:text-xl tw:font-semibold tw:text-slate-800 tw:dark:text-white tw:cursor-pointer tw:leading-7">
                                    {{ $p['label'] }}
                                </label>
                                @if($p['description'])
                                    <p class="tw:text-lg tw:text-slate-500 tw:dark:text-dark-white-400 tw:mt-0.5 tw:leading-1">
                                        {{ $p['description'] }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

@section('javascript')
    <script>
        function roleForm(initialPermissions = []) {
            return {
                permissions: initialPermissions,
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
            };
        }
    </script>
@endsection
