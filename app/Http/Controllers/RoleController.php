<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Symfony\Component\Yaml\Yaml;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        return view('roles.index', [
            'roles' => Role::with('permissions')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('roles.create', $this->getPermissionData());
    }

    public function store(StoreRoleRequest $request, ToastInterface $toast): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        $toast->success(__('permissions.rbac.created', ['name' => $role->name]));

        return redirect()->route('roles.index');
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('roles.edit', array_merge(
            ['role' => $role],
            $this->getPermissionData()
        ));
    }

    public function update(UpdateRoleRequest $request, Role $role, ToastInterface $toast): RedirectResponse
    {
        $validated = $request->validated();

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        $toast->success(__('permissions.rbac.updated', ['name' => $role->name]));

        return redirect()->route('roles.index');
    }

    public function destroy(Role $role, ToastInterface $toast): RedirectResponse
    {
        $this->authorize('delete', $role);

        $role->delete();

        $toast->success(__('permissions.rbac.deleted', ['name' => $role->name]));

        return redirect()->route('roles.index');
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
