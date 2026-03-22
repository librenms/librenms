<?php

namespace App\Policies;

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
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the bill.
     */
    public function view(User $user, Bill|int $bill): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $bill_id = $bill['id'] ?? $bill->id;

        return $this->hasGlobalPermission($user, 'view')
            || Permissions::canAccessBill($bill_id, $user);
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
    public function update(User $user, Bill|array $bill): bool
    {
        if ($this->hasGlobalPermission($user, 'update') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $bill_id = $bill->id ?? $bill['id'];

        return $this->hasGlobalPermission($user, 'update') &&
            Permissions::canAccessBill($bill_id, $user);
    }

    /**
     * Determine whether the user can delete the bill.
     */
    public function delete(User $user, Bill|array $bill): bool
    {
        if ($this->hasGlobalPermission($user, 'delete') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $bill_id = $bill->id ?? $bill['id'];

        return $this->hasGlobalPermission($user, 'delete') &&
            Permissions::canAccessBill($bill_id, $user);
    }
}
