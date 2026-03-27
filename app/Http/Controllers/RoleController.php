<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\Yaml\Yaml;

class RoleController
{
    public function index(): View
    {
        Gate::authorize('viewAny', Role::class);

        return view('roles.index', [
            'roles' => Role::with('permissions')->orderBy('name')->get(),
            'protected' => ['admin', 'global-read'],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Role::class);

        return view('roles.create', $this->getPermissionData());
    }

    public function store(StoreRoleRequest $request, ToastInterface $toast): RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $validated = $request->validated();

        $role = Role::create(['name' => $validated['name']]);
        $this->ensurePermissionsExist($validated['permissions'] ?? []);
        $role->syncPermissions($validated['permissions'] ?? []);

        $toast->success(__('permissions.rbac.created', ['name' => $role->name]));

        return redirect()->route('roles.index');
    }

    public function edit(Role $role): View
    {
        Gate::authorize('update', $role);

        return view('roles.edit', array_merge(
            ['role' => $role],
            $this->getPermissionData()
        ));
    }

    public function update(UpdateRoleRequest $request, Role $role, ToastInterface $toast): RedirectResponse
    {
        Gate::authorize('update', Role::class);

        $validated = $request->validated();
        $permissions = $validated['permissions'] ?? [];

        $role->update(['name' => $validated['name']]);
        $this->ensurePermissionsExist($permissions);
        $role->syncPermissions($permissions);

        $toast->success(__('permissions.rbac.updated', ['name' => $role->name]));

        return redirect()->route('roles.index');
    }

    public function destroy(Role $role, ToastInterface $toast): RedirectResponse
    {
        Gate::authorize('delete', $role);

        $role->delete();

        $toast->success(__('permissions.rbac.deleted', ['name' => $role->name]));

        return redirect()->route('roles.index');
    }

    private function ensurePermissionsExist(array $permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    private function getPermissionData(): array
    {
        $permissionsFile = resource_path('definitions/permissions.yaml');
        $definitions = Yaml::parseFile($permissionsFile);
        $groups = $definitions['groups'] ?? [];

        return [
            'groups' => $groups,
            'labels' => __('permissions'),
        ];
    }
}
