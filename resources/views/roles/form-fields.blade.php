{{-- Role Name --}}
<div class="tw:px-4 tw:mb-6 tw:flex tw:flex-col tw:sm:flex-row tw:sm:items-center tw:gap-4">
    <div for="name" class="tw:font-bold tw:text-xl">
        {{ __('permissions.rbac.role_name') }}
    </div>
    <div class="tw:flex-1 tw:max-w-md">
        <input type="text" name="name" id="name" required
               class="form-control @error('name') tw:border-red-500 @enderror"
               placeholder="{{ __('permissions.rbac.role_name_placeholder') }}"
               value="{{ old('name', $role->name ?? '') }}"
               @if(isset($role) && in_array(strtolower($role->name), ['admin', 'global-read', 'user'])) readonly @endif>
        @error('name')
            <p class="tw:mt-1 tw:text-xs tw:text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<hr>

{{-- Permissions --}}
<div class="tw:px-4">
    {{-- Section header --}}
    <div class="tw:flex tw:flex-col tw:md:flex-row tw:justify-between tw:mb-6 tw:gap-4 tw:w-full">
        <div class="tw:flex tw:flex-col tw:sm:flex-row tw:sm:items-center tw:gap-4 tw:md:w-1/4">
            <h4 class="tw:font-bold tw:m-0 tw:text-xl">
                {{ __('permissions.rbac.permissions') }}
            </h4>
        </div>
        <div class="tw:flex tw:flex-col tw:sm:flex-row tw:justify-end tw:gap-4 tw:flex-1">
            {{-- Search --}}
            <div class="tw:relative tw:w-full tw:max-w-md">
                <span class="tw:absolute tw:inset-y-0 tw:left-0 tw:pl-3 tw:flex tw:items-center tw:text-slate-400 tw:pointer-events-none">
                    <i class="fas fa-search tw:text-sm"></i>
                </span>
                <input type="text" x-model="search"
                       placeholder="{{ __('permissions.rbac.search_permissions') }}"
                       class="form-control input-sm tw:pl-9">
            </div>

            <div class="tw:flex tw:items-center tw:gap-2 tw:whitespace-nowrap">
                <button type="button"
                        @click="permissions = Array.from(document.querySelectorAll('input[name=\'permissions[]\']')).map(el => el.value)"
                        class="btn btn-default btn-sm">
                    <i class="fas fa-check-square tw:mr-1"></i>{{ __('permissions.rbac.select_all') }}
                </button>
                <button type="button"
                        @click="permissions = []"
                        class="btn btn-default btn-sm">
                    <i class="fas fa-square tw:mr-1"></i>{{ __('permissions.rbac.clear_all') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Permission groups grid --}}
    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4">
        @foreach($groups as $group => $perms)
            @php
    $groupPerms = array_map(function ($perm) use ($labels, $group) {
        $permName = explode('.', $perm)[1] ?? $perm;
        return [
            'name' => $perm,
            'label' => $labels[$group][$permName]['label'] ?? ($labels[$group]['label'] ?? $perm),
            'description' => $labels[$group][$permName]['description'] ?? ($labels[$group]['description'] ?? '')
        ];
    }, $perms);
            @endphp
            <div class="panel panel-default"
                 x-show="groupHasMatch('{{ $group }}', {{ json_encode($groupPerms) }})">

                {{-- Group header --}}
                <div class="panel-heading">
                    <h5 class="panel-title tw:font-bold tw:uppercase tw:tracking-wider">
                        {{ $labels[$group]['title'] ?? $group }}
                    </h5>
                </div>

                {{-- Permissions list --}}
                <div class="panel-body tw:space-y-3">
                    @foreach($groupPerms as $p)
                        <div class="checkbox tw:m-0"
                             x-show="isPermMatch('{{ $p['label'] }}', '{{ $p['description'] }}', '{{ $group }}')">
                            <label class="tw:flex tw:items-start tw:gap-3 tw:cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $p['name'] }}" id="perm-{{ $p['name'] }}"
                                       x-model="permissions"
                                       class="tw:mt-1 tw:w-4 tw:h-4">
                                <div class="tw:flex-1">
                                    <div class="tw:font-bold">
                                        {{ $p['label'] }}
                                    </div>
                                    @if($p['description'])
                                        <div class="tw:text-base tw:text-slate-500 tw:dark:text-dark-white-400">
                                            {{ $p['description'] }}
                                        </div>
                                    @endif
                                </div>
                            </label>
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
