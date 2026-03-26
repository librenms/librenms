<?php

namespace App\Policies;

use App\Facades\LibrenmsConfig;
use App\Facades\Permissions;
use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any bill.
     */
    public function viewAny(User $user): bool
    {
        if (! LibrenmsConfig::get('enable_billing')) {
            return false;
        }

        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view all models.
     */
    public function viewAll(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll');
    }

    /**
     * Determine whether the user can view the bill.
     */
    public function view(User $user, Bill $bill): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view')
            || Permissions::canAccessBill($bill, $user);
    }

    /**
     * Determine whether the user can create bills.
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the bill.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the bill.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
