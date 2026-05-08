<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use App\Models\BillPerm;
use App\Models\Device;
use App\Models\DevicePerm;
use App\Models\Port;
use App\Models\PortPerm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Permissions;

class UserPermissionsController extends Controller
{
    public function edit(Request $request, User $user): View
    {
        $this->authorize('manage', $user);
        $this->validate($request, ['tab' => Rule::in(['device', 'device-group', 'port', 'bill'])]);
        $tab = $request->string('tab', 'device');

        $deviceViewAll = Gate::forUser($user)->allows('device.viewAll');
        $portViewAll = Gate::forUser($user)->allows('port.viewAll');
        $billViewAll = Gate::forUser($user)->allows('bill.viewAll');

        $devices = $deviceViewAll ? new Collection : Device::hasAccess($user)->get();
        $groups = $deviceViewAll ? new Collection : $user->deviceGroups;
        $ports = $portViewAll ? new Collection : $user->portsOwned->load('device');
        $bills = $billViewAll ? new Collection : $user->bills;

        $allString = __('all');

        return view('user.permissions', [
            'user' => $user,
            'tab' => $tab,
            'allowDynamic' => (bool) LibrenmsConfig::get('permission.device_group.allow_dynamic'),
            'devicePermissions' => $devices,
            'deviceGroupPermissions' => $groups,
            'portPermissions' => $ports,
            'billPermissions' => $bills,
            'deviceCount' => $deviceViewAll ? $allString : $devices->count(),
            'deviceGroupCount' => $deviceViewAll ? $allString : $groups->count(),
            'portCount' => $portViewAll ? $allString : $ports->count(),
            'billCount' => $billViewAll ? $allString : $bills->count(),
        ]);
    }

    public function attachDevice(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $validated = $request->validate([
            'device_id' => ['required', 'integer', 'exists:devices,device_id'],
        ]);

        if (! Device::where('device_id', $validated['device_id'])->hasAccess($user)->exists()) {
            DevicePerm::query()->insert([
                'device_id' => $validated['device_id'],
                'user_id' => $user->user_id,
            ]);

            Permissions::invalidateCache();
        }

        return redirect()->route('users.permissions.edit', [$user, 'tab' => 'device']);
    }

    public function detachDevice(User $user, int $device): RedirectResponse
    {
        $this->authorize('manage', $user);

        DevicePerm::query()->where('device_id', $device)->where('user_id', $user->user_id)->delete();
        Permissions::invalidateCache();

        return redirect()->route('users.permissions.edit', [$user, 'tab' => 'device']);
    }

    public function attachDeviceGroup(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $validated = $request->validate([
            'device_group_id' => ['required', 'integer', 'exists:device_groups,id'],
        ]);

        $user->deviceGroups()->syncWithoutDetaching($validated['device_group_id']);
        Permissions::invalidateCache();

        return redirect()->route('users.permissions.edit', [$user, 'tab' => 'device-group']);
    }

    public function detachDeviceGroup(User $user, int $deviceGroup): RedirectResponse
    {
        $this->authorize('manage', $user);

        $user->deviceGroups()->detach($deviceGroup);
        Permissions::invalidateCache();

        return redirect()->route('users.permissions.edit', [$user, 'tab' => 'device-group']);
    }

    public function attachPort(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $validated = $request->validate([
            'port_id' => ['required', 'integer', 'exists:ports,port_id'],
        ]);

        if (! Port::where('port_id', $validated['port_id'])->hasAccess($user)->exists()) {
            PortPerm::query()->insert([
                'port_id' => $validated['port_id'],
                'user_id' => $user->user_id,
            ]);

            Permissions::invalidateCache();
        }

        return redirect()->route('users.permissions.edit', [$user, 'tab' => 'port']);
    }

    public function detachPort(User $user, int $port): RedirectResponse
    {
        $this->authorize('manage', $user);

        PortPerm::query()->where('port_id', $port)->where('user_id', $user->user_id)->delete();
        Permissions::invalidateCache();

        return redirect()->route('users.permissions.edit', [$user, 'tab' => 'port']);
    }

    public function attachBill(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $validated = $request->validate([
            'bill_id' => ['required', 'integer', 'exists:bills,bill_id'],
        ]);

        if (! Bill::where('bill_id', $validated['bill_id'])->hasAccess($user)->exists()) {
            BillPerm::query()->insert([
                'bill_id' => $validated['bill_id'],
                'user_id' => $user->user_id,
            ]);

            Permissions::invalidateCache();
        }

        return redirect()->route('users.permissions.edit', [$user, 'tab' => 'bill']);
    }

    public function detachBill(User $user, int $bill): RedirectResponse
    {
        $this->authorize('manage', $user);

        BillPerm::query()->where('bill_id', $bill)->where('user_id', $user->user_id)->delete();
        Permissions::invalidateCache();

        return redirect()->route('users.permissions.edit', [$user, 'tab' => 'bill']);
    }
}
