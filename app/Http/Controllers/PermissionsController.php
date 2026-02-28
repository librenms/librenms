<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Symfony\Component\Yaml\Yaml;

class PermissionsController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        return view('permissions.index', [
            'roles' => Role::with('permissions')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('permissions.create', $this->getPermissionData());
    }

    public function store(Request $request, ToastInterface $toast): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => 'required|unique:roles,name|regex:/^[a-z-]+$/',
            'permissions' => 'array',
        ], [
            'name.regex' => __('Role names can only contain lowercase letters and hyphens (-).'),
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        $toast->success(__('Role :name created successfully', ['name' => $role->name]));

        return redirect()->route('permissions.index');
    }

    public function edit(Role $permission): View
    {
        $role = $permission;
        $this->authorize('update', $role);

        return view('permissions.edit', array_merge(
            ['role' => $role],
            $this->getPermissionData()
        ));
    }

    public function update(Request $request, Role $permission, ToastInterface $toast): RedirectResponse
    {
        $role = $permission;
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id . '|regex:/^[a-z-]+$/',
            'permissions' => 'array',
        ], [
            'name.regex' => __('Role names can only contain lowercase letters and hyphens (-).'),
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        $toast->success(__('Role :name updated successfully', ['name' => $role->name]));

        return redirect()->route('permissions.index');
    }

    public function destroy(Role $permission, ToastInterface $toast): RedirectResponse
    {
        $role = $permission;
        $this->authorize('delete', $role);

        $role->delete();

        $toast->success(__('Role :name deleted successfully', ['name' => $role->name]));

        return redirect()->route('permissions.index');
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
